<?php

/** Home */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get(
    '/',
    function () use ($app) {
        return $app['twig']->render('index.twig', array());
    }
)->bind('home');

/** Download */
$app->get(
    '/download/{target}/{version}',
    function (Request $request, $target, $version) use ($app) {

        switch ($target) {
            case 'msmaker':
                $fileName = 'msmaker.zip';
                $fileUrl = 'https://dl.dropboxusercontent.com/s/xxciqenc06hsvwm/' . $fileName;
                break;

            case 'datapack':
                $fileName = 'datapack.zip';
                $fileUrl = 'https://dl.dropboxusercontent.com/s/xxciqenc06hsvwm/' . $fileName;
                break;

            default:
                return new Response('Error download', 500);
        }

        if (false === @\file_get_contents($fileUrl)) {
            return new Response('Error download', 500);
        }

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
            $app['db']->insert($app['db_conf']['table_log'], $logData);
        } catch (\Exception $e) {
            $app['monolog']->addAlert(\sprintf('Download %s from %s', $version, $ip));

            return new Response('Error download', 500);
        }

        $stream = function () use ($fileUrl) {
            @\readfile($fileUrl);
        };

        return $app->stream(
            $stream,
            200,
            array(
                'Content-Type'              => 'application/octet-stream',
                'Content-Transfer-Encoding' => 'Binary',
                'Content-Disposition'       => "attachment; filename=\"" . $fileName . "\""
            )
        );
    }
)
    ->bind('download')
    ->value('target', 'msmaker')
    ->value('version', 'latest');

/** Documentation */
$app->get(
    '/documentation',
    function () use ($app) {
        return $app['twig']->render('documentation.twig', array());
    }
)
    ->bind('documentation');

/** Changelog */
$app->get(
    '/changelog',
    function () use ($app) {
        return $app['twig']->render('changelog.twig', array());
    }
)
    ->bind('changelog');

/** Contact */
$app->get(
    '/contact',
    function () use ($app) {
        return $app['twig']->render('contact.twig', array());
    }
)
    ->bind('contact');

/** Error handle */
$app->error(
    function (\Exception $e, $code) use ($app) {
        if ($app['debug']) {
            return;
        }

        $page = 404 == $code ? '404.twig' : '500.twig';

        return new Response($app['twig']->render($page, array('code' => $code)), $code);
    }
);