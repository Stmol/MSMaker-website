<?php
/**
 * Created by Stmol.
 * Date: 01.09.13
 */

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Monolog\Logger;

$app = new Application();

$app['debug'] = true;

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
// Twig
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
//    'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));
// Doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'host'     => '127.0.0.1',
        'user'     => 'stmol',
        'password' => 'stmol',
        'dbname'   => 'stmolme_msmaker',
    ),
));
// Monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/main.log',
    'monolog.level'   => Logger::ALERT,
    'monolog.name'    => 'stm',
));
// Custom logger
//$app['logger'] = $app->share($app->extend('monolog', function($monolog, $app) {
//    $monolog->pushHandler(new Monolog\Handler\StreamHandler(__DIR__.'/../log/download.log', Logger::ALERT));
//
//    return $monolog;
//}));

// Error handle
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) return;

    $page = 404 == $code ? '404.twig' : '500.twig';
    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});

// Home
$app->get('/', function() use ($app) {
    return $app['twig']->render('index.twig', array());
})->bind('home');

// Download
$app->get('/download/{target}/{version}', function(Request $request, $target, $version) use ($app) {

    switch ($target) {
        case 'msmaker':
            $fileName = 'msmaker.'.$version.'.zip';
            $fileUrl = 'https://dl.dropboxusercontent.com/s/xxciqenc06hsvwm/' . $fileName;
            break;

        case 'datapack':
            $fileName = 'datapack.'.$version.'.zip';
            $fileUrl = 'https://dl.dropboxusercontent.com/s/xxciqenc06hsvwm/' . $fileName;
            break;

        default:
            return new Response('Error download', 500);
    }

    if (false === @\file_get_contents($fileUrl))
        return new Response('Error download', 500);

    $ip = $request->getClientIp();
    $ua = $request->server->get('HTTP_USER_AGENT');
    $ref = $request->server->get('HTTP_REFERER');

    $logData = array(
        'dl_time' => date('Y-m-d H:i:s'),
        'target'  => trim($target),
        'version' => trim($version),
        'ip'      => $ip,
        'ua'      => $ua,
        'ref'     => $ref,
    );

    try {
        $app['db']->insert('download_log', $logData);
    }
    catch(\Exception $e) {
        $app['monolog']->addAlert(\sprintf('Download %s from %s', $version, $ip));
        return new Response('Error download', 500);
    }

    $stream = function () use ($fileUrl) {
        \readfile($fileUrl);
    };

    return $app->stream($stream, 200, array(
        'Content-Type'              => 'application/octet-stream',
        'Content-Transfer-Encoding' => 'Binary',
        'Content-Disposition'       => "attachment; filename=\"".$fileName."\""
    ));
})
->bind('download')
->value('target', 'msmaker')
->value('version', 'latest');

$app->get('/documentation', function() use($app) {
    return $app['twig']->render('documentation.twig', array());
})
->bind('documentation');

$app->get('/changelog', function() use($app) {
    return $app['twig']->render('changelog.twig', array());
})
->bind('changelog');

$app->get('/contact', function() use($app) {
    return $app['twig']->render('contact.twig', array());
})
->bind('contact');

$app->run();