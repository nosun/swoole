<?php

namespace Nosun\Swoole\Contract\Network;

interface WebSocketProtocol {

	public function onStart($server, $workerId);
	public function onShutdown($server, $workerId);

	public function onOpen($server, $request);
	public function onMessage($server, $frame);
	public function onClose($server,$fd);

}