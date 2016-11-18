<?php

namespace Nosun\Swoole\Contract\DataBase;

/**
 * Database Driver接口
 * 数据库驱动类的接口
 * @author Tianfeng.Han
 *
 */
Interface IDatabase
{
	function query($sql);
	function connect();
	function close();
	function lastInsertId();
	function getAffectedRows();
	function errorNo();
	function quote($str);
}