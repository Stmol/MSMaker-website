<?php
/**
 * Created by Stmol.
 * Date: 01.09.13
 */

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();

// Twig
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));

// Error navigation
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) return;

    $page = 404 == $code ? '404.twig' : '500.twig';
    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});

// Home
$app->get('/', function() use ($app) {
    return $app['twig']->render('index.twig', array());
})->bind('home');

$app->run();