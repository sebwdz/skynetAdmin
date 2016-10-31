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
        $settings = json_decode($manager->getSettings(), true);
        $db = \Main\MyPdo::getMongoDb("pocdata");
        $type = $settings['type'];
        $collection = $db->$type;
        if (($data = $request->getParsedBody()['data_import']) && is_array($data)) {
            $data = array_slice($data, 0, 10);
            foreach ($data as $key => $uData) {
                foreach ($uData as $key2 => $field) {
                    if (is_string($field) && substr($field, 0, 1) == "{" || substr($field, 0, 1) == '[')
                        $uData[$key2] = json_decode($field, true);
                }
                foreach ($settings['references'] as $key2 => $link) {
                    $col = $db->$link;
                    $id = $col->findOne(array("api_id" => $uData[$key2]))['_id'];
                    unset($uData[$key2]);
                    $uData[$link] = $id;
                }
                $data[$key] = $uData;
            }
        }
        $collection->insertMany($data);
        $response->getBody()->write(sizeof($data));
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
        $st = \Main\MyPdo::getInstance()->prepare("SELECT id FROM mb_networks WHERE type = :type AND api_id = :api_id");
        $st->bindValue(":type", $settings['type']);
        $st->bindValue(":api_id", $request->getParsedBody()['table_data'][$settings['api_id']]);
        $st->execute();
        if (!$st->fetch()) {
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
        }
        $response->getBody()->write('{"result" : "ok"}');
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
        if ($network) {
            $service = new \Services\Manager();
            $interpret = (new \Managers\Manager())->findOne($settings['interpreter']);
            $tmp = $service->run_interpreter(json_decode($interpret->getSettings(), true), $request->getParsedBody()['table_data']);
            $req = array("network" => json_decode($network['data'], true), "tests" => $tmp);
            $res = json_decode(\Services\Thrift::launch("evaluate", $req), true);
            $show = 0;
            $vt = 0;
            $good = 0;
            $er = 0;
            $no = 0;
            $limit = 3.25;
            for ($it = 0; $it < sizeof($res); $it++) {
                if ($res[$it]['res'][0] > $limit)
                    $show++;
                if ($tmp[$it]["outputs"][0] > 3)
                    $vt++;
                if ($tmp[$it]["outputs"][0] > 3 && $res[$it]['res'][0] > $limit)
                    $good++;
                if ($tmp[$it]["outputs"][0] < 3)
                    $no++;
                if ($tmp[$it]["outputs"][0] < 3 && $res[$it]['res'][0] > $limit)
                    $er++;
            }
            $response->getBody()->write(json_encode(array(
                "show" => $show,
                "want" => $vt,
                "good" => $good,
                "err" => $er,
                "no" => $no
            )));
            $response = $response->withHeader('Content-type', 'application/json');
        }
        return ($response);
    }
}