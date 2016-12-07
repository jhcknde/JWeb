<?php

require dirname(dirname(__FILE__)).'/vendor/autoload.php';
require dirname(dirname(__FILE__)).'/autoload.php';

$app = new Slim\App();

$app->get('/hello/{name}', function ($request, $response, $args) {
	$db = new \DB\Db();
	$db->test();
	ImageHelper::test();
	$response->write("Hello, " . $args['name']);
	return $response;
});

$app->run();
?>
