<?php

use Monolog\Logger;
use Slim\Container;

// DIC configuration
$container = app()->getContainer();

// -----------------------------------------------------------------------------
// Slim factories
// -----------------------------------------------------------------------------

// Handle PHP Exceptions
$container['errorHandler'] = function (Container $container) {
    $displayErrorDetails = $container->get('settings')['displayErrorDetails'];
    $logger = $container->get('logger');
    return new App\Handler\Error($displayErrorDetails, $logger);
};

// Handle PHP 7 Errors
$container['phpErrorHandler'] = function (Container $container) {
    return $container['errorHandler'];
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------
$container['view'] = function (Container $container) {
    $settings = $container->get('settings');
    $engine = new League\Plates\Engine($settings['view']['path'], null);

    // Add folder shortcut (assets::file.js)
    $engine->addFolder('assets', $settings['assets']['path']);
    $engine->addFolder('view', $settings['view']['path']);

    // Register Asset extension
    $engine->loadExtension(new \Odan\Asset\PlatesAssetExtension($settings['assets']));
    return $engine;
};

$container['logger'] = function (Container $container) {
    $settings = $container->get('settings');
    $logger = new Monolog\Logger($settings['logger']['name']);

    $level = $settings['logger']['level'];
    if (!isset($level)) {
        $level = Logger::ERROR;
    }
    $logFile = $settings['logger']['file'];
    $handler = new Monolog\Handler\RotatingFileHandler($logFile, 0, $level, true, 0775);
    $logger->pushHandler($handler);

    return $logger;
};

$container['db'] = function (Container $container) {
    $settings = $container->get('settings');
    $driver = new Cake\Database\Driver\Mysql($settings['db']);
    $db = new Cake\Database\Connection(['driver' => $driver]);
    return $db;
};

$container['session'] = function (Container $container) {
    $settings = $container->get('settings');
    $sessionFactory = new \Aura\Session\SessionFactory();
    $cookieParams = $container->get('request')->getCookieParams();
    $session = $sessionFactory->newInstance($cookieParams);
    $session->setName($settings['session']['name']);
    $session->setCacheExpire($settings['session']['cache_expire']);
    return $session;
};

$container['user'] = function (Container $container) {
    $settings = $container->get('settings');
    $session = $container->get('session');
    $db = $container->get('db');
    $secret = $settings['app']['secret'];
    $user = new \App\Service\User\UserSession($session, $db, $secret);
    return $user;
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------
$container[\App\Controller\HomeController::class] = function (Container $container) {
    $view = $container->get('view');
    $db = $container->get('db');
    $user = $container->get('user');
    $logger = $container->get('logger');
    return new \App\Controller\HomeController($view, $db, $user, $logger);
};

$container[\App\Controller\UserController::class] = function (Container $container) {
    $view = $container->get('view');
    $db = $container->get('db');
    $user = $container->get('user');
    $logger = $container->get('logger');
    return new \App\Controller\UserController($view, $db, $user, $logger);
};

$container[\App\Controller\LoginController::class] = function (Container $container) {
    $view = $container->get('view');
    $db = $container->get('db');
    $user = $container->get('user');
    $logger = $container->get('logger');
    return new \App\Controller\LoginController($view, $db, $user, $logger);
};