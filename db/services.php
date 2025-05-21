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
 * External functions and service definitions for the Novalnet payment gateway plugin
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

defined('MOODLE_INTERNAL') || die();

$functions = [
    'paygw_novalnet_get_merchant_details' => [
        'classname'   => 'paygw_novalnet\external\get_merchant_details',
        'classpath'   => '',
        'description' => 'Returns the merchant details to be used in js',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'paygw_novalnet_handle_webhook_configure' => [
        'classname'   => 'paygw_novalnet\external\handle_webhook_configure',
        'classpath'   => '',
        'description' => 'configure the Webhook URL in Novalnet Admin Portal',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'paygw_novalnet_get_payment_details' => [
        'classname'   => 'paygw_novalnet\external\get_payment_details',
        'classpath'   => '',
        'description' => 'Returns the novalnet active payment details',
        'type'        => 'read',
        'ajax'        => true,
    ],
    'paygw_novalnet_create_payment' => [
        'classname'   => 'paygw_novalnet\external\create_payment',
        'classpath'   => '',
        'description' => 'Create a payment',
        'type'        => 'write',
        'ajax'        => true,
    ],
];
