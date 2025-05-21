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
 * File   get_merchant_details.php
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

use paygw_novalnet\novalnet_helper;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * Retrieves merchant details via external API.
 *
 * This class is responsible for fetching merchant information
 * by extending the external_api base class.
 */
class get_merchant_details extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'novalnetApiKey' => new external_value(PARAM_TEXT, 'Novalnet Product Activation Key'),
            'novalnetKeyPassword' => new external_value(PARAM_TEXT, 'Novalnet Payment Access Key'),
            'id' => new external_value(PARAM_TEXT, 'Novalnet Payment Access Key'),
            'accountid' => new external_value(PARAM_TEXT, 'Novalnet Payment Access Key'),
            'gateway' => new external_value(PARAM_TEXT, 'Novalnet Payment Access Key'),
        ]);
    }

    /**
     * Executes the payment configuration process with Novalnet API.
     *
     * This method performs the necessary configuration for payment processing
     * using the provided Novalnet API credentials and related parameters. It returns
     * an array containing the result of the payment configuration process.
     *
     * @param string $novalnetapikey      The API key used to authenticate with Novalnet's services.
     * @param string $novalnetkeypassword The password associated with the Novalnet API key for secure communication.
     * @param string $id                  The account ID identifier.
     * @param string $accountid           The Novalnet account ID to be configured.
     * @param string $gatewayname         The name of the payment gateway.
     *
     * @return array Returns an array containing the success status and any relevant information or error messages.
     */
    public static function execute(string $novalnetapikey, string $novalnetkeypassword,
        string $id, string $accountid, string $gatewayname): array {
        self::validate_parameters(self::execute_parameters(), [
            'novalnetApiKey' => $novalnetapikey,
            'novalnetKeyPassword' => $novalnetkeypassword,
            'id' => $id,
            'accountid' => $accountid,
            'gateway' => $gatewayname,
        ]);

        // Validate context.
        $context = \context_system::instance();

        self::validate_context($context);

        // Check capability if needed (e.g., only admins or managers).
        require_capability('moodle/site:config', $context);

        $novalnethelper = new novalnet_helper();
        $request = [
            'merchant' => [
                'signature' => $novalnetapikey,
            ],
            'custom'   => [
                'lang' => current_language(),
            ],
        ];

        $response = $novalnethelper->send_request( json_encode($request), $novalnethelper->get_action_endpoint('merchant_details'),
            ['access_key' => $novalnetkeypassword], );

        if ($novalnethelper->is_success_status($response)) {
            if ($id) {
                $gateway = new \core_payment\account_gateway($id);
                $account = new \core_payment\account($gateway->get('accountid'));
                $accountid = $gateway->get('accountid');
            } else if ($accountid) {
                $account = new \core_payment\account($accountid);
                $gateway = $account->get_gateways()[$gatewayname] ?? null;
            }

            $selectedpayments = $gateway->get_configuration()['novalnet_selected_payments'];
            $selectedpayments = !empty($selectedpayments) ? explode(',', $selectedpayments) : [];

            foreach ($selectedpayments as $key => $payment) {
                if (! in_array($payment, $response['merchant']['payment_types'])) {
                    unset($selectedpayments[$key]);
                }
            }

            $selectedpayments = implode( ',', $selectedpayments );
            if (! empty($selectedpayments) && ! empty($accountid) && ! empty($gatewayname) &&
                $gatewayname == 'novalnet') {
                global $DB;

                $table     = 'payment_gateways';
                $condition = ['accountid' => $accountid, 'gateway' => $gatewayname];

                if ($DB->record_exists($table, $condition)) {
                    $result = $novalnethelper->fetch_records($table, $condition);
                    $config = $novalnethelper->novalnet_unserialize_data($result->config);
                    $config['novalnet_selected_payments'] = $selectedpayments;
                    $result->config = $novalnethelper->novalnet_serialize_data($config);
                    $result = $novalnethelper->insert_update_records($table, $result, $condition);
                }
            }
        }

        return [
            'response' => json_encode($response),
        ];
    }

    /**
     * Returns the configuration information.
     *
     * This function defines the structure of the return type for the execute function.
     * It returns an external_single_structure that contains the configuration data,
     * including a 'response' parameter, which is the encoded merchant update array.
     *
     * @return external_single_structure A structure containing the 'response' parameter.
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'response' => new external_value(PARAM_RAW, 'Encoded merchant update Array'),
        ]);
    }
}
