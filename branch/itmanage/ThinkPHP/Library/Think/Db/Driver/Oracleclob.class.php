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
 * Oracleclob数据库驱动,2018-09-14，白景琦新增该驱动
 */
class Oracleclob extends Driver{
    protected   $ociResource  = null;
    protected   $selectSql    = 'SELECT * FROM (SELECT thinkphp.*, rownum AS numrow FROM (SELECT  %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER% %UNION%) thinkphp ) %LIMIT%%COMMENT%';
    protected   $tableName    = '';     //当前操作表名
    protected   $fields        = [];     //当前表中字段
    protected   $clobFields   = [];     //clob字段，暂时只扩展clob特殊字段
    protected   $userData     = [];     //修改、插入时传入的数据，保存到此处

    /**
     * 解析pdo连接的dsn信息
     * @access public
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config){
        $dsn  =   $config['hostname'].($config['hostport']?':'.$config['hostport']:'').'/'.$config['database'];
        return $dsn;
    }

    /**
     * 连接数据库方法： 2018-09-14，白景琦重写该方法以oci连接方式覆盖父级pdo连接方式
     * @access public
     */
    public function connect($config='',$linkNum=0,$autoConnection=false) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            try{
                if(empty($config['dsn'])) {
                    $config['dsn']  =   $this->parseDsn($config);
                }
                $this->linkID[$linkNum] = oci_connect(  $config['username'], $config['password'],$config['dsn'], 'utf8');
            }catch (\PDOException $e) {
                if($autoConnection){
                    trace($e->getMessage(),'','ERR');
                    return $this->connect($autoConnection,$linkNum);
                }elseif($config['debug']){
                    E($e->getMessage());
                }
            }
        }
        return $this->linkID[$linkNum];
    }

    /**
     * 执行语句
     * @access public
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL     
     * @return integer
     */
    public function execute($str,$fetchSql=false) {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        if(!empty($this->bind)){
            $that   =   $this;
            //判断当前修改数据是否包含clob字段
            $isFindClob = array_search ($this->clobFields[0], array_keys($this->userData));
            $isSaveClob = ($isFindClob === false) ? false:$isFindClob;
            if($isSaveClob !== false){
                $bind = [];
                foreach($this->bind as $key=>$value){
                    if(':'.$isFindClob == $key){
                        $bind[$key] = 'EMPTY_CLOB()';
                    }else{
                        $bind[$key] = '\''.$that->escapeString($value).'\'';
                    }
                }
                $this->queryStr =   strtr($this->queryStr,$bind) . ' RETURNING ' .$this->clobFields[0].' INTO :myclob';
            }else{
                $this->queryStr =   strtr($this->queryStr,array_map(function($val) use($that){
                    return '\''.$that->escapeString($val).'\'';
                },$this->bind));
            }
        }
        if($fetchSql){
            return $this->queryStr;
        }
//        $sql = "UPDATE newatp_advise SET a_content=EMPTY_CLOB() WHERE ( a_createuser = 'baijingqi' ) RETURNING a_content INTO :myclob";
        $this->executeTimes++;
        N('db_write',1); // 兼容代码        
        // 记录开始执行时间
        $this->debug(true);
        $this->ociResource = oci_parse($this->_linkID, $this->queryStr);
        if(isset($isSaveClob) && $isSaveClob !== false){
            $clob = oci_new_descriptor($this->_linkID, OCI_D_LOB);
            oci_bind_by_name($this->ociResource, ":myclob", $clob, -1, OCI_B_CLOB);
            oci_execute($this->ociResource,OCI_DEFAULT);
            $res = $clob->save( $this->userData[$this->clobFields[0]]);
            oci_commit($this->_linkID);
        }else{
            $res = oci_execute($this->ociResource);
        }

        if(empty($res)){
            $e = oci_error($this->ociResource);   // For oci_connect errors pass no handle
            E(htmlentities($e['message']));
            exit;
        }else{
            return $res;
        }
    }

    /**
     * 执行查询 返回数据集
     * @access public
     * @param string $str  sql指令
     * @param boolean $fetchSql  不执行只是获取SQL
     * @return mixed
     */
    public function query($str,$fetchSql=false) {
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr     =   $str;
        if(!empty($this->bind)){
            $that   =   $this;
            $this->queryStr =   strtr($this->queryStr,array_map(function($val) use($that){ return '\''.$that->escapeString($val).'\''; },$this->bind));
        }
        if($fetchSql){
            return $this->queryStr;
        }
        //释放前次的查询结果
        $this->queryTimes++;
        N('db_query',1); // 兼容代码
        // 调试开始
        $this->debug(true);

        $this->ociResource = oci_parse($this->_linkID, $this->queryStr);
        foreach ($this->bind as $key => $val) {
            if(is_array($val)){
                oci_bind_by_name ($this->ociResource , $val[0], $val[1]);
            }else{
                oci_bind_by_name ($this->ociResource , $key, $val);
            }
        }

        oci_execute($this->ociResource);
        $this->debug(false);
        return $this->getResult();
    }

    /**
     * 获得所有的查询数据
     * @access private
     * @return array
     */
    private function getResult() {
        //返回数据集
        oci_fetch_all($this->ociResource, $result);
        $initResult = [];
        //拼装查询结果
        foreach($result as $key=>$value){
            foreach($value as $k=>$v){
                $initResult[$k][strtolower($key)] = $v;
            }
        }
        $this->numRows = count( $initResult );
        return $initResult;
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insert($data,$options=array(),$replace=false) {
        $values  =  $fields    = array();
        $this->model  =   $options['model'];
        $this->userData = $data;
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $fields[]   =  $this->parseKey($key);
                $values[]   =  $val[1];
            }elseif(is_null($val)){
                $fields[]   =   $this->parseKey($key);
                $values[]   =   'NULL';
            }elseif(is_scalar($val)) { // 过滤非标量数据
                $fields[]   =   $this->parseKey($key);
                if(0===strpos($val,':') && in_array($val,array_keys($this->bind))){
                    $values[]   =   $this->parseValue($val);
                }else{
                    $name       =   count($this->bind);
                    $values[]   =   ':'.$name;
                    $this->bindParam($name,$val);
                }
            }
        }
        // 兼容数字传入方式
        $replace= (is_numeric($replace) && $replace>0)?true:$replace;
        $sql    = (true===$replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')'.$this->parseDuplicate($replace);
        $sql    .= $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return false | integer
     */
    public function update($data,$options) {
        $this->model  =   $options['model'];
        $this->userData = $data;
        $this->parseBind(!empty($options['bind'])?$options['bind']:array());
        $table  =   $this->parseTable($options['table']);
        $sql   = 'UPDATE ' . $table . $this->parseSet($data);
        if(strpos($table,',')){// 多表更新支持JOIN操作
            $sql .= $this->parseJoin(!empty($options['join'])?$options['join']:'');
        }
        $sql .= $this->parseWhere(!empty($options['where'])?$options['where']:'');
        if(!strpos($table,',')){
            //  单表更新支持order和lmit
            $sql   .=  $this->parseOrder(!empty($options['order'])?$options['order']:'')
                .$this->parseLimit(!empty($options['limit'])?$options['limit']:'');
        }
        $sql .=   $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,!empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 取得数据表的字段信息
     * @access public
     */
     public function getFields($tableName) {
         list($tableName) = explode(' ', $tableName);
         $this->tableName = $tableName;
         $result = $this->query("select a.column_name,data_type,decode(nullable,'Y',0,1) notnull,data_default,decode(a.column_name,b.column_name,1,0) pk "
                  ."from user_tab_columns a,(select column_name from user_constraints c,user_cons_columns col "
          ."where c.constraint_name=col.constraint_name and c.constraint_type='P'and c.table_name='".strtoupper($tableName)
          ."') b where table_name='".strtoupper($tableName)."' and a.column_name=b.column_name(+)");
         $initResult = [];
         foreach($result as $key=>$value){
             $columnName = strtolower($value['column_name']);
             if(strtolower($value['data_type']) == 'clob') $this->clobFields[] = $columnName;
             $initResult[$columnName] = $value;
         }
         $this->fields = $initResult;

         if(empty($this->clobFields)) E('不存在clob字段');
         return $initResult;
    }

    /**
     * limit
     * @access public
     * @return string
     */
	public function parseLimit($limit) {
        $limitStr    = '';
        if(!empty($limit)) {
            $limit	=	explode(',',$limit);
            if(count($limit)>1)
                $limitStr = "(numrow>" . $limit[0] . ") AND (numrow<=" . ($limit[0]+$limit[1]) . ")";
            else
                $limitStr = "(numrow>0 AND numrow<=".$limit[0].")";
        }
        return $limitStr?' WHERE '.$limitStr:'';
    }

    /**
     * 设置锁机制
     * @access protected
     * @return string
     */
    protected function parseLock($lock=false) {
        if(!$lock) return '';
        return ' FOR UPDATE NOWAIT ';
    }

    /**
     * 关闭数据库
     * @access public
     */
    public function close() {
        oci_free_statement ( $this->ociResource );
        oci_close($this->_linkID);
        $this->_linkID = null;
    }

    /**
     * 析构方法
     * @access public
     */
    public function __destruct() {
        $this->close();
    }
}
