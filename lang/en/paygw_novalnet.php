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
 * Strings for component 'paygw_novalnet', language 'en'
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

$string['gatewaydescription'] = 'Novalnet is a full service payment provider with an own payment orchestration platform for full-service processing of international and local payments around the world. By connecting to the Novalnet platform, it ensures that your transactions are protected by the highest level of security.';
$string['gatewayname'] = 'Novalnet Payments(150+ payment methods worldwide)';
$string['internalerror'] = 'An internal error has occurred. Please contact us.';
$string['pluginname'] = 'Novalnet';
$string['api_config_heading'] = 'Novalnet API Configuration';
$string['pluginname_desc'] = '<p>Novalnet Payment Plugin for Moodle is an end-to-end solution for full-service processing of international and local payments around the world. By connecting to the Novalnet platform, it ensures that your transactions are protected by the highest level of security. To this end, Novalnet is audited and certified annually in a comprehensive security audit both by BaFin and according to the Payment Card Industry Data Security Standard (PCI DSS) at the highest level 1.<br />For setup and handling of the Novalnet-Payment plugin you can find the installation guide <a href="https://www.novalnet.com/docs/plugins/installation-guides/moodle-installation-guide.pdf" target="_blank" style="text-decoration: underline; font-weight: bold; color:#0080c9;">Here</a></p>';
$string['novalnet_required_error'] = 'Please provide the required information in the field.';
$string['novalnet_trans_comments'] = 'Payment Note:';
$string['novalnet_key_password'] = 'Payment access key';
$string['novalnet_public_key'] = 'Product activation key';
$string['novalnet_public_key_help'] = 'Your product activation key is a unique token for merchant authentication and payment processing. ';
$string['novalnet_key_password_help'] = 'Your secret key used to encrypt the data to avoid user manipulation and fraud. ';
$string['novalnet_tariff_id'] = 'Select Tariff ID';
$string['novalnet_active_payments'] = 'Payment Methods';
$string['novalnet_active_payments_help'] = 'The selected payment methods will be displayed on the payment enrolment page.';
$string['novalnet_tariff_id_help'] = 'Select a Tariff ID to match the preferred tariff plan you created at the <a href="https://admin.novalnet.de" target="_blank">Novalnet Admin portal</a> for this project. ';
$string['live'] = 'Live';
$string['novalnet_test_mode'] = 'Enable test mode';
$string['novalnet_test_mode_help'] = 'The payment will be processed in the test mode therefore amount for this transaction will not be charged';
$string['sandbox'] = 'Sandbox';
$string['novalnet_vendor_script_heading'] = 'Notification / Webhook URL Setup';
$string['novalnet_webhook_configure'] = 'Configure';
$string['novalnet_webhook_url'] = 'Notification / Webhook URL';
$string['novalnet_webhook_url_help'] = 'You must add the following webhook endpoint to your <a href="https://admin.novalnet.de" target="_blank">Novalnet Admin portal</a> . This will allow you to receive notifications about the transaction status ';
$string['novalnet_callback_test_mode'] = 'Allow manual testing of the Notification / Webhook URL';
$string['novalnet_webhook_configure_success'] = 'Notification / Webhook URL is configured successfully in Novalnet Admin Portal';
$string['novalnet_webhook_notice'] = 'Are you sure you want to configure the Webhook URL in Novalnet Admin Portal?';
$string['novalnet_webhook_url_error'] = 'Please enter the valid webhook URL';
$string['novalnet_callback_emailtoaddr'] = 'Send e-mail to';
$string['novalnet_callback_test_mode_help'] = 'Enable this to test the Novalnet Notification / Webhook URL manually. Disable this before setting your shop live to block unauthorized calls from external parties.';
$string['novalnet_callback_emailtoaddr_help'] = 'Notification / Webhook URL execution messages will be sent to this e-mail. Note:To configure multiple emails, separate each one with a comma (",").';
$string['novalnet_enforce_3d'] = 'Enforce 3D secure payment outside EU';
$string['novalnet_enforce_3d_help'] = 'By enabling this option, all payments from cards issued outside the EU will be authenticated via 3DS 2.0 SCA.';
$string['novalnet_payment_action'] = 'Payment Action';
$string['novalnet_payment_action_help'] = 'Choose whether or not the payment should be charged immediately. Capture completes the transaction by transferring the funds from buyer account to merchant account. Authorize verifies payment details and reserves funds to capture it later, giving time for the merchant to decide on the order.';
$string['novalnet_payment_capture'] = 'Capture';
$string['novalnet_payment_authorize'] = 'Authorize';
$string['novalnet_payment_zero_amount_booking'] = 'Authorize with zero amount';
$string['novalnet_payment_authorize_limit'] = 'Minimum transaction amount for authorization';
$string['novalnet_payment_authorize_limit_help'] = 'In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.';
$string['novalnet_due_date'] = 'Payment due date (in days)';
$string['novalnet_invoice_due_date'] = 'Payment due date (in days)';
$string['novalnet_prepayment_due_date'] = 'Payment due date (in days)';
$string['novalnet_due_date_help'] = 'Number of days after which the payment is debited (must be between 3 and 14 days).';
$string['novalnet_invoice_due_date_help'] = 'Number of days given to the buyer to transfer the amount to Novalnet (must be greater than 7 days). If this field is left blank, 14 days will be set as due date by default.';
$string['novalnet_prepayment_due_date_help'] = 'Number of days given to the buyer to transfer the amount to Novalnet (must be between 7 and 28 days). If this field is left blank, 14 days will be set as due date by default.';
$string['novalnet_cashpayment_due_date'] = 'Slip expiry date (in days)';
$string['novalnet_cashpayment_due_date_help'] = 'Number of days given to the buyer to pay at a store. If this field is left blank, 14 days will be set as slip expiry date by default.';
$string['force_normal_payment'] = 'Force Non-Guarantee payment';
$string['force_normal_payment_help'] = 'Even if payment guarantee is enabled, payments will still be processed as non-guarantee payments if the payment guarantee requirements are not met.';
$string['instalment_cycles'] = 'Instalment cycles';
$string['instalment_cycles_help'] = 'Select the instalment cycles that can be availed in the instalment plan';
$string['instalment_cycles_text'] = '{$a} cycles';
$string['novalnet_guarantee_allow_b2b'] = 'Allow B2B Customers';
$string['novalnet_guarantee_allow_b2b_help'] = 'Allow B2B customers to place an order. <br/><br/><b>Note:</b><br/>To enable guaranteed payment for a B2B customer, you should create a company field (with the technical name set to "company") that will be displayed during the user\'s registration process.';
$string['CREDITCARD'] = 'Credit/Debit Cards';
$string['CREDITCARD_DESCRIPTION'] = 'The amount will be debited from your credit card once the order is submitted';
$string['DIRECT_DEBIT_SEPA'] = 'Direct Debit SEPA';
$string['DIRECT_DEBIT_SEPA_DESCRIPTION'] = 'Your account will be debited upon the order submission.';
$string['GUARANTEED_DIRECT_DEBIT_SEPA'] = 'Direct Debit SEPA with payment guarantee';
$string['INSTALMENT_DIRECT_DEBIT_SEPA'] = 'Instalment by SEPA direct debit';
$string['INVOICE'] = 'Invoice';
$string['INVOICE_DESCRIPTION'] = 'Once you\'ve submitted the order, you will receive an e-mail with account details to make payment';
$string['GUARANTEED_INVOICE'] = 'Invoice with payment guarantee';
$string['INSTALMENT_INVOICE'] = 'Instalment by invoice';
$string['PREPAYMENT'] = 'Prepayment';
$string['PREPAYMENT_DESCRIPTION'] = 'Once you\'ve submitted the order, you will receive an e-mail with account details to make payment';
$string['CASHPAYMENT'] = 'Barzahlen/viacash';
$string['CASHPAYMENT_DESCRIPTION'] = 'After completing your order you get a payment slip from Barzahlen that you can easily print out or have it sent via SMS to your mobile phone';
$string['ONLINE_TRANSFER'] = 'Sofort';
$string['ONLINE_TRANSFER_DESCRIPTION'] = 'Pay securely directly from your bank account.';
$string['IDEAL'] = 'iDEAL';
$string['GIROPAY'] = 'Giropay';
$string['GIROPAY_DESCRIPTION'] = 'Pay with your usual online banking login data (PIN & TAN required).';
$string['EPS'] = 'eps';
$string['PAYPAL'] = 'PayPal';
$string['PAYPAL_DESCRIPTION'] = 'After the successful verification continue to PayPal\'s website for payment.';
$string['PRZELEWY24'] = 'Przelewy24';
$string['POSTFINANCE'] = 'PostFinance E-Finance';
$string['POSTFINANCE_DESCRIPTION'] = 'Pay with your PostFinance E-Finance easy and secure online';
$string['POSTFINANCE_CARD'] = 'PostFinance Card';
$string['POSTFINANCE_CARD_DESCRIPTION'] = 'Pay with your PostFinance Card easy and secure online';
$string['MULTIBANCO'] = 'Multibanco';
$string['MULTIBANCO_DESCRIPTION'] = 'After completing your order, a payment reference will be displayed in the shop / website. Using this payment reference, you can either pay in the Multibanco ATM or through your Internet bank.';
$string['BANCONTACT'] = 'Bancontact';
$string['BANCONTACT_DESCRIPTION'] = 'After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment.';
$string['APPLEPAY'] = 'Apple Pay';
$string['APPLEPAY_DESCRIPTION'] = 'Amount will be booked from your card after successful authentication.';
$string['GOOGLEPAY'] = 'Google Pay';
$string['GOOGLEPAY_DESCRIPTION'] = 'Amount will be booked from your card after successful authentication.';
$string['TRUSTLY'] = 'Trustly';
$string['ALIPAY'] = 'Alipay';
$string['ALIPAY_DESCRIPTION'] = 'Pay with Alipay and secure online';
$string['WECHATPAY'] = 'WeChat Pay';
$string['WECHATPAY_DESCRIPTION'] = 'Pay with WeChat Pay and secure online';
$string['ONLINE_BANK_TRANSFER'] = 'Online bank transfer';
$string['ONLINE_BANK_TRANSFER_DESCRIPTION'] = ' Executed via your online banking with just a few clicks';
$string['ONLINE_BANK_TRANSFER_DESCRIPTION_1'] = ' Simple and secure bank transfer';
$string['DIRECT_DEBIT_ACH'] = 'Direct Debit ACH';
$string['DIRECT_DEBIT_ACH_DESCRIPTION'] = 'Your account will be debited upon the order submission.';
$string['MBWAY'] = 'MB Way';
$string['MBWAY_DESCRIPTION'] = 'After completing your order, a payment request notification will be sent to your mobile device. You can enter the PIN and authorises the payment.';
$string['BLIK'] = 'Blik';
$string['PAYCONIQ'] = 'Payconiq';
$string['CASH_ON_DELIVERY'] = 'Cash on pickup';
$string['CASH_ON_DELIVERY_DESCRIPTION'] = 'Pay in cash while receiving the goods at pick up point.';
$string['TWINT'] = 'TWINT';
$string['TWINT_DESCRIPTION'] = 'Pay securely from your mobile device using the TWINT app';
$string['startpayment'] = 'Start payment';
$string['novalnet_payment_error'] = 'Payment was not successful. An error occurred';
$string['err:assert:paymentrecord'] = 'Invalid request: paymentrecord not found';
$string['err:assert:paymentrecordvariables'] = 'Invalid request: one or more paymentrecord variables do not match with the intended component, paymentarea or itemid';
$string['err:validatetransaction:component'] = 'Transaction invalid: component mismatch';
$string['err:validatetransaction:paymentarea'] = 'Transaction invalid: paymentarea mismatch';
$string['err:validatetransaction:itemid'] = 'Transaction invalid: itemid mismatch';
$string['err:validatetransaction:userid'] = 'Transaction invalid: user mismatch';
$string['instalmentheading'] = 'Instalment summary: ';
$string['instalmentsno'] = 'S.No';
$string['instalmenttid'] = 'Novalnet Transaction ID';
$string['instalmentamount'] = 'Amount';
$string['instalmentnextdate'] = 'Next Instalment Date';
$string['instalmentstatus'] = 'Status';
$string['cancelled'] = 'Cancelled';
$string['pending'] = 'Pending';
$string['completed'] = 'Completed';
$string['refunded'] = 'Refunded';
$string['selectpaymentmethod'] = 'Select a payment method to authorize and complete your payment securely';
$string['course_description'] = 'Course Name: {$a->coursename} & Course Fee: {$a->coursefee}';
$string['err:nopaymentmethods'] = 'You don\'t have any payment methods enabled for Novalnet.';
$string['redirect-notify'] = 'Starting the payment will securely redirect you to the Novalnet secure page to complete your payment.';
$string['startpayment:failed:nopayment'] = 'Please choose a payment method.';
$string['startpayment:failed:title'] = 'Payment could not be started.';
$string['startpayment:failed:btncancel'] = 'Close';
$string['payment:returnpage'] = 'Processing payment status.';
$string['unknownerror'] = 'An unknown error has occurred. Please contact the system administrator.';
$string['specific_course_comment'] = 'Order comments: {$a}';
$string['wallet_card_info'] = 'Your order was successfully processed using {$a}';
$string['novalnet_payment_name'] = 'Payment name: {$a}';
$string['novalnet_transaction_id'] = 'Novalnet transaction ID: {$a}';
$string['test_order_text'] = 'Test order';
$string['guarantee_pending_text'] = 'Your order is being verified. Once confirmed, we will send you our bank details to which the order amount should be transferred. Please note that this may take up to 24 hours';
$string['sepa_guarantee_pending_text'] = 'Your order is under verification and we will soon update you with the order status. Please note that this may take upto 24 hours.';
$string['invoice_payment_bank_text'] = 'Please transfer the amount of {$a}';
$string['instalment_payment_bank_text'] = 'Please transfer the instalment cycle amount of {$a}';
$string['bank_with_due_date_text'] = ' to the following account on or before {$a}';
$string['bank_without_due_date_text'] = ' to the following account.';
$string['account_owner'] = 'Account holder: {$a}';
$string['bank_name'] = 'Bank: {$a}';
$string['bank_place'] = 'Place: {$a}';
$string['bank_iban'] = 'IBAN: {$a}';
$string['bank_bic'] = 'BIC: {$a}';
$string['multiple_reference_text'] = 'Please use any of the following payment references when transferring the amount. This is necessary to match it with your corresponding order';
$string['reference_text1'] = 'Payment Reference 1: TID {$a}';
$string['reference_text2'] = 'Payment Reference 2: {$a}';
$string['slip_expiry_date'] = 'Slip expiry date (in days): {$a}';
$string['cash_payment_stores'] = 'Store(s) near to you: ';
$string['multibanco_reference_text'] = 'Please use the following payment reference details to pay the amount of {$a} at a Multibanco ATM or through your internet banking.';
$string['multibanco_partner_reference'] = 'Partner Payment Reference: {$a}';
$string['multibanco_entity_reference'] = 'Entity: {$a}';
$string['hash_check_failed_error'] = 'While redirecting some data has been changed. The hash check failed';
$string['payment:successful:subject'] = 'Payment Confirmation & Course Enrollment Successful';
$string['payment:successful:content'] = '<br /><br />We’re pleased to confirm that your payment for the course {$a->course} has been successfully received. You are now officially enrolled and ready to begin your learning journey.<br /><br />To get started, please log in to your account and access your course here - <a href="{$a->url}" target="_blank"> {$a->url} </a>.';
$string['payment:successful:message'] = 'Your payment was successful';
$string['payment:pending:subject'] = 'Enrollment Pending – Your Payment Has Been Pending';
$string['payment:pending:content'] = '<br /><br />Thank you for registering for the course - {$a} <br />
We would like to inform you that your payment is currently pending. Your enrollment will be confirmed once the payment has been successfully processed.<br /> Upon confirmation, you will be officially enrolled and ready to begin your learning journey.';
$string['payment:pending:message'] = 'Payment is pending, and enrollment will be completed once the payment is successfully processed.';
$string['payment:authorize:subject'] = 'Payment Authorized – Enrollment Confirmation Coming Soon';
$string['payment:authorize:content'] = '<br /><br />Thank you for registering for the course - {$a}<br />
We would like to inform you that your payment has been authorized. <br />Once the payment is successfully captured, your enrollment will be complete, and you will be officially ready to begin your learning journey.';
$string['payment:authorize:message'] = 'Your payment has been authorized. Once the payment is successfully captured, your enrollment will be complete.';
$string['payment:failed:subject'] = 'Payment Unsuccessful – Enrollment Not Completed';
$string['payment:failed:content'] = '<br /><br />We regret to inform you that your recent payment could not be processed and was cancelled due to the following reason: {$a}.<br />
As a result, your enrollment has not been completed. Please review the details below and try again. If you believe this is an error or need any help, feel free to reach out to the merchant support for assistance.<br />We’re here to help and ensure your enrollment process goes smoothly.<br />';
$string['payment:failed:message'] = 'Your payment has been cancelled due to : {$a}';
$string['payment:cannotprocessstatus:subject'] = 'Payment status error';
$string['payment:cannotprocessstatus:content'] = 'Your payment has a status we cannot (yet) process. Please contact system administrator.';
$string['payment:cannotprocessstatus:message'] = 'Your payment has a status we cannot (yet) process. Please contact system administrator.';
$string['admin:onhold:subject'] = 'Payment Authorization for Course Enrollment';
$string['admin:onhold:message'] = ' We are pleased to inform you that a payment of ({$a->amount}) for the course ({$a->coursename}),
associated with Enrollment ID: ({$a->enrolid}), has been successfully authorized.<br /><br />
The Transaction ID ({$a->tid}) for this payment is {$a->paymentname}.
<br />Kindly confirm the payment or take any necessary actions regarding cancellation through
the <a href="https://admin.novalnet.de" target="_blank">Novalnet Admin portal</a>, as required.';
$string['mail:user:details'] = 'Dear Mr./Mrs. {$a},';
$string['mail:merchant:support'] = 'If you have any questions or need support, please feel free to contact the merchant support.';
$string['mail:payment:details'] = '<p> Course & Payment Details: </p><ul><li>Course Name: <b>{$a->course}</b></li>
  <li>Amount:  <b>{$a->amount}</b></li>
  <li>Payment: <b>{$a->payment}</b></li>
  <li>Payment Date: <b>{$a->date}</b></li>
  <li>Transaction ID: <b>{$a->tid}</b></li>
  <li>Transaction Note: </b><b>{$a->comments}</b></li>
</ul>';
$string['mail:admin:details'] = '<br /><br /><br />---------------------<br />Warm regards,<br />{$a}';
$string['messageprovider:payment_success'] = 'Payment Confirmation Successfully Received - Notification Delayed';
$string['messageprovider:payment_pending'] = 'Your Payment Is Still Pending - Notification Delayed';
$string['messageprovider:payment_authorize'] = 'Your Payment Has Been Authorized - Notification Delayed';
$string['messageprovider:payment_failed'] = 'Your Payment Could Not Be Processed - Notification Delayed';
$string['messageprovider:payment_cannotprocessstatus'] = 'There Was an Error with Your Payment Status - Notification Delayed';
$string['novalnet_callback_mail_subject'] = 'Novalnet Callback Script Access Report - Moodle';
$string['novalnet_amount_capture'] = 'The transaction has been confirmed on {$a}';
$string['novalnet_deactivated_message'] = 'The transaction has been canceled on {$a}';
$string['novalnet_refund_message'] = 'Refund has been initiated for the TID:{$a->ptid} with amount {$a->amount}. ';
$string['novalnet_refund_tid_message'] = 'New TID:{$a} for the refunded amount.';
$string['novalnet_callback_redirect_update_message'] = 'Transaction updated successfully for the TID: {$a->tid} with the amount {$a->amount} on {$a->date}.';
$string['novalnet_callback_cashpayment_message'] = ' The transaction has been updated with amount {$a->amount} and slip expiry date {$a->date}.';
$string['novalnet_callback_duedate_update_message'] = ' The transaction has been updated with amount {$a->amount} and due date {$a->date}.';
$string['novalnet_callback_update_onhold_message'] = 'The transaction status has been changed from pending to on-hold for the TID: {$a->tid} on {$a->date}. ';
$string['novalnet_callback_credit_message'] = 'Credit has been successfully received for the TID: {$a->parent_tid} with amount {$a->amount} on {$a->date}. Please refer PAID order details in our Novalnet Admin Portal for the TID: {$a->tid}. ';
$string['novalnet_credit_overpaid_message'] = 'The amount has been overpaid.';
$string['novalnet_chargeback_message'] = 'Chargeback executed successfully for the TID: {$a->ptid} with amount {$a->amount} on {$a->date}. The subsequent TID: {$a->tid}';
$string['novalnet_payment_reminder_message'] = 'Payment Reminder {$a} has been sent to the customer.';
$string['novalnet_collection_agency_message'] = 'The transaction has been submitted to the collection agency. Collection Reference: {$a}';
$string['novalnet_callback_instalment_prepaid_message'] = 'A new instalment transaction has been received for the Transaction ID: {$a->ptid} .The new instalment Transaction ID is {$a->tid} with the amount {$a->amount} on {$a->date}.';
$string['novalnet_callback_instalment_stopped_message'] = 'Instalment has been stopped for the TID: {$a->ptid} on {$a->date}.';
$string['novalnet_callback_instalment_refund_message'] = '& Refund has been initiated with the amount {$a}';
$string['novalnet_callback_instalment_cancelled_message'] = 'Instalment has been cancelled for the TID: {$a->ptid} on {$a->date} ';
$string['novalnet_callback_already_paid'] = 'Novalnet webhook received. Order Already Paid';
$string['novalnet_callback_tid_existed'] = 'Novalnet Callback executed. The Transaction ID already existed';
$string['novalnet_callback_unhandled_event'] = 'The webhook notification has been received for the unhandled EVENT type($a)';
$string['novalnet_callback_script_executed'] = 'Novalnet callback received. Callback Script executed already.';
$string['novalnet_callback_status_invalid'] = 'Novalnet callback received. Status is not valid.';
$string['novalnet_callback_missing_necessary_parameter'] = 'A necessary parameter is missing from the request.';
$string['novalnet_callback_not_json_format'] = 'Received data is not in the JSON format $a';
$string['novalnet_callback_unauthorised_ip'] = 'Unauthorised access from the IP $a';
$string['novalnet_callback_host_recieved_ip_empty'] = 'Unauthorised access from the IP. Host/recieved IP is empty.';
$string['novalnet_callback_host_empty'] = 'Unauthorised access from the IP. Novalnet Host name is empty.';
$string['novalnet_callback_missing_category'] = 'Required parameter category($a) not received.';
$string['novalnet_callback_missing_parameter_category'] = 'Required parameter($a->parameter) in the category($a->category) not received.';
$string['novalnet_callback_missing_tid_category'] = 'Invalid TID received in the category($a->category) not received $a->parameter';
$string['novalnet_callback_hash_check_failed'] = 'While notifying some data has been changed. The hash check failed';
$string['novalnet_callback_already_handled_shop'] = 'Process already handled in the shop.';
$string['novalnet_callback_reference_not_matching'] = 'Order reference not matching.';
$string['novalnet_callback_reference_not_found_shop'] = 'Order reference not found in the shop.';
$string['novalnet_callback_reference_empty'] = 'Reference is empty, so not able to map the order.';
$string['privacy:metadata:paygw_novalnet_transaction_detail'] = 'Stores transaction details related to Novalnet payments.';
$string['privacy:metadata:paygw_novalnet_transaction_detail:userid'] = 'The ID of the user associated with the transaction.';
$string['privacy:metadata:novalnet'] = 'Shares the required user data with Novalnet for processing payments.';
$string['privacy:metadata:novalnet:first_name'] = 'First name of the user requesting a transaction';
$string['privacy:metadata:novalnet:last_name'] = 'The last name of the user requesting a transaction.';
$string['privacy:metadata:novalnet:email'] = 'Email of the user requesting a transaction.';
$string['privacy:metadata:novalnet:customer_ip'] = 'The IP address of the user sent to Novalnet.';
$string['privacy:metadata:novalnet:customer_no'] = 'The user ID related to the transaction sent to Novalnet.';
$string['privacy:metadata:novalnet:tel'] = 'The telephone number of the user sent to Novalnet.';
$string['privacy:metadata:novalnet:mobile'] = 'The mobile number of the user sent to Novalnet.';
$string['privacy:metadata:novalnet:gender'] = 'The gender of the user sent to Novalnet.';
$string['privacy:metadata:novalnet:birth_date'] = 'The birth date of the user sent to Novalnet.';
$string['privacy:metadata:novalnet:billing'] = 'The user’s billing information (including company name, address, and other details) sent to Novalnet.';

