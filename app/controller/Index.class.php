<?php


/**
 * 测试 hello方法
 * @author zhong
 * @version 2015-02-01
 */
class IndexAction extends BaseAction{

	public function __construct(){
		//$this->_setTpl();
	}
	public function index(){
		echo 'hello index';
	}
}