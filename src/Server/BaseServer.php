<?php

namespace Nosun\Swoole\Server;

/**
 * all type server base class
 */

class BaseServer
{
    public $server;

    function __construct()
    {
        $this->init();
    }

    public function init()
    {
        // some code
    }

    public function log($msg,$path)
    {
        $log = "[" . date("Y-m-d G:i:s") ." ".floor(microtime()*1000) . "]" . $msg;

        if($path){
            error_log($log . PHP_EOL, 3, $path);
        }
        echo $log."\n";
    }
}