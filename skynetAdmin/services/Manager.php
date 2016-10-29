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

    public function     interpret($settings, $inputs) {
        $res = array();
        foreach ($inputs as $uInput) {
            if ($uInput) {
                $tmp = array();
                foreach ($settings as $key => $uSettings) {
                    $tmp[$key] = array();
                    foreach ($uSettings as $field) {
                        if (is_array($field)) {
                            $json = json_decode($uInput[$field["from"]], true);
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
        return ($res);
    }

    public function     run_interpreter($settings, $data) {
        $rq = (new \Managers\Manager())->findOne($settings["request"]);
        $rq = $this->request(json_decode($rq->getSettings(), true)['request'], $data);
        $st = \Main\MyPdo::getInstance()->query($rq);
        $inputs = $st->fetchAll();
        return ($this->interpret($settings['data'], $inputs));
    }

}