<?php

namespace Nosun\Swoole;

class Log
{

	protected static $path = '/var/log/swoole.log';

	protected function setPath($path){
		self::$path = $path;
	}

	public static function info($info,$detail){
		$path    = self::mkDir();
		$message = self::messageFormat($info,$detail);
		file_put_contents($path , $message, FILE_APPEND);
	}

	protected static function mkDir(){
		$parts   = explode('/', self::$path);
		$file    = array_pop($parts);
		$dir     = '';

		foreach($parts as $part){
			if(!is_dir($dir .= "/$part")){
				mkdir($dir);
			}
		}

		return $dir.'/'.$file;
	}

	protected static function messageFormat($info,$detail){
		$time = self::timeFormat();
		return $time .' '. $info . ' ,detail:'. $detail. PHP_EOL;
	}

	protected static function timeFormat(){
		return date('Y-m-d h:i:s',time());
	}
}