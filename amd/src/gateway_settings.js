/**
 * Novalent payment plugin
 *
 * JavaScript for configuring API credentials.
 *
 * @author       Novalnet
 * @module     paygw_novalnet/gateway_settings
 * @copyright(C) Novalnet. All rights reserved. <https://www.novalnet.de/>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
    [
        'jquery',
        'core/ajax',
        'core/notification',
        'core/str',
        'core/log'
    ],
    function(
        $,
        Ajax,
        Notification,
        Str,
        log
    ) {

    var SELECTORS = {
        SELECTED_TARIFF: '[name="novalnet_tariff_id"]',
        FORM_PAYMENTS: ['CREDITCARD', 'DIRECT_DEBIT_SEPA', 'INVOICE', 'PREPAYMENT', 'CASHPAYMENT',
            'GOOGLEPAY', 'APPLEPAY', 'PAYPAL', 'GUARANTEED_DIRECT_DEBIT_SEPA', 'GUARANTEED_INVOICE',
            'INSTALMENT_INVOICE', 'INSTALMENT_DIRECT_DEBIT_SEPA']
    };

    /**
     * Trigger the first load of the preview section and then listen for modifications
     * to the form to reload the preview with new filter values.
     * @param {jquery} formId The form element id.
     *
     */
    var init = function(formId) {
        var form = $('#' + formId);
        var selectedTariff = form.find(SELECTORS.SELECTED_TARIFF).val();

        if (selectedTariff) {
            $('[name="novalnet_selected_tariff"]').val(selectedTariff);
        }

        // Hide all payment method headers
        SELECTORS.FORM_PAYMENTS.forEach(function(method) {
            var headerId = '#id_novalnet_' + method.toLowerCase() + '_settings';
            $(headerId).hide();
        });

        if ($('[name="novalnet_public_key"]').length && $('[name="novalnet_key_password"]').length) {
            $('#novalnet_tariff_id').prop('readonly', true);
            if ('' !== $.trim($('[name="novalnet_public_key"]').val()) &&
                 '' !== $.trim($('[name="novalnet_key_password"]').val())) {
                fillNovalnetDetails();
            } else {
                nullBasicParams();
            }
            $( '[name="novalnet_public_key"], [name="novalnet_key_password"]' ).on(
                'input change',
                function(e) {
                    if ('' !== $.trim($('[name="novalnet_public_key"]').val()) &&
                         '' !== $.trim($('[name="novalnet_key_password"]').val())) {
                        if ('input' === e.type) {
                            if (e.originalEvent.inputType != undefined && 'insertFromPaste' === e.originalEvent.inputType) {
                                fillNovalnetDetails();
                            }
                        } else {
                            fillNovalnetDetails();
                        }
                    } else {
                        nullBasicParams();
                    }
                }
            ).change();
        } else {
            nullBasicParams();
        }

        $('[name="webhook_configure"]').on(
            'click',
            function() {
                let webhookUrlError = Str.get_string('novalnet_webhook_url_error', 'paygw_novalnet');
                let webhookUrlElement = $('[name="novalnet_webhook_url"]');
                let webhookUrl = $.trim(webhookUrlElement.val());
                // Validate the URL: Check for missing/empty URL or invalid format
                const regex = /^(http|https):\/\/(\w+(:\w*)?@)?(\S+)(:\d+)?(\/[\w#!:.?+=&%!\-\/]*)?$/;

                if (!webhookUrlElement.length || !webhookUrl || !regex.test(webhookUrl)) {
                    Notification.alert('Error', webhookUrlError);
                    return false;
                }

                Str.get_strings([
                    {key: 'confirm', component: 'moodle'},
                    {key: 'novalnet_webhook_notice', component: 'paygw_novalnet'},
                    {key: 'confirm', component: 'moodle'},
                    {key: 'cancel', component: 'moodle'}
                ]).done(function(strings) {
                    Notification.confirm(
                        strings[0], // Title.
                        strings[1], // Novalnet webhook notice?
                        strings[2], // Confirm.
                        strings[3], // Cancel.
                        function() {
                            handleWebhookConfigure(webhookUrl);
                        }
                    );
                }).fail(Notification.exception);
            }
        ).change();

        // Update the hidden field when the user changes the multi-select
        $('[name="novalnet_active_payments[]"]').on('change', function() {
            var selectedValues = $(this).val() || [];
            $('[name="novalnet_selected_payments"]').val(selectedValues.join(','));
        });

        // Update the hidden field when the user changes the multi-select
        $('[name="novalnet_test_mode_payments[]"]').on('change', function() {
            var selectedTestValues = $(this).val() || [];
            $('[name="novalnet_selected_test_payments"]').val(selectedTestValues.join(','));
        });
    };

    var fillNovalnetTariffDetails = function(tariff) {
        var tariffElement = $('[name="novalnet_tariff_id"]');
        var savedTariff = $('[name="novalnet_selected_tariff"]').val();

        if ('text' == tariffElement.prop( 'type' )) {
            tariffElement.replaceWith(
                '<select class="form-select" name="novalnet_tariff_id" id="id_novalnet_tariff_id"></select>'
            );
        }

        tariffElement.empty();

        for ( var tariffId in tariff ) {
            var tariffType = tariff[tariffId].type;
            var tariffValue = tariff[tariffId].name;
            tariffElement.append(
                $(
                    '<option>',
                    {
                        value: $.trim(tariffId),
                        text: $.trim(tariffValue)
                    }
                ).attr( "tariff_type", $.trim(tariffType) )
            );

            /** Assign tariff id. */
            if (savedTariff === $.trim(tariffId)) {
                tariffElement.val( $.trim(tariffId) );
                $('[name="novalnet_tariff_type"]' ).val( $.trim(tariffType) );
            }
        }
    };

    var fillNovalnetPaymentDetails = function(paymentTypes) {
        var paymentElement = $('[name="novalnet_active_payments[]"]');
        var testPaymentElement = $('[name="novalnet_test_mode_payments[]"]');
        var novalnetPayments = ['ALIPAY', 'APPLEPAY', 'BANCONTACT', 'BLIK', 'CASHPAYMENT', 'CASH_ON_DELIVERY', 'CREDITCARD',
                                'DIRECT_DEBIT_ACH', 'DIRECT_DEBIT_SEPA', 'EPS', 'GIROPAY', 'GOOGLEPAY',
                                'GUARANTEED_DIRECT_DEBIT_SEPA', 'GUARANTEED_INVOICE', 'IDEAL', 'INSTALMENT_DIRECT_DEBIT_SEPA',
                                'IDEAL', 'INSTALMENT_INVOICE', 'INVOICE', 'PREPAYMENT', 'MBWAY', 'MULTIBANCO',
                                'ONLINE_BANK_TRANSFER', 'ONLINE_TRANSFER', 'PAYCONIQ', 'PAYPAL', 'POSTFINANCE',
                                'POSTFINANCE_CARD', 'PRZELEWY24', 'TRUSTLY', 'TWINT', 'WECHATPAY'];
        var savedPayments = $('[name="novalnet_selected_payments"]').val();
        savedPayments = savedPayments ? savedPayments.split(',') : [];
        var savedTestPayments = $('[name="novalnet_selected_test_payments"]').val();
        savedTestPayments = savedTestPayments ? savedTestPayments.split(',') : [];

        paymentElement.empty(); // Remove all existing options
        testPaymentElement.empty(); // Remove all existing options
        paymentTypes.forEach(function(novalnetPayment) {

            if ($.inArray(novalnetPayment, novalnetPayments) === -1) {
                return; // Skip if payment type is not in the allowed list
            }

            var strings = [
                {
                    key: novalnetPayment,
                    component: 'paygw_novalnet'
                }
            ];

            Str.get_strings(strings).then(function(langStrings) {
                var optionText = langStrings && langStrings.length ? langStrings : novalnetPayment;

                /**
                 * Adds a new option to the given element if the value does not already exist in the element's options.
                 *
                 * @param {HTMLElement} element - The DOM element (typically a <select>) to which the option will be added.
                 * @param {string} value - The value of the option to be added.
                 * @param {string} text - The text that will be displayed for the new option.
                 * @returns {boolean} Returns true if the option was added, false if the option already exists.
                 */
                function addOptionIfNotExists(element, value, text) {
                    if (element.find(`option[value="${value}"]`).length === 0) {
                        element.append($('<option>', {
                            value: $.trim(value),
                            text: text
                        }));
                    }
                }

                // Add the option to both payment elements
                addOptionIfNotExists(paymentElement, novalnetPayment, optionText);
                addOptionIfNotExists(testPaymentElement, novalnetPayment, optionText);

                /**
                 * Selects an option from a given element if the value is present in the provided list.
                 *
                 * @param {HTMLElement} element - The DOM element where the option is located.
                 * @param {string} value - The value to check against the list.
                 * @param {Array<string>} list - An array of values to compare the value against.
                 * @returns {boolean} Returns true if the value was found and the option was selected, otherwise false.
                 */
                function selectOptionIfInList(element, value, list) {
                    if ($.inArray(value, list) !== -1) {
                        element.find(`option[value="${value}"]`).prop('selected', true);
                    }
                }

                // Set the option as selected if it's in the savedPayments list
                selectOptionIfInList(paymentElement, novalnetPayment, savedPayments);
                selectOptionIfInList(testPaymentElement, novalnetPayment, savedTestPayments);
            }).catch(function(error) {
                log.error("Error loading strings:");
                log.error(error);
                return;
            });

            // Display settings relevant to the active payment methods only
            if (SELECTORS.FORM_PAYMENTS.includes(novalnetPayment)) {
                var selectedHeaderId = '#id_novalnet_' + novalnetPayment.toLowerCase() + '_settings';
                $(selectedHeaderId).show();
            }
        });
    };

    var handleWebhookConfigure = function(webhookUrl) {
        const request = {
            methodname: 'paygw_novalnet_handle_webhook_configure',
            args: {
                novalnetApiKey: $.trim($('[name="novalnet_public_key"]').val()),
                novalnetKeyPassword: $.trim($('[name="novalnet_key_password"]').val()),
                novalnetWebhookUrl: webhookUrl
            },
        };

        Ajax.call([request])[0].then(response => {
            var parsedResponse = JSON.parse(response.response);

            let message, type;
            if (parsedResponse.result.status !== '' && parsedResponse.result.status === 'SUCCESS' &&
                parsedResponse.result.status_code === 100) {
                message = Str.get_string('novalnet_webhook_configure_success', 'paygw_novalnet');
                type = 'success';
            } else {
                message = parsedResponse.result.status_text;
                type = 'error';
            }

            Notification.addNotification({
                message: message,
                type: type
            });

            return Promise.resolve();  // Return a resolved promise to satisfy the catch-or-return rule
        }).catch(error => {
            // Handle any errors that occur during the promise chain
            log.error('Error:', error);
            Notification.addNotification({
                message: 'An error occurred while processing the request.',
                type: 'error'
            });
        });
    };

    var fillNovalnetDetails = function() {
        const request = {
            methodname: 'paygw_novalnet_get_merchant_details',
            args: {
                novalnetApiKey: $.trim($('[name="novalnet_public_key"]').val()),
                novalnetKeyPassword: $.trim($('[name="novalnet_key_password"]').val()),
                id: $.trim($('[name="id"]').val()),
                accountid: $.trim($('[name="accountid"]').val()),
                gateway: $.trim($('[name="gateway"]').val()),
            },
        };

        Ajax.call([request])[0].then(response => {
            var response = JSON.parse(response.response);
                if ('' !== response.result.status && 'SUCCESS' == response.result.status && 100 == response.result.status_code) {
                    fillNovalnetTariffDetails(response.merchant.tariff);
                    fillNovalnetPaymentDetails(response.merchant.payment_types);
                } else {
                    nullBasicParams();
                    Notification.addNotification({
                        message: response.result.status_text,
                        type: 'error'
                    });
                }
        });
    };

    /**
     * Null config values
     *
     */
    var nullBasicParams = function () {
        $('[name="novalnet_active_payments[]"]').empty();
        $('[name="novalnet_test_mode_payments[]"]').empty();
        $('[name="novalnet_tariff_id"]').empty();
        $( '#novalnet_tariff_id' ).find( 'option' ).remove();
    };

    return {
        init: init
    };
});
