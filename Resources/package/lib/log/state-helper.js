/*
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

const stateClassPrefix = 'ad-log__bg-state-';
const states = ['new', 'resolved', 'watch'];

export default {
    /**
     * @param {string} logState The log state.
     * @param {jQuery} $element
     */
    setElementBackground($element, logState) {
        for (const state of states) {
            $element.removeClass(stateClassPrefix + state);
        }

        $element.addClass(stateClassPrefix + logState);
    }
}
