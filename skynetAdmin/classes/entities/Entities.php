<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/27/16
 * Time: 9:29 PM
 */

namespace       Entities;

class           Entities {
    public function         &hydrate($vars) {
        $methods = get_class_methods($this);
        if ($vars) {
            foreach ($vars as $key => $uData) {
                foreach ($methods as $uMethods) {
                    if (strtolower($uMethods) == "set".$key) {
                        $this->$uMethods($uData);
                    }
                }
            }
        }
        return ($this);
    }
}