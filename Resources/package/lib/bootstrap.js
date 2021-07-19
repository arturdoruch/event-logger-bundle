/*
 * (c) Artur Doruch <arturdoruch@interia.pl>
 */

import './../styles/log.css';
import LogController from './controller/LogController.js';
import LogListController from './controller/LogListController.js';

import '@arturdoruch/list/styles/list.css';
import ListController from '@arturdoruch/list/lib/ListController.js';
import FilterForm from '@arturdoruch/list/lib/FilterForm.js';

const listTableSelector = '#ad-log__list';
const listContainerSelector = '#ad-log__list-container';
// Set log controller.
const logController = LogController.getInstance();
let filterForm;

if (document.querySelector(listContainerSelector)) {
    // Set log list controller.
    const logListController = LogListController.getInstance(logController, listTableSelector);
    // Setup list controller.
    filterForm = new FilterForm('form[name="filter"]');
    const listController = new ListController(listContainerSelector, filterForm);

    listController.addUpdateListener(function () {
        logListController.updateList();
    });
}

export default {
    filterForm,
    LogController
}