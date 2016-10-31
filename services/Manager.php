<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/29/16
 * Time: 4:47 PM
 */

namespace           Services;

class               Manager {

    public function     request($request, $data) {
        if (is_array($data)) {
            foreach ($data as $key => $uData)
                $request = str_replace(":" . $key, $uData, $request);
        }
        return ($request);
    }

    public function     requestLine($db, $requests, $id, $data) {
        $res = array();
        $keys = array_keys($requests);
        if (sizeof($keys) > $id) {
            $key = $keys[$id];
            $col = $db->$key;
            $req = $requests[$key]['filter'];
            foreach ($req as $key2 => $f) {
                if (substr($f, 0, 1) == '$')
                    $req[$key2] = $data[substr($f, 1)];
            }
            if ($requests[$key]['type'] == "one") {
                $tmp = $col->findOne($req);
                $tmp = array_merge((array)$tmp, $data);
                $res = $this->requestLine($db, $requests, $id + 1, $tmp);
            } else {
                $tmp = $col->find($req);
                foreach ($tmp as $val) {
                    $val = array_merge((array)$val, $data);
                    $res = array_merge($res, $this->requestLine($db, $requests, $id + 1, $val));
                }
            }
        } else
            $res = array($data);
        return ($res);
    }

    public function     requestMb($request, $data) {
        $db = \Main\MyPdo::getMongoDb("pocdata");
        $res = $this->requestLine($db, $request, 0, $data);
        return ($res);
    }

    public function     interpret($settings, $inputs) {
        $res = array();
        if (is_array($inputs)) {
            foreach ($inputs as $uInput) {
                if ($uInput) {
                    $tmp = array();
                    foreach ($settings as $key => $uSettings) {
                        $tmp[$key] = array();
                        foreach ($uSettings as $field) {
                            if (is_array($field)) {
                                if (!is_array($uInput[$field["from"]]) && !is_object($uInput[$field["from"]]))
                                    $json = json_decode($uInput[$field["from"]], true);
                                else
                                    $json = $uInput[$field["from"]];
                                foreach ($field["fields"] as $ufield) {
                                    $tmp[$key][] = $json[$ufield];
                                }
                            } else
                                $tmp[$key][] = $uInput[$field];
                        }
                    }
                    $res[] = $tmp;
                }
            }
        }
        return ($res);
    }

    public function     run_interpreter($settings, $data) {
        if ($data && is_array($data)) {
            $rq = (new \Managers\Manager())->findOne($settings["request"]);
            $inputs = $this->requestMb(json_decode($rq->getSettings(), true)['request'], $data);
            return ($this->interpret($settings['data'], $inputs));
        }
    }

}