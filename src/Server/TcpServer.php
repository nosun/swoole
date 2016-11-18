<?php

namespace Nosun\Swoole\Server;

use Nosun\Swoole\Contract\Network\TcpProtocol as Protocol;

class TcpServer extends BaseServer implements Protocol{

	public function onStart($server, $workerId)
	{

	}

	public function onShutdown($server, $workerId)
	{

	}

	public function onReceive($server,$clientId, $fromId, $data)
	{

	}

	public function onConnect($server, $fd, $fromId)
	{

	}

	public function onTimer($server, $workerId)
	{

	}

	public function onClose($server, $fd, $fromId)
	{

	}

	public function onTask($server, $taskId, $fromId, $data)
	{

	}

	public function onFinish($server, $taskId, $data)
	{

	}
}