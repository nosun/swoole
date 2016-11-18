<?php

namespace Nosun\Swoole\Database;

use Nosun\Swoole\Contract\DataBase\IDbRecord;

class MySQLiRecord implements IDbRecord
{
	/**
	 * @var \mysqli_result
	 */
	public $result;

	function __construct($result)
	{
		$this->result = $result;
	}

	function fetch()
	{
		return $this->result->fetch_assoc();
	}

	function fetchAll()
	{
		$data = array();
		while ($record = $this->result->fetch_assoc())
		{
			$data[] = $record;
		}
		return $data;
	}

	function free()
	{
		$this->result->free_result();
	}

	function __get($key)
	{
		return $this->result->$key;
	}

	function __call($func, $params)
	{
		return call_user_func_array(array($this->result, $func), $params);
	}
}