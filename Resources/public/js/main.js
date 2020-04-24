
(function() {

    var bundlesDir = '../../../bundles',
        eventLoggerDir = bundlesDir + '/arturdorucheventlogger/js',
        listDir = bundlesDir + '/arturdoruchlist/js',
        jsVendorDir = bundlesDir + '/arturdoruchjsvendor/js';

    require([
        listDir + '/main'
    ], function () {
        require.config({
            paths: {
                jquery: jsVendorDir + '/jquery/jquery.min'
            },
            packages: [
                {
                    name: 'arturdoruchEventLogger',
                    location: eventLoggerDir
                }
            ],
            shim: {
                "arturdoruchEventLogger/routing": {
                    deps: ['jquery']
                }
            }
        });

        require(['arturdoruchEventLogger/routing'], function () {

        });
    });

})();