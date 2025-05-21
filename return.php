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
 * Novalnet return page.
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

use core_payment\helper;
use paygw_novalnet\novalnet_helper;
use core\output\notification;

require_once('./../../../config.php');

$urlparams = [
    'component' => required_param('component', PARAM_COMPONENT),
    'paymentarea' => required_param('paymentarea', PARAM_AREA),
    'itemid' => required_param('itemid', PARAM_INT),
    'description' => required_param('description', PARAM_INT),
    'userid' => required_param('userid', PARAM_INT),
    'status' => optional_param('status', null, PARAM_TEXT),
    'tid' => optional_param('tid', null, PARAM_TEXT),
    'status_text' => optional_param('status_text', null, PARAM_TEXT),
];

$notifyerror = notification::NOTIFY_ERROR;
// Check for failure status and missing tid, then redirect.
if ($urlparams['status'] == 'FAILURE' && empty($urlparams['tid'])) {
    redirect(new moodle_url('/payment/gateway/novalnet/pay.php', $urlparams), $urlparams['status_text'], 0, $notifyerror);
    return;
}

$params = array_merge($urlparams, [
    'tid' => required_param('tid', PARAM_TEXT),
    'txn_secret' => required_param('txn_secret', PARAM_TEXT),
    'status' => required_param('status', PARAM_TEXT),
    'checksum' => optional_param('checksum', null, PARAM_TEXT),
    'payment_type' => required_param('payment_type', PARAM_TEXT),
    'userid' => required_param('userid', PARAM_INT),
]);

require_login();

$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context($context);
$PAGE->set_url('/payment/gateway/novalnet/return.php', $params);
$PAGE->set_pagelayout('report');
$pagetitle = get_string('payment:returnpage', 'paygw_novalnet');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

// Process status.
try {
    global $SESSION, $DB;

    $novalnethelper        = new novalnet_helper();
    $config                = (object) helper::get_gateway_configuration($params['component'],
        $params['paymentarea'], $params['itemid'], 'novalnet');
    $serverresponse       = $novalnethelper->get_transaction(['tid' => $params['tid'],
        'access_key' => $config->novalnet_key_password]);

    if ( !$novalnethelper->is_success_status( $serverresponse ) ) {
        redirect(new moodle_url('/payment/gateway/novalnet/pay.php', $urlparams),
            $serverresponse['result']['status_text'] , 0, $notifyerror);
        return;
    }
    $txnsecret = ( ! empty( $SESSION->novalnet ) && ! empty( $SESSION->novalnet['novalnet_txnsecret'] ) )
                                ? $SESSION->novalnet['novalnet_txnsecret']
                                : ( $params['txn_secret'] ? $params['txn_secret'] : null );
    $paymentrecord = ( ! empty( $serverresponse['custom'] ) && ! empty( $serverresponse['custom']['course_meta'] ) )
        ? $serverresponse['custom']['course_meta']
        : ( ( ! empty( $serverresponse['custom'] ) && ! empty( $serverresponse['custom']['inputval1'] ) ) ?
                                $serverresponse['custom']['inputval1'] : null );
    $paymentrecord = $paymentrecord ? $novalnethelper->novalnet_unserialize_data($paymentrecord, true) : null;
    $paymentrecord['userid'] = $serverresponse['customer']['customer_no'];

    if ($paymentrecord['component'] == 'enrol_fee' && $paymentrecord['paymentarea'] == 'fee') {
        $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => 'fee', 'id' => $paymentrecord['itemid']]);
    }
    $paymentrecord['courseid'] = ! empty( $courseid ) ? $courseid : null;
    $isvalidchecksum = $novalnethelper->is_valid_checksum( $params, $txnsecret, $config->novalnet_key_password );

    if ( ! $isvalidchecksum ) {
        redirect(new moodle_url('/payment/gateway/novalnet/pay.php', $urlparams),
            get_string('hash_check_failed_error', 'paygw_novalnet'), 0, $notifyerror);
    }
    $novalnethelper->check_payment_record_variables($paymentrecord, $params);
    $tid = $novalnethelper->fetch_records('paygw_novalnet_transaction_detail',
        ['tid' => $serverresponse['transaction']['tid']], 'tid');

    if ( empty( $tid ) ) {
        $issuccess = $novalnethelper->is_success_status( $serverresponse );
        if ( ! $issuccess ||
            ( $issuccess && $serverresponse['transaction']['status'] == novalnet_helper::PAYMENT_STATUS_FAILURE )
        ) {
            $message = $novalnethelper->handle_transaction_failure( $serverresponse, $paymentrecord );
            redirect( new moodle_url('/payment/gateway/novalnet/pay.php', $urlparams), $message, 0, $notifyerror);
        } else if ( $issuccess ) {
            $novalnethelper->handle_transaction_success( $serverresponse, $paymentrecord );
        }
    }

    $errormessage = ( $serverresponse['transaction']['status'] == 'FAILURE' ) ?
        $this->novalnet_response_text( $serverresponse ) : null;
    $novalnethelper->synchronize_payment_status($serverresponse['transaction'], $paymentrecord, $errormessage);
} catch (moodle_exception $me) {
    redirect(new moodle_url('/'), $me->getMessage(), 0, $notifyerror);
} catch (\Exception $e) {
    echo $e->getMessage();
    redirect(new moodle_url('/'), get_string('unknownerror', 'paygw_novalnet'), 0, $notifyerror);
}
