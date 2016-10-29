<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 10/29/16
 * Time: 7:38 PM
 */


namespace Services;

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\TBufferedTransport;
use Thrift\Exception\TException;
use Thrift\ClassLoader\ThriftClassLoader;

class               Thrift {

    static public function         launch($function, $data) {

        error_reporting(E_ALL);

        $GEN_DIR = realpath(dirname(__FILE__).'/..').'/gen-php';

        $loader = new ThriftClassLoader();
        $loader->registerNamespace('Thrift', '/Thrift/');
        $loader->registerDefinition('shared', $GEN_DIR);
        $loader->registerDefinition('network', $GEN_DIR);
        $loader->register();

        try {
            $socket = new TSocket('localhost', 9090);
            $socket->setRecvTimeout(100000);
            $transport = new TBufferedTransport($socket, 1024, 1024);
            $protocol = new TBinaryProtocol($transport);
            $client = new \network\NetworkClient($protocol);

            $transport->open();

            $work = new \network\Data();

            $work->input = json_encode($data);
            $res = $client->$function($work);

            $transport->close();
            return ($res->input);
        } catch (TException $tx) {
            print 'TException: '.$tx->getMessage()."\n";
        }
    }

}