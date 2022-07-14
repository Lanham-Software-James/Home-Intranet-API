<?php
require_once __DIR__ .'/../../db/src/greenhouse.class.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use HomeIntranet\Database\Greenhouse;

$app->group('/greenhouse', function (RouteCollectorProxy $group) {
  //Function to list all the plants
  $group->get('/plants', function (Request $request, Response $response, $args) {

    $db = new Greenhouse();
    $data = json_encode($db->getPlants());
    $response->getBody()->write($data);
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to list all the plant species
  $group->get('/species', function (Request $request, Response $response, $args) {

    $db = new Greenhouse();
    $data = json_encode($db->getPlantSpecies());
    $response->getBody()->write($data);
    return $response->withHeader('Content-Type', 'application/json');
  });  

  //Function to list all the plant locations
  $group->get('/locations', function (Request $request, Response $response, $args) {

    $db = new Greenhouse();
    $data = json_encode($db->getPlantLocations());
    $response->getBody()->write($data);
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to add a new plant
  $group->post('/add', function (Request $request, Response $response, $args) {

    $db = new Greenhouse();
    $body = $request->getParsedBody();
    
    $db->addPlant($body['plantName'], $body['plantSpecies'], $body['plantLocation']);
    
    return $response;
  });

  //Function to delete a plant
  $group->delete('/delete', function (Request $request, Response $response, $args) {

    $db = new Greenhouse();
    $body = $request->getParsedBody();
    
    $db->deletePlant($body['plantID']);
    
    return $response;
  });
});