
define(function () {

    var logTableSelector = '#ad-log__list',
        logListContainerSelector = '#ad-log__list-container',
        filterFormSelector = 'form[name="filter"]',
        pageRoute = $('div[data-page-route]').data('pageRoute');

    function createLogTable(LogTable) {
        return new LogTable(logTableSelector);
    }

    switch (pageRoute) {
        case 'arturdoruch_eventlogger_log_list':
            require([
                'arturdoruchEventLogger/controller/LogController',
                'arturdoruchEventLogger/controller/LogListController',
                'arturdoruchEventLogger/log/LogTable',
                'arturdoruchList/controller/ListController',
                'arturdoruchList/controller/FilterFormController'
            ], function(LogController, LogListController, LogTable, ListController, FilterFormController) {
                var logTable = createLogTable(LogTable);
                var logController = new LogController(logTable);
                var logListController = new LogListController(filterFormSelector);

                logController.initialize(logTable);
                logListController.initialize(logTable);

                var filterFormController = new FilterFormController(filterFormSelector);
                var listController = new ListController(logListContainerSelector, filterFormController, {
                    //gettingItemsMessage: 'Getting logs'
                });

                listController.addUpdateListListener(function ($listContainer) {
                    var logTable = createLogTable(LogTable);
                    logController.initialize(logTable);
                    logListController.initialize(logTable);
                });
            });

            break;
        case 'arturdoruch_eventlogger_log_show':
            require([
                'arturdoruchEventLogger/controller/LogController',
                'arturdoruchEventLogger/log/LogTable'
            ], function(LogController, LogTable) {
                new LogController(createLogTable(LogTable));
            });
    }

});