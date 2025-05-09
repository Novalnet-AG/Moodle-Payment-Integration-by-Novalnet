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
 * Contains class for Novalnet payment gateway.
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
 * @package paygw_novalnet
 * @copyright Copyright (c) Novalnet
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_novalnet;

/**
 * The gateway class for Novalnet payment gateway.
 *
 * @copyright  2019 Shamim Rezaie <shamim@moodle.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {

    /**
     * This function retrieves the list of supported currencies for the payment gateway
     * and allowing the gateway configuration to specify which
     * currencies are supported.
     *
     * @param \core_payment\form\account_gateway $form The payment gateway configuration form
     *
     * @return array An array of supported currencies
     */
    public static function get_supported_currencies(): array {
        return [
            'AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'INR', 'JPY',
            'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'USD',
        ];
    }

    /**
     * Adds configuration settings to the gateway configuration form
     *
     * This function allows the customization of a payment gateway's settings
     * by adding configuration options to the form. Use `$form->get_mform()` to
     * access the \MoodleQuickForm instance and add any necessary form elements
     * for configuring the gateway.
     *
     * @param \core_payment\form\account_gateway $form The payment gateway form object
     *
     * @return void
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        global $PAGE, $CFG;
        $mform = $form->get_mform();

        $mform->addElement('header', 'api_config_heading', get_string('api_config_heading', 'paygw_novalnet'));
        $mform->setExpanded('api_config_heading', true);

        $mform->addElement('text', 'novalnet_public_key', get_string('novalnet_public_key', 'paygw_novalnet'), 'size="60"');
        $mform->setType('novalnet_public_key', PARAM_TEXT);
        $mform->addHelpButton('novalnet_public_key', 'novalnet_public_key', 'paygw_novalnet');

        $mform->addElement('text', 'novalnet_key_password', get_string('novalnet_key_password', 'paygw_novalnet'), 'size="60"');
        $mform->setType('novalnet_key_password', PARAM_TEXT);
        $mform->addHelpButton('novalnet_key_password', 'novalnet_key_password', 'paygw_novalnet');

        $mform->addElement('text', 'novalnet_tariff_id', get_string('novalnet_tariff_id', 'paygw_novalnet'));
        $mform->setType('novalnet_tariff_id', PARAM_TEXT);
        $mform->addHelpButton('novalnet_tariff_id', 'novalnet_tariff_id', 'paygw_novalnet');

        // We add hidden fields.
        $mform->addElement('hidden', 'novalnet_selected_tariff');
        $mform->setType('novalnet_selected_tariff', PARAM_TEXT);

        $attributes = ['multiple' => 'multiple', 'size' => 10];
        $novalnetactivepayments = $mform->addElement('select', 'novalnet_active_payments',
            get_string('novalnet_active_payments', 'paygw_novalnet'), [], $attributes);
        $mform->setType('novalnet_active_payments', PARAM_TEXT);
        $mform->addHelpButton('novalnet_active_payments', 'novalnet_active_payments', 'paygw_novalnet');
        $novalnetactivepayments->setMultiple(true);

        $mform->addElement('hidden', 'novalnet_selected_payments');
        $mform->setType('novalnet_selected_payments', PARAM_TEXT);

        $novalnettestpayments = $mform->addElement('select', 'novalnet_test_mode_payments',
            get_string('novalnet_test_mode', 'paygw_novalnet'), [], $attributes);
        $mform->setType('novalnet_test_mode_payments', PARAM_TEXT);
        $mform->addHelpButton('novalnet_test_mode_payments', 'novalnet_test_mode', 'paygw_novalnet');
        $novalnettestpayments->setMultiple(true);

        $mform->addElement('hidden', 'novalnet_selected_test_payments');
        $mform->setType('novalnet_selected_test_payments', PARAM_TEXT);

        $mform->addElement('header', 'novalnet_vendor_script_heading',
            get_string('novalnet_vendor_script_heading', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_vendor_script_heading', false);

        $mform->addElement('text', 'novalnet_webhook_url', get_string('novalnet_webhook_url', 'paygw_novalnet'), 'size="60"');
        $mform->setType('novalnet_webhook_url', PARAM_TEXT);
        $mform->setDefault('novalnet_webhook_url', "{$CFG->wwwroot}/payment/gateway/novalnet/novalnet_webhook.php");
        $mform->addHelpButton('novalnet_webhook_url', 'novalnet_webhook_url', 'paygw_novalnet');
        $mform->addElement('button', 'webhook_configure', get_string('novalnet_webhook_configure', 'paygw_novalnet'));

        $mform->addElement('advcheckbox', 'novalnet_callback_test_mode',
            get_string('novalnet_callback_test_mode', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_callback_test_mode', 'novalnet_callback_test_mode', 'paygw_novalnet');

        $mform->addElement('text', 'novalnet_callback_emailtoaddr',
            get_string('novalnet_callback_emailtoaddr', 'paygw_novalnet'), 'size="60"');
        $mform->setType('novalnet_callback_emailtoaddr', PARAM_TEXT);
        $mform->addHelpButton('novalnet_callback_emailtoaddr', 'novalnet_callback_emailtoaddr', 'paygw_novalnet');

        $authorizeoptions = [
            'capture' => get_string('novalnet_payment_capture', 'paygw_novalnet'),
            'authorize'  => get_string('novalnet_payment_authorize', 'paygw_novalnet'),
        ];

        // CREDITCARD.
        $mform->addElement('header', 'novalnet_creditcard_settings', get_string('CREDITCARD', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_creditcard_settings', false);

        $mform->addElement('select', 'novalnet_creditcard_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_creditcard_payment_action', 'novalnet_payment_action', 'paygw_novalnet');
        $mform->addElement('text', 'novalnet_creditcard_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_creditcard_authorize_limit', PARAM_INT);
        $mform->addHelpButton('novalnet_creditcard_authorize_limit', 'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_creditcard_authorize_limit', 'novalnet_creditcard_payment_action', 'neq', 'authorize');
        $mform->addElement('advcheckbox', 'novalnet_creditcard_enforce_3d', get_string('novalnet_enforce_3d', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_creditcard_enforce_3d', 'novalnet_enforce_3d', 'paygw_novalnet');

        // DIRECT_DEBIT_SEPA.
        $mform->addElement('header', 'novalnet_direct_debit_sepa_settings', get_string('DIRECT_DEBIT_SEPA', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_direct_debit_sepa_settings', false);

        $mform->addElement('select', 'novalnet_direct_debit_sepa_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_direct_debit_sepa_payment_action', 'novalnet_payment_action', 'paygw_novalnet');
        $mform->addElement('text', 'novalnet_direct_debit_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_direct_debit_authorize_limit', PARAM_INT);
        $mform->addHelpButton('novalnet_direct_debit_authorize_limit', 'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_direct_debit_authorize_limit', 'novalnet_direct_debit_sepa_payment_action', 'neq', 'authorize');
        $mform->addElement('text', 'novalnet_direct_debit_sepa_due_date', get_string('novalnet_due_date', 'paygw_novalnet'));
        $mform->setType('novalnet_direct_debit_sepa_due_date', PARAM_INT);
        $mform->addHelpButton('novalnet_direct_debit_sepa_due_date', 'novalnet_due_date', 'paygw_novalnet');

        // INVOICE.
        $mform->addElement('header', 'novalnet_invoice_settings', get_string('INVOICE', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_invoice_settings', false);

        $mform->addElement('select', 'novalnet_invoice_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_invoice_payment_action', 'novalnet_payment_action', 'paygw_novalnet');
        $mform->addElement('text', 'novalnet_invoice_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_invoice_authorize_limit', PARAM_INT);
        $mform->addHelpButton('novalnet_invoice_authorize_limit', 'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_invoice_authorize_limit', 'novalnet_invoice_payment_action', 'neq', 'authorize');
        $mform->addElement('text', 'novalnet_invoice_due_date', get_string('novalnet_invoice_due_date', 'paygw_novalnet'));
        $mform->setType('novalnet_invoice_due_date', PARAM_INT);
        $mform->addHelpButton('novalnet_invoice_due_date', 'novalnet_invoice_due_date', 'paygw_novalnet');

        // PREPAYMENT.
        $mform->addElement('header', 'novalnet_prepayment_settings', get_string('PREPAYMENT', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_prepayment_settings', false);

        $mform->addElement('text', 'novalnet_prepayment_due_date', get_string('novalnet_prepayment_due_date', 'paygw_novalnet'));
        $mform->setType('novalnet_prepayment_due_date', PARAM_INT);
        $mform->addHelpButton('novalnet_prepayment_due_date', 'novalnet_prepayment_due_date', 'paygw_novalnet');

        // CASHPAYMENT.
        $mform->addElement('header', 'novalnet_cashpayment_settings', get_string('CASHPAYMENT', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_cashpayment_settings', false);

        $mform->addElement('text', 'novalnet_cashpayment_due_date', get_string('novalnet_cashpayment_due_date', 'paygw_novalnet'));
        $mform->setType('novalnet_cashpayment_due_date', PARAM_INT);
        $mform->addHelpButton('novalnet_cashpayment_due_date', 'novalnet_cashpayment_due_date', 'paygw_novalnet');

        // GOOGLEPAY.
        $mform->addElement('header', 'novalnet_googlepay_settings', get_string('GOOGLEPAY', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_googlepay_settings', false);

        $mform->addElement('select', 'novalnet_googlepay_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_googlepay_payment_action', 'novalnet_payment_action', 'paygw_novalnet');
        $mform->addElement('text', 'novalnet_googlepay_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_googlepay_authorize_limit', PARAM_INT);
        $mform->addHelpButton('novalnet_googlepay_authorize_limit', 'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_googlepay_authorize_limit', 'novalnet_googlepay_payment_action', 'neq', 'authorize');
        $mform->addElement('advcheckbox', 'novalnet_googlepay_enforce_3d', get_string('novalnet_enforce_3d', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_googlepay_enforce_3d', 'novalnet_enforce_3d', 'paygw_novalnet');

        // APPLEPAY.
        $mform->addElement('header', 'novalnet_applepay_settings', get_string('APPLEPAY', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_applepay_settings', false);

        $mform->addElement('select', 'novalnet_applepay_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_applepay_payment_action', 'novalnet_payment_action', 'paygw_novalnet');
        $mform->addElement('text', 'novalnet_applepay_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_applepay_authorize_limit', PARAM_INT);
        $mform->addHelpButton('novalnet_applepay_authorize_limit', 'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_applepay_authorize_limit', 'novalnet_applepay_payment_action', 'neq', 'authorize');

        // PAYPAL.
        $mform->addElement('header', 'novalnet_paypal_settings', get_string('PAYPAL', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_paypal_settings', false);

        $mform->addElement('select', 'novalnet_paypal_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_paypal_payment_action', 'novalnet_payment_action',
            'paygw_novalnet');
        $mform->addElement('text', 'novalnet_paypal_authorize_limit', get_string('novalnet_payment_authorize_limit',
            'paygw_novalnet'));
        $mform->setType('novalnet_paypal_authorize_limit', PARAM_INT);
        $mform->addHelpButton('novalnet_paypal_authorize_limit', 'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_paypal_authorize_limit', 'novalnet_paypal_payment_action', 'neq', 'authorize');

        // GUARANTEED_DIRECT_DEBIT_SEPA.
        $mform->addElement('header', 'novalnet_guaranteed_direct_debit_sepa_settings',
            get_string('GUARANTEED_DIRECT_DEBIT_SEPA', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_guaranteed_direct_debit_sepa_settings', false);

        $mform->addElement('select', 'novalnet_guaranteed_direct_debit_sepa_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_guaranteed_direct_debit_sepa_payment_action', 'novalnet_payment_action',
            'paygw_novalnet');
        $mform->addElement('text', 'novalnet_guaranteed_direct_debit_sepa_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_guaranteed_direct_debit_sepa_authorize_limit', PARAM_INT);
        $mform->setDefault('novalnet_guaranteed_direct_debit_sepa_authorize_limit', 999);
        $mform->addHelpButton('novalnet_guaranteed_direct_debit_sepa_authorize_limit',
            'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_guaranteed_direct_debit_sepa_authorize_limit',
            'novalnet_guaranteed_direct_debit_sepa_payment_action', 'neq', 'authorize');
        $mform->addElement('advcheckbox', 'novalnet_guaranteed_direct_debit_sepa_allow_b2b',
            get_string('novalnet_guarantee_allow_b2b', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_guaranteed_direct_debit_sepa_allow_b2b', 'novalnet_guarantee_allow_b2b',
            'paygw_novalnet');
        $mform->setDefault('novalnet_guaranteed_direct_debit_sepa_allow_b2b', 1);
        $mform->addElement('advcheckbox', 'novalnet_guaranteed_direct_debit_sepa_force_payment',
            get_string('force_normal_payment', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_guaranteed_direct_debit_sepa_force_payment', 'force_normal_payment',
            'paygw_novalnet');

        // GUARANTEED_INVOICE.
        $mform->addElement('header', 'novalnet_guaranteed_invoice_settings', get_string('GUARANTEED_INVOICE',
            'paygw_novalnet'));
        $mform->setExpanded('novalnet_guaranteed_invoice_settings', false);

        $mform->addElement('select', 'novalnet_guaranteed_invoice_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_guaranteed_invoice_payment_action', 'novalnet_payment_action',
            'paygw_novalnet');
        $mform->addElement('text', 'novalnet_guaranteed_invoice_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_guaranteed_invoice_authorize_limit', PARAM_INT);
        $mform->setDefault('novalnet_guaranteed_invoice_authorize_limit', 999);
        $mform->addHelpButton('novalnet_guaranteed_invoice_authorize_limit', 'novalnet_payment_authorize_limit', 'paygw_novalnet');
        $mform->hideIf('novalnet_guaranteed_invoice_authorize_limit', 'novalnet_guaranteed_invoice_payment_action',
            'neq', 'authorize');
        $mform->addElement('advcheckbox', 'novalnet_guaranteed_invoice_allow_b2b',
            get_string('novalnet_guarantee_allow_b2b', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_guaranteed_invoice_allow_b2b', 'novalnet_guarantee_allow_b2b', 'paygw_novalnet');
        $mform->setDefault('novalnet_guaranteed_invoice_allow_b2b', 1);
        $mform->addElement('advcheckbox', 'novalnet_guaranteed_invoice_force_payment',
            get_string('force_normal_payment', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_guaranteed_invoice_force_payment', 'force_normal_payment', 'paygw_novalnet');

        // INSTALMENT_INVOICE.
        $instalmentcycles = [];
        foreach ([ '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '15', '18', '21', '24', '36' ] as $cycle) {
            $instalmentcycles[$cycle] = get_string('instalment_cycles_text', 'paygw_novalnet', $cycle);
        }

        $mform->addElement('header', 'novalnet_instalment_invoice_settings', get_string('INSTALMENT_INVOICE', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_instalment_invoice_settings', false);

        $mform->addElement('select', 'novalnet_instalment_invoice_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_instalment_invoice_payment_action', 'novalnet_payment_action', 'paygw_novalnet');
        $mform->addElement('text', 'novalnet_instalment_invoice_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_instalment_invoice_authorize_limit', PARAM_INT);
        $mform->setDefault('novalnet_instalment_invoice_authorize_limit', 1998);
        $mform->addHelpButton('novalnet_instalment_invoice_authorize_limit', 'novalnet_payment_authorize_limit',
            'paygw_novalnet');
        $mform->hideIf('novalnet_instalment_invoice_authorize_limit', 'novalnet_instalment_invoice_payment_action', 'neq',
            'authorize');
        $mform->addElement('advcheckbox', 'novalnet_instalment_invoice_allow_b2b',
            get_string('novalnet_guarantee_allow_b2b', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_instalment_invoice_allow_b2b', 'novalnet_guarantee_allow_b2b', 'paygw_novalnet');
        $mform->setDefault('novalnet_instalment_invoice_allow_b2b', 1);
        $instalmentinvoicecycles = $mform->addElement('select', 'novalnet_instalment_invoice_total_period',
            get_string('instalment_cycles', 'paygw_novalnet'), $instalmentcycles);
        $mform->addHelpButton('novalnet_instalment_invoice_total_period', 'instalment_cycles', 'paygw_novalnet');
        $mform->setType('novalnet_instalment_invoice_total_period', PARAM_TEXT);
        $instalmentinvoicecycles->setMultiple(true);
        $mform->setDefault('novalnet_instalment_invoice_total_period', ['2']);

        // INSTALMENT_DIRECT_DEBIT_SEPA.
        $mform->addElement('header', 'novalnet_instalment_direct_debit_sepa_settings',
            get_string('INSTALMENT_DIRECT_DEBIT_SEPA', 'paygw_novalnet'));
        $mform->setExpanded('novalnet_instalment_direct_debit_sepa_settings', false);

        $mform->addElement('select', 'novalnet_instalment_direct_debit_sepa_payment_action',
            get_string('novalnet_payment_action', 'paygw_novalnet'), $authorizeoptions);
        $mform->addHelpButton('novalnet_instalment_direct_debit_sepa_payment_action', 'novalnet_payment_action', 'paygw_novalnet');
        $mform->addElement('text', 'novalnet_instalment_direct_debit_sepa_authorize_limit',
            get_string('novalnet_payment_authorize_limit', 'paygw_novalnet'));
        $mform->setType('novalnet_instalment_direct_debit_sepa_authorize_limit', PARAM_INT);
        $mform->setDefault('novalnet_instalment_direct_debit_sepa_authorize_limit', 1998);
        $mform->addHelpButton('novalnet_instalment_direct_debit_sepa_authorize_limit', 'novalnet_payment_authorize_limit',
            'paygw_novalnet');
        $mform->hideIf('novalnet_instalment_direct_debit_sepa_authorize_limit',
            'novalnet_instalment_direct_debit_sepa_payment_action', 'neq', 'authorize');

        $mform->addElement('advcheckbox', 'novalnet_instalment_direct_debit_sepa_allow_b2b',
            get_string('novalnet_guarantee_allow_b2b', 'paygw_novalnet'));
        $mform->addHelpButton('novalnet_instalment_direct_debit_sepa_allow_b2b', 'novalnet_guarantee_allow_b2b', 'paygw_novalnet');
        $mform->setDefault('novalnet_instalment_direct_debit_sepa_allow_b2b', 1);

        $instalmentsepacycles = $mform->addElement('select', 'novalnet_instalment_direct_debit_sepa_total_period',
            get_string('instalment_cycles', 'paygw_novalnet'), $instalmentcycles);
        $mform->addHelpButton('novalnet_instalment_direct_debit_sepa_total_period', 'instalment_cycles', 'paygw_novalnet');
        $mform->setType('novalnet_instalment_direct_debit_sepa_total_period', PARAM_TEXT);
        $instalmentsepacycles->setMultiple(true);
        $mform->setDefault('novalnet_instalment_direct_debit_sepa_total_period', ['2']);

        // Add the javascript required to enhance this mform.
        $PAGE->requires->js_call_amd('paygw_novalnet/gateway_settings', 'init', [$mform->getAttribute('id')]);
    }

    /**
     * Validates the gateway configuration form by checking the submitted data.
     *
     * This function is invoked when the user submits the gateway configuration form.
     * It checks for errors in the provided data and populates the $errors array with
     * any validation issues that are encountered.
     *
     * @param \core_payment\form\account_gateway $form The form object that holds the form's data and structure.
     * @param \stdClass $data The form data submitted by the user, containing all the input values.
     * @param array $files The files uploaded through the form, if any.
     * @param array &$errors An array of validation errors that will be populated if any issues are found.
     *
     * @return void
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form,
                                                 \stdClass $data, array $files, array &$errors): void {
        if ($data->enabled && ( empty( $data->novalnet_public_key ) || empty( $data->novalnet_key_password ) ) ) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
            $errorsmsg = get_string('novalnet_required_error', 'paygw_novalnet');
            if (empty( $data->novalnet_public_key )) {
                $errors['novalnet_public_key'] = $errorsmsg;
            }

            if (empty( $data->novalnet_key_password )) {
                $errors['novalnet_key_password'] = $errorsmsg;
            }
        }
    }
}
