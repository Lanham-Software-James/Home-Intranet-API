<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\JwtAuthentication;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$secret = parse_ini_file(__DIR__ . '/config/secret.ini');

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//JWT Middleware Configuration
$app->add(new JwtAuthentication([
  "ignore" => [
                "/auth/token",
                "/s3/carousel"
              ],
  "secret" => $secret['secret'],
  "error" => function ($response, $arguments) {
                $data["status"] = "error";
                $data["message"] = $arguments["message"];
        
                $response->getBody()->write(
                    json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
                );
        
                return $response->withHeader("Content-Type", "application/json");
            }
]));

//CORS Configuration
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, hue_access_token, hue_refresh_token, hue_username')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

//CORS preflight
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

//API Base
$app->get('/', function (Request $request, Response $response, $args) {
  $response->getBody()->write('Boo! Did I scare you?');
  return $response;
});

require __DIR__ . "/routes/auth.php";
require __DIR__ . "/routes/greenhouse.php";
require __DIR__ . "/routes/library.php";
require __DIR__ . "/routes/user.php";
require __DIR__ . "/routes/litterbox.php";
require __DIR__ . "/routes/lights.php";
require __DIR__ . "/routes/s3.php";

$app->run();