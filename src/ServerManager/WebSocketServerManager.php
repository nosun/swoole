<?php namespace Nosun\Swoole\Manager;

use Nosun\Swoole\Contract\Network\WebSocketProtocol as Protocol;

class WebSocketServer extends BaseServerManager
{

    protected $sockType   = SWOOLE_SOCK_TCP;
    protected $serverType = 'websocket';
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
            $this->serverType = 'websocket_ssl';
        }
    }

    // create swoole server, set server, set callback function
    protected function addCallback()
    {
        $this->server->on('Open', array($this, 'onOpen'));
        $this->server->on('Message', array($this, 'onMessage'));
        $this->server->on('Close', array($this, 'onClose'));
    }

    protected function checkProtocol($protocol){

        if (!($protocol instanceof Protocol))
        {
            throw new \Exception("The protocol is not instanceof " . Protocol::class );
        }
    }

    public function onOpen($server, $fd){
        $this->protocol->onOpen($server, $fd);
    }

    public function onMessage($server,$frame){
        $this->protocol->onMessage($server, $frame);

    }

    public function onClose($server, $fd){
        $this->protocol->onClose($server, $fd);
    }
}
