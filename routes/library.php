<?php
require_once __DIR__ .'/../../db/src/library.class.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use HomeIntranet\Database\Library;

/** 
*** API Functions related to library
**/
$app->group('/library', function (RouteCollectorProxy $group) {

  //Function to list all the books and authors
  $group->get('/books', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['library_read']){
      return $response->withStatus(401);
    }

    $db = new Library();
    
    $data = json_encode($db->getBookAuthors());
    $response->getBody()->write($data);
    
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to list all the authors
  $group->get('/authors', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['library_read']){
      return $response->withStatus(401);
    }

    $db = new Library();

    $data = json_encode($db->getAuthors());
    $response->getBody()->write($data);
    
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to check in a book
  $group->put('/checkin', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['library_check']){
      return $response->withStatus(401);
    }

    $db = new Library();
    $body = $request->getParsedBody();

    
    $db->checkInBook($body['bookID']);
    
    $db->logActivity($decoded['user'], 3, $body['bookID']);
    return $response;
  });

  //Function to check out a book
  $group->put('/checkout', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['library_check']){
      return $response->withStatus(401);
    }

    $db = new Library();
    $body = $request->getParsedBody();

    $db->checkOutBook($body['bookID'], $decoded['user']);

    $db->logActivity($decoded['user'], 4, $body['bookID']);
    return $response;
  });

  //Function to add a new book
  $group->post('/add', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['library_write']){
      return $response->withStatus(401);
    }

    $db = new Library();
    $body = $request->getParsedBody();
    
    $newBookID = $db->addBook($body['bookTitle'], $body['authorFirstName'], $body['authorMiddleName'], $body['authorLastName']);

    $db->logActivity($decoded['user'], 5, $newBookID['data']['new_book_id']);
    return $response;
  });

  // Function to delete a book
  $group->delete('/delete', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['library_delete']){
      return $response->withStatus(401);
    }    

    $db = new Library();
    $body = $request->getParsedBody();
    
    $db->deleteBook($body['bookID'], $body['authorID']);

    $db->logActivity($decoded['user'], 6, $body['bookID']);
    return $response;
  });
});