<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Fila0\User\UserProvider;

$app = new Application();
$app->register(new TranslationServiceProvider(), array(
    'locale_fallback' => 'es',
));
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
}));

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/dashboard',
            'form' => array('login_path' => '/login', 'check_path' => '/dashboard/login_check'),
            'logout' => array('logout_path' => '/dashboard/logout'),
            'users' => $app->share(function () use ($app) {
                return new UserProvider($app['db']);
            }),
        ),
    ),
    'security.access_rules' => array(
        array('^/dashboard', array('ROLE_ADMIN', 'ROLE_USER')), // , 'https'),
    )
));

return $app;
