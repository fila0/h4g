<?php

require __DIR__.'/parameters.php';

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array (
        'driver'    => 'pdo_mysql',
        'host'      => MYSQL_HOST,
        'dbname'    => MYSQL_DB,
        'user'      => MYSQL_USER,
        'password'  => MYSQL_PASS,
        'charset'   => 'utf8',
    ),
));