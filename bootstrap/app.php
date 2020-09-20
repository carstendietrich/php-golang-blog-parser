<?php

use App\B3HeadersPropagator;
use OpenCensus\Trace\Exporter\JaegerExporter;
use OpenCensus\Trace\Tracer;

require_once __DIR__ . '/../vendor/autoload.php';


(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

Tracer::start(
    new JaegerExporter(
        env('JAEGER_SERVICE_NAME', 'php-golang-blog-parser'),
        [
            'host' => env('JAEGER_HOST', 'localhost'),
            'port' => env('JAEGER_PORT', 6831),
        ]
    ),
    [
        'propagator' => new B3HeadersPropagator(),
    ]
);

date_default_timezone_set(env('APP_TIMEZONE', 'Europe/Berlin'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/
/** @var \Laravel\Lumen\Application $app */
$app = Tracer::inSpan(['name' => 'Init Lumen PHP framework'], static function () {
    return new Laravel\Lumen\Application(
        dirname(__DIR__)
    );
});

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

Tracer::inSpan(['name' => 'Load config'], static function () use ($app) {
    $app->configure('app');
});

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/


Tracer::inSpan(['name' => 'Load application routes'], static function () use ($app) {
    $app->router->group([
        'namespace' => 'App\Http\Controllers',
    ], function ($router) {
        require __DIR__ . '/../routes/web.php';
    });
});

return $app;
