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

$app->get('/api/managers/evaluate/{id}/{movie}', function (Request $request, Response $response) {
    $url = "127.0.0.1/index.php/api/managers/evaluate/" . $request->getAttribute("id");
    $fields = array("table_data" => array("movies.id" => urlencode($request->getAttribute("movie"))));
    $response = $response->withHeader('Content-type', 'application/json');
    $response->getBody()->write(post($url, $fields));
    return ($response);
});

$app->get('/api/managers/evaluate/{id}', function (Request $request, Response $response) {
    $url = "127.0.0.1/index.php/api/managers/evaluate/" . $request->getAttribute("id");
    $dsn = 'pgsql:host=localhost;dbname=pocdata';
    $usr = 'sebastien';
    $pwd = 'passpass';
    $pdo = new Slim\PDO\Database($dsn, $usr, $pwd);
    $st = $pdo->query("SELECT id FROM users");
    $resut = array("show" => 0, "want" => 0, "good" => 0, "err" => 0, "no" => 0);
    while (($t = $st->fetch())) {
        $fields = array("table_data" => array("users.id" => urlencode($t['id'])));
        $tmp = post($url, $fields);
        $x = json_decode($tmp, true);
        echo $tmp . "\n";
        $resut['show'] += $x['show'];
        $resut['want'] += $x['want'];
        $resut['good'] += $x['good'];
        $resut['err'] += $x['err'];
        $resut['no'] += $x['no'];
    }
    echo json_encode($resut). "\n";
    $response = $response->withHeader('Content-type', 'application/json');
    return ($response);
});

/*
if ($vt) {
    echo "good => \t\t" . $good . ' / ' . $vt . " \t" . round(($good / $vt) * 100, 2) ." % \n";
    echo "bad => \t\t\t" . $er . ' / ' . $no . " \t" . round(($er / $no) * 100, 2) . " % \n";
    echo "show => \t\t" . $show . ' / ' . sizeof($res) . " \t" . round(($show / sizeof($res)) * 100, 2) . " % \n";
    echo "want => \t\t" . $vt . ' / ' . sizeof($res) . " \t" . round(($vt / sizeof($res)) * 100, 2) . " % \n";
    echo "don't want => \t\t" . $no . ' / ' . sizeof($res) . " \t" . round(($no / sizeof($res)) * 100, 2) . " % \n";
    echo "don't want / want => \t" . $no . ' / ' . $vt . " \t" . round(($no / $vt) * 100, 2) . " % \n";
}
*/

$app->get('/api/learnAll/{session}/{evaluate}', function (Request $request, Response $response) {
    $st = \Main\MyPdo::getInstance()->query("SELECT * FROM mb_object WHERE type = 'user'");
    $res = $st->fetchAll();
    $this->view->render($response, "api/tests/learn.html.twig", array("movies" => json_encode($res)));
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
        $obj = $tmp;
        $data[] = $obj;
        if (sizeof($data) > 4) {
            $res[] = $data;
            $data = array();
        }
    }
    $res[] = $data;
    $w = 0;
    $t = 0;
    foreach ($res as $uRes) {
        $t += sizeof($uRes);
        $tmp = post($url, array("data_import" => $uRes));
        $w += $tmp;
    }
    $response->getBody()->write($w / $t);
    $response = $response->withHeader('Content-type', 'application/json');
    return ($response);
});