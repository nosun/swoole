<?php namespace Nosun\Swoole\Client;

class Redis {

	protected $_redis;
	protected $config;

	public function __construct($config){
		$this->config['host'] = isset($config['host']) ? $config['host'] : '127.0.0.1';
		$this->config['port'] = isset($config['port']) ? $config['port'] : 6379;
		$this->config['timeout'] = isset($config['timeout']) ? $config['timeout'] : 0.0;
		$this->config['pass'] = isset($config['pass']) ? $config['pass'] : '';
		$this->config['database'] = isset($config['database']) ? $config['database'] : 1;
		$this->config['pconnect'] = isset($config['pconnect']) ? $config['pconnect'] : false;

		try {
			if ($this->_redis)
			{
				unset($this->_redis);
			}
			$this->_redis = new \Redis();
			$this->connect();
		}catch (\RedisException $e){
			//
		}
	}

	protected function connect(){

		if ($this->config['pconnect'])
		{
			$this->_redis->pconnect($this->config['host'], $this->config['port'], $this->config['timeout']);
		}
		else
		{
			$this->_redis->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
		}
		if(!empty($this->config['pass'])){
			$this->_redis->auth($this->config['pass']);
		}
		if (!empty($this->config['database']))
		{
			$this->_redis->select($this->config['database']);
		}
	}

    /**
	 * Dynamically make a Redis command.
     *
     * @param $method
     * @param $parameters
     * @return bool|mixed
     * @throws \Exception
     * @throws \RedisException
     */

	public function __call($method, $parameters)
	{
		$reConnect = false;
		while (1)
		{
			try
			{
				$result = call_user_func_array(array($this->_redis, $method), $parameters);
			}
			catch (\RedisException $e)
			{
				//已重连过，仍然报错
				if ($reConnect)
				{
					throw $e;
				}
				// Todo make error log;
				$this->_redis->close();
				$this->connect();
				$reConnect = true;
				continue;
			}
			return $result;
		}
		//不可能到这里
		return false;
	}

}
