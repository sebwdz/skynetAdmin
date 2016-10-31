<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/27/16
 * Time: 9:40 PM
 */

namespace       Managers;

class           Manager extends BaseManager {

    function __construct()
    {
        $this->className = "mb_managers";
        $this->entities = \Entities\Manager::class;
    }
}