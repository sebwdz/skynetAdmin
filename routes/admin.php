<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->get('/managers/{type}/{id}', function (Request $request, Response $response) {
    return (new \Controllers\ManagerSettings())->show_edit($this->view, $request, $response);
});

$app->get('/managers/{type}', function (Request $request, Response $response) {
    return (new \Controllers\ManagerSettings())->show_edit($this->view, $request, $response);
});

$app->get('/managers', function (Request $request, Response $response) {
    return (new \Controllers\ManagerSettings())->show($this->view, $request, $response);
});

$app->post('/managers/{type}', function (Request $request, Response $response) {
    return (new \Controllers\ManagerSettings())->add($this->view, $request, $response);
});

$app->post('/managers/{type}/{id}', function (Request $request, Response $response) {
    return (new \Controllers\ManagerSettings())->add($this->view, $request, $response);
});


?>