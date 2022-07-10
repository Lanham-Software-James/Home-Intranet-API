<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';
require_once '../db/db.class.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

//Options preflight
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

//CORS Configuration
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

//API Base
$app->get('/', function (Request $request, Response $response, $args) {

  $response->getBody()->write('Home Intranet API Version 0.2');
  return $response;
});

/** 
*** API Functions related to library
**/
$app->group('/library', function (RouteCollectorProxy $group) {

  //Function to display number of books
  $group->get('/count', function (Request $request, Response $response, $args) {

    $db = new DB();
    $q = json_encode($db->countBooks());
    $response->getBody()->write($q);
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to list all the books and authors
  $group->get('/list', function (Request $request, Response $response, $args) {

    $db = new DB();
    $q = json_encode($db->getBookAuthors());
    $response->getBody()->write($q);
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to check in a book
  $group->put('/checkin', function (Request $request, Response $response, $args) {

    $db = new DB();
    $body = $request->getParsedBody();

    $db->checkInBook($body['bookID']);
    
    return $response;
  });

  //Function to check out a book
  $group->put('/checkout', function (Request $request, Response $response, $args) {

    $db = new DB();
    $body = $request->getParsedBody();

    $db->checkOutBook($body['bookID'], $body['name']);
    
    return $response;
  });

  //Function to add a new book
  $group->post('/add', function (Request $request, Response $response, $args) {

    $db = new DB();
    $body = $request->getParsedBody();
    
    $db->addBook($body['bookTitle'], $body['authorFirstName'], $body['authorMiddleName'], $body['authorLastName']);
    
    return $response;
  });

  /**
  *** THIS NEEDS TO BE FIXED!!!
  ***/
  //Function to delete a book
  // $group->delete('/delete', function (Request $request, Response $response, $args) {

  //   $db = new DB();
  //   $body = $request->getParsedBody();
    
  //   $db->deleteBook($body['bookID']);
    
  //   return $response;
  // });
});

$app->run();