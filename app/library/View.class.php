<?php

/**
 * 模板实现
 */
class View{
	
	private static $data=array();
	const VIEW_PATH='view';

	public static function assign($key,$val){
		self::$data[$key]=$val;
	}

	public static function display($tpl=''){
		if(defined('CONST_VIEW')){
			$path=CONST_VIEW;
		}else{
			$path=__APP__.'/'.self::VIEW_PATH.'/';
			defined('CONST_VIEW') || define('CONST_VIEW', $path);
		}		
		if(!empty($tpl)){
			$file=$path.$tpl;
		}else{
			$act=CONST_ACTION;
			$act{0} = strtolower($act{0});
			$file=$path . $act . '/' . CONST_METHOD;
		}
		$file.=__EXT__;
		if(is_file($file)){
			extract(self::$data);
			include $file;
		}
		return false;		
	}
}