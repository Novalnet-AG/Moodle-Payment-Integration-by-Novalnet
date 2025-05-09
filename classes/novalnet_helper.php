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
 * Contains helper class for working with Novalnet.
 *
 * File   novalnet_helper.php
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

use curl;
use moodle_exception;
use moodle_url;
use core_payment\helper;
use core_user;
use core_privacy\local\request\transform;
use core\output\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Class novalnet_helper
 *
 * Provides helper functions related to Novalnet payment integration.
 *
 */
class novalnet_helper {

    /**
     * Payport Endpoint URL.
     *
     * @var string
     */
    private $endpoint = 'https://payport.novalnet.de/v2/';

    /**
     * A string representing a new line and can be used to store a newline character.
     *
     * @var string
     */
    public $newline;

    /**
     * @var string version value of the current Novalnet payment plugin.
     */
    public const NOVALNET_VERSION = '1.0.0';

    /**
     * @var string Payment has been successfully authorized or captured for the order.
     */
    public const PAYMENT_STATUS_CONFIRMED = 'CONFIRMED';

    /**
     * @var string Payment is pending authorization or capture for the order.
     */
    public const PAYMENT_STATUS_PENDING = 'PENDING';

    /**
     * @var string Payment is currently on hold and awaiting further action for the order.
     */
    public const PAYMENT_STATUS_ON_HOLD = 'ON_HOLD';

    /**
     * @var string Payment method has been deactivated, preventing further transactions for the order.
     */
    public const PAYMENT_STATUS_DEACTIVATED = 'DEACTIVATED';

    /**
     * @var string Payment authorization or capture has failed for the order.
     */
    public const PAYMENT_STATUS_FAILURE = 'FAILURE';

    /**
     * Supported payment types based on process.
     *
     * @var array
     */
    private $supports = [
        'authorize'           => [
            'CREDITCARD',
            'APPLEPAY',
            'GOOGLEPAY',
            'DIRECT_DEBIT_SEPA',
            'PAYPAL',
            'INVOICE',
            'PREPAYMENT',
            'GUARANTEED_DIRECT_DEBIT_SEPA',
            'GUARANTEED_INVOICE',
            'INSTALMENT_INVOICE',
            'INSTALMENT_DIRECT_DEBIT_SEPA',
        ],
        'instalment'          => [
            'INSTALMENT_DIRECT_DEBIT_SEPA',
            'INSTALMENT_INVOICE',
        ],
        'guarantee'           => [
            'GUARANTEED_DIRECT_DEBIT_SEPA',
            'GUARANTEED_INVOICE',
        ],
        'pay_later'           => [
            'INVOICE',
            'PREPAYMENT',
            'CASHPAYMENT',
            'MULTIBANCO',
        ],
        'invoice_payments'    => [
            'INVOICE',
            'INSTALMENT_INVOICE',
            'GUARANTEED_INVOICE',
        ],
        'sepa_payments'       => [
            'DIRECT_DEBIT_SEPA',
            'INSTALMENT_DIRECT_DEBIT_SEPA',
            'GUARANTEED_DIRECT_DEBIT_SEPA',
        ],
        'due_date'           => [
            'DIRECT_DEBIT_SEPA',
            'PREPAYMENT',
            'INVOICE',
            'CASHPAYMENT',
        ],
        'form_type_payments' => [
            'DIRECT_DEBIT_SEPA',
            'CREDITCARD',
            'GUARANTEED_DIRECT_DEBIT_SEPA',
            'INSTALMENT_DIRECT_DEBIT_SEPA',
            'GUARANTEED_INVOICE',
            'INSTALMENT_INVOICE',
            'APPLEPAY',
            'GOOGLEPAY',
            'DIRECT_DEBIT_ACH',
            'MBWAY',
        ],
    ];

    /**
     * helper constructor.
     *
     */
    public function __construct() {
        $this->newline = '<br />';
    }

    /**
     * Returns the supported Novalnet payment based on process
     *
     * @param string $process      The process/feature.
     * @param string $paymenttype The payment type need to be checked.
     *
     * @return array
     */
    public function get_supports($process, $paymenttype = '') {

        if (! empty( $this->supports[$process] )) {
            if ('' !== $paymenttype) {
                return in_array( $paymenttype, $this->supports[$process], true );
            }
            return $this->supports[$process];
        }
        return [];
    }

    /**
     * Get action URL
     *
     * @param string $action the action.
     */
    public function get_action_endpoint($action = '') {
        return $this->endpoint . str_replace( '_', '/', $action );
    }

    /**
     * Perform unserialize data.
     *
     * @param string|null $data
     * @param bool $needasarray
     *
     * @return array|null
     */
    public function novalnet_unserialize_data($data, $needasarray = true) {
        if (empty($data)) {
            return null;
        }
        $result = json_decode($data, $needasarray, 512, JSON_BIGINT_AS_STRING);

        if (json_last_error() === 0) {
            return $result;
        }

        return $result ? $result : null;
    }

    /**
     * Perform serialize data.
     *
     * @param array $data
     *
     * @return string
     */
    public function novalnet_serialize_data(array $data) {
        $result = '{}';

        if (! empty($data)) {
            $result = json_encode($data, JSON_UNESCAPED_SLASHES);
        }
        return $result;
    }

    /**
     * Retrieves messages from server response.
     *
     * @param array $data The response data.
     *
     * @return string
     */
    public function novalnet_response_text($data) {
        if (! empty( $data['result']['status_text'] )) {
            return $data['result']['status_text'];
        }
        if (! empty( $data['status_text'] )) {
            return $data['status_text'];
        }
        return get_string( 'novalnet_payment_error', 'paygw_novalnet' );
    }

    /**
     * checks if the provided payment record contains the necessary variables
     * to ensure that the payment process is valid and complete.
     *
     * @param stdClass $record Stored Novalnet payment record containing the payment data.
     * @param string $params    contains payment record
     *
     * @throws moodle_exception If any required parameter is missing or invalid.
     */
    public function check_payment_record_variables($record, $params) {
        if (empty($record)) {
            throw new moodle_exception('err:assert:paymentrecord', 'paygw_novalnet');
        }

        if ($record['component'] != $params['component']) {
            throw new moodle_exception('err:validatetransaction:component', 'paygw_novalnet');
        }

        if ($record['paymentarea'] != $params['paymentarea']) {
            throw new moodle_exception('err:validatetransaction:paymentarea', 'paygw_novalnet');
        }

        if ($record['itemid'] != $params['itemid']) {
            throw new moodle_exception('err:validatetransaction:itemid', 'paygw_novalnet');
        }

        if ($record['userid'] != $params['userid']) {
            throw new moodle_exception('err:validatetransaction:userid', 'paygw_novalnet');
        }

        if ($record['component'] != $params['component'] &&
            $record['paymentarea'] != $params['paymentarea'] && $record['itemid'] != $params['itemid']) {
            throw new moodle_exception('err:assert:paymentrecordvariables', 'paygw_novalnet');
        }
    }

    /**
     * Check for the success status of the
     * Novalnet payment call.
     *
     * @param array $data The given array.
     *
     * @return boolean
     */
    public static function is_success_status($data) {
        return ( ( ! empty( $data['result']['status'] ) && 'SUCCESS' === $data['result']['status'] ) ||
            ( ! empty( $data['status'] ) && 'SUCCESS' === $data['status'] ) );
    }

    /**
     * Determine the redirect URL.
     *
     * @param string $component
     * @param string $paymentarea
     * @param string $itemid
     * @return moodle_url
     */
    public static function determine_redirect_url($component, $paymentarea, $itemid) {
        global $CFG, $DB;
        // Find redirection.
        $url = new moodle_url('/');
        // Method only exists in 3.11+.
        if (method_exists('\core_payment\helper', 'get_success_url')) {
            $url = helper::get_success_url($component, $paymentarea, $itemid);
        } else if ($component == 'enrol_fee' && $paymentarea == 'fee') {
            require_once($CFG->dirroot . '/course/lib.php');
            $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => 'fee', 'id' => $itemid]);
            if (!empty($courseid)) {
                $url = course_get_url($courseid);
            }
        }
        return $url;
    }

    /**
     * Store instalment data.
     *
     * @param array $data The instalment data.
     *
     * @return array
     */
    public function store_instalment_data($data) {
        if (! empty( $data['instalment'] )) {
            $transaction = $this->fetch_records('paygw_novalnet_transaction_detail',
                ['tid' => $data['transaction']['tid']], 'additionalinfo');
            $additionalinfo = ! empty( $transaction->additionalinfo ) ?
                $this->novalnet_unserialize_data( $transaction->additionalinfo ) : null;

            if (! empty( $additionalinfo ) && ! empty( $additionalinfo['instalment_total_amount'] )) {
                $data['instalment']['total_amount'] = $additionalinfo['instalment_total_amount'];
            }

            $instalment = $data['instalment'];
            if (! empty( $instalment['cycles_executed'] )) {
                $instalmentdetails['instalment_cycle_amount'] = $instalment['cycle_amount'];
                $instalmentdetails['instalment_total_cycles'] = count( $instalment['cycle_dates'] );
                $instalmentdetails['instalment_total_amount'] = $data['instalment']['total_amount'];
                $lastcycleamount = 0;
                for ($i = 1; $i <= $instalmentdetails['instalment_total_cycles']; $i++) {
                    $instalmentdetails['instalment' . $i] = [];
                    if (1 < $i && $i < $instalmentdetails['instalment_total_cycles']) {
                        $instalmentdetails['instalment' . $i]['amount'] = $instalment['cycle_amount'];
                    } else if ($i === $instalmentdetails['instalment_total_cycles']) {
                        $instalmentdetails['instalment' . $i]['amount'] = $data['instalment']['total_amount'] - $lastcycleamount;
                    }
                    $lastcycleamount += $instalment['cycle_amount'];
                    if (! empty( $instalment['cycle_dates'][$i + 1] )) {
                        $instalmentdates[] = $i . '-' . $instalment['cycle_dates'][$i + 1];
                    }

                    $cyclesexecuted = $instalment['cycles_executed'];
                    $instalmentkey = 'instalment' . $cyclesexecuted;
                    $instalmentdetails[$instalmentkey] = [
                        'tid'                  => $data['transaction']['tid'],
                        'paid_date'            => $instalment['cycle_dates'][$cyclesexecuted],
                        'next_instalment_date' => $instalment['cycle_dates'][$cyclesexecuted + 1],
                    ];

                    foreach ([
                        'instalment_cycles_executed' => 'cycles_executed',
                        'due_instalment_cycles'      => 'pending_cycles',
                        'amount'                     => 'cycle_amount',
                    ] as $key => $value) {
                        if (! empty( $instalment[$value] )) {
                            $instalmentdetails[$instalmentkey][$key] = $instalment[$value];
                            $instalmentdetails[$key] = $instalment[$value];
                        }
                    }
                }
                $instalmentdetails['future_instalment_dates'] = implode( '|', $instalmentdates );
            }
        }

        if (! empty( $instalmentdetails )) {
            return $this->novalnet_serialize_data($instalmentdetails);
        }
    }

    /**
     * Check for Valid checksum recevied from Novalnet server.
     *
     * @param string $data       The payment data.
     * @param string $txnsecret The txn secret.
     * @param string $accesskey The access key.
     *
     */
    public function is_valid_checksum($data, $txnsecret, $accesskey) {
        if (! empty( $data['checksum'] ) && ! empty( $data['tid'] ) &&
            ! empty( $data['status'] ) && ! empty( $txnsecret ) && ! empty( $accesskey )) {
            $checksum = hash( 'sha256', $data['tid'] . $txnsecret . $data['status'] . strrev( $accesskey ) );
            if ($checksum === $data['checksum']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save payment details with customer and course details.
     *
     * @param transaction details $transaction
     * @param update parameters $paymentrecord
     * @return void
     */
    private function save_transaction_details($transaction, $paymentrecord) {
        global $USER;

        $data = new \stdClass();
        $data->userid  = isset ( $transaction['customer']['customer_no'] ) ? $transaction['customer']['customer_no'] : $USER->id;
        $data->orderid = isset ( $transaction['transaction']['order_no'] ) ? $transaction['transaction']['order_no']
                            : (! empty( $paymentrecord['orderid'] ) ? $paymentrecord['orderid'] : null );
        $data->component = $paymentrecord['component'];
        $data->paymentarea = $paymentrecord['paymentarea'];
        $data->itemid = $paymentrecord['itemid'];
        $data->courseid = $paymentrecord['courseid'];
        $data->tid = $transaction['transaction']['tid'];
        $data->amount = isset ( $transaction['transaction']['amount'] ) ? $transaction['transaction']['amount'] : 0;
        $data->paidamount = isset ( $transaction['transaction']['amount'] ) ? $transaction['transaction']['amount'] : 0;
        $data->currency = isset ( $transaction['transaction']['currency'] ) ? $transaction['transaction']['currency'] : null;
        $data->paymenttype = $transaction['transaction']['payment_type'];
        $data->gatewaystatus = $transaction['transaction']['status'];
        $time = time();
        $data->timecreated = $time;
        $data->timemodified = $time;
        $data->transactioninfo = $transaction['transaction']['comments'];

        if ($this->get_supports( 'instalment', $transaction['transaction']['payment_type'] )) {
            $data->additionalinfo = $this->store_instalment_data( $transaction );
        }

        if ($this->get_supports( 'pay_later', $transaction['transaction']['payment_type'] ) ||
            in_array( $transaction['transaction']['payment_type'], ['GUARANTEED_INVOICE', 'INSTALMENT_INVOICE'] )) {
            if (! empty( $data->additionalinfo )) {
                $data->additionalinfo = $this->novalnet_serialize_data( $this->novalnet_unserialize_data(
                    $data->additionalinfo ) + $transaction['transaction']['bank_details'] );
            } else if (! empty( $transaction['transaction']['bank_details'] )) {
                $data->additionalinfo = $this->novalnet_serialize_data( $transaction['transaction']['bank_details'] );
            } else if (! empty( $transaction['transaction']['nearest_stores'] )) {
                $data->additionalinfo = $this->novalnet_serialize_data( $transaction['transaction']['nearest_stores'] );
            }
        }

        if (in_array( $transaction['transaction']['status'], ['PENDING', 'ON_HOLD'], true ) ||
            ! $this->is_success_status( $transaction )) {
            $data->paidamount = 0;
        }

        $this->insert_update_records('paygw_novalnet_transaction_detail', $data, ['tid' => $transaction['transaction']['tid']]);
    }

    /**
     * Unenrol the user from the course based on the payment reference.
     *
     * This function is triggered when a user should be unenrolled from
     * a course due to a specific event, such as a failed payment or cancellation.
     *
     * @param array $eventdata Data related to the event that triggered this function.
     * @param string $paymentreference A unique identifier for the payment transaction related to the user's enrollment.
     * @return void
     */
    public function unenrol_user_from_course(array $eventdata, array $paymentreference) {
        global $DB;

        $component = $itemid = null;

        $orderno = ! empty( $eventdata['transaction']['order_no'] ) ? $eventdata['transaction']['order_no'] : null;
        $userid  = ! empty( $eventdata['customer']['customer_no'] ) ? $eventdata['customer']['customer_no'] : null;

        if ($orderno) {
            $paymentdata = $this->fetch_records('payments', ['id' => $orderno]);
        }

        if (empty( $userid )) {
            $userid = ( ! empty( $paymentdata ) && ! empty( $paymentdata->userid ) ) ?
                $paymentdata->userid : $paymentreference['userid'];
        }

        if (! empty( $paymentdata )) {
            $component = $paymentdata->component ? $paymentdata->component : $paymentreference['component'];
            $itemid   = $paymentdata->itemid ? $paymentdata->itemid : $paymentreference['itemid'];
        }

        if (! empty( $component ) && ! empty( $itemid ) && $component == 'enrol_fee') {
            // A course was the product. Let's unenrol the user.
            $instance = $DB->get_record('enrol', ['enrol' => 'fee', 'id' => $itemid], '*', MUST_EXIST);
            $plugin = enrol_get_plugin('fee');
            $plugin->unenrol_user( $instance, $userid );
        }
    }

    /**
     * Sends a notification message to the user regarding their payment status.
     *
     * @param int $userto User ID to send the notification to
     * @param string $status The current payment status (e.g., 'success', 'failure', etc.)
     * @param array $data Optional data to be passed to get_string for message customization
     * @param string|null $message Optional custom message to override the default notification
     * @return void
     */
    public function notify_user(int $userto, string $status, array $data = [],  $message = null) {
        global $USER, $DB;

        $user      = $userto ? $userto : $USER->id;
        $userto    = $DB->get_record('user', ['id' => $user], '*', MUST_EXIST);
        $eventdata = new \core\message\message();
        $eventdata->courseid = SITEID;
        $eventdata->component = 'paygw_novalnet';
        $eventdata->name = 'payment_' . $status;
        $eventdata->notification = 1;
        $eventdata->userfrom = core_user::get_noreply_user();
        $eventdata->userto = $userto;
        $eventdata->subject = get_string('payment:' . $status . ':subject', 'paygw_novalnet');
        $eventdata->fullmessage = $message ? $message : get_string('payment:' . $status . ':message', 'paygw_novalnet');
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = '';
        $eventdata->smallmessage = '';

        if (isset($data['url'])) {
            $eventdata->contexturl = $data['url'];
        }
        message_send($eventdata);
    }

    /**
     * Deliver course
     *
     * @param string $transactionrecord
     * @return string
     */
    public function deliver_course($transactionrecord) {
        // Deliver course.
        $payable = helper::get_payable($transactionrecord['component'],
                $transactionrecord['paymentarea'], $transactionrecord['itemid']);
        $cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(),
                helper::get_gateway_surcharge('novalnet'));
        $paymentid = helper::save_payment($payable->get_account_id(),
                $transactionrecord['component'], $transactionrecord['paymentarea'],
                $transactionrecord['itemid'], $transactionrecord['userid'],
                $cost, $payable->get_currency(), 'novalnet');
        helper::deliver_order($transactionrecord['component'], $transactionrecord['paymentarea'],
                $transactionrecord['itemid'], $paymentid, $transactionrecord['userid']);

        return $paymentid;
    }

    /**
     * Forming header data for cURL call to send a request
     *
     * This function prepares and returns the necessary headers for an HTTP request
     * to be used with cURL, including the payment access key.
     *
     * @param string $paymentaccesskey The payment access key used for authorization.
     * @return array The array of headers for the cURL request.
     */
    public function get_header($paymentaccesskey) {
        $encodeddata        = base64_encode($paymentaccesskey);

        // Build the Headers array.
        $headers = [
            'Content-Type:application/json',
            'Charset:utf-8',
            'Accept:application/json',
            'X-NN-Access-Key:' . $encodeddata,
        ];

        return $headers;
    }

    /**
     * Forms transaction details to send a payment request.
     *
     * @param array $data which may include information about transaction
     *
     * @return array
     */
    public function get_transaction($data) {
        $endpoint = $this->get_action_endpoint('transaction_details');

        // Prepare the parameters.
        $params = [
            'transaction' => ['tid' => $data['tid']],
            'custom' => ['lang' => strtoupper(current_language())],
        ];

        // Send the request.
        $response = $this->send_request(json_encode($params), $endpoint, ['access_key' => $data['access_key']]);

        return $response;
    }

    /**
     * Update payment method if transactions id given
     *
     * @param  $orderupdate   The order details.
     *
     * @return array
     */
    public function handle_order_id_update($orderupdate) {
        $endpoint = $this->get_action_endpoint( 'transaction_update' );

        $parameters = [
            'transaction' => [
                'tid'      => $orderupdate['tid'],
                'order_no' => $orderupdate['paymentid'],
            ],
            'custom'      => [
                'lang'         => strtoupper(current_language()),
                'shop_invoked' => 1,
            ],
        ];

        // Send API request call.
        $response = $this->send_request(json_encode($parameters), $endpoint, ['access_key' => $orderupdate['access_key']]);

        return $response;
    }

    /**
     * Formating the date as per the shop structure.
     *
     * @param date $date The date value.
     *
     * @return string
     */
    public function novalnet_formatted_date($date = '') {
        if (! empty( $date )) {
            return transform::date(strtotime($date));
        }
        return transform::datetime(time());
    }

    /**
     * Get localised string of a cost
     *
     * @param float $cost
     * @param string $currency
     *
     * @return string
     */
    public function novalnet_shop_amount_format(?float $cost = null, ?string $currency = null): ?string {
        if ( empty( $cost ) && empty( $currency ) ) {
            return null;
        }

        $cost = $cost / 100;
        $locale = get_string('localecldr', 'langconfig');
        $fmt = \NumberFormatter::create($locale, \NumberFormatter::CURRENCY);
        return numfmt_format_currency($fmt, $cost, $currency);
    }

    /**
     * Converting the amount into cents
     *
     * @param float $amount The amount.
     *
     * @return int
     */
    public function novalnet_formatted_amount($amount) {
        return str_replace( ',', '', sprintf( '%0.2f', $amount ) ) * 100;
    }

    /**
     * Format the text.
     *
     * @param string $text The test value.
     *
     * @return int|boolean
     */
    public function novalnet_format_text($text) {
        return html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
    }

    /**
     * Get the due date for a given payment method based on the payment configuration.
     *
     * @param array $config The payment configuration array containing settings
     *                      related to payment methods and due dates.
     * @param string $payment The payment method for which the due date is to be fetched.
     *
     * @return string The due date corresponding to the provided payment method.
     */
    public function get_due_date($config, $payment) {
        $config   = (array) $config;
        $duedate = $config['novalnet_' . strtolower( $payment ) . '_due_date'];

        if (!empty( $duedate )) {
            $duedate = date('Y-m-d', strtotime('+'. $duedate .' days'));
        }
        return $duedate;
    }

    /**
     * Create a merchant data based on the payment configuration.
     *
     * @param \stdClass $config Configuration object containing merchant data request parameters
     * @return \stdClass Merchant data containing relevant merchant information
     */
    public function get_merchant_data($config) {
        $data = [
            'signature' => $config->novalnet_public_key,
            'tariff' => $config->novalnet_tariff_id,
        ];

        return $data;
    }

    /**
     * Create Customer data for a specific user.
     *
     * @param \stdClass $user
     * @return array Customer information
     */
    public function get_customer_data() {
        global $USER;

        $data = [
            'first_name'  => $USER->firstname,
            'last_name'   => $USER->lastname,
            'email'       => $USER->email,
            'customer_ip' => ! empty($USER->lastip) ? $USER->lastip : getremoteaddr(),
            'customer_no' => $USER->id,
            'tel'         => $USER->phone1,
            'mobile'      => $USER->phone2,
            'gender'      => 'u',
            'billing'     => [
                'street'       => !empty($USER->address) ? $USER->address : (!empty($USER->profile['address']) ?
                    $USER->profile['address'] : ' '),
                'city'         => $USER->city,
                'zip'          => isset($USER->zipcode) ? $USER->zipcode : (!empty($USER->profile['zipcode']) ?
                    $USER->profile['zipcode'] : '-'),
                'country_code' => $USER->country,
            ],
            'shipping' => [
                'same_as_billing'    => '1',
            ],
        ];

        if (isset($USER->company) || isset($USER->profile['company'])) {
            $data['billing']['company'] = isset($USER->company) ? $USER->company : (!empty($USER->profile['company']) ?
                $USER->profile['company'] : ' ');
        }

        if (isset($USER->state) || isset($USER->profile['state'])) {
            $data['billing']['state'] = isset($USER->state) ? $USER->state : (!empty($USER->profile['state']) ?
                $USER->profile['state'] : ' ');
        }

        if (isset($USER->birthdate) || isset($USER->profile['birthdate'])) {
            $data['birth_date'] = isset($USER->birthdate) ? $USER->birthdate : (!empty(trim($USER->profile['birthdate'])) ?
                $USER->profile['birthdate'] : ' ');
            $data['birth_date'] = date('Y-m-d', $data['birth_date']);
            unset($data['billing']['company']);
        }

        return array_filter($data);
    }

    /**
     * Retrieves the amount associated with a course.
     *
     * @param array $params Associative array containing course details
     *
     * @return float|int Returns the course amount, typically as a float or integer.
     */
    public function get_course_amount($params, $formattedamount = false) {
        $payable = helper::get_payable($params['component'], $params['paymentarea'], $params['itemid']);
        $currency = $payable->get_currency();
        $surcharge = helper::get_gateway_surcharge('novalnet');
        $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);

        if ($formattedamount) {
            $amount = helper::get_cost_as_string($amount, $currency, $surcharge );
        }
        return $amount;
    }

    /**
     * Retrieves the currency associated with a course.
     *
     * @param array $params Associative array containing course details
     *
     * @return string Returns the course currency.
     */
    public function get_course_currency($params) {
        $payable = helper::get_payable($params['component'], $params['paymentarea'], $params['itemid']);
        $currency = $payable->get_currency();

        return $currency;
    }

    /**
     * Create transaction data for a specific user.
     *
     * @param \stdClass $params A standard class object containing the necessary parameters
     *                          for retrieving transaction data (e.g., user ID, transaction type).
     * @return Transaction  Returns transaction data for the user.
     */
    public function get_transaction_data($params) {
        global $USER, $CFG;

        $urlparams = [
            'component' => $params['component'],
            'paymentarea' => $params['paymentarea'],
            'itemid' => $params['itemid'],
            'description' => $params['description'],
            'internalid' => $params['internalid'],
            'userid' => $USER->id,
        ];

        $returnurl = new moodle_url($CFG->wwwroot . '/payment/gateway/novalnet/return.php', $urlparams);
        $config = (object) helper::get_gateway_configuration($params['component'],
            $params['paymentarea'], $params['itemid'], 'novalnet');
        $amount = $this->get_course_amount($params);
        $testpaymentmethods = ! empty( $config->novalnet_selected_test_payments ) ?
            explode(',', $config->novalnet_selected_test_payments) : [];

        $data = [
            'test_mode'        => (int) in_array( $params['paymentmethodid'], $testpaymentmethods ),
            'payment_type'     => $params['paymentmethodid'],
            'amount'           => $this->novalnet_formatted_amount($amount),
            'currency'         => $this->get_course_currency($params),
            'return_url'       => $returnurl->out(false),
            'error_return_url' => $returnurl->out(false),
            'system_name'      => 'moodle',
            'system_url'       => $CFG->wwwroot,
            'system_version'   => $CFG->release . '-NN' . self::NOVALNET_VERSION,
        ];

        if ($this->get_supports( 'due_date', $params['paymentmethodid'])) {
            $data['due_dates'][$params['paymentmethodid']] = $this->get_due_date($config, $params['paymentmethodid']);
        }

        return $data;
    }

    /**
     * Creates the hosted page data based on the given payment method ID.
     *
     * @param int $paymentmethodid The ID of the payment method used for generating the hosted page data.
     * @return array Returns an data needed for the hosted page
     */
    public function get_hosted_page_data($paymentmethodid) {

        $data = [
            'display_payments' => [$paymentmethodid],
            'hide_blocks'      => ['ADDRESS_FORM', 'SHOP_INFO', 'LANGUAGE_MENU', 'HEADER', 'TARIFF'],
            'skip_pages'       => ['CONFIRMATION_PAGE', 'SUCCESS_PAGE'],
        ];

        if (! $this->get_supports( 'form_type_payments', $paymentmethodid)) {
            $data['skip_pages'] = ['CONFIRMATION_PAGE', 'SUCCESS_PAGE', 'PAYMENT_PAGE'];
        }

        return $data;
    }

    /**
     * Create custom data.
     *
     *
     * @param \stdClass $inputvars The input data, typically a user object or similar structure.
     * @return array The custom data
     */
    public function get_custom_data($input = []) {
        $data = [
            'lang'         => strtoupper(current_language()),
        ];

        if (! empty( $input )) {
            global $USER;

            $inputvars = [
                'component'   => $input['component'],
                'paymentarea' => $input['paymentarea'],
                'itemid'      => $input['itemid'],
                'userid'      => $USER->id,
            ];

            if (! empty( $inputvars )) {
                $data['input1'] = 'course_meta';
                $data['inputval1'] = json_encode( $inputvars );
            }
        }

        return $data;
    }

    /**
     * Create the installment data based on the configuration.
     *
     * @param \stdClass $payment The payment object containing payment-related information.
     * @param \stdClass $config Configuration data related to installment settings.
     *
     * @return Customer Returns an instance of the Customer class with the installment data.
     */
    public function get_instalment_data($payment, $config) {
        $config      = (array) $config;
        $selectedcycles = array_values($config['novalnet_' . strtolower($payment) . '_total_period']);

        // Instalment data.
        $data = [
            'preselected_cycle' => min($selectedcycles),
            'cycles_list' => $selectedcycles,
        ];

        return $data;
    }

    /**
     * Get the payment endpoint URL to send a request.
     *
     * @param object $config The configuration object containing necessary data
     *                       to generate the payment endpoint URL.
     * @param array $transaction Optional transaction data to modify or enrich
     *                           the payment endpoint URL. Defaults to an empty array.
     *
     * @return string  generates and returns the URL for the payment endpoint.
     */
    public function get_payment_endpoint(object $config, $transaction = []) {
        $action          = 'seamless_payment';
        $config          = (array)$config;
        $payment         = strtolower($transaction['payment_type']);
        $paymentaction  = $config["novalnet_{$payment}_payment_action"];
        $authorizelimit = $config["novalnet_{$payment}_authorize_limit"];

        if ($this->get_supports( 'authorize', $transaction['payment_type'] ) && $paymentaction == 'authorize') {
            if (empty( $authorizelimit ) || ! $this->is_valid_digit( (int) $authorizelimit ) ||
                ( (int) $transaction['amount'] >= $authorizelimit )) {
                $action  = 'seamless_authorize';
            }
        }

        return $this->get_action_endpoint( $action );
    }

    /**
     * Removing / unset the gateway used sessions.
     *
     * @return void.
     */
    public function novalnet_unset_payment_session() {
        global $SESSION;

        if (! empty( $SESSION->novalnet )) {
            unset($SESSION->novalnet);
        }
    }

    /**
     * Validates the given input data is numeric or not.
     *
     * @param int $input The input value.
     *
     * @return boolean
     */
    public function is_valid_digit($input) {
        return (bool) ( preg_match( '/^[0-9]+$/', $input ) );
    }

    /**
     * Generates the payment parameters for a transaction.
     *
     * @param array $params An associative array containing the required transaction details.
     *
     * @return array An array containing the generated payment parameters.
     */
    public function initiate_payment_process(array $params) {
        $config = (object) helper::get_gateway_configuration($params['component'],
            $params['paymentarea'], $params['itemid'], 'novalnet');
        $data = [];
        $data['merchant']    = $this->get_merchant_data($config);
        $data['customer']    = $this->get_customer_data();
        $data['transaction'] = $this->get_transaction_data($params);
        $data['hosted_page'] = $this->get_hosted_page_data($params['paymentmethodid']);
        $data['custom']      = $this->get_custom_data($params);

        if ($this->get_supports( 'instalment', $data['transaction']['payment_type'] )) {
            $data['instalment'] = $this->get_instalment_data($data['transaction']['payment_type'], $config);
        } else if ($data['transaction']['payment_type'] == 'PAYPAL' && ! empty( $params['description'] )) {
            $data['cart_info']['line_items'][] = [
                'name'        => $params['description'],
                'price'       => $data['transaction']['amount'],
                'quantity'    => '1',
                'category'    => 'digital',
            ];
        }

        $endpoint  = $this->get_payment_endpoint($config, $data['transaction']);
        $response  = $this->send_request(json_encode( $data ), $endpoint, ['access_key' => $config->novalnet_key_password]);

        return $response;
    }

    /**
     * Handles the transaction success process for completing an order.
     *
     * @param array    $serverresponse The response data received from the payment server.
     * @param object   $paymentrecord        The order object that is being processed.
     * @param bool     $iswebhook      A flag to indicate if the request is a webhook (default is false).
     *
     * @return array|string Returns an array with transaction details or an error message if the process fails.
     */
    public function handle_transaction_success($serverresponse, $paymentrecord, $iswebhook = false) {
        $comments = null;
        $config = (object) helper::get_gateway_configuration($paymentrecord['component'],
            $paymentrecord['paymentarea'], $paymentrecord['itemid'], 'novalnet');

        // Now finally, perform actual order delivery if paid.
        switch ( $serverresponse['transaction']['status'] ) {
            case self::PAYMENT_STATUS_DEACTIVATED:
                $comments .= get_string('novalnet_deactivated_message', 'paygw_novalnet',
                    $this->novalnet_formatted_date()). $this->newline . $this->newline;
                break;
            case self::PAYMENT_STATUS_PENDING:
                break;
            case self::PAYMENT_STATUS_ON_HOLD:
                break;
            case self::PAYMENT_STATUS_CONFIRMED:
                $paymentid = $this->deliver_course($paymentrecord);
                if ($paymentid) {
                    $orderupdate = [
                        'tid' => $serverresponse['transaction']['tid'],
                        'access_key' => $config->novalnet_key_password,
                        'paymentid' => $paymentid,
                    ];

                    $response = $this->handle_order_id_update($orderupdate);
                    if ($response['transaction']['order_no']) {
                        $serverresponse['transaction']['order_no'] = $response['transaction']['order_no'];
                    }

                    if ('ONLINE_TRANSFER_CREDIT' === $serverresponse['transaction']['payment_type']) {
                        $serverresponse['transaction']['payment_type'] = $response['transaction']['payment_type'];
                    }

                    if (isset($response['transaction']['invoice_ref']) && !empty($response['transaction']['invoice_ref'])) {
                        $serverresponse['transaction']['invoice_ref'] = $response['transaction']['invoice_ref'];
                    }
                    $paymentrecord['orderid'] = $paymentid;
                }
                break;
        }

        // Form order comments.
        $comments .= $this->prepare_payment_comments( $serverresponse );
        $serverresponse['transaction']['comments'] = $comments;
        $this->save_transaction_details($serverresponse, $paymentrecord);

        if (! empty( $iswebhook )) {
            return $comments;
        }

        $this->novalnet_unset_payment_session();
    }

    /**
     * Handles transaction failure by canceling the order
     * and redirecting the user to the checkout page with an error message.
     *
     * @param array  $serverresponse The response data received from the payment gateway server.
     * @param object $paymentrecord   The order object representing the payment transaction.
     * @param bool   $iswebhook      Indicates if the request is coming from a webhook (default is false).
     *
     * @return array An array containing the result of the transaction handling process.
     */
    public function handle_transaction_failure($serverresponse, $paymentrecord, $iswebhook = false) {
        $error = $this->novalnet_response_text( $serverresponse );
        $message = get_string('payment:failed:message', 'paygw_novalnet', $error);

        if (! $iswebhook) {
            // Notify user payment failed.
            $comments = str_replace('<br />', PHP_EOL, PHP_EOL . $this->form_comments( $serverresponse, true ));
            $this->synchronize_payment_status($serverresponse['transaction'],
                $paymentrecord, '<br />' . $comments, $error, $iswebhook );
        }
        $this->novalnet_unset_payment_session();

        return $message;
    }

    /**
     * Get Store instalment data.
     *
     * @param array $instalment.
     *
     * @return array
     */
    public function get_stored_instalment_data($instalment) {

        $instalmentrows    = [];
        $haspendingcycles = false;

        if (! empty( $instalment ) && ! empty( $instalment['instalment_total_cycles'] )) {

            $futuredates = explode( '|', $instalment['future_instalment_dates'] );
            foreach ($futuredates as $futuredate) {
                $datearray = explode( '-', $futuredate );
                $cycle      = $datearray['0'];
                unset( $datearray['0'] );
                $futureinstalmentdates[$cycle] = implode( '-', $datearray );
            }

            for ($i = 1; $i <= $instalment['instalment_total_cycles']; $i++) {
                $instalmentrows[$i] = [
                    'status' => ( ! empty( $instalment['is_instalment_cancelled'] ) &&
                        1 === (int) $instalment['is_instalment_cancelled'] ) ? 'cancelled' : 'pending',
                ];
                $instalmentrows[$i]['amount'] = 0;
                if (1 === $i && ! empty( $instalment['instalment']['amount'] )) {
                    $instalmentrows[$i]['amount'] = $instalment['instalment']['amount'];
                } else if (! empty( $instalment["instalment$i"]['amount'] )) {
                    $instalmentrows[$i]['amount'] = $instalment["instalment$i"]['amount'];
                }

                if (! empty( $instalment["instalment$i"]['paid_date'] )) {
                    $instalmentrows[$i]['amount'] = $instalmentrows[$i]['amount'];
                }
                if (! empty( $instalment["instalment$i"]['tid'] )) {
                    $instalmentrows[$i]['tid']    = $instalment["instalment$i"]['tid'];
                    $instalmentrows[$i]['status'] = 'completed';
                    if (! empty( $instalment['is_full_cancelled'] ) && ( 1 === (int) $instalment['is_full_cancelled'] )) {
                        $instalmentrows[$i]['amount'] = 0;
                    }
                } else {
                    $haspendingcycles = true;
                }

                $instalmentrows[$i]['date']        = ( ! empty( $futureinstalmentdates[$i] ) ) ?
                    $this->novalnet_formatted_date( $futureinstalmentdates[$i] ) : '';
                $instalmentrows[$i]['status_text'] = get_string($instalmentrows[$i]['status'], 'paygw_novalnet');
            }

            if (! empty( $instalment['is_instalment_cancelled'] )) {
                $instalmentrows['is_full_cancelled']       = $instalment['is_full_cancelled'];
                $instalmentrows['is_instalment_cancelled'] = $instalment['is_instalment_cancelled'];
            }
            $instalmentrows['has_pending_cycle'] = $haspendingcycles;
        }

        return $instalmentrows;
    }

    /**
     * Prepare the Novalnet transaction comments.
     *
     * @param array $data The data.
     * @return array
     */
    public function prepare_payment_comments($data) {
        $comments = $this->form_comments( $data );
        if ('PENDING' === $data['transaction']['status'] &&
            in_array( $data['transaction']['payment_type'], [ 'GUARANTEED_INVOICE', 'INSTALMENT_INVOICE' ], true )) {
            $comments .= $this->newline . $this->newline . get_string('guarantee_pending_text', 'paygw_novalnet');
        } else if ('PENDING' === $data['transaction']['status'] &&
            in_array( $data['transaction']['payment_type'],
            [ 'GUARANTEED_DIRECT_DEBIT_SEPA', 'INSTALMENT_DIRECT_DEBIT_SEPA' ], true )) {
            $comments .= $this->newline . $this->newline . get_string('sepa_guarantee_pending_text', 'paygw_novalnet');
        } else if (! empty( $data['transaction']['bank_details'] ) && ! empty( $data['transaction']['amount'] ) &&
            empty( $data['instalment']['prepaid'] )) {
            $comments .= $this->form_amount_transfer_comments( $data );
        } else if (! empty( $data['transaction']['nearest_stores'] )) {
            $comments .= $this->form_nearest_store_comments( $data );
        } else if (! empty( $data['transaction']['partner_payment_reference'] )) {
            $formattedamount = $this->novalnet_shop_amount_format($data['transaction']['amount'], $data['transaction']['currency']);
            $comments .= $this->newline . get_string('multibanco_reference_text', 'paygw_novalnet', $formattedamount );
            $comments .= $this->newline . get_string('multibanco_partner_reference', 'paygw_novalnet',
                $data['transaction']['partner_payment_reference'] );

            if (! empty( $data['transaction']['service_supplier_id'] )) {
                $comments .= $this->newline . get_string('multibanco_entity_reference', 'paygw_novalnet',
                    $data['transaction']['service_supplier_id'] ) . $this->newline;
            }
        }

        return $comments;
    }

    /**
     * Form payment comments.
     *
     * @param array   $data The comment data.
     * @param boolean $iserror The error.
     *
     * @return string
     */
    public function form_comments($data, $iserror = false) {
        $comments = '';

        if ($this->is_success_status( $data ) && in_array( $data['transaction']['payment_type'],
            ['GOOGLEPAY', 'APPLEPAY'], true )) {
            $payment = ( 'GOOGLEPAY' === $data['transaction']['payment_type'] ) ? 'Google Pay' : 'Apple Pay';
            $cardmask = isset( $data['transaction']['payment_data']['card_brand'] ) ? sprintf(
                '(%1$s ****%2$s)',
                strtolower(
                    $data['transaction']['payment_data']['card_brand']
                ),
                $data['transaction']['payment_data']['last_four']
            ) : '';
            $comments .= get_string('wallet_card_info', 'paygw_novalnet', $payment. ' ' .$cardmask) . $this->newline;
        }

        $paymentname = get_string($data['transaction']['payment_type'], 'paygw_novalnet');
        $comments .= get_string('novalnet_payment_name', 'paygw_novalnet', $paymentname ). $this->newline;
        if (! empty( $data['transaction']['tid'] )) {
            $comments .= get_string('novalnet_transaction_id', 'paygw_novalnet', $data['transaction']['tid'] ). $this->newline;
            if (! empty( $data['transaction']['test_mode'] )) {
                $comments .= get_string('test_order_text', 'paygw_novalnet'). $this->newline;
            }
        }
        if ($iserror) {
            $comments .= $this->newline . get_string('payment:failed:message', 'paygw_novalnet',
                $this->novalnet_response_text( $data ));
        }
        return $comments;
    }

    /**
     * Form Bank details comments.
     *
     * @param array $input     The input data.
     *
     * @return string
     */
    public function form_amount_transfer_comments($input) {
        $orderamount = $input['transaction']['amount'];
        if (! empty( $input['instalment']['cycle_amount'] )) {
            $orderamount = $input['instalment']['cycle_amount'];
        }
        $formattedamount = $this->novalnet_shop_amount_format($orderamount, $input['transaction']['currency']);
        $formattedduedate = $this->novalnet_formatted_date( $input['transaction']['due_date'] );

        if (in_array( $input['transaction']['status'], ['CONFIRMED', 'PENDING'], true ) &&
            ! empty( $input['transaction']['due_date'] )) {
            $comments = $this->newline . $this->newline . get_string('invoice_payment_bank_text',
                'paygw_novalnet', $formattedamount ) . get_string('bank_with_due_date_text', 'paygw_novalnet',
                $formattedduedate )  . $this->newline . $this->newline;

            if (! empty( $input['instalment']['cycle_amount'] )) {
                $comments = $this->newline . $this->newline . get_string('instalment_payment_bank_text', 'paygw_novalnet',
                    $formattedamount ) . get_string('bank_with_due_date_text', 'paygw_novalnet',
                    $formattedduedate )  . $this->newline . $this->newline;
            }
        } else {
            $comments = $this->newline . $this->newline . get_string('invoice_payment_bank_text',
                'paygw_novalnet', $formattedamount ) . get_string('bank_without_due_date_text',
                'paygw_novalnet' )  . $this->newline . $this->newline;
            if (! empty( $input['instalment']['cycle_amount'] )) {
                $comments = $this->newline . $this->newline . get_string('instalment_payment_bank_text',
                    'paygw_novalnet', $formattedamount ) . get_string('bank_without_due_date_text',
                    'paygw_novalnet' )  . $this->newline . $this->newline;
            }
        }

        foreach ([
            'account_holder' => 'account_owner',
            'bank_name'      => 'bank_name',
            'bank_place'     => 'bank_place',
            'iban'           => 'bank_iban',
            'bic'            => 'bank_bic',
        ] as $key => $text) {
            if (! empty( $input['transaction']['bank_details'][$key] )) {
                $comments .= get_string($text, 'paygw_novalnet', $input['transaction']['bank_details'][$key] ) . $this->newline;
            }
        }
        $comments .= get_string('multiple_reference_text', 'paygw_novalnet') . $this->newline;
        $comments .= get_string('reference_text1', 'paygw_novalnet', $input['transaction']['tid'] ) . $this->newline;

        if (! empty( $input['transaction']['invoice_ref'] )) {
            $comments .= get_string('reference_text2', 'paygw_novalnet', $input['transaction']['invoice_ref'] ) . $this->newline;
        }

        return $this->novalnet_format_text( $comments );
    }

    /**
     * Generates the nearest store comments.
     *
     * @param array $data The comment data to be processed.
     *
     * @return string The HTML markup of the form populated with the comment data.
     */
    public function form_nearest_store_comments($data) {
        $neareststores = $data['transaction']['nearest_stores'];
        $countries      = get_string_manager()->get_list_of_countries();
        $comments       = '';

        if (! empty( $data['transaction']['due_date'] )) {
            $comments .= $this->newline . get_string('slip_expiry_date', 'paygw_novalnet',
                $this->novalnet_formatted_date( $data['transaction']['due_date'] ) );
        }
        $comments .= $this->newline . $this->newline . get_string('cash_payment_stores',
            'paygw_novalnet'). $this->newline . $this->newline;

        foreach ($neareststores as $neareststore) {
            foreach ([
                'store_name',
                'street',
                'city',
                'zip',
            ] as $addressdata) {
                if (! empty($neareststore[$addressdata])) {
                    $comments .= $neareststore[$addressdata] . $this->newline;
                }
            }

            if (! empty($neareststore['country_code']) && !empty($countries[$neareststore['country_code']])) {
                $comments .= $countries[$neareststore['country_code']];
                $comments .= $this->newline . $this->newline;
            }
        }
        return $comments;
    }

    /**
     * Insert or update data in the specified database table.

     * @param string $table The name of the table
     * where data will be inserted or updated.
     * @param array $updatedata The data to be inserted or updated in the table.
     * @param array $condition (optional) An associative array that defines the condition
     * for updating an existing record.
     *
     * @return void This method does not return anything.
     */
    public function insert_update_records($table, $updatedata, array $condition = []) {
        global $DB;

        $data = ! empty( $condition ) ? $this->fetch_records($table, $condition) : null;

        if (! empty( $data ) && ! empty( $updatedata )) {
            $update = (object) array_merge((array) $data, (array) $updatedata);

            if ($update) {
                $DB->update_record($table, (object)$update);
                return;
            }
        }

        return $DB->insert_record($table, $updatedata);
    }

    /**
     * Fetches existing Novalnet transaction records based on the provided conditions.
     *
     * @param string $table The name of the table from which to fetch the transaction records.
     * @param array $condition An associative array containing the conditions to filter the records.
     * @param string $fields A comma-separated list of fields to select. Defaults to '*' (all fields).
     *
     * @return null|Transaction Returns a Transaction object
     * if records are found, or null if no matching records are found.
     */
    public function fetch_records($table, array $condition, $fields = '*') {
        global $DB;

        $result = $DB->get_records($table, $condition, 'id DESC', $fields, 0, 1);

        return ! empty( $result ) ? reset( $result ) : null;
    }

    /**
     * check novalnet transaction already is exists.
     *
     * @param array $condition
     *
     * @return null|Transaction
     */
    public function check_transaction_exists(array $condition) {
        global $DB;

        $transactionrecord = $DB->get_records('paygw_novalnet_transaction_detail', $condition, 'id DESC', '*', 0, 1);

        return ! empty( $transactionrecord ) ? reset( $transactionrecord ) : null;
    }

    /**
     * Fetches information related to a Novalnet transaction, including its comments.
     *
     * @param int $tid The transaction ID to search for.
     *
     * @return null|Transaction Returns a Transaction object
     * with the details if found, or null if not.
     */
    public function fetch_transaction_info_comments($tid) {
        $comments = null;
        $transactionrecord = $this->fetch_records('paygw_novalnet_transaction_detail', ['tid' => $tid]);

        if (!empty($transactionrecord->transactioninfo)) {
            $comments = \html_writer::start_tag('div', ['class' => 'card card-body d-inline-block w-100 mb-3']);
            $comments .= \html_writer::tag('p', $transactionrecord->transactioninfo);
            $comments .= \html_writer::end_tag('div');
        }

        return $comments;
    }

    /**
     * Check if a Novalnet transaction exists and fetch installment cycle comments.
     *
     * @param int $tid The transaction ID to check for the existence of Novalnet transaction.
     *
     * @return null|Transaction Returns the transaction object if found, or null if no transaction exists for the given ID.
     */
    public function fetch_instalment_cycle_comments($tid) {
        $instalmenttable = null;
        $transactionrecord = $this->fetch_records('paygw_novalnet_transaction_detail', ['tid' => $tid]);
        $instalment = $this->get_stored_instalment_data($this->novalnet_unserialize_data($transactionrecord->additionalinfo, true));
        if (!empty($instalment)) {
            $instalmenttable = \html_writer::start_tag('div', ['class' => 'card card-body d-inline-block w-100 mb-3']);
            $instalmenttable .= \html_writer::tag('h4', get_string('instalmentheading', 'paygw_novalnet'), ['class' => 'lead']);

            $table = new \html_table();
            $table->head = [
                get_string('instalmentsno', 'paygw_novalnet'),
                get_string('instalmenttid', 'paygw_novalnet'),
                get_string('instalmentamount', 'paygw_novalnet'),
                get_string('instalmentnextdate', 'paygw_novalnet'),
                get_string('instalmentstatus', 'paygw_novalnet'),
            ];

            $table->data = [];

            foreach ($instalment as $cycle => $instalment) {
                if (! is_array( $instalment )) {
                    continue;
                }

                if ($transactionrecord->amount == $transactionrecord->refundedamount || 0 === $instalment['amount']) {
                    $instalment['status']      = 'refunded';
                    $instalment['status_text'] = get_string('refunded', 'paygw_novalnet');
                    if (empty( $instalment['tid'] )) {
                        $instalment['status']      = 'cancelled';
                        $instalment['status_text'] = get_string('cancelled', 'paygw_novalnet');
                    }
                }

                $row = [
                    $cycle,
                    ! empty( $instalment['tid'] ) ? $instalment['tid'] : '-',
                    $this->novalnet_shop_amount_format($instalment['amount'], $transactionrecord->currency),
                    ! empty( $instalment['date'] ) ? $instalment['date'] : '-',
                    $instalment['status_text'],
                ];

                if ($row != null) {
                    $table->data[] = $row;
                }
            }

            $instalmenttable .= \html_writer::table($table);
            $instalmenttable .= \html_writer::end_tag('div');
        }
        return $instalmenttable;
    }

    /**
     * Updates the comments for a transaction and optionally notifies the customer.
     *
     * @param string $message The comment/message to be added to the transaction.
     * @param string $tid The unique id for the transaction.
     * @param bool $notifycustomer Whether or not to notify the customer about the updated comment (default is false).
     * @param bool $appendcomments Whether to append the comment to existing ones or replace them (default is true).
     *
     * @return null|Transaction Returns the updated transaction object
     * or null if the operation failed.
     */
    public function update_comments($message, $tid, $notifycustomer = false, $appendcomments = true) {
        $comments = $message;
        if ($appendcomments) {
            $nncomments = $this->fetch_records('paygw_novalnet_transaction_detail',
                ['tid' => $tid], 'transactioninfo')->transactioninfo;
            if (! empty( $nncomments )) {
                $comments = $nncomments . $this->newline . $this->newline . $message;
            }
        }

        // Update transaction info.
        $updatedata = new \stdClass();
        $updatedata->transactioninfo = $comments;
        $this->insert_update_records('paygw_novalnet_transaction_detail', $updatedata, ['tid' => $tid]);

        $instalment = $this->fetch_instalment_cycle_comments($tid);
        if (!empty( $instalment )) {
            $comments .= $instalment;
        }

        // Notify customer if required.
        if ($notifycustomer) {
            $transaction = $this->fetch_records( 'paygw_novalnet_transaction_detail', ['tid' => $tid] );
            $this->synchronize_payment_status( (array)$transaction, null, null, null, true );
        }
    }

    /**
     * Store Instalment Data from Webhook
     *
     * This function processes the data received via a webhook and stores the instalment information.
     *
     * @param array $data  The data received from the webhook.
     *
     * @return bool Returns true if the data was successfully processed
     * and stored, otherwise false.
     */
    public function store_instalment_data_webhook($data) {
        $instalmentdetails = $this->fetch_records('paygw_novalnet_transaction_detail',
            ['tid' => $data['event']['parent_tid']], 'additionalinfo');
        $instalmentdetails = $this->novalnet_unserialize_data($instalmentdetails->additionalinfo);

        if (! empty( $instalmentdetails )) {
            $cyclesexecuted                                    = $data['instalment']['cycles_executed'];
            $instalment['tid']                                 = $data['transaction']['tid'];
            $instalment['amount']                              = $data['instalment']['cycle_amount'];
            $instalment['paid_date']                           = date( 'Y-m-d H:i:s' );
            $instalment['next_instalment_date']                = ( ! empty( $data['instalment']['next_cycle_date'] ) ) ?
                $data['instalment']['next_cycle_date'] : '';
            $instalment['instalment_cycles_executed']          = $cyclesexecuted;
            $instalment['due_instalment_cycles']               = $data['instalment']['pending_cycles'];
            $instalmentdetails['instalment' . $cyclesexecuted] = $instalment;
        }

        return $this->novalnet_serialize_data( $instalmentdetails );
    }

    /**
     * Synchronizes the payment status between the transaction and the payment record.
     *
     * @param object $transaction The transaction object that needs to be updated.
     * @param object $paymentrecord The payment record containing the payment status.
     *
     * @return null|Transaction Returns null if no changes were made. Returns the
     * updated transaction object if the payment status was synchronized successfully.
     */
    public function synchronize_payment_status($transaction, $paymentrecord = null,
        $comments = null, $errormessage = null , $iswebhook = false) {
        global $SITE;

        if (empty( $transaction )) {
            return;
        }

        $redirecturl = new moodle_url('/');
        $emailurl = [];
        $notification = notification::NOTIFY_INFO;
        $emailmessage = 'cannotprocessstatus';
        $userid = ( ! empty( $paymentrecord ) && $paymentrecord['userid'] ) ?
            $paymentrecord['userid'] : $transaction['userid'];
        $courseid = ( ! empty( $paymentrecord ) && $paymentrecord['courseid'] ) ?
            $paymentrecord['courseid'] : $transaction['courseid'];
        $user = $this->fetch_records( 'user', [ "id" => $userid ] );
        $userfullname = $user ? fullname( $user ) : null;
        $course = get_course( $courseid );
        $coursename = ( ! empty( $course ) && ! empty( $course->fullname ) ) ? $course->fullname : null;
        $formattedamount = $this->novalnet_shop_amount_format( $transaction['amount'], $transaction['currency'] );
        $formatteddate   = $this->novalnet_formatted_date();
        $payment = ! empty( $transaction['payment_type'] ) ? $transaction['payment_type'] : $transaction['paymenttype'];
        $paymentname = get_string( $payment, 'paygw_novalnet');
        $status = ! empty( $transaction['status'] ) ? $transaction['status'] : $transaction['gatewaystatus'];
        $component = ( ! empty( $paymentrecord ) && $paymentrecord['component'] ) ?
            $paymentrecord['component'] : $transaction['component'];
        $paymentarea = ( ! empty( $paymentrecord ) && $paymentrecord['paymentarea'] )
            ? $paymentrecord['paymentarea'] : $transaction['paymentarea'];
        $itemid = ( ! empty( $paymentrecord ) && $paymentrecord['itemid'] ) ?
            $paymentrecord['itemid'] : $transaction['itemid'];
        $config = (object) helper::get_gateway_configuration( $component, $paymentarea, $itemid, 'novalnet' );

        if (empty( $comments )) {
            $comments = $this->fetch_transaction_info_comments($transaction['tid']);
            $comments .= $this->fetch_instalment_cycle_comments($transaction['tid']);
        }

        $data = (object) [
            'course' => $coursename,
            'amount' => $formattedamount,
            'payment' => $paymentname,
            'date' => $formatteddate,
            'tid' => $transaction['tid'],
            'comments' => $comments,
        ];
        $content = get_string( 'mail:user:details', 'paygw_novalnet', $userfullname );

        // Determine the message based on payment status.
        switch ( $status ) {
            case self::PAYMENT_STATUS_CONFIRMED:
                $redirecturl = $this->determine_redirect_url($component, $paymentarea, $itemid);
                $emailurl = ['url' => $redirecturl->out()];
                $emailmessage = 'successful';
                $notification = notification::NOTIFY_SUCCESS;
                $content .= get_string( 'payment:successful:content', 'paygw_novalnet',
                    (object) [ 'course' => $coursename, 'url' => $redirecturl->out() ] );
                break;

            case self::PAYMENT_STATUS_PENDING:
                $emailmessage = 'pending';
                $content .= get_string( 'payment:pending:content', 'paygw_novalnet', $coursename );
                break;

            case self::PAYMENT_STATUS_FAILURE:
            case self::PAYMENT_STATUS_DEACTIVATED:
                $notification = notification::NOTIFY_ERROR;
                $emailmessage = 'failed';
                $content .= get_string( 'payment:failed:content', 'paygw_novalnet', $errormessage );
                break;

            case self::PAYMENT_STATUS_ON_HOLD:
                $emailmessage = 'authorize';
                $content .= get_string( 'payment:authorize:content', 'paygw_novalnet', $coursename );
                $this->send_on_hold_mail_for_admin( [
                    'tid'         => $transaction['tid'],
                    'amount'      => $formattedamount,
                    'coursename'  => $coursename,
                    'enrolid'     => $itemid,
                    'paymentname' => $paymentname,
                    'mailto'      => !empty( $config->novalnet_callback_emailtoaddr ) ?
                        explode( ',', $config->novalnet_callback_emailtoaddr ) : [],
                ] );
                break;
            default:
                $emailmessage = 'cannotprocessstatus';
                $content .= get_string( 'payment:cannotprocessstatus:content', 'paygw_novalnet', $coursename );
        }

        if ($emailmessage) {
            $message = get_string('payment:' . $emailmessage . ':message', 'paygw_novalnet');
            if ($errormessage) {
                $message = get_string('payment:' . $emailmessage . ':message', 'paygw_novalnet', $errormessage);
            }

            $content .= get_string( 'mail:payment:details', 'paygw_novalnet', $data );
            if ($emailmessage != 'failed') {
                $content .= get_string( 'mail:merchant:support', 'paygw_novalnet' );
            }
            $content .= get_string( 'mail:admin:details', 'paygw_novalnet', $SITE->fullname );

            // Notify user and redirect.
            $this->notify_user( $userid, $emailmessage, $emailurl, $content );

            if (! $iswebhook) {
                redirect($redirecturl, $message, 0, $notification);
            }
        }
    }

    /**
     * Sends a request to a specified URL.
     * @param array $data The data to send in the request.
     * @param string $url The target URL to which the request will be sent.
     * @param array $args Optional additional arguments to customize the request.
     *
     * @return null|Transaction Returns a `Transaction` object on success, or `null` on failure.
     */
    public function send_request($data, $url, $args = []) {
        $headers = $this->get_header($args['access_key']);
        $curl = new curl();
        $curl->setHeader($headers);
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_POST' => true,
        ];

        $jsonresponse = $curl->post($url, $data, $options);
        $response = json_decode($jsonresponse, true);

        if (!empty($curl->get_errno())) {
            debugging('cURL: Error '.$curl->get_errno().' when calling '.$url, DEBUG_DEVELOPER);
        }

        if ($msg = $curl->error) {
            throw new moodle_exception('Could not discover service endpoints: ' . $msg);
        }

        return $response;
    }

    /**
     * Send a notification email to the admin when a task or request is put on hold.
     *
     * @param array $data Array containing the necessary data to compose the email.
     *
     * @return void
     */
    public function send_on_hold_mail_for_admin($data): void {
        if (empty( $data ) || empty( $data['mailto'] )) {
            return; // Exit early if there's no data.
        }
        $data = (object) $data;
        $mailsubject     = get_string( 'admin:onhold:subject', 'paygw_novalnet' );
        $user             = \core_user::get_noreply_user();
        $user->mailformat = 1; // HTML format.
        $mailcontent     = get_string( 'admin:onhold:message', 'paygw_novalnet', $data );

        foreach ($data->mailto as $email) {
            if (!empty( $email )) {
                $user->email = $email;
                email_to_user(
                        $user,
                        get_admin(),
                        $mailsubject,
                        $mailcontent,
                );
            }
        }
    }

    /**
     * Sorts Novalnet payment data based on predefined criteria.
     *
     * @param array $input Array containing Novalnet payment information to be sorted.
     *
     * @return array Sorted array of Novalnet payments.
     */
    public function sort_novalnet_payments($input) {
        // Desired sort order.
        $sortorder = [
            "DIRECT_DEBIT_SEPA", "DIRECT_DEBIT_ACH", "CREDITCARD", "APPLEPAY", "GOOGLEPAY",
            "INVOICE", "PREPAYMENT", "GUARANTEED_INVOICE", "GUARANTEED_DIRECT_DEBIT_SEPA",
            "INSTALMENT_INVOICE", "INSTALMENT_DIRECT_DEBIT_SEPA", "IDEAL", "ONLINE_TRANSFER",
            "GIROPAY", "CASHPAYMENT", "PRZELEWY24", "EPS", "PAYPAL", "MBWAY", "POSTFINANCE",
            "POSTFINANCE_CARD", "BANCONTACT", "MULTIBANCO", "ONLINE_BANK_TRANSFER", "ALIPAY",
            "WECHATPAY", "TRUSTLY", "BLIK",
        ];

        // Create a map for order priority.
        $ordermap = array_flip($sortorder);

        // Filter and sort input array.
        usort($input, function ($a, $b) use ($ordermap) {
            $posa = isset($ordermap[$a]) ? $ordermap[$a] : PHP_INT_MAX;
            $posb = isset($ordermap[$b]) ? $ordermap[$b] : PHP_INT_MAX;
            return $posa - $posb;
        });

        return $input;
    }

}
