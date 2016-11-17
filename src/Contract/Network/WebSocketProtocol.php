<?php

namespace Nosun\Swoole\Contract\Network;

interface WebSocketProtocol {
	public function onOpen($server, $req);
	public function onMessage($server, $frame);
	public function onClose($server,$fd);
}