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
 * File   handle_webhook_configure.php
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
 * Class handle_webhook_configure
 *
 * Handles configuration for webhook through external API.
 */
class handle_webhook_configure extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'novalnetApiKey' => new external_value(PARAM_TEXT, 'Novalnet Product Activation Key'),
            'novalnetKeyPassword' => new external_value(PARAM_TEXT, 'Novalnet Payment Access Key'),
            'novalnetWebhookUrl' => new external_value(PARAM_TEXT, 'Novalnet Webhook Url'),
        ]);
    }

    /**
     * Configures a webhook URL in the Novalnet server
     *
     * This function is responsible for configuring the webhook URL on the Novalnet server
     * using the provided API key, key password, and the webhook URL. It ensures that the
     * necessary parameters are sent to the Novalnet server for webhook configuration.
     *
     * @param string $novalnetapikey The API key used for authentication with Novalnet's API
     * @param string $novalnetkeypassword The password associated with the API key for secure communication
     * @param string $novalnetwebhookurl The URL of the webhook that will be configured on the Novalnet server
     *
     * @return array Returns the response from the Novalnet server, typically including success status and any relevant data
     */
    public static function execute(string $novalnetapikey, string $novalnetkeypassword, string $novalnetwebhookurl): array {
        self::validate_parameters(self::execute_parameters(), [
            'novalnetApiKey' => $novalnetapikey,
            'novalnetKeyPassword' => $novalnetkeypassword,
            'novalnetWebhookUrl' => $novalnetwebhookurl,
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
            'webhook'  => [
                'url' => $novalnetwebhookurl,
            ],
            'custom'   => [
                'lang' => current_language(),
            ],
        ];

        $response = $novalnethelper->send_request( json_encode($request), $novalnethelper->get_action_endpoint('webhook_configure'),
            ['access_key' => $novalnetkeypassword] );
        if ( ! empty( $response['result']['status'] ) && 'SUCCESS' === $response['result']['status'] ) {
            $response['result']['status_text'] = get_string('novalnet_webhook_configure_success', 'paygw_novalnet');
        }

        return [
            'response' => json_encode($response),
        ];
    }

    /**
     * Returns the description of the expected result value for the function.
     *
     * This method defines the structure of the data that will be returned
     * when the function is called. In this case, it returns an encoded
     * merchant update array.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'response' => new external_value(PARAM_RAW, 'Encoded merchant update Array'),
        ]);
    }
}
