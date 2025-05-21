<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Novalnet payment plugin
 *
 * This class contains a list of webservice functions related to the Novalnet payment gateway.
 *
 * File   create_payment.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License
 * that is bundled with this package in the file freeware_license_agreement.txt
 *
 * DISCLAIMER
 *
 * If you wish to customize Novalnet payment extension for your needs, please contact technic@novalnet.de for more information.
 *
 * @package    paygw_novalnet
 * @copyright  2025 Novalnet <technic@novalnet.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

declare(strict_types=1);

namespace paygw_novalnet\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

use core_payment\helper;
use paygw_novalnet\novalnet_helper;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * This class contains a list of webservice functions related to the Novalnet payment gateway.
 *
 * @package     paygw_novalnet
 *
 * @package    paygw_novalnet
 * @copyright  2025 Novalnet <technic@novalnet.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_payment extends external_api {

    /**
     * This method handles tasks such as updating the payment status, logging transaction details,
     * and triggering any necessary follow-up actions before a successful transaction.
     *
     * @param string $component The name of the component.
     * @param string $paymentarea The specific area or scope of the payment within the component.
     * @param int $itemid The internal identifier used by the component to reference the item.
     * @param string $description A description or note related to the transaction.
     * @param string|null $paymentmethodid (Optional) The ID of the payment method
     * used for the transaction, or null if not specified.
     *
     * @return array A result array containing the status of the transaction and any additional data.
     */
    public static function execute(string $component, string $paymentarea, int $itemid,
            string $description, ?string $paymentmethodid = null): array {
        global $SESSION;

        $params = self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'description' => $description,
            'paymentmethodid' => $paymentmethodid,
        ]);

        try {
            // Typical filth: we want to be able to refer the record to the payment.
            // So we create the record, create the payment and then update the record again.
            $novalnethelper = new novalnet_helper();
            $response       = $novalnethelper->initiate_payment_process( $params );

            if ( $novalnethelper->is_success_status( $response ) ) {
                $redirecturl = $response['result']['redirect_url'];
                $SESSION->novalnet = [
                    'novalnet_txnsecret' => $response['transaction']['txn_secret'],
                    'selected_payment'   => $response['transaction']['payment_type'],
                ];
                $success = true;
            } else {
                debugging('Error occured while trying to process payment: ' . $response['result']['status_text'], DEBUG_DEVELOPER);
                $success = false;
                $message = $response['result']['status_text'];
                $redirecturl = null;
            }
        } catch (\Exception $e) {
            debugging('Exception while trying to process payment: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $success = false;
            $message = get_string('internalerror', 'paygw_novalnet') . $e->getMessage();
            $redirecturl = null;
        }

        return [
            'success' => $success,
            'message' => $message,
            'redirecturl' => $redirecturl,
        ];
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'description' => new external_value(PARAM_TEXT, 'Payment description'),
            'paymentmethodid' => new external_value(PARAM_TEXT, 'Payment method ID', VALUE_DEFAULT, null, NULL_ALLOWED),
        ]);
    }

    /**
     * Returns a payment page URL.
     *
     * This function constructs and returns the URL of the payment page, which is used for processing payments within the system.
     *
     * @return external_function_parameters The parameters for the external function, including the constructed payment URL.
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'success' => new external_value(PARAM_BOOL, 'Whether everything was successful or not.'),
            'message' => new external_value(PARAM_RAW,
                    'Message (usually the error message). Unused or not available if everything went well',
                    VALUE_OPTIONAL),
            'redirecturl' => new external_value(PARAM_RAW, 'Message (usually the error message).', VALUE_OPTIONAL),
        ]);
    }
}
