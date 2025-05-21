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
 * Contains validation class for working with Novalnet.
 *
 * File   novalnet_validation.php
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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * Class novalnet_validation
 *
 * This class is responsible for performing validation tasks related to Novalnet.
 * It includes methods for validating payment details, transaction data, etc.
 *
 */
class novalnet_validation {

    /**
     * @var novalnet_helper
     * The helper class used for Novalnet-related functionality.
     */
    private $novalnethelper;

    /**
     * Constructor for the novalnet_validation class.
     *
     * Initializes the class with the provided helper file for validation.
     *
     * @param string $novalnethelper The helper file that
     * contains the necessary validation functions or settings.
     */
    public function __construct() {
        $this->novalnethelper = new novalnet_helper();
    }

    /**
     * Restrict the available payment gateways based on the provided configuration and payment data.
     *
     * This function processes the given configuration, payment data, and payment type to determine
     * which payment gateways should be valid or restricted. It returns an array of valid payment gateways
     * based on the configuration rules and the provided payment information.
     *
     * @param string $config        The configuration settings that define the restrictions.
     * @param array  $paymentdata  The payment information such as amount, currency, etc.
     *
     * @return array  An array of valid payment gateways based on the given configuration and data.
     */
    public function display_valid_payments($config, $paymentdata = []) {
        if ( empty( $config['novalnet_public_key'] ) || empty( $config['novalnet_key_password'] ) ) {
            return [];
        }

        $activepaymentmethods = explode(',', $config['novalnet_selected_payments']);
        $invoiceguaranteeenabled = in_array('GUARANTEED_INVOICE', $activepaymentmethods);
        $sepaguaranteeenabled = in_array('GUARANTEED_DIRECT_DEBIT_SEPA', $activepaymentmethods);
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $pattern    = '#(?:Chrome|CriOS|FxiOS|EdgiOS|OPiOS)/(\d+)#';

        foreach ($activepaymentmethods as $key => $payment) {
            if (in_array($payment, ['DIRECT_DEBIT_SEPA', 'INVOICE']) ||
                $this->novalnethelper->get_supports( 'instalment', $payment ) ||
                $this->novalnethelper->get_supports( 'guarantee', $payment )) {
                if (in_array($payment, ['GUARANTEED_DIRECT_DEBIT_SEPA', 'DIRECT_DEBIT_SEPA']) && $sepaguaranteeenabled) {
                    $guaranteecheck = $this->check_guarantee('GUARANTEED_DIRECT_DEBIT_SEPA', $paymentdata, $config);

                    if (($payment === 'GUARANTEED_DIRECT_DEBIT_SEPA' && !$guaranteecheck) ||
                        ($payment === 'DIRECT_DEBIT_SEPA' && (!$guaranteecheck &&
                        empty($config['novalnet_guaranteed_direct_debit_sepa_force_payment']) || $guaranteecheck))) {
                        unset($activepaymentmethods[$key]);
                    }
                }

                if (in_array($payment, ['GUARANTEED_INVOICE', 'INVOICE']) && $invoiceguaranteeenabled) {
                    $guaranteecheck = $this->check_guarantee('GUARANTEED_INVOICE', $paymentdata, $config);

                    if (($payment === 'GUARANTEED_INVOICE' && !$guaranteecheck) ||
                        ($payment === 'INVOICE' && (!$guaranteecheck &&
                        empty($config['novalnet_guaranteed_invoice_force_payment']) ||
                        $guaranteecheck))) {
                        unset($activepaymentmethods[$key]);
                    }
                }

                if ($this->novalnethelper->get_supports( 'instalment', $payment )) {
                    $guaranteecheck = $this->check_guarantee($payment, $paymentdata, $config);
                    if (! $guaranteecheck) {
                        unset( $activepaymentmethods[$key] );
                    }
                }
            } else if (( ! empty( $useragent ) && ! stripos($useragent, "Mac OS") || !stripos($useragent, "Safari") ||
                preg_match($pattern, $useragent, $matchedagent)) && $payment == 'APPLEPAY') {
                unset($activepaymentmethods[$key]);
            }
        }

        return $activepaymentmethods;
    }

    /**
     * Checks if the guarantee conditions are met for a given payment.
     *
     * @param string $payment The payment method or identifier.
     * @param array $paymentdata Optional data related to the payment.
     * @param mixed|null $config Optional configuration for the check (can be an object or array).
     *
     * @return bool Returns true if guarantee conditions are satisfied, false otherwise.
     */
    private function check_guarantee(string $payment, $paymentdata = [], $config = null) {
        global $USER;

        $isvaliddob    = true;
        $orderamount   = $this->novalnethelper->novalnet_formatted_amount($paymentdata['amount']);
        $countrieslist = ['AT', 'DE', 'CH'];
        $minamount     = 999;
        $company       = isset($USER->company) ? $USER->company :
            (!empty($USER->profile['company']) ? $USER->profile['company'] : ' ');
        $birthdate = isset($USER->birthdate) ? $USER->birthdate : (!empty($USER->profile['birthdate']) ?
                $USER->profile['birthdate'] : ' ');

        if ( !empty( trim($company) ) && $config['novalnet_' . strtolower( $payment ) . '_allow_b2b'] != false ) {
            $countrieslist  = ['AT', 'DE', 'CH', 'BE', 'DK', 'BG', 'IT', 'ES', 'SE', 'PT',
                'NL', 'IE', 'HU', 'GR', 'FR', 'FI', 'CZ'];
        } else if (!empty(trim($birthdate))) {
            if ( time() < strtotime( '+18 years', (int)$birthdate ) ) {
                $isvaliddob = false;
            }
        }

        if ( $this->novalnethelper->get_supports( 'instalment', $payment ) ) {
            $validinstalmentcycles = false;
            $minamount               = 1998;
            $installmentcycles      = $config['novalnet_' . strtolower($payment) . '_total_period'];

            if ( ! empty( $installmentcycles ) && ( $orderamount / min( $installmentcycles ) ) >= 999 ) {
                $validinstalmentcycles = true;
            }

            if ( $validinstalmentcycles == false  || empty( $installmentcycles ) ) {
                return false;
            }
        }

        if ( in_array( $USER->country, $countrieslist ) && ( $paymentdata['currency'] == 'EUR' ) &&
            ( $orderamount >= $minamount ) && $isvaliddob ) {
            return true;
        }

        return false;
    }
}
