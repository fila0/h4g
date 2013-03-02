<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Fila0\Utils\Api;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\User;

$app->match('/', function (Request $request) use ($app) {

    $data = array(
        'name' => 'Your name',
        'email' => 'Your email',
    );

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('email', 'email', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Email())
        ))
        ->add('password', 'repeated', array(
            'first_name'  => 'password',
            'second_name' => 'confirm',
            'type'        => 'password',
            'constraints' => array(new Assert\NotBlank())
        ))
        // ->add('name', 'text', array(
        //     'required' => false
        // ))
        // ->add('url', 'url', array(
        //     'required' => false
        // ))
        // ->add('phone', 'text', array(
        //     'required' => false
        // ))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $roles = array(
                'ROLE_USER'
            );
            $user = new User($data['email'], $data['password'], $roles, true, true, true, true);

            // find the encoder for a UserInterface instance
            $encoder = $app['security.encoder_factory']->getEncoder($user);

            // compute the encoded password for foo
            $password = $encoder->encodePassword($data['password'], $user->getSalt());

            $apiKey = uniqid();
            $apiSecret = base64_encode(hash_hmac ('sha256', (time()*rand(0,10)) . rand(0,99999) . $apiKey, SECRET_KEY));

            $params = array(
                'username' => $data['email'],
                'email' => $data['email'],
                'password' => $password,
                'roles' => serialize($roles),
                'apiKey' => $apiKey,
                'apiSecret' => $apiSecret
            );

            $insertSql = "INSERT INTO `users` (
                `id` ,
                `api_key` ,
                `api_secret` ,
                `username` ,
                `email` ,
                `password` ,
                `roles` ,
                `contactname` ,
                `phone` ,
                `url` ,
                `logo_url` ,
                `cif` ,
                `account_number` ,
                `last_login`
            )
                VALUES (
                NULL , :apiKey, :apiSecret, :email, :email, :password, :roles, '', '', '', '', '', '', NOW()
            );";
            $app['db']->executeUpdate($insertSql, $params);

            // redirect somewhere
            return $app->redirect($app['url_generator']->generate('dashboard'));
        }
    }

    // display the form
    return $app['twig']->render('index.html', array('form' => $form->createView()));
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

$app->get('/dashboard', function(Request $request) use ($app) {

    $token = $app['security']->getToken();

    if (null !== $token) {
        $user = $token->getUser();
    }
    else {
        return $app->redirect($app['url_generator']->generate('login_path'));
    }

    var_dump($user->getData());die;

    return $app['twig']->render('dashboard.html');
})
->bind('dashboard');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $page = 404 == $code ? '404.html' : '500.html';

    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});
