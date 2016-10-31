<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/27/16
 * Time: 9:28 PM
 */

namespace       Managers;

use Entities\Entities;

class           BaseManager {

    protected   $className;
    protected   $entities;

    public function         addOne($entities) {
        $query = "INSERT INTO ".$this->className." (";
        $values = "VALUES (";
        $data = \Utils\Utils::classToArray($entities);
        foreach ($data as $key => $uData) {
            if (!is_null($uData)) {
                $query .= $key . ", ";
                $values .= ":" . $key . ", ";
            }
        }
        $query = substr($query, 0, strlen($query) - 2);
        $values = substr($values, 0, strlen($values) - 2);
        $query .= ") ".$values.")";
        $tmpt = \Main\MyPdo::getInstance()->prepare($query);
        foreach ($data as $key => $uData) {
            if (!is_null($uData))
                $tmpt->bindValue(":" . $key, $uData);
        }
        $tmpt->execute();
    }

    public function         updateOne($entities, $id) {
        $query = "UPDATE ".$this->className." SET ";
        $data = \Utils\Utils::classToArray($entities);
        foreach ($data as $key => $uData) {
            if (!is_null($uData))
                $query .= $key . " = :".$key." , ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= "WHERE id = :id";
        $tmpt = \Main\MyPdo::getInstance()->prepare($query);
        foreach ($data as $key => $uData) {
            if (!is_null($uData))
                $tmpt->bindValue(":" . $key, $uData);
        }
        $tmpt->bindValue(":id", $id);
        $tmpt->execute();
    }

    public function         findOne($id) {
        $query = "SELECT * FROM ".$this->className." WHERE id = :id";
        $tmpt = \Main\MyPdo::getInstance()->prepare($query);
        $tmpt->bindValue(":id", (int)$id);
        $tmpt->execute();
        $x = $tmpt->fetchAll();
        if (sizeof($x))
            return ((new $this->entities())->hydrate($x[0]));
        return (null);
    }

    public function         findAll() {
        $query = "SELECT * FROM ".$this->className;
        $tmpt = \Main\MyPdo::getInstance()->prepare($query);
        $tmpt->execute();
        $x = $tmpt->fetchAll();
        for ($it = 0; $it < sizeof($x); $it++)
            $x[$it] = (new $this->entities())->hydrate($x[$it]);
        return ($x);
    }

}