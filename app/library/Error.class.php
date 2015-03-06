<?php
class Error{
	
	public static $errCode;
	public static $errMsg;
	
	public static function setError($errCode,$errMsg){
		self::$errCode=$errCode;
		self::$errMsg=$errMsg;
		//保存下错误.
		$str='errCOde='.$errCode.';errMsg='.$errMsg.';';
		$arr=array($_GET,$_POST);
		Factory::getSystemLog()->push($str,$arr);
	}
	public static function getError(){
		return array('errCode'=>self::$errCode,'errMsg'=>self::$errMsg);
	}
	
}