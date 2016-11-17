<?php

namespace Nosun\Swoole\Contract\Network;

interface TcpProtocol {

	public function onStart($server, $workerId);
	public function onShutdown($server, $worker_id);

	public function onConnect($server, $client_id, $from_id);
	public function onReceive($server,$client_id, $from_id, $data);
	public function onClose($server, $client_id, $from_id);

	public function onTask($server, $task_id, $from_id, $data);
	public function onFinish($server, $task_id, $data);

}