<?php namespace Nosun\Swoole\Manager;

use Nosun\Swoole\Contract\Network\WebSocketProtocol;

class WebSocketServer extends Server implements WebSocketProtocol
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

    // create swoole server，set server，set callback function
    protected function addCallback()
    {
        $this->sw->on('Open', array($this, 'onOpen'));
        $this->sw->on('Message', array($this, 'onMessage'));
        $this->sw->on('Close', array($this, 'onClose'));
    }

    public function onOpen($server, $fd){
        $this->protocol->onClose($server, $fd);
    }

    public function onMessage($server,$frame){
        $this->protocol->onClose($server, $frame);

    }

    public function onClose($server, $fd){
        $this->protocol->onClose($server, $fd);
    }
}