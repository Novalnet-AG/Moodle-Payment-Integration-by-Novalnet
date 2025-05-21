/**
 *  Novalnet payment plugin
 *
 * Payment integration for the Novalnet system
 *
 * @author       Novalnet
 * @module     paygw_novalnet/startpayment
 * @copyright(C) Novalnet. All rights reserved. <https://www.novalnet.de/>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from 'paygw_novalnet/repository';
import * as Notification from 'core/notification';
import * as str from 'core/str';
import log from 'core/log';

/**
 * Detect selected payment method (if we have one).
 *
 * @returns {String|null}
 */
function getSelectedPaymentMethod() {
    let el = document.querySelector('input[name="method"][type="radio"]:checked');

    if (typeof el !== 'undefined' && el !== null) {
        return el.value;
    }
    return null;
}

/**
 * Show a notification based on the result passed to the function.
 *
 * @param {Object} result - The result object containing data for the notification.
 * @returns {Promise} A promise that resolves when the notification is shown.
 */
async function showNotification(result) {
    try {
        const strings = await str.get_strings([
            { key: 'startpayment:failed:title', component: 'paygw_novalnet' },
            { key: 'startpayment:failed:btncancel', component: 'paygw_novalnet' }
        ]);
        Notification.alert(strings[0], result.message, strings[1]);
    } catch (error) {
        log.error('Error getting strings:');
        log.error(error);
        return;
    }
}

/**
 * Create payment in the backend and redirect.
 *
 * @param {String} selector
 * @returns {Promise}
 */
export const startPayment = (selector) => {
    document.querySelectorAll('button' + selector).forEach(function(button) {
        button.addEventListener('click', e => {
            e.preventDefault();
            const dataset = e.currentTarget.dataset;
            var PaymentMethod = getSelectedPaymentMethod();

            if (PaymentMethod === null) {
                Notification.alert('', str.get_string('startpayment:failed:nopayment', 'paygw_novalnet'));
                return;
            }

            Repository.createPayment(
                    dataset.component,
                    dataset.paymentarea,
                    dataset.itemid,
                    dataset.description,
                    PaymentMethod
            ).then(result => {
                if (result.success) {
                    window.location.href = result.redirecturl;
                } else {
                    showNotification(result);
                }
            }).catch(Notification.exception);
        });
    });
};
