<?php  namespace Nosun\Swoole\Client;

class Mqtt {

    protected $mq;
    protected $host;
    protected $port;
    protected $keepalive;

    public function __construct($host='127.0.0.1',$port='1883',$keepalive=5){
        $this->host = $host;
        $this->port = $port;
        $this->keepalive = $keepalive;
        $this->mq = new \Mosquitto\client();
        $this->mq->connect($host, $port, 5);
    }

    public function publish($topic,$msg,$qos,$retain){
        $this->mq->publish($topic, $msg, $qos, $retain);
    }

    public function close(){
        $this->mq->disconnect();
    }
}
