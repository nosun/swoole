<?php

namespace Nosun\Swoole\Contract;

interface ServerManagerContract {

    public function run();                   // get arg for start,stop,reload...
    public function send ($client_id,$data); // send message to client
    public function close($client_id);       // close a connection
    public function shutdown();              // shutdown
    public function setProtocol($protocol);  // set protocol for server type

}