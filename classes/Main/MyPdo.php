<?php

/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/26/16
 * Time: 4:25 PM
 */

namespace Main;

class   MyPdo
{
    static private $pdo;
    static private $mongo;
    /*
     * @return \Slim\PDO\Database $pdo
     */
    public static function  getInstance() {
        if (self::$pdo == null) {
            $dsn = 'pgsql:host=localhost;dbname=skynet';
            $usr = 'sebastien';
            $pwd = 'passpass';
            self::$pdo = new \Slim\PDO\Database($dsn, $usr, $pwd);
        }
        return (self::$pdo);
    }

    /*
     * @return \MongoDB\Database $db
     */
    public static function  getMongoDb($str) {
        if (self::$mongo == null) {
            self::$mongo = new \MongoDB\Client();
        }
        return (self::$mongo->$str);
    }
}