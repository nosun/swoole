<?php

use Nosun\Swoole\Contract\Network\WebSocketProtocol as Protocol;
use Nosun\Swoole\Server\BaseServer;

class WebSocketServer extends BaseServer implements Protocol{

	public function onStart($server, $workerId)
	{

	}

	public function onOpen($server,$request)
	{

	}

	public function onMessage($server,$frame)
	{

	}

	public function onShutdown($server, $workerId)
	{

	}

	public function onClose($server, $frame)
	{

	}

	public function onTask($server, $taskId, $fromId, $data)
	{

	}

	public function onFinish($server, $taskId, $data)
	{

	}
}