<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\CorsMiddleware;


require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add(new CorsMiddleware);

// //Options preflight
// $app->options('/{routes:.+}', function ($request, $response, $args) {
//   return $response;
// });

// //CORS Configuration
// $app->add(function ($request, $handler) {
//   $response = $handler->handle($request);
//   return $response
//           ->withHeader('Access-Control-Allow-Origin', '*')
//           ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
//           ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
// });

//API Base
$app->get('/', function (Request $request, Response $response, $args) {
  $response->getBody()->write('Home Intranet API Version 0.2');
  return $response;
});

require __DIR__ . "/routes/greenhouse.php";
require __DIR__ . "/routes/library.php";

$app->run();