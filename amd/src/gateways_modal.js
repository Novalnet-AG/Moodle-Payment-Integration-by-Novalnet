/**
 *  Novalnet payment plugin
 *
 * This module is responsible for Novalnet content in the gateways modal.
 *
 * @author       Novalnet
 * @module     paygw_novalnet/gateways_modal
 * @copyright(C) Novalnet. All rights reserved. <https://www.novalnet.de/>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import ModalFactory from 'core/modal_factory';

/**
 * Show modal with the Novalnet placeholder.
 *
 * @returns {Promise}
 */
const showModalWithPlaceholder = async() => {
    const modal = await ModalFactory.create({
        body: await Templates.render('paygw_novalnet/novalnet_button_placeholder', {})
    });
    modal.show();
};

/**
 * Process.
 *
 * @param {String} component
 * @param {String} paymentArea
 * @param {String} itemId
 * @param {String} description
 * @returns {Promise<>}
 */
export const process = (component, paymentArea, itemId, description) => {
    return showModalWithPlaceholder()
        .then(() => {
            location.href = M.cfg.wwwroot + '/payment/gateway/novalnet/pay.php?' +
                'component=' + component +
                '&paymentarea=' + paymentArea +
                '&itemid=' + itemId +
                '&description=' + description;
            return new Promise(() => null);
        });
};
