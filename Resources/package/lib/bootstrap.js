/*
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

import '@arturdoruch/log/styles/log.css';
import LogController from '@arturdoruch/log/lib/controller/LogController';
import LogListController from '@arturdoruch/log/lib/controller/LogListController';

import '@arturdoruch/list/styles/list.css';
import ListController from '@arturdoruch/list/lib/ListController';
import FilterFormController from '@arturdoruch/list/lib/FilterFormController';

// Set log controller.
const logController = LogController.getInstance();

const listTableSelector = '#ad-log__list';
const listContainerSelector = '#ad-log__list-container';

if (document.querySelector(listContainerSelector)) {
    // Set log list controller.
    const logListController = LogListController.getInstance(logController, listTableSelector);

    // Setup list controller.
    const filterFormController = new FilterFormController('form[name="filter"]');
    const listController = new ListController(listContainerSelector, filterFormController);

    listController.addUpdateListener(function () {
        logListController.updateList();
    });
}
