<?php
require_once __DIR__ .'/../../db/src/library.class.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

CONST HUE_BASE_URL = "https://api.meethue.com/";

/** 
*** API Functions related to library
**/
$app->group('/hue', function (RouteCollectorProxy $group) {

  //Function to get the access_token, refresh_token, and app username from Hue API
  $group->post('/login', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['hue_read']){
      return $response->withStatus(401);
    }

    $hue = parse_ini_file(__DIR__ . '/../config/hue.ini');
    $request_body = $request->getParsedBody();
    $data = array();

    //Get the access token
    $url = HUE_BASE_URL . "v2/oauth2/token";

    $headers = [
      "Content-Type: application/x-www-form-urlencoded",
      "Authorization: Basic " . base64_encode($hue['clientid'] . ':' . $hue['clientsecret'])
    ];

    $code = $request_body['code'];
    $body = "grant_type=authorization_code&code=$code";

    $hue_data = hueCurl('POST', $url, $headers, $body);
    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }

    $data['access_token'] = $hue_data['access_token'];
    $data['refresh_token'] = $hue_data['refresh_token'];


    //Press Link Button
    $url = HUE_BASE_URL . "route/api/0/config";

    $headers = [
      "Content-Type: application/json",
      "Authorization: Bearer " . $data['access_token']
    ];

    $body = json_encode( array( "linkbutton"=> true ) );

    $hue_data = hueCurl('PUT', $url, $headers, $body);
    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }


    //Get Username
    $url = HUE_BASE_URL . "route/api";

    $headers = [
      "Content-Type: application/json",
      "Authorization: Bearer " . $data['access_token']
    ];

    $body = json_encode( ["devicetype"=>"home_intranet"] );

    $hue_data = hueCurl('POST', $url, $headers, $body);

    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }

    $data['username'] = $hue_data[0]['success']['username'];

    //Send Response
    $response->getBody()->write( json_encode( $data ) );
    
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to refresh the access_token, refresh_token, and app username from Hue API
  $group->post('/refresh', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['hue_read']){
      return $response->withStatus(401);
    }

    $hue = parse_ini_file(__DIR__ . '/../config/hue.ini');

    $data = array();

    //Get the access token
    $url = HUE_BASE_URL . "v2/oauth2/token";

    $headers = [
      "Content-Type: application/x-www-form-urlencoded",
      "Authorization: Basic " . base64_encode($hue['clientid'] . ':' . $hue['clientsecret'])
    ];

    $body = "grant_type=refresh_token&refresh_token=" . $request->getHeaderLine('hue_refresh_token');

    $hue_data = hueCurl('POST', $url, $headers, $body);

    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }

    $data['access_token'] = $hue_data['access_token'];
    $data['refresh_token'] = $hue_data['refresh_token'];


    //Press Link Button
    $url = HUE_BASE_URL . "route/api/0/config";

    $headers = [
      "Content-Type: application/json",
      "Authorization: Bearer " . $data['access_token']
    ];

    $body = json_encode( array( "linkbutton"=> true ) );

    $hue_data = hueCurl('PUT', $url, $headers, $body);

    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }

    //Get Username
    $url = HUE_BASE_URL . "route/api";

    $headers = [
      "Content-Type: application/json",
      "Authorization: Bearer " . $data['access_token']
    ];

    $body = json_encode( ["devicetype"=>"home_intranet"] );

    $hue_data = hueCurl('POST', $url, $headers, $body);

    $data['username'] = $hue_data[0]['success']['username'];

    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }

    //Send Response
    $response->getBody()->write( json_encode( $data ) );
    
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to get all the lights
  $group->get('/lights/list', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['hue_read']){
      return $response->withStatus(401);
    }

    //Get Devices
    $url = HUE_BASE_URL . "route/clip/v2/resource/light";

    $headers = [
      "Authorization: Bearer " . $request->getHeaderLine('hue_access_token'),
      "hue-application-key: " . $request->getHeaderLine('hue_username')
    ];

    $hue_data = hueCurl('GET', $url, $headers);

    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }

    //Send Response
    $response->getBody()->write( json_encode( $hue_data ) );
    
    return $response->withHeader('Content-Type', 'application/json');
  });

  //Function to toggle light on/off
  $group->put('/lights/toggle', function (Request $request, Response $response, $args) {

    $decoded = $request->getAttribute("token");
    $permissions = json_decode($decoded["scope"], true);

    if(!$permissions['hue_write']){
      return $response->withStatus(401);
    }

    $request_body = $request->getParsedBody();

    $url = HUE_BASE_URL . "route/clip/v2/resource/light/" . $request_body['id'];

    $body = json_encode([
      "target" => [
        "rid" => $request_body['id']
      ],
      "on" => [
        "on" => $request_body['on']
      ]
    ]);

    $headers = [
      "Authorization: Bearer " . $request->getHeaderLine('hue_access_token'),
      "hue-application-key: " . $request->getHeaderLine('hue_username'),
      "Content-Type: application/json"
    ];

    $hue_data = hueCurl('PUT', $url, $headers, $body);

    if(isset($hue_data['error'])){
      $response->getBody()->write( json_encode($hue_data['error_message']) );
      return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
    }

    //Send Response
    $response->getBody()->write( json_encode($hue_data) );
    
    return $response->withHeader('Content-Type', 'application/json');
  });

    //Function to change light brightness
    $group->put('/lights/brightness', function (Request $request, Response $response, $args) {

      $decoded = $request->getAttribute("token");
      $permissions = json_decode($decoded["scope"], true);
  
      if(!$permissions['hue_write']){
        return $response->withStatus(401);
      }
  
      $request_body = $request->getParsedBody();
  
      $url = HUE_BASE_URL . "route/clip/v2/resource/light/" . $request_body['id'];
  
      $body = json_encode([
        "target" => [
          "rid" => $request_body['id']
        ],
        "dimming" => [
          "brightness" => $request_body['brightness']
        ]
      ]);
  
      $headers = [
        "Authorization: Bearer " . $request->getHeaderLine('hue_access_token'),
        "hue-application-key: " . $request->getHeaderLine('hue_username'),
        "Content-Type: application/json"
      ];
  
      $hue_data = hueCurl('PUT', $url, $headers, $body);

      if(isset($hue_data['error'])){
        $response->getBody()->write( json_encode($hue_data['error_message']) );
        return $response->withStatus($hue_data['error'])->withHeader('Content-Type', 'application/json');
      }
  
      //Send Response
      $response->getBody()->write( json_encode($hue_data) );
      
      return $response->withHeader('Content-Type', 'application/json');
    });

});

function hueCurl($method, $url, $headers = null, $body = null) {
  $curl = curl_init($url);

  switch ($method) {
    case 'GET':
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
      break;
    case 'POST':
      curl_setopt($curl, CURLOPT_POST, 1);
      break;
    case 'PUT':
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    default:
      break;
  }

  if($headers) {
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  }
  
  if($body) {
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
  }
  
  
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

  $hue_response = json_decode( curl_exec($curl), true );
  $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if($http_status !== 200){
    return ["error" => $http_status, "error_message" => $hue_response['fault']['faultstring']];
  }

  curl_close($curl);

  return $hue_response;
}
