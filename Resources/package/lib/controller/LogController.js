/*
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

import $ from 'jquery';
import eventRegistry from '@arturdoruch/event-registry';
import scrollBar from '@arturdoruch/browser/lib/scroll-bar';
import FlashMessenger from '@arturdoruch/flash-messenger';
import screenUtils from '@arturdoruch/util/lib/screen-utils';
import stringUtils from '@arturdoruch/util/lib/string-utils';
import contentEvents from '@arturdoruch/ui/lib/content-events';
import httpClient from './../log/http-client';
import loadEventDispatcher from './../log/load-event-dispatcher';
import stateHelper from './../log/state-helper';

let instance;
let isDetailPage = true;
let isLogOpen = true;
let _logTable;
let messenger;
let $modalContainer;
let elementSelectors = {
    log: '#ad-log__log',
    logModalContainer: '#ad-log__container',
};

class LogController {

    static getInstance(flashMessenger, logSelector, logModalContainerSelector) {
        if (!instance) {
            instance = new LogController(flashMessenger, logSelector, logModalContainerSelector);
        }

        return instance;
    }

    /**
     * @param {FlashMessenger} [flashMessenger]
     * @param {string} [logSelector]
     * @param {string} [logModalContainerSelector]
     */
    constructor(flashMessenger, logSelector, logModalContainerSelector) {
        if (instance) {
            throw new Error('Constructor of the class "LogController" can not be called directly. Call "LogController.getInstance()" instead.');
        }

        elementSelectors = $.extend(elementSelectors, {
            log: logSelector, logModalContainer: logModalContainerSelector
        });
        messenger = flashMessenger || new FlashMessenger();
        $modalContainer = $(elementSelectors.logModalContainer);

        // Add default load listeners.
        LogController.addLoadListener(function ($log) {
            registerLogActionEvents($log);
            // Fix not working anchors after load modal content.
            eventRegistry.on('click', $log.find('a[target="_blank"]'), function (e) {
                const anchor = e.target;
                window.open(anchor.href, anchor.target).focus();
            });

            contentEvents.slide();
            contentEvents.openInBrowser();
        });
        //registerLogEvents();

        eventRegistry.on('resize', window, setModalContainerHeight);
    }

    /**
     * Registers listener calling after the log content is loaded.
     * Allows to register events on the log HTML elements.
     *
     * @param {function} listener
     *
     * @return {LogController}
     */
    static addLoadListener(listener) {
        loadEventDispatcher.addListener(listener);

        return this;
    }

    /**
     * Dispatches listeners to the log "load" event.
     */
    static dispatchLoadEvent() {
        const $log = $(elementSelectors.log);

        if ($log.length > 0) {
            loadEventDispatcher.dispatch($log);
        }
    }

    /**
     * @return {FlashMessenger}
     */
    getFlashMessenger() {
        return messenger;
    }

    /**
     * @param {LogTable} logTable
     */
    setLogTable(logTable) {
        _logTable = logTable;
        isDetailPage = false;
        registerTableEvents();
    }
}

export default {
    getInstance: LogController.getInstance,
    addLoadListener: LogController.addLoadListener,
    dispatchLoadEvent: LogController.dispatchLoadEvent,
}


function registerLogEvents() {
    if ($modalContainer.length > 0) {
        eventRegistry.on('click', $modalContainer.find('*[data-log-action="close"]'), hide);
        eventRegistry.on('click', $modalContainer, function (e) {
            if ($modalContainer.is(e.target)) {
                hide();
            }
        });
    }

    LogController.dispatchLoadEvent();
}


function registerTableEvents() {
    const $table = _logTable.getTable();

    eventRegistry.on('click', _logTable.getLogAnchors(), show);
    registerLogActionEvents($table);
}

function registerLogActionEvents($parentElement) {
    eventRegistry.on('click', $parentElement.find('*[data-log-action="remove"]'), remove);
    eventRegistry.on('click', $parentElement.find('*[data-log-action="change-state"]'), changeState);
    eventRegistry.on('click', $parentElement.find('*[data-log-action="copy-url"]'), copyUrlToClipboard);
}

/**
 * Loads modal element with log details.
 */
function show(e) {
    const logUrl = e.target.href;

    setModalContainerHeight();

    httpClient.show(logUrl)
        .done(function (res) {
            $modalContainer.html(res);
            $modalContainer.show();
            scrollBar.unload();

            registerLogEvents();
            isLogOpen = true;
        })
        .fail(function (response) {
            messenger.add('error', response.responseText);
            messenger.display();
        });
}

/**
 * Hides modal element with log details.
 */
function hide() {
    if (isLogOpen === false) {
        return;
    }

    $modalContainer.html('');
    $modalContainer.scrollTop();
    $modalContainer.hide();
    scrollBar.load();

    isLogOpen = false;
}

/**
 * Removes a log.
 */
function remove(e) {
    const url = e.target.getAttribute('formaction');

    httpClient.remove(url)
        .done(function (content) {
            messenger.add('success', content);
            if (_logTable) {
                _logTable.removeLastSelectedRow();
            }
        })
        .fail(function (response) {
            messenger.add('error', response.responseText);
        })
        .always(function () {
            messenger.display();

            if (isDetailPage) {
                setTimeout(function () {
                    window.location.replace( $('a[data-log-action="view-list"]').first().attr('href') );
                }, 2000);
            } else {
                hide();
            }
        });
}


function changeState(e) {
    const url = e.currentTarget.getAttribute('formaction');
    const state = e.currentTarget.value;

    httpClient.changeState(url, state)
        .done(function (log) {
            if (!log) {
                messenger.add('notice', `Log <b>${log.id}</b> state not changed.`);

                return;
            }

            if (_logTable) {
                _logTable.updateRowState(log);
            }

            updateDisplayedState(log);
            messenger.add('success', 'Log <b>' + log.id + '</b> state has been changed to <b>' + log.stateString + '</b>.');
        })
        .fail(function (response) {
            messenger.add('error', response.responseText);
        })
        .always(function () {
            messenger.display();
        });
}

/**
 * @param {{}} log
 */
function updateDisplayedState(log) {
    if (!isLogOpen) {
        return;
    }

    const $row = $('tr.ad-log__log-state-row');
    const $stateCell = $row.find('td[data-log-state]');
    const $changedStateAtCell = $row.find('td[data-log-changed-state-at]');

    $stateCell.text(log.stateString.replace(/^(.)(.+)$/, function (matches, firstLetter, rest) {
        return firstLetter.toUpperCase() + rest;
    }));
    $changedStateAtCell.text(log.changedStateAt);
    $changedStateAtCell.prev('th').text('Changed state at');

    stateHelper.setElementBackground($row, log.stateString);
}

function copyUrlToClipboard(e) {
    stringUtils.copyToClipboard(e.currentTarget.href);
    messenger.add('notice', 'Copied log URL to the clipboard.');
    messenger.display('notice', 4);
}

function setModalContainerHeight() {
    $modalContainer.css('height', screenUtils.getHeight());
}