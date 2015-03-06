<?php
/**
* 日志方法
**/
class Logger{
	private $logpath = "/tmp/logs/sys";
	private $logname = "sys.log";
	private $logfile;
	private $handle;
	private $logData = array();
	private $enabled = false;
	private $startTime = 0;
	private $endTime = 0;
	
	/**
	 * 初始化log
	 * @param string $filepath  文件路径
	 */
	public function __construct ($filepath = '') {
		if (!empty($filepath)) {
			if (substr($filepath, -1, 1) == '/') {
				$this->logpath = substr($filepath, 0, -1);
			} else {
				$this->logpath = $filepath;
			}
		}
	}
	/**
	 * 开启log
	 */
	public function start ($startTime = null) {
		$path = $this->logpath . "/" . date('Y/m-d');
		$dir=dirname($path);
		if (!is_dir($dir)) {
			if(!mkdir($dir, 0777, true)){
				trigger_error('创建目录失败 : ' . $dir, E_USER_WARNING);
				return;
			}
		}
		$this->logfile = $path .'-'.$this->logname;
		$this->enabled = true;
		$this->startTime = empty($startTime) ? microtime(true) : $startTime;
	}
	
	/**
	 * 写入log
	 * @param string $key
	 * @param string $value
	 */
	public function push ($key, $value='') {
		if (empty($key)) {
			return;
		}
		if(is_array($value) || is_object($value)){
			$value=var_export($value,true);
		}
		$this->logData[] = '['.date('Y-m-d H:i:s').']'.$key.$value.PHP_EOL;
	}
	
	/**
	 * 记录log
	 */
	public function flush () {
		if (!$this->enabled) {
			return;
		}
		if(empty($this->logData)){
			return false;
		}
		$message = $this->genLogMessage();
		$handle=fopen($this->logfile, "ab+"); //创建文件
		if (!$handle) {
			trigger_error('system log file open error : ' . $this->logfile, E_USER_WARNING);
			return;
		}
		$this->handle = $handle;
		//写日志
		if(!@fwrite($this->handle, $message)){
			trigger_error('system log file write error : ' . $this->logfile, E_USER_WARNING);
		}
		$this->enabled = false;
		$this->startTime = 0;
		$this->endTime = 0;
		fclose($this->handle);
	}
	/**
	 * 拼凑信息
	 * @return string
	 */
	public function genLogMessage () {
		$message = '';
		foreach ($this->logData as $k => $v) {
			$message .= $k.'=>'.$v;
		}
		$this->endTime = microtime(true);
		$runtime = sprintf('%0.8f',($this->endTime - $this->startTime));
		$message = "[".date('Y-m-d H:i:s')."][runtime:{$runtime}]".PHP_EOL . $message;
		return $message;
	}
	
	/**
	 * 析构方法
	 */
	public function __destruct(){
		if(is_resource($this->handle)){
    		fclose($this->handle);
    	}
		if ($this->enabled) {
			$this->flush();
		}
	}
}
