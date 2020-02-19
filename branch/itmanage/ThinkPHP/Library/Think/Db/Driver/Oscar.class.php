<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Db\Driver;
use Think\Db\Driver;

/**
 * Oscar数据库驱动 
 */
class Oscar extends Driver{
    protected $selectSql  =     'SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%  %LIMIT% ';

      /**
     * 解析pdo连接的dsn信息
     * @access public
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config){

       
        $dsn='odbc:DRIVER={OSCAR ODBC Driver};Server='.$config['hostname'].';database='.$config['database'].';port='.$config['hostport'].';';
        //echo $dsn;
        //echo "<br/>";

       return $dsn;
    }
    
    /**
     * 执行语句
     * @access public
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL
     * @return mixed
     */
    public function execute($str,$fetchSql=false) {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        if(!empty($this->bind)){
            $that   =   $this;
            // $this->queryStr =   strtr($this->queryStr,array_map(function($val) use($that){ return '\''.$that->escapeString($val).'\''; },$this->bind));
              $this->queryStr =   strtr($this->queryStr,array_map(function($val) use($that){ return '\''.$that->escapeString($val).'\''; },$this->bind));
        }
        if($fetchSql){
            return $this->queryStr;
        }
        //释放前次的查询结果
        if ( !empty($this->PDOStatement) ) $this->free();
        $this->executeTimes++;
        N('db_write',1); // 兼容代码
        // 记录开始执行时间
        $this->debug(true);
        $this->PDOStatement =   $this->_linkID->prepare($str);
        if(false === $this->PDOStatement) {
            E($this->error());
        }
        foreach ($this->bind as $key => $val) {
            if(is_array($val)){
                $this->PDOStatement->bindValue($key, $val[0], $val[1]);
            }else{
                $this->PDOStatement->bindValue($key, $val);
            }
        }
        $this->bind =   array();
        $result =   $this->PDOStatement->execute();
        $this->debug(false);
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = $this->PDOStatement->rowCount();
            return $this->numRows;
        }
    }
    
    /**
     * 取得数据表的字段信息
     * @access public
     */
    public function getFields($tableName) {
        
        $this->initConnect(true);
        //echo "oscar ---getFields函数开始 ";
        list($tableName) = explode(' ', $tableName);
  		$sql ="SELECT a.attname \"Field\",  INFO_SCHEM.format_type(a.atttypid, a.atttypmod) \"Type\",  DECODE(a.attnotnull, 't', 'YES', 'f', 'NO') as \"NULL\" , '' as \"Key\" ,DECODE(a.atthasdef, 'f', 'NULL', a.atthasdef)  as \"DEDAULT\",'' as Extra FROM INFO_SCHEM.v_sys_attribute a ,sys_class sc  WHERE a.attrelid = sc.oid   and sc.relname='".strtoupper($tableName)."'  AND a.attnum > 0 AND NOT  a.attisdropped ORDER BY a.attnum; ";
  	
        $result = $this->query($sql);
        $info   =   array();
        if($result){
            // print_r($result);
            //echo "开始循环值";
           foreach($result as $key => $val){
         
                $info[strtolower($val['FIELD'])] = array(
                    'name'    => strtolower($val['FIELD']),
                    'type'    => strtolower($val['TYPE']),
                    'notnull' => (bool) ($val['NULL'] =='YES'), // 1表示不为Null
                    'default' => $val['DEDAULT'],
                    'primary' => $val['Key'],
                    'autoinc' => $val['EXTRA'],
                    
                    // 'KEY' => (strtolower($val['KEY']) == 'pri'),
                    // 'EXTRA' => (strtolower($val['EXTRA']) == 'auto_increment'),
                );
                 // print_r ($info);
            }
            // print_r ($info);
        } 
     
 
        //print_r($info);
        // echo "--------";
        // print_r($result);   
  //echo "oscar ---getFields函数结束 ";
        return $result;
    }


    public function getTables($dbName='') {
        $sql='SELECT TABLE_NAME FROM tables ';
        $result   =  $this->query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = trim(current($val));
             echo current($val);
        }
        return $info;
    }
    
    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL指令
     * @return string
     */
    public function escapeString($str) {
        // return str_replace("'", "''", $str);
         return str_replace("'", "''", $str);
    }

    /**
     * limit
     * @access public
     * @param $limit limit表达式
     * @return string
     */
	public function parseLimit($limit) {
        $limitStr    = '';
        if(!empty($limit)) {
            $limit	=	explode(',',$limit);
            if(count($limit)>1)
                $limitStr = " limit " . $limit[0] . " offset " .$limit[1] ;
            else
                $limitStr = " limit ".$limit[0] ;
        }
        return $limitStr;
    }
    
  
  
   
  
  
  
  
    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        $this->_linkID = null;
    }



 // public function insert($data,$options=array(),$replace=false) {
 //        //echo '11111';
        
 //        echo "oscar- insert函数开始";
 //        echo "fields内容开始---";
 //        // print_r($fields);
 //        print_r($data);

 //        echo "fields内容结束---";

 //        $values  =  $fields    = array();
 //        $this->model  =   $options['model'];
 //        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
 //        foreach ($data as $key=>$val){
 //            if(is_array($val) && 'exp' == $val[0]){
 //                $fields[]   =  $this->parseKey($key);
 //                $values[]   =  $val[1];
                
 //                echo  '定位as'.$fields;
 //            }elseif(is_null($val)){
 //                $fields[]   =   $this->parseKey($key);
 //                $values[]   =   'NULL';
 //                  echo  '定位de'.$fields;
 //            }elseif(is_scalar($val)) { // 过滤非标量数据
 //                $fields[]   =   $this->parseKey($key);
 //                echo  '定位de'.$fields;
 //                if(0===strpos($val,':') && in_array($val,array_keys($this->bind))){
 //                    $values[]   =   $this->parseValue($val);
 //                }else{
 //                    $name       =   count($this->bind);
 //                    $values[]   =   ':'.$name;
 //                    $this->bindParam($name,$val);
 //                }
 //            }
 //        }
 //        // 兼容数字传入方式
 //        $replace= (is_numeric($replace) && $replace>0)?true:$replace;
 //        $sql    = (true===$replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')'.$this->parseDuplicate($replace);
 //        $sql    .= $this->parseComment(!empty($options['comment'])?$options['comment']:'');
 //        echo '定位sql111语句 <br/>'.$sql.'<br/>';
 //        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
 //        echo "oscar- insert函数结束";
 //    }






}





