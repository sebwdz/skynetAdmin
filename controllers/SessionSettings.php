<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/26/16
 * Time: 5:00 PM
 */

namespace Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\Twig as View;

class SessionSettings
{
    public function show(View $view, Request $request, Response $response) {
        $response = $view->render($response, "learn/session/settings.html.twig", ["tickets" => array("a", "b")]);
        return $response;
    }
}