<?php


isset($_GET['a']) || $_GET['a']='index';
isset($_GET['m']) || $_GET['m']='index';


if(!defined('__APP__')){
	define("__APP__", dirname(dirname(__FILE__)).'/app');
}

require dirname(__APP__).'/core/bt.php';

