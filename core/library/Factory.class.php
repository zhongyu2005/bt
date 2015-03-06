<?php
/**
 * 简单工厂类
 */
class Factory{
	
	protected static $SYS_LOG = array();
    protected static $DB = array();
    protected static $MODEL=array();
    protected static $CACHER=array();


    public static function flush(){
        $list=self::$SYS_LOG;
        if(empty($list)){
            return false;
        }
        foreach($list as $v){
            $v->flush();
        }
    }

	/**
	 * 获取系统日志实例
	 * @return SystemLog
	 */
	public static function getSystemLog ($key='web') {
		if(isset(self::$SYS_LOG[$key])){
			return self::$SYS_LOG[$key];
		}
		$logPath=Conf::get('LOG_PATH') . $key;
		$log=new Logger($logPath);
		$log->start();
		self::$SYS_LOG[$key]=$log;
		return self::$SYS_LOG[$key];
	}

    /**
     * 获取db
     *@return MySql
     */
    public static function getDb($dbname = NULL)
    {
        if (empty($dbname)) {
            $dbId = 'DEFAULT_DB';
            $config = Conf::get($dbId);
        } else {
            $dbId = $dbname;
            $config = Conf::get($dbId);
        }

        if (!$config) {
            return null;
        }
        if (!isset(self::$DB[$dbId])) {
            $db = new Db($config['DB_HOST'], $config['DB_USER'], $config['DB_PWD'], $config['DB_NAME']);
            self::$DB[$dbId] = $db;
        }
        return self::$DB[$dbId];
    }

    /**
     * 获取model
     */
    public static function getModel($tab=null,$dbname=null){
        $tab=empty($tab) ? '_def' : $tab;
        if(!isset(self::$MODEL[$tab])){
            $mod=new Model($tab,$dbname);
            self::$MODEL[$tab]=$mod;
        }
        return self::$MODEL[$tab];
    }

    /**
     * 获取缓存类
     * @param string $type
     * @return FileCache | RedisCache
     */
    public static function getCacher ($type = '', $model = '') {
    	$type or $type =Conf::get('DEFAULT_CACHE');
    	if (!in_array($type, array('redis', 'file'))) {
    		//'remote'
    		$type = 'redis';
    	}
    	//如果不是正式服务上，不是用redis缓存
    	if (true == Conf::get('DEBUG') && 'redis' == $type) {
    		$type = 'file';
    	}
    	$cacheId = $type.$model;
    	if (isset(self::$CACHER[$cacheId])) {
    		return self::$CACHER[$cacheId];
    	}
    
    	switch ($type) {
    		case 'file':
    			$c = new FileCache();
    			$c->setModel($model);
    			$c->setPath(Conf::get('LOG_PATH').'cache');
    			self::$CACHER[$cacheId] = $c;
    			break;
    		case 'remote':
    			if (!class_exists("FileCache")) {
    				include_once SUISHI_PHP_PATH . '/Cache/RemoteCacher.class.php';
    			}
    			$c = new RemoteCacher(C('REMOTE_CACHE_HOST'), C('REMOTE_CACHE_PORT'), 'weixinapp');
    			self::$CACHER[$cacheId] = $c;
    			break;
    		default:
    			$c = new RedisCache(Conf::get('CACHE_REDIS_HOST'),Conf::get('CACHE_REDIS_PORT'));
    			self::$CACHER[$cacheId] = $c;
    			break;
    	}
    	return self::$CACHER[$cacheId];
    }
}