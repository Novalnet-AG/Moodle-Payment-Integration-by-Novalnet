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
 * Lib functions.
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


/**
 * User profile page callback.
 *
 * Used to add a section for displaying the payment transaction details.
 *
 * @param \core_user\output\myprofile\tree $tree The user's profile tree where the setting will be added.
 * @param stdClass $user The user object.
 * @param bool $iscurrentuser Indicates whether the current user is viewing their own profile.
 * @param stdClass $course The course object (if applicable).
 * @return void Returns nothing.
 */
function paygw_novalnet_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $DB;

    if (!$iscurrentuser) {
        return;
    }

    $tree->add_category(new core_user\output\myprofile\category('paygw_novalnet',
        get_string('novalnet_trans_comments', 'paygw_novalnet'), 'loginactivity'));
    $sql = "
        SELECT tid
        FROM {paygw_novalnet_transaction_detail}
        WHERE courseid = :courseid AND userid = :userid AND userunenroled = 0
        ORDER BY timecreated DESC
        LIMIT 1
    ";

    if (! empty($course) && ! empty($course->id)) {
        $params = ['courseid' => $course->id, 'userid' => $user->id];
        $paymentrecord = $DB->get_record_sql($sql, $params);

        if (! empty( $paymentrecord ) && ! empty( $paymentrecord->tid )) {
            $url = new moodle_url( '/payment/gateway/novalnet/novalnet_comments.php', [ 'tid' => $paymentrecord->tid ] );
            $tree->add_node(new core_user\output\myprofile\node('paygw_novalnet', 'nn_course_comment_' . $course->id,
            get_string('specific_course_comment', 'paygw_novalnet', $course->fullname), null, $url));
        }
    } else {
        $mycourses = enrol_get_all_users_courses($user->id, true, null);

        if (!empty($mycourses)) {
            foreach ($mycourses as $mycourse) {
                $ccontext = context_course::instance($mycourse->id);
                if (isset($mycourse->visible) && $mycourse->visible == 0 &&
                    ! has_capability('moodle/course:viewhiddencourses', $ccontext)) {
                        continue;
                }

                if (!empty( $mycourse ) && !empty( $mycourse->id )) {
                    $params = ['courseid' => $mycourse->id, 'userid' => $user->id];
                    $paymentrecord = $DB->get_record_sql($sql, $params);
                    if (! empty( $paymentrecord ) && ! empty( $paymentrecord->tid )) {
                        $url = new moodle_url( '/payment/gateway/novalnet/novalnet_comments.php',
                            [ 'tid' => $paymentrecord->tid ] );
                        $tree->add_node(new core_user\output\myprofile\node('paygw_novalnet', 'nn_course_comment_' . $mycourse->id,
                        get_string('specific_course_comment', 'paygw_novalnet', $mycourse->fullname), null, $url->out(false)));
                    }
                }
            }
        }
    }
}
