<?php

use Igorw\Silex\ConfigServiceProvider;
use Monolog\Logger;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();

$env = getenv('APP_ENV') ? : 'prod';

/** Config */
$app->register(new ConfigServiceProvider(__DIR__ . "/../config/$env.json"));

/** Url Generator */
$app->register(new UrlGeneratorServiceProvider());

/** Twig */
$app->register(
    new TwigServiceProvider(),
    array(
        'twig.path' => array(__DIR__ . '/../templates'),
    )
);

$app['twig'] = $app->share(
    $app->extend(
        'twig',
        function ($twig, $app) {
            if (!$app['debug']) {
                $twig->setCache(__DIR__ . '/../cache/twig');
            }

            return $twig;
        }
    )
);

/** Doctrine */
$app->register(
    new DoctrineServiceProvider(),
    array(
        'db.options' => array(
            'driver'   => $app['db']['driver'],
            'host'     => $app['db']['host'],
            'user'     => $app['db']['user'],
            'password' => $app['db']['password'],
            'dbname'   => $app['db']['dbname'],
        ),
    )
);

/** Monolog */
$app->register(
    new Silex\Provider\MonologServiceProvider(),
    array(
        'monolog.logfile' => __DIR__ . '/../log/main.log',
        'monolog.level'   => Logger::ALERT,
        'monolog.name'    => 'stm',
    )
);
// Custom logger
//$app['logger'] = $app->share($app->extend('monolog', function($monolog, $app) {
//    $monolog->pushHandler(new Monolog\Handler\StreamHandler(__DIR__.'/../log/download.log', Logger::ALERT));
//
//    return $monolog;
//}));

return $app;