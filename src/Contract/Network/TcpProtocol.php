<?php

namespace Nosun\Swoole\Contract\Network;

interface TcpProtocol {
	public function onConnect($server, $client_id, $from_id);
	public function onReceive($server,$client_id, $from_id, $data);
	public function onClose($server, $client_id, $from_id);
}