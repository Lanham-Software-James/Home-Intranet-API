<?php
require_once __DIR__ .'/../../db/src/user.class.php';

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
});