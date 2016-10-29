<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/27/16
 * Time: 10:02 PM
 */

namespace           Utils;

class               Utils {

    static public function classToArray($class) {
        $props = (new \ReflectionClass($class))->getProperties();
        $array = array();
        foreach ($props as $prop) {
            $array[$prop->getName()] = $class->{'get' . ucfirst($prop->getName())}();
        }
        return ($array);
    }

}