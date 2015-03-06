<?php

class RequestClient
{
	//http请求code
	private static $_HTTP_CODE = 0;
	//http请求详细信息
	private static $_HTTP_INFO = NULL;
	//第三方返回的原始信息
	private static $_HTTP_RESPONSE;
	//自定义错误
	private static $_ERROR = '';
	//超时时间
	private static $_TIME_OUT = 30; //秒
	//http error code
	private static $_HTTP_ERROR_CODE = 0;
	//http error
	private static $_HTTP_ERROR = '';
	
	/**
	 * 获取本次请求原始信息
	 * @return string json string
	 */
	public static function getResponse ()
	{
		return self::$_HTTP_RESPONSE;
	}
	
	/**
	 * 获取本次请求错误信息
	 * @return string
	 */
	public static function getError ()
	{
		$msg = '';
		if(self::getHttpCode() != '200'){			
			$msg = 'http_code:'.self::getHttpCode();
		}
		if (self::$_HTTP_ERROR_CODE) {
			$msg .= ', http_error_code:' .self::$_HTTP_ERROR_CODE
			. ", http_error:".self::$_HTTP_ERROR;
		}
		return $msg;	
	}
	
	/**
	 * 获取本次请求httpcode
	 * @return int
	 */
	public static function getHttpCode ()
	{
		return self::$_HTTP_CODE;
	}
	
	/**
	 * 获取本次请求httpInfo
	 * @return array
	 */
	public static function getHttpInfo ()
	{
		return self::$_HTTP_INFO;
	}
	
    /**
     * http 请求
     * @param string $url
     * @param array $params
     * @param string $data
     * @return string
     */
    public static function http($url, $params = array(), $data = null)
    {
    	$curl = curl_init();
    	if(empty($data)){
    		$body = '';
    		if(!empty($params)) {
    			if (is_array($params)) {
    				$body = http_build_query($params);
    			}
    		}
    	}else{
    		$url = $url . (strpos($url, '?') ? '&' : '?') . (is_array($params) ? http_build_query($params) : $params);
    		$body = $data;
    	}
    	curl_setopt($curl, CURLOPT_URL, $url);
    	curl_setopt($curl, CURLOPT_TIMEOUT, self::$_TIME_OUT);
    	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::$_TIME_OUT);
    	curl_setopt($curl, CURLOPT_POST, 1);
    	curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    	curl_setopt($curl, CURLOPT_SSLVERSION,1);//升级ssl
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    	//$urlArr = parse_url($url);
    	//$port = empty($urlArr['port']) ? 80 : $urlArr['port'];
    	//curl_setopt($curl, CURLOPT_PORT, $port);
    	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:'));
    	//获取的信息以文件流的形式返回,不直接输出
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    	$response = curl_exec($curl);
    	self::$_HTTP_CODE = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    	self::$_HTTP_INFO = curl_getinfo($curl);
    	self::$_HTTP_ERROR_CODE = curl_errno($curl);
    	self::$_HTTP_ERROR = curl_error($curl);
    	curl_close($curl);
    	self::$_HTTP_RESPONSE = $response;
    	return $response;
    }  
}