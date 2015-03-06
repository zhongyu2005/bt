<?php
$_log=dirname(__BT__).'/log_app/';
$config=array(
				
		'DEFAULT_DB'=>array(
				'DB_HOST'=>'127.0.0.1',
				'DB_NAME'=>'tr_weixin',
				'DB_USER'=>'root',
				'DB_PWD'=>'',
		),
		'LOG_PATH'=>$_log,
		//cache
		'DEFAULT_CACHE'=>'redis',
		
		'CACHE_REDIS_HOST'=>'127.0.0.1',
		'CACHE_REDIS_PORT'=>'6379',
);
return $config;