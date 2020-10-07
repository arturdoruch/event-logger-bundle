
# EventLoggerBundle

Symfony bundle for logging (into custom storage (e.g. database)),
viewing and managing logs of any event.

## Functionality

 * Logs any event with custom context into any system (e.g: database, file)
 * Provides page with list of event logs, which allows:
    - filtering and viewing logs  
    - changing state of the log (predefined states: `new`, `resolved`, `watch`)
    - removing the log
 * Provides page displaying log details     

## Installation

```sh
composer require arturdoruch/event-logger-bundle
```

Register this bundle and `ArturDoruchListBundle` in `AppKernel` class.

```php
public function registerBundles()
{
    $bundles = [
        // Other bundles
        new ArturDoruch\EventLoggerBundle\ArturDoruchEventLoggerBundle(),
        new ArturDoruch\ListBundle\ArturDoruchListBundle(),
    ];
```

### Frontend

Install [@arturdoruch/event-logger-bundle](Resources/package/README.md) Node.js package with 
CSS styles and JavaScript code handling actions on the log list and details pages.

```
yarn add link:vendor/arturdoruch/event-logger-bundle/Resources/package
```

## Usage

todo