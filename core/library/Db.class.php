<?php

/*
 * 数据库操作类
 */

class Db {

    private $db=null;
    protected $sql='';
    //args
    protected $ag_dsn='';
    protected $ag_host='';
    protected $ag_name='';
    protected $ag_pass='';
    protected $ag_dbname='';
    //time
    protected $start_time='';
    protected $end_time='';
    

    public function Db($host,$name,$pass,$dbname) {
        $this->ag_dsn = "mysql:host={$host};dbname={$dbname}";
        $this->ag_host = $host;
        $this->ag_name = $name;
        $this->ag_pass = $pass;
        $this->ag_dbname = $dbname;
    }
    
    protected function _connect(){
        $this->start_time=microtime(true);
        $this->db = new PDO($this->ag_dsn, $this->ag_name, $this->ag_pass);
        $this->db->exec("SET NAMES 'utf8'");
        $this->end_time=microtime(true);
        $this->log('connect db');
    }
    protected function log($str){
        $str=$this->ag_host.';time:'.sprintf('%0.8f',$this->end_time-$this->start_time).';'.$str;
        Factory::getSystemLog('sql')->push($str);
    }

    public function _sql() {
        return $this->sql;
    }
    /**
     * 原生态sql执行。有数据则返回，没有则执行
     * @param string $sql
     * @return result
     */
    public function query($sql){
        if(empty($sql)){
            return false;
        }
        $this->sql = $sql;
        $rs=false;
        try{
            if(empty($this->db)){
                $this->_connect();
            }
            $this->start_time=microtime(true);
            $rs = $this->db->query($sql);
            $this->end_time=microtime(true);
            $this->log($this->sql);
            if($this->db->errorCode()!='00000'){
                $this->setError();
            }
            if(is_object($rs)){
                $list = array();
                while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
                    $list[] = $row;
                }
                return $list;
            }
        }catch(Exception $e){
            Factory::getSystemLog('sql')->push($this->sql,$e->getMessage());
        }
        return $rs;
    }
    /**
     * 执行返回一条
     * @param string $sql
     * @return result
     */
    public function queryOne($sql){
        if(empty($sql)){
            return false;
        }
        $this->sql = $sql;
        $rs=false;
        try{
            if(empty($this->db)){
                $this->_connect();
            }
            $this->start_time=microtime(true);
            $rs = $this->db->query($sql);
            $this->end_time=microtime(true);
            $this->log($this->sql);
            if($this->db->errorCode()!='00000'){
                $this->setError();
            }
            if(is_object($rs)){
                $row = $rs->fetch(PDO::FETCH_ASSOC);
                return $row;
            }
        }catch(Exception $e){
            Factory::getSystemLog('sql')->push($this->sql,$e->getMessage());
        }
        return $rs;
    }

     /**
     * 原生态sql执行。返回执行结果
     * @param string $sql
     * @return result
     */
    public function exec($sql) {
        if(empty($sql)){
            return false;
        }
        $this->sql = $sql;
        $rs=false;
        try{
            if(empty($this->db)){
                $this->_connect();
            }
            $this->start_time=microtime(true);
            $rs = $this->db->exec($sql);
            $this->end_time=microtime(true);
            $this->log($this->sql);
            if($this->db->errorCode()!='00000'){
                $this->setError();
            }
        }catch(Exception $e){
            Factory::getSystemLog('sql')->push($this->sql,$e->getMessage());
        }
        return $rs;
    }
    
    /**
     *  获取插入自增长id
     */
    public function lastInsertId(){
    	return $this->db->lastInsertId();
    }

    private function setError(){
        $str=$this->sql.'. error: '.var_export($this->db->errorInfo(),true);
        Factory::getSystemLog('sql')->push($str);

    }
    public function getError(){
        return $this->db->errorInfo();
    }

    /*
     * 事务的支持
     */
    public function startTrans(){
        $this->db->beginTransaction();
    }
    public function commit(){
        $this->db->commit();
    }
    public function rollback(){
        $this->db->rollBack();
    }

}
