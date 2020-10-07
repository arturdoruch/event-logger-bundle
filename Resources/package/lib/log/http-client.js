/*!
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

import ajax from '@arturdoruch/helper/lib/ajax';

export default {
    /**
     * @param {string} url
     * @returns {{}}
     */
    show(url) {
        return ajax.send(url, '', true);
    },

    /**
     * @param {string} url
     * @returns {{}}
     */
    remove(url) {
        return ajax.send({
            url: url,
            method: 'DELETE'
        })
    },

    /**
     * @param {string} url
     * @param {string} state
     * @returns {{}}
     */
    changeState: function (url, state) {
        return ajax.send({
            url: url,
            method: 'POST',
            data: {
                state: state
            }
        })
    },

    /**
     * @param {string} url
     * @param {number} state
     * @param {string[]} ids The log ids.
     * @returns {{}}
     */
    changeStateMany: function (url, state, ids) {
        return ajax.send({
            url: url,
            method: 'POST',
            data: {
                state: state,
                ids: ids
            }
        }, '', true);
    },

    /**
     * @param {string} url
     * @param {string[]} ids The log ids.
     * @returns {*}
     */
    removeMany: function (url, ids) {
        return ajax.send({
            url: url,
            method: 'POST',
            data: {
                ids: ids
            }
        }, '', true);
    }
}
