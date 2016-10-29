<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/28/16
 * Time: 9:45 PM
 */


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/managers/request/{id}', function (Request $request, Response $response) {
    return ((new \Controllers\ApiManagers())->request($request, $response));
});

$app->post('/api/managers/import/{id}', function (Request $request, Response $response) {
    return ((new \Controllers\ApiManagers()))->import($request, $response);
});

$app->post('/api/managers/interpret/{id}', function (Request $request, Response $response) {
    return ((new \Controllers\ApiManagers()))->interpret($request, $response);
});

$app->post('/api/managers/session/{id}', function (Request $request, Response $response) {
    return ((new \Controllers\ApiManagers()))->session($request, $response);
});

$app->post('/api/managers/evaluate/{id}', function (Request $request, Response $response) {
    return ((new \Controllers\ApiManagers()))->evaluate($request, $response);
});

function        post($url, $fields) {
    $fields_string = http_build_query($fields);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    curl_close($ch);
    return ($result);
}

$app->get('/api/managers/request/{id}', function (Request $request, Response $response) {
    $url = "127.0.0.1/index.php/api/managers/request/" . $request->getAttribute("id");
    $fields = array("table_data" => array("movies.id" => urlencode(200)));
    $response = $response->withHeader('Content-type', 'application/json');
    $response->getBody()->write(post($url, $fields));
    return ($response);
});


$app->get('/api/managers/interpret/{id}', function (Request $request, Response $response) {
    $url = "127.0.0.1/index.php/api/managers/interpret/" . $request->getAttribute("id");
    $fields = array("table_data" => array("movies.id" => urlencode(200)));
    $response = $response->withHeader('Content-type', 'application/json');
    $response->getBody()->write(post($url, $fields));
    return ($response);
});

$app->get('/api/managers/session/{id}', function (Request $request, Response $response) {
    $url = "127.0.0.1/index.php/api/managers/session/" . $request->getAttribute("id");
    $fields = array("table_data" => array("movies.id" => urlencode(200)));
    $response = $response->withHeader('Content-type', 'application/json');
    $response->getBody()->write(post($url, $fields));
    return ($response);
});

$app->get('/api/managers/evaluate/{id}', function (Request $request, Response $response) {
    $url = "127.0.0.1/index.php/api/managers/evaluate/" . $request->getAttribute("id");
    $fields = array("table_data" => array("movies.id" => urlencode(200)));
    $response = $response->withHeader('Content-type', 'application/json');
    $response->getBody()->write(post($url, $fields));
    return ($response);
});

$app->get('/api/managers/import/{id}', function (Request $request, Response $response) {
    $dsn = 'pgsql:host=localhost;dbname=pocdata';
    $usr = 'sebastien';
    $pwd = 'passpass';
    $pdo = new Slim\PDO\Database($dsn, $usr, $pwd);

    $url = "127.0.0.1/index.php/api/managers/request/" . $request->getAttribute("id");
    $rq = json_decode(post($url, array()), true);

    $st = $pdo->prepare($rq["request"]);
    $st->execute();
    $data = array();
    $res = array();
    $url = "127.0.0.1/index.php/api/managers/import/"  . $request->getAttribute("id");
    while (($tmp = $st->fetch())) {
        $obj = array("api_id" => $tmp['id'], "data" => $tmp);
        $data[] = $obj;
        if (sizeof($data) > 50) {
            $res[] = $data;
            $data = array();
        }
    }
    $w = 0;
    foreach ($res as $uRes) {
        $tmp = post($url, array("data_import" => $uRes));
        $w += json_decode($tmp)->write;
    }
    $response->getBody()->write($w);

    $response = $response->withHeader('Content-type', 'application/json');
    return ($response);
});