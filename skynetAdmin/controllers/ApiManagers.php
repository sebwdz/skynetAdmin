<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/28/16
 * Time: 9:53 PM
 */

namespace       Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class           ApiManagers {
    public function         request(Request $request, Response $response) {
        $manager = (new \Managers\Manager())->findOne($request->getAttribute("id"));
        $response = $response->withHeader('Content-type', 'application/json');
        $settings = json_decode($manager->getSettings(), true);
        if ($manager->getType() == "importer" || $manager->getType() == "interpreter") {
            $manager = (new \Managers\Manager())->findOne($settings["request"]);
            $settings = json_decode($manager->getSettings(), true);
        }
        if ($manager->getType() == "request") {
            $service = new \Services\Manager();
            $request = $service->request($settings['request'], json_decode($request->getParsedBody()["table_data"], true));
            $response->getBody()->write(json_encode(array("request" => $request)));
        } else
            $response->getBody()->write('{"error" : "manager isn\'t a request manager"}');
        return ($response);
    }

    public function         import(Request $request, Response $response) {
        $row = 0;
        $error = 0;
        $response = $response->withHeader('Content-type', 'application/json');
        $manager = (new \Managers\Manager())->findOne($request->getAttribute("id"));
        if (($data = $request->getParsedBody()['data_import']) && is_array($data)) {
            foreach ($data as $uData) {
                $query = "INSERT INTO mb_object(type, data, api_id) VALUES (:type, :data, :api_id)";
                $st = \Main\MyPdo::getInstance()->prepare($query);
                $st->bindValue(":type", json_decode($manager->getSettings())->type);
                if (!is_string($uData['data']))
                    $uData['data'] = json_encode($uData['data']);
                $st->bindValue(":data", $uData['data']);
                $st->bindValue(":api_id", $uData['api_id']);
                try {
                    $st->execute();
                    $row++;
                } catch (\Exception $e) {
                    $error++;
                }
            }
        }
        $response->getBody()->write(json_encode(array("writes" => $row, "errors" => $error)));
        return ($response);
    }

    public function     interpret(Request $request, Response $response) {
        $manager = (new \Managers\Manager())->findOne($request->getAttribute("id"));
        $settings = json_decode($manager->getSettings(), true);
        $service = new \Services\Manager();
        $res = $service->run_interpreter($settings, $request->getParsedBody()["table_data"]);
        $response->getBody()->write(json_encode(array("results" => $res)));
        return ($response);
    }

    public function     session(Request $request, Response $response) {
        $manager = (new \Managers\Manager())->findOne($request->getAttribute("id"));
        $settings = json_decode($manager->getSettings(), true);
        $network = (new \Managers\Manager())->findOne($settings['network']);
        $network = json_decode($network->getSettings(), true);
        $interpret = (new \Managers\Manager())->findOne($settings['interpreter']);
        $service = new \Services\Manager();
        $res = $service->run_interpreter(json_decode($interpret->getSettings(), true), $request->getParsedBody()['table_data']);
        $rq = array("network" => $network, "session" => $settings['session'], "exps" => $res);
        $res = \Services\Thrift::launch("learn", $rq);
        $query = "INSERT INTO mb_networks(type, data, api_id) VALUES (:type, :data, :api_id)";
        $st = \Main\MyPdo::getInstance()->prepare($query);
        $st->bindValue(":type", $settings['type']);
        $st->bindValue(":data", $res);
        $st->bindValue(":api_id", $request->getParsedBody()['table_data'][$settings['api_id']]);
        $st->execute();
        $response = $response->withHeader('Content-type', 'application/json');
        return ($response);
    }

    public function     evaluate(Request $request, Response $response) {
        $manager = (new \Managers\Manager())->findOne($request->getAttribute("id"));
        $settings = json_decode($manager->getSettings(), true);

        $query = "SELECT mb_networks.data FROM mb_networks WHERE type = :type AND api_id = :api_id";
        $st = \Main\MyPdo::getInstance()->prepare($query);
        $st->bindValue(":type", $settings['type']);
        $st->bindValue(":api_id", $request->getParsedBody()["table_data"][$settings['api_id']]);
        $st->execute();
        $network = $st->fetch();
        $query = "SELECT opinion.api_id, opinion.data->>'rate' as rate, userl.data->>'views' as views FROM mb_object as opinion
        INNER JOIN mb_object as userl ON userl.api_id = CAST(opinion.data->>'fk_user_id' AS INT) AND userl.type = 'user'
        WHERE CAST(opinion.data->>'fk_movie_id' AS INT) = 200 AND opinion.type = 'opinion_test'";
        $st = \Main\MyPdo::getInstance()->prepare($query);
        $st->execute();
        $rq = $st->fetchAll();
        $inter = (new \Managers\Manager())->findOne($settings['interpreter']);
        $set = json_decode($inter->getSettings(), true);
        $service = new \Services\Manager();
        $res = $service->interpret($set['data'], $rq);
        $req = array("network" => json_decode($network['data'], true), "tests" => $res);
        \Services\Thrift::launch("evaluate", $req);
        $response = $response->withHeader('Content-type', 'application/json');
        return ($response);
    }
}