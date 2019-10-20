<?php

use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini;

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require(BASE_PATH . '/vendor/autoload.php');

// Use config to define parameters
$config = new Ini(APP_PATH . '/config.ini');

// Use Loader() to autoload model
$loader = new Loader();
$loader->registerDirs(
	[
		APP_PATH . $config->phalcon->controllersDir,
		APP_PATH . $config->phalcon->modelsDir,
		APP_PATH . $config->phalcon->componentsDir
	]
);
$loader->register();

// Create a DI
$di = new FactoryDefault();

// Setup the view component
$di->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

// Setup a base URI
$di->set(
    'url',
    function () {
        $url = new UrlProvider();
        $url->setBaseUri('/');
        return $url;
    }
);

// Setup the cache service
$di->setShared(
	'cache',
	[
        'className' => 'Phalcon\Cache\Backend\File',
		'arguments' => [
			[
				'type' => 'instance',
		        'className' => 'Phalcon\Cache\Frontend\Data',
				'arguments' => [
					[
						'lifetime' => 10 // 7*24*60*60 // prod: 1 week, dev: 10 seconds
					]
				]
			],
			[
                'type'  => 'parameter',
				'value' => [
					'cacheDir' => APP_PATH . '/cache/'
				]
			]
		]
	]
);

// Setup the cache service
$di->setShared(
	'logger',
    [
        'className' => 'Phalcon\Logger\Adapter\File',
        'arguments' => [
            [
                'type'  => 'parameter',
                'value' => APP_PATH . '/logs/error.log'
            ]
        ]
    ]
);

// Setup the cache service
$di->setShared(
	'apiHostaway',
	[
		'className' => 'ApiHostaway',
		'properties' => [
			[
				'name' => 'cache',
				'value' => [
					'type' => 'service',
					'name' => 'cache'
				]
			],
			[
				'name' => 'logger',
				'value' => [
					'type' => 'service',
					'name' => 'logger'
				]
			]
		]
	]
);

$application = new Application($di);

try {
    // Handle the request
    $response = $application->handle();

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
