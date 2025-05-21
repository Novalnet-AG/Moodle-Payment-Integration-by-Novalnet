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
 * Redirects to the Novalnet checkout page for payment.
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

require_once(__DIR__ . '/../../../config.php');

require_login();
$component = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);

$context = context_system::instance(); // Because we "have no scope".
$PAGE->set_context($context);
$params = [
    'component' => $component,
    'paymentarea' => $paymentarea,
    'itemid' => $itemid,
    'description' => $description,
];
$PAGE->set_url('/payment/gateway/novalnet/pay.php', $params);
$PAGE->set_pagelayout('report');

if (isset($itemid)) {
    require_once($CFG->dirroot . '/course/lib.php');
    global $DB;

    $novalnethelper = new novalnet_helper();
    // Fetch course ID from enrol table.
    $courseid = $DB->get_field('enrol', 'courseid', ['enrol' => 'fee', 'id' => $itemid]);

    // If courseid is found, fetch course details and set description.
    if ($courseid) {
        $course = get_course($courseid);
        $coursename = $course->fullname ? $course->fullname : null;
        $coursefee = $novalnethelper->get_course_amount($params, true);
        $coursedescription = get_string('course_description', 'paygw_novalnet',
            ['coursename' => $coursename, 'coursefee' => $coursefee]);
    }
}

if ( $description ) {
    $PAGE->set_title(empty($description) ? $coursedescription : $description);
    $PAGE->set_heading(!empty($coursedescription) ? $coursedescription : $description);
}
$PAGE->requires->js_call_amd('paygw_novalnet/startpayment', 'startPayment', ['[data-action="novalnet-startpayment"]']);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectpaymentmethod', 'paygw_novalnet'), 2);

$paymentmethods = \paygw_novalnet\external\get_payment_details::execute($component, $paymentarea, $itemid);

if ( empty($paymentmethods['response']) ) {
    echo \html_writer::div(get_string('err:nopaymentmethods', 'paygw_novalnet'), 'alert alert-warning');
} else {
    $wcontext = (object)['methods' => array_values($paymentmethods['response'])];

    echo $OUTPUT->render_from_template('paygw_novalnet/novalnet_startpayment', (object)$params);
    echo '<br />';
    echo $OUTPUT->render_from_template('paygw_novalnet/novalnet_select_method', $wcontext);
    echo $OUTPUT->render_from_template('paygw_novalnet/novalnet_startpayment', (object)$params);
}

global $SESSION;

$selectedpayment = ( ! empty( $SESSION->novalnet ) && ! empty( $SESSION->novalnet['selected_payment'] ) )
    ? $SESSION->novalnet['selected_payment'] : null;
$selectedradioid = '#method_' . $selectedpayment;
$escapedpayment = json_encode($selectedpayment); // Safely quote string for JS.
$escapedradioid = json_encode($selectedradioid); // Safely quote string for JS.

$PAGE->requires->js_amd_inline("
    window.addEventListener('load', function () {
        const selectedRadioId = $escapedradioid;
        const selectedPayment = $escapedpayment;

        const selectedRadio = document.querySelector(selectedRadioId);
        if (selectedPayment && selectedRadio && selectedRadio.value == selectedPayment) {
            selectedRadio.checked = true;
        } else {
            const firstRadio = document.querySelector('.paymentmethod input[type=\"radio\"]');
            if (firstRadio) {
                firstRadio.checked = true;
            }
        }
    });
");

echo $OUTPUT->footer();
