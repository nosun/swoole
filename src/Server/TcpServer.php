<?php namespace Nosun\Swoole\Manager;

use Nosun\Swoole\Contract\Network\TcpProtocol;

class TcpServer extends Server implements TcpProtocol
{
    protected $sockType   = SWOOLE_SOCK_TCP;
    protected $serverType = 'swoole_server';
    protected $ssl        = false;

    public function __construct($conf)
    {
        $this->ssl = isset($conf['main']['ssl']) ? $conf['main']['ssl'] : false;
        $this->init();
        parent::__construct($conf);
    }

    protected function init()
    {
        if($this->ssl == true){
            $this->serverType = 'socket_ssl';
        }
    }

    // create swoole server，set server，set callback function
    protected function addCallback()
    {
        $this->sw->on('Connect', array($this, 'onConnect'));
        $this->sw->on('Receive', array($this, 'onReceive'));
        $this->sw->on('Close', array($this, 'onClose'));
    }

    /*
    |--------------------------------------------------------------------------
    | Protocol 类回调函数
    |--------------------------------------------------------------------------
    |
    | 将回调函数设置在 protocol 类中实现
    |
    */

    public function onConnect($server, $fd, $fromId)
    {
        $this->protocol->onConnect($server, $fd, $fromId);
    }

    public function onClose($server, $fd, $fromId)
    {
        $this->protocol->onClose($server, $fd, $fromId);
    }


    /*
    |--------------------------------------------------------------------------
    | onReceive 回调函数
    |--------------------------------------------------------------------------
    |
    | 可以通过socket 发消息查看 Server的信息，不过需要preSysCmd
    |
    */

    public function onReceive($server, $fd, $fromId, $data)
    {
        if($data == $this->preSysCmd . "reload")
        {
            $ret = intval($server->reload());
            $server->send($fd, $ret);
        }
        elseif($data ==  $this->preSysCmd . "info")
        {
            $info = $server->connection_info($fd);
            $server->send($fd, 'Info: '.var_export($info, true).PHP_EOL);
        }
        elseif($data ==  $this->preSysCmd . "stats")
        {
            $server_status = $server->stats();
            $server->send($fd, 'Stats: '.var_export($server_status, true).PHP_EOL);
        }
        else
        {
            $this->protocol->onReceive($server, $fd, $fromId, $data);
        }
    }

}
