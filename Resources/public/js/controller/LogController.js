/*!
 * (c) 2017 Artur Doruch <arturdoruch@interia.pl>
 */

define([
    'arturdoruchJs/component/eventManager',
    'arturdoruchJs/component/Messenger',
    '../log/httpClient',
    'arturdoruchJs/util/screenUtils',
    'arturdoruchJs/util/stringUtils',
    'arturdoruchJs/helper/scrollBar',
    'arturdoruchJs/tool/userInterface',
    'arturdoruchJs/util/browserUtils',
    '../log/showLogEventDispatcher'
], function (em, Messenger, httpClient, screenUtils, stringUtils, scrollBar, userInterface, browserUtils, showLogEventDispatcher) {

    var $container,
        logArticleSelector,
        _logTable,
        messenger,
        isLogOpen = false,
        isDetailPage = true;

    var Class = function (logTable) {
        _logTable = logTable;
        messenger = new Messenger({ removeAfter: 10 });
        $container = $('#ad-log__container');
        logArticleSelector = '#ad-log__log';

        setLogEvents();
        em.on('resize', window, setDetailsContainerHeight);
    };

    Class.prototype.initialize = function (logTable) {
        _logTable = logTable;
        setTableButtonEvents();
        isDetailPage = false;
    };


    function setTableButtonEvents() {
        var $table = _logTable.getTable();

        em.on('click', _logTable.getLogAnchors(), show);
        em.on('click', $table.find('*[data-log-action="remove"]'), remove);
        em.on('click', $table.find('*[data-log-action="change-state"]'), changeState);
        em.on('click', $table.find('*[data-log-action="copy-url"]'), copyUrlToClipboard);
    }


    function setLogEvents() {
        em.on('click', $container.find('*[data-log-action="close"]'), hide);
        em.on('click', $container, function (e) {
            if ($container.is(e.target)) {
                hide();
            }
        });

        var $log = $(logArticleSelector);

        if ($log.length === 0) {
            return;
        }

        em.on('click', $log.find('*[data-log-action="remove"]'), remove);
        em.on('click', $log.find('*[data-log-action="change-state"]'), changeState);
        em.on('click', $log.find('*[data-log-action="copy-url"]'), copyUrlToClipboard);

        browserUtils.attachOpenContentEvent();
        userInterface.attachSlideContentEvent();
        // Fix not working anchors after load modal content.
        em.on('click', $log.find('a[target="_blank"]'), function (e) {
            var anchor = e.target;
            window.open(anchor.href, anchor.target).focus();
        });

        showLogEventDispatcher.dispatch($log);
    }

    /**
     * Loads log details popup.
     */
    function show(e) {
        var logUrl = e.target.href;

        setDetailsContainerHeight();

        httpClient.show(logUrl)
            .done(function (res) {
                $container.html(res);
                $container.show();
                scrollBar.unload();

                setLogEvents();

                isLogOpen = true;
            })
            .fail(function (response) {
                messenger.add('error', response.responseText);
                messenger.display();
            });
    }

    /**
     * Hides log details
     */
    function hide() {
        if (isLogOpen !== true) {
            return;
        }

        $container.html('');
        $container.scrollTop();
        $container.hide();
        scrollBar.load();

        isLogOpen = true;
    }

    /**
     * Removes single log file.
     */
    function remove(e) {
        var url = e.target.getAttribute('formaction');

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
                    }, 2000)
                } else {
                    hide();
                }
            });
    }

    /**
     * Removes single log file.
     */
    function changeState(e) {
        var url = e.currentTarget.getAttribute('formaction'),
            state = e.currentTarget.value;

        httpClient.changeState(url, state)
            .done(function (log) {
                if (!log) {
                    messenger.add('notice', 'Log <b>' + log.id + '</b> state not changed.');
                    return;
                }

                if (_logTable) {
                    _logTable.updateRowState(log);
                }

                updateState(log);
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
    function updateState(log) {
        if (!isLogOpen && !isDetailPage) {
            return;
        }

        var $row = $('tr.ad-log__log-state-row');
        var $stateCell = $row.find('td[data-log-state]'),
            $changedStateAtCell = $row.find('td[data-log-changed-state-at]');

        $stateCell.text(stringUtils.camelCaseToHuman(log.stateString));
        $changedStateAtCell.text(log.changedStateAt);
        $changedStateAtCell.prev('th').text('Changed state at');

        _logTable.setStateBackground(log, $row);
    }

    function copyUrlToClipboard(e) {
        stringUtils.copyToClipboard(e.currentTarget.href);
        messenger.add('notice', 'Copied log url to the clipboard.');
        messenger.display('notice', 4);
    }

    function setDetailsContainerHeight() {
        $container.css('height', screenUtils.getHeight());
    }

    return Class;
});