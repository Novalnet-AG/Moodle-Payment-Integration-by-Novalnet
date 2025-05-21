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
 * paygw_novalnet gateway plugin uninstallation.
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

/**
 * Uninstall hook for the Novalnet payment gateway.
 *
 * @package    paygw_novalnet
 * @return     bool True on successful uninstall.
 */
function xmldb_paygw_novalnet_uninstall() {

    $novalnethelper = new novalnet_helper();
    $table          = 'payment_gateways';
    $result         = $novalnethelper->fetch_records($table, [ 'gateway' => 'novalnet' ]);
    if ( ! empty( $result ) ) {
        $result->config = null;
        $result         = $novalnethelper->insert_update_records($table, $result, [ 'id' => $result->id, 'gateway' => 'novalnet' ]);
    }

    return true;
}
