<?php
require_once __DIR__ .'/../../db/src/litterbox.class.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use HomeIntranet\Database\LitterBox;

$app->group('/litterbox', function (RouteCollectorProxy $group) {
  //Function to list all the plants
  $group->get('/fosters', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['litterbox_read']){
      return $response->withStatus(401);
    }

    $db = new LitterBox();
    $data = json_encode($db->getFosters());
    $response->getBody()->write($data);
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to add a new foster
  $group->post('/add', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['litterbox_write']){
      return $response->withStatus(401);
    }

    $db = new LitterBox();
    $body = $request->getParsedBody();

    $newFosterID = $db->addFoster($body['fosterName']);
    
    $db->logActivity($decoded['user'], 14, $newFosterID['data']['new_foster_id']);
    return $response;
  });

   //Function to delete a foster
   $group->delete('/delete', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['litterbox_delete']){
      return $response->withStatus(401);
    }

    $db = new LitterBox();
    $body = $request->getParsedBody();
    
    $db->deleteFoster($body['fosterID']);
    
    $db->logActivity($decoded['user'], 15, $body['fosterID']);
    return $response;
  });
});