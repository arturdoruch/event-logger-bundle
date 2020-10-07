/*!
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

let listeners = [];

export default {
    /**
     * Adds listener called after the log content is loaded.
     * The listener receives argument: $log.
     *
     * @param {function} listener
     */
    addListener: function(listener) {
        listeners.push(listener);
    },

    /**
     * Dispatches listeners to the log "load" event.
     *
     * @param {jQuery} $log The jQuery object with the log element.
     */
    dispatch: function($log) {
        for (let i in listeners) {
            listeners[i].call(null, $log);
        }
    }
}
