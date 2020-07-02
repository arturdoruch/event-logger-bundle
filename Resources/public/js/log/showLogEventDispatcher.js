/*!
 * (c) 2020 Artur Doruch <arturdoruch@interia.pl>
 */

define([], function () {

    var listeners = [];

    return {
        /**
         * Attaches listener to the  log event.
         * The listener receives arguments: $log.
         *
         * @param {function} listener
         */
        addListener: function(listener) {
            listeners.push(listener);
        },

        /**
         * @param {jQuery} $log The jQuery object with log article.
         */
        dispatch: function($log) {
            for (var i in listeners) {
                listeners[i].call(null, $log);
            }
        }
    }
});