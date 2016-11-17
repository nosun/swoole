<?php

namespace Nosun\Swoole\Server;

/**
 * 协议基类，实现一些公用的方法
 * @package Swoole\Network
 */

class BaseServer
{
    public $server;

    function __construct($config)
    {
        $this->init();
    }

    public function init()
    {
        // some code
    }

    /**
     * 打印Log信息
     * @param $msg
     * @param string $path
     */

    public function log($msg,$path)
    {
        $log = "[" . date("Y-m-d G:i:s") ." ".floor(microtime()*1000) . "]" . $msg;

        if($path){
            error_log($log . PHP_EOL, 3, $path);
        }
        echo $log."\n";
    }

}