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
 * Webhook for receiving events from Novalnet system.
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

define('NO_MOODLE_COOKIES', true);

use core_payment\helper;
use paygw_novalnet\novalnet_helper;

require_once(__DIR__ . '/../../../config.php');

/**
 * Novalnet Webhook API Class.
 *
 * This class is designed to handle the Novalnet webhook API. It listens for incoming webhook notifications
 * from Novalnet and processes them to ensure proper payment handling and status updates.
 *
 * The webhook allows the system to get notifications about various events related to payment transactions,
 * such as payment status changes, cancellations, refunds, etc. This class ensures the system responds accordingly.
 */
class novalnet_webhook {
    /**
     * Configuration value for payment settings.
     *
     * @var object
     */
    private $config;

    /**
     * The allowed hostname for Novalnet requests.
     *
     * @var string
     */
    private $novalnethostname = 'pay-nn.de';

    /**
     * Helper array contain various helper methods or configuration
     * values for processing payments with Novalnet.
     *
     * @var object
     */
    private $novalnethelper;

    /**
     * Data that contains transaction information.
     *
     * @var array
     */
    private $eventdata = [];

    /**
     * Type of the event received from Novalnet.
     *
     * @var string
     */
    private $eventtype;

    /**
     * Unique Transaction ID (TID) for the event received from Novalnet.
     *
     * This TID helps in tracking and referencing the specific transaction
     * associated with the event.
     *
     * @var string
     */
    private $eventtid;

    /**
     * Parent Transaction ID (TID) for the event, used when the
     * current transaction is related to a previously processed transaction.
     *
     * @var string
     */
    private $parenttid;

    /**
     * The Order ID of the current event.
     *
     * @var int
     */
    private $orderid;

    /**
     * The formatted amount of the current event as per the shop's settings.
     *
     * @var int
     */
    private $formattedamount;

    /**
     * The formatted date of the current event according to the shop's locale.
     *
     * @var string
     */
    private $formatteddate;

    /**
     * Order reference values.
     *
     * @var object
     */
    private $orderreference;

    /**
     * Payment reference values.
     *
     * @var array
     */
    private $paymentreference = [];

    /**
     * This property controls the decision to send an email notification to the customer.
     * If set to `true`, an email will be sent to the customer; otherwise, no email will be sent.
     *
     * @var bool
     */
    private $notifycustomer;

    /**
     * The Return response to Novalnet.
     *
     * @var array
     */
    private $response;

    /**
     * Flag indicating whether to append previous comments to the new order comments.
     *
     * @var bool $appendcomments
     */
    private $appendcomments = true;

    /**
     * Holds additional data used to update transaction records.
     *
     * @var array $updatedata
     */
    private $updatedata = [];

    /**
     * Mandatory Parameters.
     *
     * @var array
     */
    private $mandatory = [
        'event'       => [
            'type',
            'checksum',
            'tid',
        ],
        'merchant'    => [
            'vendor',
            'project',
        ],
        'result'      => [
            'status',
        ],
        'transaction' => [
            'tid',
            'payment_type',
            'status',
        ],
    ];

    /**
     * novalnet_webhook constructor.
     *
     * Initializes the novalnet_webhook class, typically used to handle webhook responses from Novalnet.
     */
    public function __construct() {
        $this->novalnethelper    = new novalnet_helper();
        $this->paymentreference = $this->get_novalnet_payment_reference();
        $this->config            = $this->get_novalnet_config();
        // Authenticate request host.
        $this->authenticate_event_data();

        // Set Event data.
        $this->eventtype       = $this->eventdata['event']['type'];
        $this->eventtid        = $this->eventdata['event']['tid'];
        $this->orderid         = ! empty ($this->eventdata['transaction']['order_no']) ?
            $this->eventdata['transaction']['order_no'] : null;
        $this->parenttid       = !empty($this->eventdata['event']['parent_tid']) ?
            $this->eventdata['event']['parent_tid'] : $this->eventtid;
        $this->formatteddate   = $this->novalnethelper->novalnet_formatted_date();

        if (isset ( $this->eventdata['transaction']['amount'] )) {
            $this->formattedamount = $this->novalnethelper->novalnet_shop_amount_format(
                $this->eventdata['transaction']['amount'], $this->eventdata['transaction']['currency'] );
        }
        // Get order reference.
        $this->get_order_reference();
        $this->start_process();
    }

    /**
     * Initiates the processing of the callback script.
     *
     * This function starts the process by performing necessary tasks
     * as defined in the callback script.
     *
     * @return void
     */
    private function start_process() {
        if ($this->novalnethelper->is_success_status( $this->eventdata ) &&
            ( !empty( $this->orderreference ) || $this->eventtype == 'PAYMENT')) {
            $this->notifycustomer = false;
            switch ( $this->eventtype ) {
                case 'PAYMENT':
                    if (empty( $this->orderreference )) {
                        $this->handle_communication_failure();
                    } else {
                        $this->display_message( [ 'message' => get_string(
                            'novalnet_callback_tid_existed', 'paygw_novalnet') ] );
                    }
                    break;
                case 'TRANSACTION_CAPTURE':
                case 'TRANSACTION_CANCEL':
                    $this->notifycustomer = true;
                    $this->handle_transaction_capture_cancel();
                    break;
                case 'TRANSACTION_REFUND':
                    $this->notifycustomer = true;
                    $this->handle_transaction_refund();
                    break;
                case 'TRANSACTION_UPDATE':
                    $this->handle_transaction_update();
                    break;
                case 'CREDIT':
                    $this->handle_credit();
                    break;
                case 'INSTALMENT':
                    $this->notifycustomer = true;
                    $this->handle_instalment();
                    break;
                case 'INSTALMENT_CANCEL':
                    $this->notifycustomer = true;
                    $this->handle_instalment_cancel();
                    break;
                case 'CHARGEBACK':
                    $this->handle_chargeback();
                    break;
                case 'PAYMENT_REMINDER_1':
                    $this->handle_payment_reminder( 1 );
                    $this->notifycustomer = true;
                    break;
                case 'PAYMENT_REMINDER_2':
                    $this->handle_payment_reminder( 2 );
                    $this->notifycustomer = true;
                    break;
                case 'SUBMISSION_TO_COLLECTION_AGENCY':
                    $this->submission_to_collection_agency();
                    $this->notifycustomer = true;
                    break;
                default:
                    $this->display_message(
                        [ 'message' => get_string( 'novalnet_callback_unhandled_event', 'paygw_novalnet', $this->eventtype) ]
                    );
            }

            if (! empty( $this->updatedata['update'] )) {
                $this->updatedata['update']->timemodified = time();
                $this->novalnethelper->insert_update_records('paygw_novalnet_transaction_detail',
                    $this->updatedata['update'], ['tid' => $this->orderreference->tid]);
            }

            if ($this->response) {
                $this->novalnethelper->update_comments( $this->response['message'],
                    $this->orderreference->tid, $this->notifycustomer, $this->appendcomments );
                $this->send_notification_mail(
                    ['message' => $this->response['message']]
                );
                $this->display_message( $this->response['message'] );
            } else {
                $this->display_message( ['message' => get_string( 'novalnet_callback_script_executed', 'paygw_novalnet')] );
            }
        } else if ($this->eventdata['transaction']['payment_type'] != 'ONLINE_TRANSFER_CREDIT') {
            $msgtxt = ! ( $this->novalnethelper->is_success_status( $this->eventdata ) ) ? 'novalnet_callback_status_invalid' :
                'novalnet_callback_script_executed';
            $this->display_message( [ 'message' => get_string( $msgtxt, 'paygw_novalnet') ] );
        }
    }

    /**
     * Authenticate the server request for Novalnet payment integration
     *
     * @return array
     */
    private function get_novalnet_request_data() {
        try {
            $jsoninput       = @file_get_contents('php://input');
            $requestdata     = $this->novalnethelper->novalnet_unserialize_data($jsoninput);

            if (empty( $requestdata ) || ! is_array( $requestdata )) {
                $this->display_message( ['message' => get_string( 'novalnet_callback_missing_necessary_parameter',
                    'paygw_novalnet')] );
            }
        } catch ( Exception $e ) {
            $this->display_message( ['message' => get_string('novalnet_callback_not_json_format', 'paygw_novalnet')] );
        }

        return $requestdata;
    }

    /**
     * Retrieves the payment reference data associated with the Novalnet gateway.
     *
     * @return object
     */
    private function get_novalnet_payment_reference() {
        $paymentreference = [];
        $this->eventdata  = $this->get_novalnet_request_data();
        $custom            = ! empty( $this->eventdata['custom'] ) ? $this->eventdata['custom'] : null;
        $event             = ! empty( $this->eventdata['event'] ) ? $this->eventdata['event'] : null;
        $fields            = 'component, paymentarea, itemid, userid';

        if (! empty( $custom )) {
            $paymentreference = ! empty( $custom['course_meta'] ) ? $custom['course_meta'] :
              ( ( $custom['input1'] && $custom['input1'] == 'course_meta' ) ? $custom['inputval1'] : null );
            $paymentreference = $paymentreference ?
                $this->novalnethelper->novalnet_unserialize_data($paymentreference, true) : null;
        }

        if (empty( $paymentreference ) && ! empty( $event )) {
            $tid = ! empty( $event['parent_tid'] ) ? $event['parent_tid'] : $event['tid'];
            $paymentreference = (array) $this->novalnethelper->fetch_records(
                'paygw_novalnet_transaction_detail', ['tid' => $tid], $fields );
        }

        if (empty( $paymentreference ) && ! empty( $this->eventdata['transaction'] ) &&
            ! empty( $this->eventdata['transaction']['order_no'] )) {
            $paymentreference = (array) $this->novalnethelper->fetch_records( 'paygw_novalnet_transaction_detail',
                ['orderid' => $this->eventdata['transaction']['order_no']], $fields );
        }

        if (! empty( $paymentreference ) && $paymentreference['component'] == 'enrol_fee' &&
            $paymentreference['paymentarea'] == 'fee') {
            global $DB;

            $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => 'fee', 'id' => $paymentreference['itemid']]);
            $paymentreference['courseid'] = ! empty( $courseid ) ? $courseid : null;
        }

        return (array)$paymentreference;
    }

    /**
     * Retrieves the payment configuration data associated with the Novalnet gateway.
     *
     * @return array
     */
    private function get_novalnet_config() {
        $config       = [];

        if (! empty( $this->eventdata['transaction'] ) && ! empty( $this->eventdata['transaction']['order_no'] )) {
            $paymentdata = $this->novalnethelper->fetch_records('payments',
                ['id' => $this->eventdata['transaction']['order_no']]);
            if ($paymentdata) {
                $config = helper::get_gateway_configuration( $paymentdata->component,
                    $paymentdata->paymentarea, $paymentdata->itemid, 'novalnet' );
            }
        }

        if (empty( $config ) && ! empty ( $this->paymentreference )) {
            $config = helper::get_gateway_configuration( $this->paymentreference['component'],
                $this->paymentreference['paymentarea'], $this->paymentreference['itemid'], 'novalnet' );
        }

        return $config;
    }

    /**
     * Authenticate server request
     *
     */
    private function authenticate_event_data() {
        // Host based validation.
        if (! empty( $this->novalnethostname )) {
            $novalnethostip = gethostbyname( $this->novalnethostname );
            // Authenticating the server request based on IP.
            $requestreceivedip = $this->get_ip_address( $novalnethostip );
            if (! empty( $novalnethostip ) && ! empty( $requestreceivedip )) {
                if ($novalnethostip !== $requestreceivedip && empty( $this->config['novalnet_callback_test_mode'] )) {
                    $this->display_message( ['message' => get_string( 'novalnet_callback_unauthorised_ip',
                        'paygw_novalnet', $requestreceivedip)] );
                }
            } else {
                $this->display_message( ['message' => get_string( 'novalnet_callback_host_recieved_ip_empty',
                    'paygw_novalnet')] );
            }
        } else {
            $this->display_message( ['message' => get_string( 'novalnet_callback_host_empty', 'paygw_novalnet')] );
        }

        $this->validate_event_data();
        $this->validate_checksum();
    }

    /**
     * Validate eventdata
     *
     */
    private function validate_event_data() {
        // Validate request parameters.
        foreach ($this->mandatory as $category => $parameters) {
            if (empty( $this->eventdata[$category] )) {
                // Could be a possible manipulation in the notification data.
                $this->display_message( ['message' => get_string( 'novalnet_callback_missing_category',
                    'paygw_novalnet', $category)] );
            } else if (! empty( $parameters )) {
                foreach ($parameters as $parameter) {
                    if (empty( $this->eventdata[$category][$parameter] )) {
                        // Could be a possible manipulation in the notification data.
                        $this->display_message(['message' => get_string( 'novalnet_callback_missing_parameter_category',
                            'paygw_novalnet', ['parameter' => $parameter, 'category' => $category])]);
                    } else if (in_array( $parameter, ['tid', 'parent_tid'], true ) &&
                        ! preg_match( '/^\d{17}$/', $this->eventdata[$category][$parameter] )) {
                        $this->display_message(
                            ['message' => get_string( 'novalnet_callback_missing_tid_category',
                                'paygw_novalnet', ['category' => $category, 'parameter' => $parameter])] );
                    }
                }
            }
        }
    }

    /**
     * Validate checksum
     *
     */
    private function validate_checksum() {
        $tokenstring = $this->eventdata['event']['tid'] . $this->eventdata['event']['type'] . $this->eventdata['result']['status'];

        if (isset( $this->eventdata['transaction']['amount'] )) {
            $tokenstring      .= $this->eventdata['transaction']['amount'];
        }
        if (isset( $this->eventdata['transaction']['currency'] )) {
            $tokenstring        .= $this->eventdata['transaction']['currency'];
        }

        if (! empty( $this->config['novalnet_key_password'] )) {
            $tokenstring .= strrev( $this->config['novalnet_key_password'] );
        }

        $generatedchecksum = hash( 'sha256', $tokenstring );

        if ($generatedchecksum !== $this->eventdata['event']['checksum']) {
            $this->display_message( ['message' => get_string( 'novalnet_callback_hash_check_failed',
                'paygw_novalnet')] );
        }

        if (! empty( $this->eventdata['custom']['shop_invoked'] )) {
            $this->display_message( ['message' => get_string( 'novalnet_callback_already_handled_shop',
                'paygw_novalnet')] );
        }
    }

    /**
     * Get the valid IP address.
     *
     * @param ADDR $novalnethostip Novalnet Host IP.
     * @return ADDR|array
     */
    private function get_ip_address($novalnethostip) {

        $ipkeys = [
            'HTTP_X_FORWARDED_HOST',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ipkeys as $key) {
            if (array_key_exists( $key, $_SERVER ) === true) {
                if (in_array( $key, ['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_HOST'], true )) {
                    $forwardedip = ! empty( $_SERVER[$key] ) ? explode( ',', $_SERVER[$key] ) : [];
                    return in_array( $novalnethostip, $forwardedip, true ) ? $novalnethostip : $_SERVER[$key];
                }
                return $_SERVER[$key];
            }
        }
    }

    /**
     * Get order reference.
     *
     * @return void
     */
    private function get_order_reference() {

        if (! empty( $this->orderid ) || ! empty( $this->parenttid )) {
            $this->orderreference = $this->novalnethelper->check_transaction_exists(['tid' => $this->parenttid]);

            if (! empty( $this->orderid ) && empty( $this->orderreference )) {
                $this->orderreference = $this->novalnethelper->check_transaction_exists(['orderid' => $this->orderid]);
            }
        }

        // Order id check.
        if (! empty( $this->orderreference ) && ! empty( $this->orderid ) && $this->orderreference->orderid !== $this->orderid) {
            $this->display_message( ['message' => get_string( 'novalnet_callback_reference_not_matching',
                'paygw_novalnet')] );
        }

        if (empty( $this->orderreference )) {
            if ('ONLINE_TRANSFER_CREDIT' === $this->eventdata['transaction']['payment_type']) {
                $transactionid = $this->eventdata['transaction']['tid'];
                // Update the transaction TID for updating the initial payment.
                $this->eventdata['transaction']['tid'] = $this->parenttid;
                $this->handle_communication_failure();
                // Reassign the transaction TID after the initial payment is updated.
                $this->eventdata['transaction']['tid'] = $transactionid;
                $this->orderreference = $this->novalnethelper->check_transaction_exists( ['tid' => $this->parenttid] );
            } else if ('PAYMENT' === $this->eventdata['event']['type']) {
                $this->handle_communication_failure();
            } else {
                $this->display_message( ['message' => get_string( 'novalnet_callback_reference_not_found_shop',
                    'paygw_novalnet')] );
            }
        }
    }

    /**
     * Complete the order in-case response failure from Novalnet server.
     *
     * @return void
     */
    private function handle_communication_failure() {
        if ( empty($this->paymentreference )) {
            $this->display_message( ['message' => get_string( 'novalnet_callback_reference_empty',
                'paygw_novalnet')] );
        }

        try {
            $message    = null;
            $issuccess = $this->novalnethelper->is_success_status( $this->eventdata );

            if (( $issuccess && $this->eventdata['transaction']['status'] == novalnet_helper::PAYMENT_STATUS_FAILURE )
                || ! $issuccess) {
                $message = $this->novalnethelper->handle_transaction_failure( $this->eventdata, $this->paymentreference, true );
            } else if ($issuccess) {
                $message = $this->novalnethelper->handle_transaction_success( $this->eventdata, $this->paymentreference, true );
                $this->novalnethelper->update_comments( $message, $this->eventdata['transaction']['tid'], true, false );
            }
            $this->send_notification_mail(['message' => $message]);
            $this->display_message( ['message' => $message] );
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Handle transaction capture/cancel
     *
     */
    private function handle_transaction_capture_cancel() {
        $this->appendcomments = false;
        $this->response['message'] = '';
        if ('TRANSACTION_CAPTURE' === $this->eventtype) {
            $this->response['message'] = get_string('novalnet_amount_capture',
                'paygw_novalnet', $this->formatteddate) . PHP_EOL . PHP_EOL;
        }

        if ((  $this->novalnethelper->get_supports( 'invoice_payments', $this->eventdata['transaction']['payment_type'] ) ||
            $this->eventdata['transaction']['payment_type'] == 'PREPAYMENT' ) &&
                empty( $this->eventdata['transaction']['bank_details'] )) {
            $this->eventdata['transaction']['bank_details'] = $this->novalnethelper->novalnet_unserialize_data(
                $this->orderreference->additionalinfo );
        }
        $this->response['message'] .= $this->novalnethelper->handle_transaction_success(
            $this->eventdata, $this->paymentreference, true );
    }

    /**
     * Handle transaction refund
     *
     */
    private function handle_transaction_refund() {
        if (! empty( $this->eventdata['transaction']['refund']['amount'] )) {
            $refundedamount = $this->eventdata['transaction']['refund']['amount'];
            $formattedamount = $this->novalnethelper->novalnet_shop_amount_format(
                $refundedamount, $this->eventdata['transaction']['currency'] );
            $this->response['message'] = get_string('novalnet_refund_message', 'paygw_novalnet',
                (object)[ 'ptid' => $this->parenttid, 'amount' => $formattedamount ]);

            if (! empty( $this->eventdata['transaction']['refund']['tid'] )) {
                $this->response['message'] .= '<br />' . get_string('novalnet_refund_tid_message',
                    'paygw_novalnet', $this->eventdata['transaction']['refund']['tid']);
            }

            // Update transaction details.
            $updatedata = new \stdClass();
            $updatedata->refundedamount = $this->orderreference->refundedamount + $refundedamount;
            $updatedata->gatewaystatus  = $this->eventdata['transaction']['status'];

            if ($this->novalnethelper->get_supports( 'instalment', $this->eventdata['transaction']['payment_type'] )) {
                $instalments = $this->novalnethelper->novalnet_unserialize_data( $this->orderreference->additionalinfo );
                foreach ($instalments as $key => $data) {
                    if (! empty( $data['tid'] ) && (int) $data['tid'] === (int) $this->eventdata['transaction']['tid']) {
                        if (strpos( $instalments[$key]['amount'], '.' )) {
                            $instalments[$key]['amount'] *= 100;
                        }
                        $instalments[$key]['amount'] -= $refundedamount;
                        $updatedata->additionalinfo   = $this->novalnethelper->novalnet_serialize_data( $instalments );
                    }
                }
            }

            if (empty( $this->orderreference->userunenroled ) && $this->orderreference->amount <= $updatedata->refundedamount) {
                // Unenrol user from a course.
                $this->novalnethelper->unenrol_user_from_course($this->eventdata, $this->paymentreference);
                $updatedata->userunenroled    = 1;
            }
            $this->updatedata['update'] = $updatedata;
        }
    }

    /**
     * Handle transaction update
     *
     */
    private function handle_transaction_update() {
        $this->appendcomments        = true;
        $this->response['message']   = null;
        $updatedata = new \stdClass();
        $updatedata->gatewaystatus    = $this->eventdata['transaction']['status'];
        $updatemessage = get_string( 'novalnet_callback_redirect_update_message', 'paygw_novalnet',
            (object)[ 'tid' => $this->eventtid, 'amount' => $this->formattedamount, 'date' => $this->formatteddate ] );

        if (in_array( $this->eventdata['transaction']['status'], ['PENDING', 'ON_HOLD', 'CONFIRMED', 'DEACTIVATED'], true )) {
            if (in_array( $this->eventdata['transaction']['update_type'], ['AMOUNT_DUE_DATE', 'DUE_DATE'], true )) {
                $updatedata->amount = $this->eventdata['transaction']['amount'];
                $formattedduedate  = $this->novalnethelper->novalnet_formatted_date( $this->eventdata['transaction']['due_date'] );
                $messagekey = ($this->eventdata['transaction']['payment_type'] === 'CASHPAYMENT')
                    ? 'novalnet_callback_cashpayment_message' : 'novalnet_callback_duedate_update_message';
                $this->response['message'] .= get_string( $messagekey, 'paygw_novalnet',
                    (object)[ 'amount' => $this->formattedamount, 'date' => $formattedduedate ] );
            } else if ($this->eventdata['transaction']['update_type'] === 'STATUS') {
                $this->appendcomments = false;

                if (( $this->novalnethelper->get_supports( 'invoice_payments', $this->eventdata['transaction']['payment_type'] ) ||
                    $this->eventdata['transaction']['payment_type'] == 'PREPAYMENT' ) &&
                        empty( $this->eventdata['transaction']['bank_details'] )) {
                    $this->eventdata['transaction']['bank_details'] = $this->novalnethelper->novalnet_unserialize_data(
                        $this->orderreference->additionalinfo );
                }

                if ('CASHPAYMENT' === $this->eventdata['transaction']['payment_type']) {
                    $this->eventdata['transaction']['nearest_stores'] = $this->novalnethelper->novalnet_unserialize_data(
                        $this->orderreference->additionalinfo );
                }

                if (in_array( $this->orderreference->gatewaystatus, ['PENDING', 'ON_HOLD'], true )) {
                    if ($this->eventdata['transaction']['status'] === 'ON_HOLD') {
                        $this->response['message'] .= PHP_EOL . get_string( 'novalnet_callback_update_onhold_message',
                            'paygw_novalnet', (object)['tid' => $this->eventtid, 'date' => $this->formatteddate] );
                        // Payment not yet completed, set transaction status to "AUTHORIZE".
                    } else if ($this->eventdata['transaction']['status'] === 'CONFIRMED') {
                        $this->response['message'] .= $updatemessage;
                    }
                }
                $this->response['message'] .= '</br>' . $this->novalnethelper->handle_transaction_success(
                    $this->eventdata, $this->paymentreference, true );
            } else {
                $updatedata->amount        = $this->eventdata['transaction']['amount'];
                $this->response['message'] = PHP_EOL . $updatemessage;
            }
        }
        $this->updatedata['update'] = $updatedata;
    }

    /**
     * Handle credit
     *
     */
    private function handle_credit() {
        $data = (object) [
            'parent_tid' => $this->parenttid,
            'amount'     => $this->formattedamount,
            'date'       => $this->formatteddate,
            'tid'        => ( $this->eventdata['transaction']['payment_type'] === 'ONLINE_TRANSFER_CREDIT' ) ?
                $this->parenttid : $this->eventtid,
        ];
        $comments = get_string( 'novalnet_callback_credit_message', 'paygw_novalnet', $data );

        if ($this->orderreference->paidamount < $this->orderreference->amount &&
            in_array($this->eventdata['transaction']['payment_type'],
                ['INVOICE_CREDIT', 'CASHPAYMENT_CREDIT', 'MULTIBANCO_CREDIT', 'ONLINE_TRANSFER_CREDIT'])) {
            $paidamount = $this->orderreference->paidamount + $this->eventdata['transaction']['amount'];
            $amounttobepaid = (int) $this->orderreference->amount - $this->orderreference->refundedamount;
            $updatedata = new \stdClass();
            $updatedata->gatewaystatus    = $this->eventdata['transaction']['status'];
            $updatedata->paidamount   = $paidamount;

            $this->response['message'] = $comments;
            if (( (int) $paidamount >= (int) $amounttobepaid )) {
                $updatedata->orderid = $this->novalnethelper->deliver_course($this->paymentreference);

                if ($updatedata->orderid) {
                    $orderupdate = [
                        'tid'        => $this->orderreference->tid,
                        'access_key' => $this->config['novalnet_key_password'],
                        'paymentid'  => $updatedata->orderid,
                    ];
                    $this->novalnethelper->handle_order_id_update($orderupdate);
                }

                if ((int) $paidamount > (int) $amounttobepaid) {
                    $this->response['message'] .= '<br />' . get_string( 'novalnet_credit_overpaid_message',
                        'paygw_novalnet' );
                }
            }
            $this->updatedata['update'] = $updatedata;
        } else if (in_array($this->eventdata['transaction']['payment_type'], ['CREDIT_ENTRY_SEPA', 'DEBT_COLLECTION_SEPA',
            'CREDIT_ENTRY_CREDITCARD', 'DEBT_COLLECTION_CREDITCARD', 'CREDITCARD_REPRESENTMENT', 'BANK_TRANSFER_BY_END_CUSTOMER',
            'GOOGLEPAY_REPRESENTMENT', 'APPLEPAY_REPRESENTMENT', 'DEBT_COLLECTION_DE', 'CREDIT_ENTRY_DE'])) {
            $this->response['message'] = $comments;
        } else {
            $this->response['message'] = get_string( 'novalnet_callback_already_paid', 'paygw_novalnet');
        }
    }

    /**
     * Handle instalment
     *
     */
    private function handle_instalment() {
        if ('CONFIRMED' === $this->eventdata['transaction']['status'] &&
            ! empty( $this->eventdata['instalment']['cycles_executed'] )) {
            $this->appendcomments       = false;
            $data = (object) [
                'ptid'   => $this->parenttid,
                'tid'    => $this->eventtid,
                'amount' => $this->formattedamount,
                'date'   => $this->formatteddate,
            ];

            $this->response['message'] = get_string( 'novalnet_callback_instalment_prepaid_message', 'paygw_novalnet', $data );
            $updatedata = new \stdClass();
            $updatedata->additionalinfo   = $this->novalnethelper->store_instalment_data_webhook($this->eventdata);

            if ('INSTALMENT_INVOICE' === $this->eventdata['transaction']['payment_type'] &&
                empty( $this->eventdata['transaction']['bank_details'] )) {
                $this->eventdata['transaction']['bank_details'] = $this->novalnethelper->novalnet_unserialize_data(
                    $this->orderreference->additional_info );
            }

            $this->response['message']  .= '<br />' . '<br />' . $this->novalnethelper->prepare_payment_comments($this->eventdata);
            $this->updatedata['update'] = $updatedata;
        }
    }

    /**
     * Handle instalment cancel
     *
     */
    private function handle_instalment_cancel() {
        if ('CONFIRMED' === $this->eventdata['transaction']['status'] &&
            'DEACTIVATED' !== (string) $this->orderreference->gatewaystatus) {
            $updatedata = new \stdClass();
            $updatedata->gatewaystatus    = 'DEACTIVATED';
            $instalments = $this->novalnethelper->fetch_records('paygw_novalnet_transaction_detail',
                ['tid' => $this->orderreference->tid], 'additionalinfo');
            $instalments = $this->novalnethelper->novalnet_unserialize_data($instalments->additionalinfo);
            $canceltype = $this->eventdata['instalment']['cancel_type'];

            if (! empty( $instalments )) {
                $instalments['is_instalment_cancelled'] = 1;
                $instalments['is_full_cancelled']       = 1;
                if (! empty( $canceltype )) {
                    $instalments['is_full_cancelled'] = ( 'ALL_CYCLES' === (string) $canceltype ) ? 1 : 0;
                }
                $updatedata->additionalinfo = $this->novalnethelper->novalnet_serialize_data( $instalments );
            }

            $messagekey = ( 'REMAINING_CYCLES' === (string) $canceltype ) ?
                'novalnet_callback_instalment_stopped_message' : 'novalnet_callback_instalment_cancelled_message';
            $this->response['message'] = get_string( $messagekey, 'paygw_novalnet',
                (object)['ptid' => $this->parenttid, 'date' => $this->formatteddate] );

            if (isset( $this->eventdata['transaction']['refund']['amount'] )) {
                $formattedamount = $this->novalnethelper->novalnet_shop_amount_format(
                    $this->eventdata['transaction']['refund']['amount'], $this->eventdata['transaction']['currency']);
                $this->response['message'] .= get_string( 'novalnet_callback_instalment_refund_message',
                    'paygw_novalnet', $formattedamount );
            }

            if (empty( $this->orderreference->userunenroled )) {
                // Unenrol user from a course.
                $this->novalnethelper->unenrol_user_from_course($this->eventdata, $this->paymentreference);
                $updatedata->userunenroled    = 1;
            }
            $this->updatedata['update'] = $updatedata;
        }
    }

    /**
     * Handle chargeback
     *
     */
    private function handle_chargeback() {
        if ($this->orderreference->gatewaystatus == 'CONFIRMED' && ! empty( $this->eventdata['transaction']['amount'] )) {
            $data = (object) [
                'ptid'   => $this->parenttid,
                'amount' => $this->formattedamount,
                'date'   => $this->formatteddate,
                'tid'    => $this->eventtid,
            ];
            $this->response['message']  = get_string( 'novalnet_chargeback_message', 'paygw_novalnet', $data );
        }
    }

    /**
     * Handle payment_reminders
     *
     * @param int $remindercount The number of reminder send to customer.
     */
    private function handle_payment_reminder($remindercount) {
        $this->response['message']  = get_string( 'novalnet_payment_reminder_message', 'paygw_novalnet', $remindercount );
    }

    /**
     * Handle Submission to Collection Agency
     *
     */
    private function submission_to_collection_agency() {
        $this->response['message']  = get_string( 'novalnet_collection_agency_message',
            'paygw_novalnet', $this->eventdata['collection']['reference'] );
    }

    /**
     * Print the Webhook messages.
     *
     * @param array $data The data.
     *
     * @return void
     */
    private function display_message($data) {
        echo json_encode($data);
        exit;
    }

    /**
     * Send notification mail.
     *
     * @param array $comments        Formed comments.
     */
    private function send_notification_mail($comments): void {
        if (empty( $comments['message'] )) {
            return; // Exit early if there's no message.
        }

        $emails = !empty( $this->config['novalnet_callback_emailtoaddr'] ) ?
            explode(',', $this->config['novalnet_callback_emailtoaddr']) : [];
        $mailsubject     = get_string( 'novalnet_callback_mail_subject', 'paygw_novalnet', ' ' );
        $user             = \core_user::get_noreply_user();
        $user->mailformat = 1; // HTML format.

        if (! empty ($this->eventdata['transaction']['order_no'])) {
            $mailsubject = get_string( 'novalnet_callback_mail_subject',
                'paygw_novalnet', $this->eventdata['transaction']['order_no']);
        }

        foreach ($emails as $email) {
            if (!empty( $email )) {
                $user->email = $email;
                email_to_user(
                    $user,
                    get_admin(),
                    $mailsubject,
                    $comments['message'],
                );
            }
        }
    }
}

new novalnet_webhook();
