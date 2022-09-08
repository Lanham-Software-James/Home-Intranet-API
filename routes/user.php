<?php
require_once __DIR__ .'/../../db/src/user.class.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use HomeIntranet\Database\User;

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
    
    // $db->logActivity($decoded['user'], 8); Disable read logging, garbage
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
    
    // $db->logActivity($decoded['user'], 9);  Disable read logging, garbage
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
    
    $newUserID = $db->addUser($body['userName'], $hashed_password, $body['firstName'], $body['lastName'], $body['userRole']);
    
    $db->logActivity($decoded['user'], 10, $newUserID['data']['new_user_id']);
    return $response;
  });

    //Function to delete a user
    $group->delete('/delete', function (Request $request, Response $response, $args) {

      $decoded = $request->getAttribute("token");
      $permissions = json_decode($decoded["scope"], true);
        
      if(!$permissions['user_delete']){
        return $response->withStatus(401);
      }    
  
      $db = new User();
      $body = $request->getParsedBody();
      
      $db->deleteUser($body['userID']);
      
      $db->logActivity($decoded['user'], 11, $body['userID']);
      return $response;
    });

});