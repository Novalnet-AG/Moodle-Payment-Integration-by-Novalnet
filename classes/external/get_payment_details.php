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
 * File   get_payment_details.php
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
 */

declare(strict_types=1);

namespace paygw_novalnet\external;

use external_api;
use external_function_parameters;
use external_value;
use core_payment\helper;
use paygw_novalnet\novalnet_helper;
use paygw_novalnet\novalnet_validation;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * This class contains a list of webservice functions related to the Novalnet payment gateway.
 *
 * @package     paygw_novalnet
 *
 * @package    paygw_novalnet
 * @copyright  2025 Novalnet <technic@novalnet.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_payment_details extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Component'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'An identifier for payment area in the component'),
        ]);
    }

    /**
     * Returns the payment values required by the Novalnet JavaScript SDK.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @return string[]
     */
    public static function execute(string $component, string $paymentarea, int $itemid): array {
        global $OUTPUT;

        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
        ]);

        $config             = helper::get_gateway_configuration($component, $paymentarea, $itemid, 'novalnet');
        $payable            = helper::get_payable($component, $paymentarea, $itemid);
        $currency           = $payable->get_currency();
        $surcharge          = helper::get_gateway_surcharge('novalnet');
        $amount             = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
        $novalnetvalidation = new novalnet_validation();
        $novalnethelper     = new novalnet_helper();
        $paymentmethods     = $novalnetvalidation->display_valid_payments($config, ['currency' => $currency, 'amount' => $amount]);
        $paymentmethods     = $novalnethelper->sort_novalnet_payments($paymentmethods);

        $result             = [];
        foreach ($paymentmethods as $method) {
            $imagename = $method;
            $paymentdescription1 = null;
            $paymentdescription = $method . '_DESCRIPTION';

            if ( $novalnethelper->get_supports( 'sepa_payments', $method ) ) {
                $imagename = 'sepa';
                $paymentdescription = 'DIRECT_DEBIT_SEPA_DESCRIPTION';
            } else if ( $novalnethelper->get_supports( 'invoice_payments', $method ) ) {
                $imagename = 'invoice';
                $paymentdescription = 'INVOICE_DESCRIPTION';
            } else if ( in_array( $method, [ 'ONLINE_TRANSFER', 'IDEAL', 'EPS', 'PRZELEWY24', 'TRUSTLY', 'PAYCONIQ', 'BLIK' ] ) ) {
                $paymentdescription = 'ONLINE_TRANSFER_DESCRIPTION';
            }

            $paymentdescription = get_string( $paymentdescription, 'paygw_novalnet' );

            if ( $method == 'ONLINE_BANK_TRANSFER' ) {
                $paymentdescription  = get_string('ONLINE_BANK_TRANSFER_DESCRIPTION', 'paygw_novalnet');
                $paymentdescription1 = get_string('ONLINE_BANK_TRANSFER_DESCRIPTION_1', 'paygw_novalnet');
            }

            $result[] = (object)[
                'id' => $method,
                'description' => get_string($method, 'paygw_novalnet'),
                'images' => $OUTPUT->image_url('novalnet_payments/'.strtolower($imagename), 'paygw_novalnet')->out(),
                'payment_description' => $paymentdescription,
                'payment_description1' => ( $paymentdescription1 ) ? $paymentdescription1 : null,
            ];
        }

        return [
            'response' => $result,
        ];
    }

    /**
     * Returns the description of the method result value.
     * This function describes the structure of the response that will be returned from the external function.
     *
     * @return external_function_parameters Returns the parameters structure for the response.
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'response' => new external_value(PARAM_RAW, 'Encoded merchant update Array'),
        ]);
    }
}
