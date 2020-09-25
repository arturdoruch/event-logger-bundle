
# EventLoggerBundle

Symfony bundle for logging (into custom storage (e.g. database)),
viewing and managing logs of any event.


*IMPORTANT: Bundle tested only on Symfony 3.*

## Functionality

 * Logs any event with custom context into, any system (e.g: database, file). 
 * Page with list of event logs, which allows:
    - filtering and viewing logs  
    - changing state of the log (predefined states: `new`, `resolved`, `watch`)
    - removing the log
 * Page displaying log details     

## Installation

```sh
composer require arturdoruch/event-logger-bundle
```

Register bundle in `AppKernel` class, in `registerBundles()` method.

```php
new ArturDoruch\EventLoggerBundle\ArturDoruchEventLoggerBundle(),
```

### Frontend

Install [@arturdoruch/log](https://github.com/arturdoruch/js-log) package for JavaScript support, for pages 
displaying log list and log details.

## Usage

todo