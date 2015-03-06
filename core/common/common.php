<?php

/*
 * 公共文件
 */

function dump($ar, $flg = 0) {
    echo '<pre>';
    print_r($ar);
    echo '</pre>';
    if ($flg) {
        exit;
    }
}
/**
 * url编码原生中文字符串
 * @param unknown $data
 * @return string
 */
function code_unescaped($data){
	if(version_compare(PHP_VERSION,'5.4.0','<')){
		$data=array_map('code_urlencode', $data);
		return urldecode(json_encode($data));
	}
	return urldecode(json_encode($data,JSON_UNESCAPED_UNICODE));
}
/**
 * url编码数组和字符串
 * @param array|string $data
 * @return array|string
 * 因php5.4版本不支持，并且传入的内容有双引号，加上addslashes
 */
function code_urlencode($data){
	if(is_array($data)){
		foreach ($data as $k=>$v){
			$v=is_array($v) ? array_map('code_urlencode', $v) : urlencode(addslashes($v));
			$data[urlencode($k)]=$v;
		}
		return $data;
	}else{
		return urlencode(addslashes($data));
	}
}
/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @return mixed
 */
function I($name,$default='',$filter=null) {
	if (strpos ( $name, '.' )) { // 指定参数来源
		list ( $method, $name ) = explode ( '.', $name, 2 );
	} else { // 默认为自动判断
		$method = 'param';
	}
	switch (strtolower ( $method )) {
		case 'get' :
			$input = & $_GET;
			break;
		case 'post' :
			$input = & $_POST;
			break;
		case 'put' :
			parse_str ( file_get_contents ( 'php://input' ), $input );
			break;
		case 'param' :
			switch ($_SERVER ['REQUEST_METHOD']) {
				case 'POST' :
					$input = $_POST;
					break;
				case 'PUT' :
					parse_str ( file_get_contents ( 'php://input' ), $input );
					break;
				default :
					$input = $_GET;
			}
			break;
		case 'request' :
			$input = & $_REQUEST;
			break;
		case 'session' :
			$input = & $_SESSION;
			break;
		case 'cookie' :
			$input = & $_COOKIE;
			break;
		case 'server' :
			$input = & $_SERVER;
			break;
		case 'globals' :
			$input = & $GLOBALS;
			break;
		default :
			return NULL;
	}
	if ('' == $name) { // 获取全部变量
		$data = $input;
		$filters = isset ( $filter ) ? $filter : 'htmlspecialchars'; // 默认必须过滤
		if ($filters) {
			if (is_string ( $filters )) {
				$filters = explode ( ',', $filters );
			}
			foreach ( $filters as $filter ) {
				if (function_exists ( $filter )) {
					$data = array_map ( $filter, $data ); // 参数过滤
				}
			}
		}
	} elseif (isset ( $input [$name] )) { // 取值操作
		$data = $input [$name];
		$filters = isset ( $filter ) ? $filter : 'htmlspecialchars'; // 默认必须过滤
		if ($filters) {
			if (is_string ( $filters )) {
				$filters = explode ( ',', $filters );
			}
			foreach ( $filters as $filter ) {
				if (function_exists ( $filter )) {
					$data = is_array ( $data ) ? array_map ( $filter, $data ) : $filter ( $data ); // 参数过滤
				}
			}
		}
	} else { // 变量默认值
		$data = isset ( $default ) ? $default : NULL;
	}
	return $data;
}

/**
 * 输出json数据
 * @param mixed $data 主数据
 * @param int $error error code
 * @param string $msg error message
 */
function printJson ($data = null, $error = 0, $msg = '', $exit = true) {
	$ar=array('data'=>$data, 'error'=>$error, 'msg'=>$msg);
	if(function_exists('code_unescaped')){
		echo code_unescaped($ar);
	}else{
		echo json_encode($ar);
	}

	if ($exit === true) {
		exit;
	}
}

/**
 * 表单令牌验证
 */
function form_token(){
    $val= md5(uniqid() . rand(1, 100));
    $_SESSION['__hash__']=$val;
    $str='<input type="hidden" name="__hash__" value="'.$val.'" />';
    return $str;
}
function token_check(){
    $flg=$_POST['__hash__']==$_SESSION['__hash__'];
    unset($_SESSION['__hash__']);
    return $flg;
}
/*
 * 获取IP
 */

function get_client_ip($type = 0) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = ip2long($ip);
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}
