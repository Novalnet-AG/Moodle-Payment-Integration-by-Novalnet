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

namespace paygw_novalnet;

use paygw_novalnet\novalnet_helper;

/**
 * Class Observer
 *
 * To handle user enrolment deletion events.
 */
class observer {
    /**
     * To handle user enrolment deletion events.
     * This function is triggered when a user's enrolment is deleted in Moodle.
     * It handles necessary actions that need to be performed when an enrolment is deleted.
     *
     * @param \core\event\user_enrolment_deleted $event The event object
     * containing information about the user enrolment deletion.
     */
    public static function user_enrolment_deleted($event) {
        global $DB;
        $novalnethelper    = new novalnet_helper();
        $unenroleddata = (object)$event->other['userenrolment'];

        $sql = "
            SELECT gateway
            FROM {payments}
            WHERE paymentarea = :paymentarea AND itemid = :itemid AND userid = :userid
            ORDER BY timecreated DESC
            LIMIT 1
        ";
        $params = ['paymentarea' => $unenroleddata->enrol, 'itemid' => $unenroleddata->enrolid, 'userid' => $unenroleddata->userid];
        $paymentrecord = $DB->get_record_sql($sql, $params);

        if ( ! empty( $paymentrecord->gateway ) && $paymentrecord->gateway == 'novalnet' ) {
            $sql = "
                SELECT *
                FROM {paygw_novalnet_transaction_detail}
                WHERE paymentarea = :paymentarea AND itemid = :itemid AND userid = :userid
                ORDER BY timecreated DESC
                LIMIT 1
            ";
            $novalnetrecord = $DB->get_record_sql($sql, $params);

            if (! empty( $novalnetrecord ) && empty( $novalnetrecord->userunenroled )) {
                $novalnetrecord->userunenroled = 1;
                $novalnetrecord->timemodified = time();
                $novalnethelper->insert_update_records('paygw_novalnet_transaction_detail',
                    $novalnetrecord, ['id' => $novalnetrecord->id]);
            }
        }
    }
}

