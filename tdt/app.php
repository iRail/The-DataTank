<?php

require 'Slim/Slim.php';
require 'rb.php';
require 'models.php';
require 'serializer.php';

$app = new Slim(array(
    'debug' => true,
    'log.enable' => true,
    'log.path' => '/tmp/',
    'log.level' => 4,
    'database.dsn' => 'sqlite:/tmp/tdt.db',
    'database.user' => '',
    'database.password' => '',
    'tdt.content-type' => 'json'
));
$app->setName('TheDataTank');

R::setup($app->config('database.dsn'),
         $app->config('database.user'),
         $app->config('database.password')
);

$app->get('/', function() {
    echo '<h1>The Data-Tank</h1>';
});

require 'api.php';

$app->run();

?>
