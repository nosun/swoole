<?php namespace Nosun\Swoole\Client;

class Socket
{

    protected $client;
    protected $host;
    protected $port;

    public function __construct($host,$port,$type)
    {
        $this->type = constant($type);
        $this->host = $host;
        $this->port = $port;
        $this->client = new \swoole_client($this->type);
    }

    public function connect($host,$port){

        if ($this->client->connect($host, $port)) {
            return true;
        }

        return false;
    }


    public function send($msg)
    {
        $result = $this->client->send($msg);
        return $result;
    }
}
