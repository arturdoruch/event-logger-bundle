/*
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

import $ from 'jquery';
import eventRegistry from '@arturdoruch/event-registry';
import LogController from './LogController';
import httpClient from './../log/http-client';
import LogTable from './../log/LogTable';

let instance;
/**
 * @type {LogTable}
 */
let logTable;
let freeze = false;
let messenger;

/**
 * @type {LogController}
 */
let _logController;

export default class LogListController {
    /**
     * @param {LogController} logController
     * @param {string} [logTableSelector = '#ad-log__list']
     *
     * @return {LogListController}
     */
    static getInstance(logController, logTableSelector) {
        if (!instance) {
            instance = new LogListController(logController, logTableSelector);
        }

        return instance;
    }

    constructor(logController, logTableSelector) {
        if (instance) {
            throw new Error('Constructor of the class "LogListController" can not be called directly. Call "LogListController.getInstance()" instead.');
        }

        logTable = new LogTable(logTableSelector || '#ad-log__list');
        _logController = logController;
        messenger = _logController.getFlashMessenger();

        this.updateList();
    }

    updateList() {
        logTable.update();
        registerLogsEvents();
        _logController.setLogTable(logTable);
    }
}


function registerLogsEvents() {
    eventRegistry.on('click', $('button[data-log-action="change-state-many"]'), manageHandler);
    eventRegistry.on('click', $('button[data-log-action="remove-many"]'), manageHandler);
}


function manageHandler(e) {
    const button = e.target;

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
    let logIds = logTable.getCheckedLogIds(),
        xhr;

    if (logIds.length === 0) {
        messenger.add('notice', 'Select logs to manage.');
        messenger.display();

        return;
    }

    if (freeze === true) {
        return;
    }

    freeze = true;

    if (action === 'remove-many') {
        xhr = httpClient.removeMany(url, logIds)
            .done(function (body) {
                handleRemoveResult(logIds);
            });
    } else {
        xhr = httpClient.changeStateMany(url, parseInt(state), logIds)
            .done(function (body) {
                handleChangeStateResult(body.success);

                for (let failure of body.failure) {
                    messenger.add('error', failure);
                }
            });
    }

    xhr
        .fail(function (response) {
            messenger.add('error', response.responseText);
        })
        .always(function () {
            messenger.display();
            logTable.unCheckAll();

            freeze = false;
        });
}

/**
 * @param {[]} removedIds
 */
function handleRemoveResult(removedIds) {
    if (removedIds.length > 0) {
        for (let id of removedIds) {
            logTable.removeRowByLogId(id);
        }

        messenger.add('success', 'Removed <b>' + removedIds.length + '</b> logs.');
    }
}

/**
 * @param {{}} logs
 */
function handleChangeStateResult(logs) {
    if (logs.length === 0) {
        return;
    }

    let renamed = 0;

    for (let log of logs) {
        if (log) {
            logTable.updateRowState(log);
            renamed++;
        }
    }

    if (renamed === 0) {
        messenger.add('notice', 'Logs state not changed.');
    } else {
        messenger.add('success', 'Changed state to <b>' + logs[0].stateString + '</b> of <b>' + renamed + '</b> logs.');
    }
}
