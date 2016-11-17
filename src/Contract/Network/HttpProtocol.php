<?php

namespace Nosun\Swoole\Contract\Network;

interface HttpProtocol {
	public function onStart($server, $workerId);
	public function onShutdown($server, $worker_id);

	public function onRequest($request, $response);

	public function onTask($server, $task_id, $from_id, $data);
	public function onFinish($server, $task_id, $data);
}