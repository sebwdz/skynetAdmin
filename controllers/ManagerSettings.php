<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/27/16
 * Time: 9:09 PM
 */

namespace       Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\Twig as View;

class           ManagerSettings {
    public function         show(View $view, Request $request, Response $response) {
        $managers = (new \Managers\Manager())->findAll();
        return ($view->render($response, "manager/list.html.twig", array("managers" => $managers)));
    }

    public function         show_edit(View $view, Request $request, Response $response) {
        $manager = null;
        if ($request->getAttribute("id"))
            $manager = (new \Managers\Manager())->findOne($request->getAttribute("id"));
        $response = $view->render($response, "manager/edit.html.twig", array("manager" => $manager));
        return $response;
    }

    public function         add(View $view, Request $request, Response $response) {
        $manager = new \Entities\Manager();
        $manager->setName($request->getParsedBody()["name"]);
        $manager->setType($request->getAttribute("type"));
        $manager->setSettings($request->getParsedBody()["settings"]);
        if ($request->getAttribute("id"))
            (new \Managers\Manager())->updateOne($manager, $request->getAttribute("id"));
        else
            (new \Managers\Manager())->addOne($manager);
        return ($this->show($view, $request, $response));
    }
}