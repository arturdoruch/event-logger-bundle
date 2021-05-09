
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

 1. Add URLs to the required repositories (not published on the https://packagist.org) in `composer.json` file of your application

    ```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/arturdoruch/class-validator"
        },
        {
            "type": "vcs",
            "url": "https://github.com/arturdoruch/css-styles"
        },
        {
            "type": "vcs",
            "url": "https://github.com/arturdoruch/exception-formatter"
        }
    ]
    ```

 2. Run the composer command

    ```sh
    composer require arturdoruch/event-logger-bundle
    ```

 3. Register this and the `ArturDoruchListBundle` bundles in `Kernel` class

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