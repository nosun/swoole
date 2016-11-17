<?php

namespace Nosun\Swoole\Contract\Network;

interface UdpProtocol {
	public function onReceive($server,$client_id, $from_id, $data);
	public function onClose($server, $client_id, $from_id);
}