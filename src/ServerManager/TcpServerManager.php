<?php namespace Nosun\Swoole\ServerManager;

use Nosun\Swoole\Contract\Network\TcpProtocol as Protocol;

class TcpServerManager extends BaseServerManager
{
    protected $sockType   = SWOOLE_SOCK_TCP;
    protected $serverType = 'socket';

    public function __construct($conf)
    {
        $this->init($conf);
        parent::__construct($conf);
    }

    protected function init($conf)
    {
        if(isset($conf['main']['listen']['ssl']) && $conf['main']['listen']['ssl'] == true){
            $this->serverType = 'socket_ssl';
        }
    }

    // create swoole server，set server，set callback function
    protected function addCallback()
    {
        $this->server->on('Connect', array($this, 'onConnect'));
        $this->server->on('Receive', array($this, 'onReceive'));
        $this->server->on('Close', array($this, 'onClose'));
    }

    protected function checkProtocol($protocol){

        if (!($protocol instanceof Protocol))
        {
            throw new \Exception("The protocol is not instanceof Protocol");
        }
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
