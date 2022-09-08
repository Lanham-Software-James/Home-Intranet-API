<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Aws\S3\S3Client;

$app->group('/s3', function (RouteCollectorProxy $group) {

  //Function to get the images for the carousel from the S3 bucket
  $group->get('/carousel', function (Request $request, Response $response, $args) {
    $s3 = parse_ini_file(__DIR__ . '/../config/s3.ini');
    $s3client = new S3Client(['region' => 'us-east-1', 'version' => 'latest', 'credentials' => ["key" => $s3['keyid'], "secret" => $s3['key']]]);
   
    $res = [];

    $contents = $s3client->listObjects([
      'Bucket' => $s3['bucket'],
      'Prefix' => 'homepage-carousel/'
    ]);

    foreach ($contents['Contents'] as $content) {
      if (substr($content['Key'], -1) != '/') {
        array_push($res, $s3['http'] . $content['Key']);
      }
    }

    $res = json_encode($res);
    
    $response->getBody()->write($res);
    
    return $response->withHeader('Content-Type', 'application/json');;
  });

  //Function to get all files
  $group->get('/objects', function (Request $request, Response $response, $args) {

    //If file read
    // $decoded = $request->getAttribute("token");
    // $permissions = json_decode($decoded["scope"], true);

    // if(!$permissions['hue_read']){
    //   return $response->withStatus(401);
    // }

    $params = $request->getQueryParams();

    $s3 = parse_ini_file(__DIR__ . '/../config/s3.ini');
    $s3client = new S3Client(['region' => 'us-east-1', 'version' => 'latest', 'credentials' => ["key" => $s3['keyid'], "secret" => $s3['key']]]);
   
    $res = [];
    $dirs = [];

    $contents = $s3client->listObjects([
      'Bucket' => $s3['bucket'],
      'Prefix' => $params['location']
    ]);

    foreach ($contents['Contents'] as $content) {
      if(strcmp($params['location'], $content['Key']) == 0){

      }
      else if(substr($content['Key'], -1) == '/' && isFile($dirs, $content['Key'])){
        $fileName = explode('/', $content['Key']);
        $fileCount = count($fileName) - 2;

        array_push($dirs, $content['Key']);
        array_push($res, $fileName[$fileCount] . "/");
      } 
      else if(isFile($dirs, $content['Key'])){
        $fileName = explode('/', $content['Key']);
        $fileCount = count($fileName) - 1;
        
        array_push($res, $fileName[$fileCount]);
      }
        
    }

    $res = json_encode($res);
    
    $response->getBody()->write($res);
    
    return $response->withHeader('Content-Type', 'application/json');;
  });
});

function isFile($dirs, $content) {
  $val = true;

  foreach ($dirs as $key => $value) {
    if(str_contains($content, $value)) {
      $val = false;
    }
  }

  return $val;
}