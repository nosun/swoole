<?php

namespace Nosun\Swoole\Contract;

interface Driver {

    function setProtocol($protocol);  // set protocol for server type
    function run();                   // get arg for start,stop,reload...
    function send ($client_id,$data); // send message to client
    function close($client_id);       // close a connection

}