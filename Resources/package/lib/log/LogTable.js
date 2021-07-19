/*!
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

import $ from 'jquery';
import eventRegistry from '@arturdoruch/event-registry';
import stateHelper from './state-helper.js';

export default class LogTable {
    /**
     * @param {jQuery|HTMLTableElement|string} tableSelector
     */
    constructor(tableSelector) {
        this._tableSelector = tableSelector;
        //this.update();
    }

    /**
     * Updates table and registers events.
     */
    update() {
        this.$table = $(this._tableSelector);

        if (this.$table.length === 0) {
            return;
            //throw new Error(`The log table with selector "" does not exists.`);
        }

        this.$checkboxes = this.$table.find('input[name="ids[]"]');
        this.$selectAllCheckbox = this.$table.find('input[name="select-all-logs"]');
        this.$selectedRow = null;

        this._registerEvents();
    }

    /**
     * Removes last selected row.
     */
    removeLastSelectedRow() {
        if (this.$selectedRow) {
            this.$selectedRow.remove();
        }
    }

    /**
     * Removes table row by log id.
     *
     * @param {string} id The log id.
     */
    removeRowByLogId(id) {
        this.$table.find('input[value="' + id + '"]')
            .prop('checked', false)
            .closest('tr')
            .remove();
    }

    /**
     * @returns {jQuery}
     */
    getTable() {
        return this.$table;
    }

    /**
     * Un check (deselect) all checkboxes.
     */
    unCheckAll() {
        this.$selectAllCheckbox.prop('checked', false);
        this.$checkboxes.prop('checked', false);
    }

    /**
     * Gets checked logs.
     *
     * @returns {Array}
     */
    getCheckedLogIds() {
        let ids = [];

        this.$table.find('input[name="ids[]"]:checked').each(function () {
            ids.push(this.value);
        });

        return ids;
    }

    /**
     * @returns {*} The list of anchors to view log.
     */
    getLogAnchors() {
        return this.$table.find('a[data-show-log]');
    }

    /**
     * @param {{}} log
     */
    updateRowState(log) {
        const input = this.$table.find('input[value="' + log.id + '"]'),
            $td = input.closest('td');

        stateHelper.setElementBackground($td, log.stateString);
    }

    /**
     * @private
     */
    _registerEvents() {
        eventRegistry.on('click', this.$table.find('tr td'), function (e) {
            this._selectRow( $(e.currentTarget).closest('tr') );
        }, [], this, false);

        // Toggle selection of all checkboxes.
        eventRegistry.on('change', this.$selectAllCheckbox, function (e) {
            this.$checkboxes.prop('checked', $(e.target).prop('checked'))
        }, [], this, false);
    }

    /**
     * Selects table row.
     *
     * @private
     * @param {jQuery} $row
     */
    _selectRow($row) {
        if ($row === this.$selectedRow) {
            return;
        }

        if (this.$selectedRow) {
            this.$selectedRow.removeClass('active');
        }

        $row.addClass('active');

        this.$selectedRow = $row;
    }
}
