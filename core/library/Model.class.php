<?php
/**
* model 模型类
**/
class Model{

    private $tab = '';
    private $where = '';
    private $field = '';
    private $limit = '';
    private $order = '';
    private $data = null;

    public function Model($tab='',$dbname=null){
        if($tab!=''){
            $this->tab=$tab;
        }
    	$this->db=Factory::getDb($dbname);
    }

    public function field($field = '') {
        $field = empty($field) ? '*' : trim($field);
        $this->field = $field;
        return $this;
    }

    public function limit($limit = '') {
        $limit = empty($limit) ? '1' : trim($limit);
        $this->limit = $limit;
        return $this;
    }

    public function order($order = '') {
        $order = empty($order) ? '' : trim($order);
        $this->order = $order;
        return $this;
    }

    public function table($tab = '') {
        $tab = empty($tab) ? '' : trim($tab);
        $this->tab = $tab;
        return $this;
    }

    public function where($where = '') {
        if (is_array($where)) {
            $_where = '';
            foreach ($where as $k => $v) {
                $_where.=" $k='{$v}' ";
            }
            $this->where = $_where;
        } elseif (is_string($where)) {
            $this->where = " {$where} ";
        }
        return $this;
    }

    public function select() {
        $sql = $this->_merge();
        $this->_clear();
        $rs = $this->db->query($sql);
        return $rs;
    }

    public function find() {
        $sql = $this->limit(1)->_merge();
        $this->_clear();
        $rs = $this->db->queryOne($sql);
        if (empty($rs)) {
            return false;
        }
        return $rs;
    }
    public function query($sql){
        return $this->db->query($sql);
    }
    public function queryOne($sql){
        return $this->db->queryOne($sql);
    }

    public function data($data) {
        $this->data = $data;
        return $this;
    }

    public function add($data=null) {
        $table = $this->tab;
        $data = empty($data) ? $this->data : $data;
        if (is_array($data) && count($data) > 0 && !empty($table)) {
            $ks = $vs = array();
            foreach ($data as $k => $v) {
                $ks[] = trim($k);
                $vs[] = "'" . trim($v) . "'";
            }
            $sql = sprintf("INSERT INTO {$table} (%s) VALUES(%s)", implode(',', $ks), implode(',', $vs));
            $this->_clear();
            $this->db->exec($sql);
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function save($data=null, $where = '') {
        $table = $this->tab;
        $where = trim($where) == '' ? $this->where : trim($where);
        $data = empty($data) ? $this->data : $data;
        if (is_array($data) && count($data) > 0 && !empty($table) && !empty($where)) {
            $val = array();
            foreach ($data as $k => $v) {
                $val[] = " `$k`='{$v}' ";
            }
            $sql = sprintf("UPDATE {$table} SET  %s", implode(',', $val));
            if (!empty($where)) {
                $sql.=" WHERE {$where} ";
            }
            $this->_clear();
            return $this->db->exec($sql);
        }
        return false;
    }

    public function del() {
        $field = $this->field;
        $tab = $this->tab;
        $where = $this->where;
        $limit = $this->limit;
        if(empty($tab) || empty($where)){
        	return false;
        }
        $sql = "DELETE FROM {$tab} WHERE {$where}";
        if ($limit) {
            $sql.=" LIMIT {$limit}";
        }
        $this->_clear();
        $rs = $this->db->exec($sql);
        return $rs;
    }

    private function _merge() {
        $field = trim($this->field) ? trim($this->field) : '*';
        $tab = trim($this->tab);
        $where = trim($this->where);
        $limit = trim($this->limit);
        $order = trim($this->order);
        $sql = 'SELECT ' . $field . ' FROM ' . $tab;
        if ($where) {
            $sql.=" WHERE {$where} ";
        }
        if ($order) {
            $sql.=" ORDER BY {$order} ";
        }
        if ($limit) {
            $sql.=" LIMIT {$limit} ";
        }
        return $sql;
    }
    private function _clear() {
        $this->field = null;
        $this->where = null;
        $this->limit = null;
        $this->order = null;
        $this->data = null;
    }
}