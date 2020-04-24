/*!
 * (c) 2017 Artur Doruch <arturdoruch@interia.pl>
 */

/**
 * Controls logs list table.
 */
define([
    'arturdoruchJs/component/eventManager',
    'arturdoruchJs/component/Messenger',
    '../log/httpClient',
    '../DatePicker'
], function (em, Messenger, httpClient, DataPicker) {

    var messenger,
        _logTable,
        freeze = false;

    var Class = function (filterFormSelector) {
        messenger = new Messenger({ removeAfter: 10 });

        setFilterFormEvents(filterFormSelector);
    };

    /**
     * @param {LogTable} logTable
     */
    Class.prototype.initialize = function (logTable) {
        _logTable = logTable;
        setEvents();
    };

    function setEvents() {
        em.on('click', $('button[data-log-action="change-state-many"]'), manageHandler);
        em.on('click', $('button[data-log-action="remove-many"]'), manageHandler);
    }

    function setFilterFormEvents(filterFormSelector) {
        var filterForm = document.querySelector(filterFormSelector),
            dateFields = filterForm.querySelectorAll('input[data-type="date"]'),
            prevFieldName;

        for (var i = 0; i < dateFields.length; i++) {
            var field = dateFields[i];

            if (/To\]$/.test(field.name)) {
                new DataPicker(prevFieldName, field.name, field.dataset.format);
            }

            prevFieldName = field.name;
        }
    }

    function manageHandler(e) {
        var button = e.target;

        setTimeout(function () {
            manage(button.dataset.logAction, button.getAttribute('formaction'), button.value);
        }, 200);
    }

    /**
     * @param {string} action
     * @param {string} url
     * @param {string} [state]
     */
    function manage(action, url, state) {
        var ids = _logTable.getCheckedLogIds(),
            xhr;

        if (ids.length === 0) {
            messenger.add('notice', 'Select logs to manage.');
            messenger.display();

            return;
        }

        if (freeze === true) {
            return;
        }

        freeze = true;

        if (action === 'remove-many') {
            xhr = httpClient.removeMany(url, ids)
                .done(function (body) {
                    handleRemoveResult(ids);
                });
        } else {
            xhr = httpClient.changeStateMany(url, parseInt(state), ids)
                .done(function (body) {
                    var failure = body.failure,
                        failureLength = failure.length;

                    handleChangeStateResult(body.success, state);

                    if (failureLength > 0) {
                        for (var i = 0; i < failureLength; i++) {
                            messenger.add('error', failure[i]);
                        }
                    }
                });
        }

        xhr
            .fail(function (response) {
                messenger.add('error', response.responseText);
            })
            .always(function () {
                messenger.display();
                _logTable.unCheckAll();

                freeze = false;
            });
    }

    /**
     * @param {[]} removedIds
     */
    function handleRemoveResult(removedIds) {
        var length = removedIds.length;

        if (length > 0) {
            for (var i = 0; i < length; i++) {
                _logTable.removeRowByLogId(removedIds[i]);
            }

            messenger.add('success', 'Removed <b>' + length + '</b> logs.');
        }
    }

    /**
     *
     * @param {{}} logs
     * @param {number} state The log new state.
     */
    function handleChangeStateResult(logs, state) {
        var length = logs.length,
            renamed = 0;

        if (length === 0) {
            return;
        }

        for (var i = 0; i < length; i++) {
            var log = logs[i];

            if (log) {
                _logTable.updateRowState(log);
                renamed++;
            }
        }

        if (renamed === 0) {
            messenger.add('notice', 'Logs state not changed.');
        } else {
            messenger.add('success', 'Changed state to <b>' + logs[0].stateString + '</b> of <b>' + renamed + '</b> logs.');
        }
    }

    return Class;
});