<?php

namespace zhongyu;

/**
 * Created by PhpStorm.
 * User: zhongyu
 * Date: 2016/6/22
 * Time: 11:23
 * @init,@redis,@mysql,@export cvs,@rand-weight,@php-cli,@curl,@time,@upload,@log,@factory,@config
 */
class Bt
{
    public $db=null;
    public $logfile='/temp';

    public static function getInstance($key='0'){
        static $_ar=[];
        if(isset($_ar[$key])){
            return $_ar[$key];
        }
        $_ar[$key]=new Bt();
        return $_ar[$key];
    }

    /**
     * 初始化
     */
    public function baseInit(){
        ini_set('max_execution_time', 0);//不限制执行时间
        ini_set('magic_quotes_runtime',0);//关闭自动反斜线转移引号
        header("content-type:text/html;charset=utf-8");//设置output的编码
        date_default_timezone_set("PRC");//设置默认时间格式
        error_reporting(E_ALL);//开启错误报告级别为所有
        ini_set('display_errors',true);//开启错误信息显示
        ini_set("error_log",__DIR__.'/'. date('Y-m-d') . ".log");//设置php错误路径
    }

    /**
     * 获取redis对象
     * keys * ,DBSIZE.
     */
    public function getRedis($host='127.0.0.1',$port='6379'){
        static $obj=null;
        $key=$host.'_'.$port;
        if(isset($obj[$key])){
            return $obj[$key];
        }
        try{
            $obj[$key]=new Redis();
            $obj[$key]->connect($host,$port);
        }catch(Exception $e){
            var_dump($e);
            return false;
        }
        return $obj[$key];
    }

    /**
     * 获取PDO-mysql对象
     */
    public function getMySql($host,$user,$pwd,$db){
        static $obj=null;
        $key=$host.'_'.$user.'_'.$db;
        if(isset($obj[$key])){
            return $obj[$key];
        }
        $obj[$key] = new \PDO('mysql:host='.$host.';dbname='.$db, $user, $pwd);
        $obj[$key]->exec("SET NAMES 'utf8mb4'");
        return $obj[$key];
    }

    /**
     * mysql，add，update，query,find
     *   $sth=$db->prepare($sql);$r=$sth->execute(['1','帅11']);
     */
    public function mysqlAdd($tab,array $set){

        $sql="INSERT INTO %s (%s) values(%s) ";
        $key=$pre=$val=[];
        foreach ($set as $k=>$v){
            $key[]='`'.$k.'`';
            $pre[]='?';
            $val[]=$v;
        }
        $sql=sprintf($sql,$tab,implode(',',$key),implode(',',$pre));
        if(empty($this->db)){
            return [$sql,$val];
        }
        $sth=$this->db->prepare($sql);
        $r=$sth->execute($val);
        if($r){
            //成功，返回插入的id
            return $this->db->lastInsertId();
        }
        //返回true or false
        return $r;
    }

    /**
     *  $sth=$db->prepare($sql);$r=$sth->execute(['1','帅11']);
     * @param $tab
     * @param $where
     * @param array $set
     */
    public function mysqlUpdate($tab,$where,array $set){
        $sql='UPDATE `'.$tab.'` SET ';
        $key=$val=[];
        foreach ($set as $k=>$v){
            $key[]='`'.$k.'`=?';
            $val[]=$v;
        }
        $sql.=implode(',',$key) .' WHERE '.$where;
        if(empty($this->db)){
            return [$sql,$val];
        }
        $sth=$this->db->prepare($sql);
        $r=$sth->execute($val);
        if($r){
            //返回受影响的行数
            return $sth->rowCount();
        }
        //返回true or false
        return $r;
    }
    public function mysqlFind($field,$tab,$where){
        $sql="SELECT {$field} FROM {$tab} WHERE {$where}";
        if(empty($this->db)){
            return $sql;
        }
        $res=$this->db->query($sql);
        $r=$res->fetch(\PDO::FETCH_OBJ);
        return $r;
    }
    public function mysqlSelect($field,$tab,$where){
        $sql="SELECT {$field} FROM {$tab} WHERE {$where}";
        if(empty($this->db)){
            return $sql;
        }
        $res=$this->db->query($sql);
        $list=[];
        while($r=$res->fetch(\PDO::FETCH_OBJ)){
            $list[]=$r;
        }
        return $list;
    }
    public function mysqlQuery($sql){
        $res=$this->db->query($sql);
        $list=[];
        while($r=$res->fetch(\PDO::FETCH_OBJ)){
            $list[]=$r;
        }
        return $list;
    }

    /**
     * export cvs
     */
    public function exportCsv($set){
        $csvName = date("Y-m-d_").time().".csv";; //user.csv
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$csvName.'"');
        header('Cache-Control: max-age=0');

        $out = fopen('php://output', 'w');
        foreach ($set as $fields) {
            foreach ($fields as &$v){
                $v=mb_convert_encoding($v,'gbk','utf-8');
            }
            unset($v);
            fputcsv($out, $fields);
        }
        fclose($out);
    }

    /**
     * 概率算法，获取当前的概率
     * @param array $arr 奖项和概率 如：array(1=>80,2=>10,3=>5 4=>4 5=>1)
     */
    public function getRand($arr){
        $ret='';
        $sum=array_sum($arr);
        foreach ($arr as $k=>$v){
            $rand=rand(1, $sum);
            if($rand<=$v){
                $ret=$k;
                break;
            }
            $sum-=$v;
        }
        return $ret;
    }

    /**
     * php-cli
     */
    public function run_cli($file,$args){
        $php='php';
        $cmd=$php.' '.$file." '{$args}'";
        if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
            $cmd = "start /b ".$cmd;
            pclose(popen($cmd, 'r'));
        }else{
            $cmd.=" > /dev/null &";
            exec($cmd);
        }
    }

    /**
     * curl
     */
    public function http($url,$data,&$error){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);//超时时间
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);//在发起连接前等待的时间，如果设置为0，则不等待
        if(!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_SSLVERSION,1);//升级ssl
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);//ssl
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);//ssl
        curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );
        $UserAgent="Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.93 Safari/537.36";
        curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
        //获取的信息以文件流的形式返回,不直接输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

//        curl_setopt ($curl,CURLOPT_REFERER,'	http://leshou.com/login');//前一页地址
//        curl_setopt($curl, CURLOPT_ENCODING, "gzip, deflate");//'gzip,deflate');这个是解释gzip
//        curl_setopt($curl, CURLOPT_HEADER, 0);//启用时会将头文件的信息作为数据流输出
//        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);//自动跟随重定向
//        curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1); //是否抓取跳转后的页面
//        curl_setopt($curl, CURLOPT_COOKIE, $cook);//带入cookie
        /*
        //自动更换IP
        $ar=array();
        $i=4;
        while($i>0){
            $ar[]=rand(1,255);
            $i--;
        }
        $ip=implode('.',$ar);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'CLIENT-IP:'.$ip, 'X-FORWARDED-FOR:'.$ip
        ]);

        */
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * getTimerBefore(time() - 30); //输出30秒前
     * @param $timer
     */
    public function getTimerBefore($timer)
    {
        $created = time() - $timer;
        $timeArr = array
        (
            '1'        => '秒',
            '60'       => '分钟',
            '3600'     => '小时',
            '86400'    => '天',
            '604800'   => '星期',
            '2592000'  => '个月',
            '31536000' => '年'
        );
        foreach($timeArr as $Key => $Value)
        {
            $timeNum = floor($created/$Key);
            if (0 != $timeNum)
            {
                $timeStr = $timeNum.$Value.'前';
            }
        }
        return $timeStr;
    }

    /**
     * 上传图片
     */
    public function upload(){
// 	    printJson('http://pic.wx.suofeiya.com.cn/upload/web/images/201603/56f9e14d508141496.jpg');
        $file=$_FILES['file'];
        if(empty($file)){
            printJson(null,1,"未上传文件.请重试");
        }
        $suffix = pathinfo($file['name'],PATHINFO_EXTENSION);
        $suffix=strtolower($suffix);
        if(!in_array($suffix, array('jpg','png','bmp','gif','jpeg'))){
            printJson(null,1,"上传文件格式不被支持");
        }
        $route = 'Upload/act/'.date("Ym/").md5(time()).'.'.$suffix;
        if(!is_dir(dirname(LIB_PATH).'/www/Upload/act/'.date("Ym/"))){
            mkdir(dirname(LIB_PATH).'/www/Upload/act/'.date("Ym/"),0777,true);
        }
        $flag = copy($file['tmp_name'], dirname(LIB_PATH).'/www/'.$route);
        if($flag == 1) {
            $url='http://'.$_SERVER['SERVER_NAME'].dirname(dirname($_SERVER['SCRIPT_NAME'])).'/'.$route;
            $url=str_replace('index.php/', '', $url);
            printJson(compact('url'),200);
        }else{
            Logger::debug("act upload error",array($flag,$route,$file['tmp_name']));
        }
        printJson(null,1,"上传失败");
        /*
        $file=file_get_contents($file['tmp_name']);
        $url="http://pic.wx.suofeiya.com.cn/task/task.php?opt=uploadPic";
        $res=RequestClient::http($url,null,array('block'=>'actbao','pic'=>base64_encode($file)));
        Logger::debug("上传comment结果返回".$res,RequestClient::getError());
        if(empty($res) || $res=='500'){
            printJson(null,1,"上传失败,请重试");
        }
        $res=json_decode($res,true);
        if(!isset($res['url'])){
            printJson(null,1,"上传失败.请重试");
        }
        printJson($res['url']);
        */
    }

    function log($key,$val){
        $msg='['.date("Ymd H:i:s").']'.$key.'>>>'.var_export($val,true).PHP_EOL;
//        file_put_contents($this->logfile,$msg,FILE_APPEND);
        $fp=fopen($this->logfile.'/'.date("Ymd").'.log','ab+');
        @fwrite($fp, $msg);
        fclose($fp);
    }

    /**
     * 根据width 缩放图片
     */
    public function zoom_jpg($t1,$max,&$w,&$h){
        $img=imagecreatefromstring(file_get_contents($t1));
        $ar=list($w, $h) = getimagesize($t1);
        if($w > $h){
            $h=$h*($max/$w);
            $w=$max;
        }else{
            $w=$w*($max/$h);
            $h=$max;
        }
        $image=imagecreatetruecolor($w, $h);
        imagecopyresampled($image,$img,0,0,0,0,$w,$h,$ar[0],$ar[1]);
        imagedestroy($img);
        return $image;
    }

    /**
     * 裁剪图片
     * @param $original
     * @return mixed
     */
    public function cut_jpg($original){
        list($width, $height) = getimagesize($original);
        $qrcImg = imagecreatefromstring(file_get_contents($original));
        $image_p = imagecreatetruecolor(290, 290);
        imagecopyresampled($image_p, $qrcImg, 0, 0, 30, 30, 320, 320, $width-20, $height-20);
        imagedestroy($qrcImg);
        return $image_p;
    }

}
