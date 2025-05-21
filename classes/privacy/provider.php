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
 * Privacy Subsystem implementation for paygw_novalnet.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License
 * that is bundled with this package in the file freeware_license_agreement.txt
 *
 * DISCLAIMER
 *
 * If you wish to customize Novalnet payment plugin for your needs, please contact technic@novalnet.de for more information.
 *
 * @package    paygw_novalnet
 * @copyright  2025 Novalnet <technic@novalnet.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace paygw_novalnet\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy API implementation for paygw_novalnet.
 */
class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\data_provider {

    /**
     * Returns metadata about this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored in this plugin.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'paygw_novalnet_transaction_detail',
            [
                'userid' => 'privacy:metadata:paygw_novalnet_transaction_detail:userid',
            ],
            'privacy:metadata:paygw_novalnet_transaction_detail'
        );

        // Data sent to Novalnet external server.
        $collection->add_external_location_link('novalnet', [
            'first_name' => 'privacy:metadata:novalnet:first_name',
            'last_name' => 'privacy:metadata:novalnet:last_name',
            'email' => 'privacy:metadata:novalnet:email',
            'customer_ip' => 'privacy:metadata:novalnet:customer_ip',
            'customer_no' => 'privacy:metadata:novalnet:customer_no',
            'tel' => 'privacy:metadata:novalnet:tel',
            'mobile' => 'privacy:metadata:novalnet:mobile',
            'gender' => 'privacy:metadata:novalnet:gender',
            'birth_date' => 'privacy:metadata:novalnet:birth_date',
            'billing' => 'privacy:metadata:novalnet:billing',
        ], 'privacy:metadata:novalnet');

        return $collection;
    }
}
