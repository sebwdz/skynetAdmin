<?php

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig("templates/");
    $twig = $view->getEnvironment();
    $twig->addGlobal("session", $_SESSION);
    return $view;
};

include ("routes/admin.php");
include ("routes/api.php");

$app->run();