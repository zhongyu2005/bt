<?php

/*
 * 初始化操作方法
 */

class Bt {

	/**
	 * 入口
	 */
    static public function run() {
        spl_autoload_register('bt::autoload');
        set_error_handler('bt::errorHandler');
        register_shutdown_function('bt::shutdown');
        self::init();
        //开启session
        session_start();
        
        //路由处理.
        if(isset($_GET[Conf::ACTION_NAME]) && isset($_GET[Conf::METHOD_NAME]) ){
            $actionName=trim($_GET[Conf::ACTION_NAME]);
        	$action=$actionName.'Action';
        	$method=trim($_GET[Conf::METHOD_NAME]);

            define('CONST_ACTION', $actionName);
            define('CONST_METHOD',$method);
            
        	if(class_exists($action)){
        		$cls=new $action();
        		if(method_exists($cls, $method)){                    
        			call_user_func(array($cls,$method));
        			return false;
        		}
        	}
        }
        //错误处理.
        if(!function_exists('notFound')){
        	function notFound(){
        		echo '404';
        	}
        }
        notFound();
    }
    
    /**
     * 部分功能初始化
     */
    public static function init(){
        //引入公共方法
        require(__BT__ . '/common/common.php');
        if(defined("__APP__")){
        	$file = __APP__ . '/common/common.php';
        	if (is_file($file)) {
        		require($file);
        	}
        	$config=__APP__.'/library/Config.php';
        	if(is_file($config)){
        		$conf=include $config;
        		//初始化conf
        		Conf::init($conf);
        	}
        }
        //定义常量.
        define('PHP_CLI',php_sapi_name());
        define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
        if(PHP_CLI!='cli'){
        	define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
        	define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
        	define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
        	define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);
        	define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false);
        	define('IS_AJAX',       ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false);
        }
    }
    /**
     * 运行监听
     */
    public static function shutdown(){
        if($e=error_get_last()){
            $msg=$e['message'] .' in ' .$e['file'] .' on '.$e['line'];
            Factory::getSystemLog('php')->push($msg);
            Factory::flush();
        }
    }
    
    /**
     * 错误监听
     */
    static public function errorHandler($errno, $errstr, $errfile, $errline) {
        $err_msg = "信息：{$errno}。内容：{$errstr},发生在文件{$errfile}的{$errline}行";
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                break;
            default:
                # code...
                break;
        }
        Factory::getSystemLog('php')->push($err_msg);
        return true;
    }

    /**
     * 自动加载类。
     * @param string $class
     */
    static public function autoload($class) {
        //$class = ucfirst(strtolower($class));
        $action=str_replace('Action', '', $class);
        $ext = '.class' . __EXT__;
        //核心类库
        $file = __BT__ . '/library/' . $class . $ext;
        if (is_file($file)) {
            require($file);
            return;
        }
        $req=array();
        if(defined("__APP__")){
        	$req[] = __APP__ . '/library/' . $class . $ext;
        	//引入控制器
        	$req[] = __APP__ . '/controller/' . $action . $ext;
        	/* 
        	//引入model
        	$req[] = __APP__ . '/model/' . $class . $ext;
        	//引入plugin
        	$req[] = __APP__ . '/plugin/' . $class . $ext;
        	 */
        }
        if(defined('APP_GROUP')){
        	$group=strtolower(APP_GROUP);
        	$req[] = __APP__ . '/library/' .$group.'/'. $class . $ext;
        	$req[] = __APP__ . '/controller/'.$group.'/'. $action . $ext;
        	//$req[] = __APP__ . '/model/'.$group.'/'. $class . __EXT__;
        }
        foreach ($req as $v) {
            if (is_file($v)) {
                require($v);
                return;
            }
        }
    }

}
