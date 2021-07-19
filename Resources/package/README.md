# Log

Frontend part of Symfony [`EventLoggerBundle`](https://github.com/arturdoruch/EventLoggerBundle).
Contains CSS styles and JavaScript code handling actions on the log list and details pages.

## Installation

```
yarn add link:vendor/arturdoruch/event-logger-bundle/Resources/package
```

## Usage

Import bootstrap file on the pages with log list and log details. 

```js
// Application main js file.
import '@arturdoruch/event-logger-bundle/lib/bootstrap';

// Set globally ProcessNoticer for AJAX requests.
//import '@arturdoruch/process-noticer/styles/process-notice.css';
//import ajax from '@arturdoruch/helper/lib/ajax';
//ajax.setProcessNoticer();
``` 

or initialize the classes yourself, with custom arguments.

### Setting the log "load" event listeners

If you need to register events on the log HTML elements,
add listener to the "load" event by calling the `LogController.addLoadListener()` method.

```js
import LogController from '@arturdoruch/event-logger-bundle/lib/controller/LogController';

LogController.addLoadListener(function ($log) {
    // Register some events on the log elements.
});
```

### Dispatching the log "load" event

On the log details page manually dispatch the "load" event by calling the `LogController.dispatchLoadEvent()` method.

```js
import LogController from '@arturdoruch/event-logger-bundle/lib/controller/LogController';

LogController.dispatchLoadEvent();
```