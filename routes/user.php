<?php
require_once __DIR__ .'/../../db/src/user.class.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use HomeIntranet\Database\User;
use \Firebase\JWT\JWT;

$app->group('/user', function (RouteCollectorProxy $group) {
  //Function to list all the users
  $group->get('/users', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);
      
    if(!$permissions['user_read']){
      return $response->withStatus(401);
    }    

    $db = new User();
    
    $q = json_encode($db->getUsers());
    $response->getBody()->write($q);
    
    return $response->withHeader('Content-Type', 'application/json');;
  });

  //Function to list all the roles
  $group->get('/roles', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);
      
    if(!$permissions['user_read']){
      return $response->withStatus(401);
    }    

    $db = new User();
    
    $q = json_encode($db->getRoles());
    $response->getBody()->write($q);
    
    return $response->withHeader('Content-Type', 'application/json');;
  });


  //Function to add a new user
  $group->post('/add', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);
      
    if(!$permissions['user_write']){
      return $response->withStatus(401);
    }    

    $db = new User();
    $body = $request->getParsedBody();

    $hashed_password = password_hash($body['password'], PASSWORD_DEFAULT);
    
    $db->addUser($body['userName'], $hashed_password, $body['firstName'], $body['lastName'], $body['userRole']);
    
    return $response;
  });

});