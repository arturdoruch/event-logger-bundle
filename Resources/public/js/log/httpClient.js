/*!
 * (c) 2017 Artur Doruch <arturdoruch@interia.pl>
 */

define(['arturdoruchJs/component/ajax'], function(ajax) {

    return {
        show: function (url) {
            return ajax.send(url, null, true);
        },

        /**
         * @param {string} url
         * @returns {*}
         */
        remove: function (url) {
            return ajax.send({
                url: url,
                type: 'DELETE'
            })
        },

        /**
         * @param {string} url
         * @param {string} state
         * @returns {*}
         */
        changeState: function (url, state) {
            return ajax.send({
                url: url,
                type: 'POST',
                data: {
                    state: state
                },
                contentType: 'application/x-www-form-urlencoded'
            })
        },

        /**
         * @param {string} url
         * @param {number} state
         * @param {string[]} ids The log ids.
         * @returns {*}
         */
        changeStateMany: function (url, state, ids) {
            return ajax.send({
                url: url,
                type: 'POST',
                data: {
                    state: state,
                    ids: ids
                },
                contentType: 'application/x-www-form-urlencoded'
            }, null, true);
        },

        /**
         * @param {string} url
         * @param {string[]} ids The log ids.
         * @returns {*}
         */
        removeMany: function (url, ids) {
            return ajax.send({
                url: url,
                type: 'POST',
                data: {
                    ids: ids
                },
                contentType: 'application/x-www-form-urlencoded'
            }, null, true);
        }

        /*
         * Purges logs from given channel
         *
         * @param {string} url
         * @param {string} channel
         * @return {*}
         */
        /*purgeLogs: function (url, channel) {
            return ajax.send({
                url: url,
                type: 'POST',
                data: {
                    channel: channel
                }
            })
        }*/
    }
});