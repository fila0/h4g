<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Fila0\Utils\Api;


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array());
})
->bind('homepage')
;

$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->get('/{slug}/', function(Request $request, $slug) use ($app) {
	$params = $request->query->all();
	$api = new Api ($app, $slug, $params);	
	if ($api->execute ()) return $app['twig']->render('api.html',$api->showResults());
	else return $app['twig']->render('api.html',$api->showError());	
})
->assert("slug", "isServerUp|insertDonation|getProjectDatas");


$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $page = 404 == $code ? '404.html' : '500.html';

    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});
