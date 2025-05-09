/**
 * Novalent payment plugin
 *
 * Novalnet repository module to encapsulate all AJAX requests that can be made to Novalnet.
 *
 * @author       Novalnet
 * @module     paygw_novalnet/repository
 * @copyright(C) Novalnet. All rights reserved. <https://www.novalnet.de/>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import $ from 'jquery';

/**
 * Create a payment at Novalnet.
 *
 * @param {string} component
 * @param {string} paymentArea
 * @param {number} itemId
 * @param {string} description
 * @param {number} paymentMethodId
 * @returns {Promise<{shortname: string, name: string, description: String}[]>}
 */
export const createPayment = (component, paymentArea, itemId, description, paymentMethodId) => {
    let args = {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
            description
        };
    if (paymentMethodId !== undefined) {
        args.paymentmethodid = paymentMethodId;
    }

    const request = {
        methodname: 'paygw_novalnet_create_payment',
        args: args
    };
    $('[data-action="novalnet-startpayment"]').prop('disabled', true);

    return Ajax.call([request])[0];
};
