<?php

namespace Nosun\Swoole\Contract\Network;

interface HttpProtocol {
	public function onRequest($request, $response);
}