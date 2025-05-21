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
 * Novalnet payment transaction details overview page.
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
use paygw_novalnet\novalnet_helper;
require('../../../config.php');
require_login();

$tid   = required_param('tid', PARAM_TEXT);
$PAGE->set_url('/payment/gateway/novalnet/novalnet_comments.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('novalnet_trans_comments', 'paygw_novalnet'));
$PAGE->set_heading(get_string('novalnet_trans_comments', 'paygw_novalnet'));

global $OUTPUT;
$novalnethelper = new novalnet_helper();
echo $OUTPUT->header();

if ( ! empty( $tid ) ) {
    $transactioninfo = $novalnethelper->fetch_transaction_info_comments($tid);

    if (!empty( $transactioninfo )) {
        echo $transactioninfo;
    }

    $instalment = $novalnethelper->fetch_instalment_cycle_comments($tid);

    if (!empty( $instalment )) {
        echo $instalment;
    }
} else {
    echo \html_writer::div(get_string('err:assert:paymentrecord', 'paygw_novalnet'), 'alert alert-warning');
}
echo $OUTPUT->footer();
