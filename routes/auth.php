<?php
require_once __DIR__ .'/../../db/src/user.class.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use HomeIntranet\Database\User;
use \Firebase\JWT\JWT;

$app->group('/auth', function (RouteCollectorProxy $group) {

  $group->post('/token', function (Request $request, Response $response, $args) {

    $db = new User();
    $body = $request->getParsedBody();
    
    $user = $db->getUserForAuth($body['userName']);

    if(!isset($user['password'])) {
      $values = [
        "success" => false,
        "message" => "Incorrect Username.",
        "is_admin" => null,
        "token" => null
      ];
      
      $response->getBody()->write(json_encode($values));
      
    } else if (!password_verify($body['password'], $user['password'])) {
      $values = [
        "success" => false,
        "message" => "Incorrect Password.",
        "is_admin" => null,
        "token" => null
      ];
      
      $response->getBody()->write(json_encode($values));

    } else {
      $resVals = [
        "success" => true,
        "message" => "Login Successful!",
        "is_admin" => ($user['user_role']) == 2 ? 'true' : 'false',
        "token" => generateToken($user['user_role'])
      ];

      $response->getBody()->write(json_encode($resVals));
    }
    
    return $response->withHeader('Content-Type', 'application/json');;
  });
});

//Function to generate JWT based on user role
function generateToken($role): string {

  $secret = parse_ini_file(__DIR__.'/../config/secret.ini');
  $db = new User();
  $query = $db->getPermissionsByRoleID($role);

  $values = [
    "iat" => time(),
    "exp" => time() + 604800,
    "scope" => $query['permissions']
  ];

  return JWT::encode($values, $secret['secret'], 'HS256');
}