<?php

namespace Nosun\Swoole\Contract\Network;

interface UdpProtocol {

	public function onStart($server, $workerId);
	public function onShutdown($server, $worker_id);

	public function onPacket($server, string $data, array $client_info);

	public function onTask($server, $task_id, $from_id, $data);
	public function onFinish($server, $task_id, $data);
}