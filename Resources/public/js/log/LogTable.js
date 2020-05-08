/*!
 * (c) 2017 Artur Doruch <arturdoruch@interia.pl>
 */

define([
    'arturdoruchJs/component/eventManager'
], function (em) {

    var $table,
        $selectedRow,
        $checkboxes,
        $selectAllCheckbox,
        logStateClassPrefix = 'ad-log__bg-state-',
        logStates = ['new', 'resolved', 'watch'];

    /**
     * @param {|jQuery|HTMLTableElement} tableElement
     */
    var LogTable = function (tableElement) {
        $table = $(tableElement);
        $checkboxes = $table.find('input[name="ids[]"]');
        $selectAllCheckbox = $table.find('input[name="select-all-logs"]');

        setEvents();
    };

    LogTable.prototype = {
        /**
         * Removes last selected row.
         */
        removeLastSelectedRow: function () {
            if ($selectedRow) {
                $selectedRow.remove();
            }
        },

        /**
         * Removes table row by log id.
         *
         * @param {string} id The log id.
         */
        removeRowByLogId: function (id) {
            $table.find('input[value="' + id + '"]')
                .prop('checked', false)
                .closest('tr')
                .remove();
        },

        /**
         * @returns {jQuery}
         */
        getTable: function () {
            return $table;
        },

        /**
         * Un check (deselect) all checkboxes.
         */
        unCheckAll: function() {
            $selectAllCheckbox.prop('checked', false);
            $checkboxes.prop('checked', false);
        },

        /**
         * Gets checked logs.
         *
         * @returns {Array}
         */
        getCheckedLogIds: function () {
            var ids = [];

            $table.find('input[name="ids[]"]:checked').each(function () {
                ids.push(this.value);
            });

            return ids;
        },

        /**
         * @returns {*} The list of anchors to view log.
         */
        getLogAnchors: function () {
            return $table.find('a[data-show-log]');
        },

        /**
         * @param {{}} log
         */
        updateRowState: function(log) {
            var input = $table.find('input[value="' + log.id + '"]'),
                $td = input.closest('td');

            this.setStateBackground(log, $td);
        },

        /**
         * @param {{}} log
         * @param {jQuery} $element
         */
        setStateBackground: function (log, $element) {
            for (var i in logStates) {
                $element.removeClass(logStateClassPrefix + logStates[i]);
            }

            $element.addClass(logStateClassPrefix + log.stateString);
        }
    };

    function setEvents() {
        em.on('click', $table.find('tr td'), function (e) {
            selectRow( $(e.currentTarget).closest('tr') );
        }, [], null, false);

        em.on('change', $selectAllCheckbox, toggleCheckRows, [], null, false);
    }

    /**
     * Selects table row.
     * @param {jQuery} $row
     */
    function selectRow($row) {
        if ($row === $selectedRow) {
            return;
        }

        if ($selectedRow) {
            $selectedRow.removeClass('active');
        }

        $row.addClass('active');

        $selectedRow = $row;
    }

    function toggleCheckRows(e) {
        $checkboxes.prop('checked', $(e.target).prop('checked'));
    }

    return LogTable
});