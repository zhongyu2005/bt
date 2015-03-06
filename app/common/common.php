<?php

/**
 * js，css，生成器
 * @param $type=css,js
 */
function dom_help($url){
	if(empty($url)){
		return false;
	}
	if(is_string($url)){
		$url=array($url);
	}
	if(!is_array($url)){
		return false;
	}
	$_arr=array();
	foreach ($url as $v){
		$arr=pathinfo($v);
		$ext=strtolower($arr['extension']);
		if(!in_array($ext, array('js','css'))){
			continue;
		}
		switch($ext){
			case 'js':
				$_arr[]='<script type="text/javascript" src="'.$v.'"></script>';
				break;
			case 'css':
				$_arr[]='<link rel="stylesheet" type="text/css" href="'.$v.'" />';
				break;
		}
	}
	$str='';
	if(!empty($_arr)){
		$str=implode("\n", $_arr);
	}
	return $str;
}

/**
 * 生成url
 */
function url($action,$method,$args=null){
	$url='http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
	$url.='?a='.$action.'&m='.$method;
	if(!empty($args)){
		$url.=http_build_query($args);
	}
	return $url;
}