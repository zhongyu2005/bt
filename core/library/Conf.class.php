<?php

if(!defined('define')){
	define('BASE_LOG', dirname(__BT__).'/log_extPlugin/');
}

/**
* 配置文件
**/
class Conf{
	const ACTION_NAME = 'a';
	const METHOD_NAME = 'm';
	const TEMPLATE_DIR = 'view';//以lib目录为根
	const TEMPLATE_PREFIX = '.php';

	protected static $_conf = array(

		//log
		'DEBUG'=>false,//开启debug后，获取的缓存不为redis.
		'LOG_PATH'=>BASE_LOG,

		//cache
		'DEFAULT_CACHE'=>'redis',

		'CACHE_REDIS_HOST'=>'127.0.0.1',
		'CACHE_REDIS_PORT'=>'6379',

		//db-ext
		'DEFAULT_DB'=>array(
			'DB_HOST'=>'127.0.0.1',
			'DB_NAME'=>'test',
    		'DB_USER'=>'root',
    		'DB_PWD'=>'',
		)

	);

	public static function get($name=''){
		if(!empty($name) && isset(self::$_conf[$name])){
			return self::$_conf[$name];
		}else if(empty($name)){
			return self::$_conf;
		}
		return false;
	}
	public static function set($name,$val){
		self::$_conf[$name]=$val;
		return true;
	}

	public static function init($arr){
		if(is_array($arr)){
			foreach ($arr as $k=>$v){
				self::$_conf[$k]=$v;
			}
		}
	}
}
