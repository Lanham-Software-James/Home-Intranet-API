<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

require_once '../db/db.class.php';

$app = AppFactory::create();

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($request, $handler) {

  $CORSini = parse_ini_file('cors.ini');

  $response = $handler->handle($request);
  return $response
          ->withHeader('Access-Control-Allow-Origin', $CORSini['local'], $CORSini['server'])
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', function (Request $request, Response $response, $args) {

  $response->getBody()->write('Home Intranet API Version 0.01');
  return $response;

});

$app->get('/listbooks', function (Request $request, Response $response, $args) {

  $db = new DB();
  $q = json_encode($db->getBookAuthors());
  $response->getBody()->write($q);
  return $response->withHeader('Content-Type', 'application/json');

});

$app->run();