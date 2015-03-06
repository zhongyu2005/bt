<?php

/*
 * 定义常用目录
 */

//定义常量
define("__EXT__", '.php');
define("__BT__",dirname(__FILE__));//定义框架目录

//记录时间和内存.
// $GLOBALS['_beginTime'] = microtime(TRUE);
// if(function_exists('memory_get_usage')) $GLOBALS['_startUseMems'] = memory_get_usage();


//关闭自动处理函数
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    ini_set('magic_quotes_runtime', 0);
}
ini_set('date.timezone', 'PRC');
header("Content-type:text/html;charset=utf-8");

require(__BT__ . '/library/Bt.class.php');

Bt::run();
