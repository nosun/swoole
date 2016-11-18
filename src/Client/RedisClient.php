<?php namespace Nosun\Swoole\Client;

class RedisClient {

	protected $_redis;
	protected $host;
	protected $port;
	protected $database;
	protected $password;
	protected $timeout;
	protected $pconnect;

	public function __construct($host='127.0.0.1', $port=6379, $database=1, $password=null, $timeout=0.0, $pconnect=false){
		$this->host     = $host;
		$this->port     = $port;
		$this->database = $database;
		$this->password = $password;
		$this->timeout  = $timeout;
		$this->pconnect = $pconnect;

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

		if ($this->pconnect)
		{
			$this->_redis->pconnect($this->host, $this->port, $this->timeout);
		}
		else
		{
			$this->_redis->connect($this->host, $this->port, $this->timeout);
		}
		if(!empty($this->pass)){
			$this->_redis->auth($this->pass);
		}
		if (!empty($this->database))
		{
			$this->_redis->select($this->database);
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
