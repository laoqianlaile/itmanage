<?php
namespace Org\MyCache;
class MyRedis{
	private $redis;

	//��ǰ���ݿ�ID��
	protected $dbId = 0;

	//��ǰȨ����֤��
	protected $auth;

	/**
	 * ʵ�����Ķ���,����ģʽ.
	 */
	static private $_instance=array();

	private  $k;

	//������������
	protected $attr=array(
		//���ӳ�ʱʱ�䣬redis�����ļ���Ĭ��Ϊ300��
		'timeout'=>30,
		//ѡ������ݿ⡣
		'db_id'=>0,
	);

	//ʲôʱ�����½�������
	protected $expireTime;

	protected $host;

	protected $port;

	private function __construct($config = [],$attr=array()){
		$this->attr        =    array_merge($this->attr,$attr);
		$this->redis    =    new \Redis();
		$this->port        =    $config['port'] ? $config['port'] : 6379;
		$this->host        =    $config['host'] ? $config['host'] : '127.0.0.1';
		$this->redis->connect($this->host, $this->port, $this->attr['timeout']);

		if(isset($config['auth'])) {
			$this->auth($config['auth']);
			$this->auth    =    $config['auth'];
		}
		$this->expireTime    =    time() + $this->attr['timeout'];
	}

	/**
	 * �õ�ʵ�����Ķ���.
	 * Ϊÿ�����ݿ⽨��һ������
	 * ������ӳ�ʱ���������½���һ������
	 * @param array $config
	 * @param int $dbId
	 */
	public static function getInstance($config, $attr = array()){
		//�����һ���ַ�����������Ϊ�����ݿ��ID�š��Լ�д����
		if(!is_array($attr)) {
			$dbId    =    $attr;
			$attr    =    array();
			$attr['db_id']    =    $dbId;
		}
		$attr['db_id']    =    isset($attr['db_id']) && !empty($attr['db_id']) ? $attr['db_id'] : 0;
		$k    =    md5(implode('', $config).$attr['db_id']);
		if(!isset(static::$_instance[$k]) || !(static::$_instance[$k] instanceof self)) {
			static::$_instance[$k] = new self($config,$attr);
			static::$_instance[$k]->k        =    $k;
			static::$_instance[$k]->dbId    =    $attr['db_id'];

			//�������0�ſ⣬ѡ��һ�����ݿ⡣
			if($attr['db_id'] != 0) static::$_instance[$k]->select($attr['db_id']);
		} elseif( time() > static::$_instance[$k]->expireTime) {
			static::$_instance[$k]->close();
			static::$_instance[$k]         =     new self($config,$attr);
			static::$_instance[$k]->k    =    $k;
			static::$_instance[$k]->dbId=    $attr['db_id'];

			//�������0�ſ⣬ѡ��һ�����ݿ⡣
			if($attr['db_id']!=0){
				static::$_instance[$k]->select($attr['db_id']);
			}
		}
		return static::$_instance[$k];
	}

	private function __clone(){}

	/**
	 * ִ��ԭ����redis����
	 * @return \Redis
	 */
	public function getRedis(){
		return $this->redis;
	}

	/*****************hash���������*******************/

	/**
	 * �õ�hash����һ���ֶε�ֵ
	 * @param string $key ����key
	 * @param string  $field �ֶ�
	 * @return string|false
	 */
	public function hGet($key,$field){
		return $this->redis->hGet($key,$field);
	}

	/**
	 * Ϊhash���趨һ���ֶε�ֵ
	 * @param string $key ����key
	 * @param string  $field �ֶ�
	 * @param string $value ֵ��
	 * @return bool
	 */
	public function hSet($key,$field,$value){
		return $this->redis->hSet($key,$field,$value);
	}

	/**
	 * �ж�hash���У�ָ��field�ǲ��Ǵ���
	 * @param string $key ����key
	 * @param string  $field �ֶ�
	 * @return bool
	 */
	public function hExists($key,$field){
		return $this->redis->hExists($key,$field);
	}

	/**
	 * ɾ��hash����ָ���ֶ� ,֧������ɾ��
	 * @param string $key ����key
	 * @param string  $field �ֶ�
	 * @return int
	 */
	public function hdel($key,$field){
		$fieldArr=explode(',',$field);
		$delNum=0;

		foreach($fieldArr as $row) {
			$row=trim($row);
			$delNum+=$this->redis->hDel($key,$row);
		}

		return $delNum;
	}

	/**
	 * ����hash��Ԫ�ظ���
	 * @param string $key ����key
	 * @return int|bool
	 */
	public function hLen($key){
		return $this->redis->hLen($key);
	}

	/**
	 * Ϊhash���趨һ���ֶε�ֵ,����ֶδ��ڣ�����false
	 * @param string $key ����key
	 * @param string  $field �ֶ�
	 * @param string $value ֵ��
	 * @return bool
	 */
	public function hSetNx($key,$field,$value){
		return $this->redis->hSetNx($key,$field,$value);
	}

	/**
	 * Ϊhash�����ֶ��趨ֵ��
	 * @param string $key
	 * @param array $value
	 * @return array|bool
	 */
	public function hMset($key,$value){
		if(!is_array($value))
			return false;
		return $this->redis->hMset($key,$value);
	}

	/**
	 * Ϊhash�����ֶ��趨ֵ��
	 * @param string $key
	 * @param array|string $value string��','�ŷָ��ֶ�
	 * @return array|bool
	 */
	public function hMget($key,$field){
		if(!is_array($field))
			$field=explode(',', $field);
		return $this->redis->hMget($key,$field);
	}

	/**
	 * Ϊhash�������ۼӣ����Ը���
	 * @param string $key
	 * @param int $field
	 * @param string $value
	 * @return bool
	 */
	public function hIncrBy($key,$field,$value){
		$value=intval($value);
		return $this->redis->hIncrBy($key,$field,$value);
	}

	/**
	 * ��������hash��������ֶ�
	 * @param string $key
	 * @return array|bool
	 */
	public function hKeys($key){
		return $this->redis->hKeys($key);
	}

	/**
	 * ��������hash����ֶ�ֵ��Ϊһ����������
	 * @param string $key
	 * @return array|bool
	 */
	public function hVals($key){
		return $this->redis->hVals($key);
	}

	/**
	 * ��������hash����ֶ�ֵ��Ϊһ����������
	 * @param string $key
	 * @return array|bool
	 */
	public function hGetAll($key){
		return $this->redis->hGetAll($key);
	}

	/*********************���򼯺ϲ���*********************/

	/**
	 * ����ǰ�������һ��Ԫ��
	 * ���value�Ѿ����ڣ������score��ֵ��
	 * @param string $key
	 * @param string $score ��ֵ
	 * @param string $value ֵ
	 * @return bool
	 */
	public function zAdd($key,$score,$value){
		return $this->redis->zAdd($key,$score,$value);
	}

	/**
	 * ��$value��Ա��orderֵ������$num,����Ϊ����
	 * @param string $key
	 * @param string $num ���
	 * @param string $value ֵ
	 * @return �����µ�order
	 */
	public function zinCry($key,$num,$value){
		return $this->redis->zinCry($key,$num,$value);
	}

	/**
	 * ɾ��ֵΪvalue��Ԫ��
	 * @param string $key
	 * @param stirng $value
	 * @return bool
	 */
	public function zRem($key,$value){
		return $this->redis->zRem($key,$value);
	}

	/**
	 * ������order�������к�0��ʾ��һ��Ԫ�أ�-1��ʾ���һ��Ԫ��
	 * @param string $key
	 * @param int $start
	 * @param int $end
	 * @return array|bool
	 */
	public function zRange($key,$start,$end){
		return $this->redis->zRange($key,$start,$end);
	}

	/**
	 * ������order�ݼ����к�0��ʾ��һ��Ԫ�أ�-1��ʾ���һ��Ԫ��
	 * @param string $key
	 * @param int $start
	 * @param int $end
	 * @return array|bool
	 */
	public function zRevRange($key,$start,$end){
		return $this->redis->zRevRange($key,$start,$end);
	}

	/**
	 * ������order�������к󣬷���ָ��order֮���Ԫ�ء�
	 * min��max������-inf��+inf����ʾ���ֵ����Сֵ
	 * @param string $key
	 * @param int $start
	 * @param int $end
	 * @package array $option ����
	 *     withscores=>true����ʾ�����±�ΪOrderֵ��Ĭ�Ϸ�����������
	 *     limit=>array(0,1) ��ʾ��0��ʼ��ȡһ����¼��
	 * @return array|bool
	 */
	public function zRangeByScore($key,$start='-inf',$end="+inf",$option=array()){
		return $this->redis->zRangeByScore($key,$start,$end,$option);
	}

	/**
	 * ������order�ݼ����к󣬷���ָ��order֮���Ԫ�ء�
	 * min��max������-inf��+inf����ʾ���ֵ����Сֵ
	 * @param string $key
	 * @param int $start
	 * @param int $end
	 * @package array $option ����
	 *     withscores=>true����ʾ�����±�ΪOrderֵ��Ĭ�Ϸ�����������
	 *     limit=>array(0,1) ��ʾ��0��ʼ��ȡһ����¼��
	 * @return array|bool
	 */
	public function zRevRangeByScore($key,$start='-inf',$end="+inf",$option=array()){
		return $this->redis->zRevRangeByScore($key,$start,$end,$option);
	}

	/**
	 * ����orderֵ��start end֮�������
	 * @param unknown $key
	 * @param unknown $start
	 * @param unknown $end
	 */
	public function zCount($key,$start,$end){
		return $this->redis->zCount($key,$start,$end);
	}

	/**
	 * ����ֵΪvalue��orderֵ
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function zScore($key,$value){
		return $this->redis->zScore($key,$value);
	}

	/**
	 * ���ؼ�����score�����������ָ����Ա������ţ���0��ʼ��
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function zRank($key,$value){
		return $this->redis->zRank($key,$value);
	}

	/**
	 * ���ؼ�����score�����������ָ����Ա������ţ���0��ʼ��
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function zRevRank($key,$value){
		return $this->redis->zRevRank($key,$value);
	}

	/**
	 * ɾ�������У�scoreֵ��start end֮���Ԫ�ء�����start end
	 * min��max������-inf��+inf����ʾ���ֵ����Сֵ
	 * @param unknown $key
	 * @param unknown $start
	 * @param unknown $end
	 * @return ɾ����Ա��������
	 */
	public function zRemRangeByScore($key,$start,$end){
		return $this->redis->zRemRangeByScore($key,$start,$end);
	}

	/**
	 * ���ؼ���Ԫ�ظ�����
	 * @param unknown $key
	 */
	public function zCard($key){
		return $this->redis->zCard($key);
	}
	/*********************���в�������************************/

	/**
	 * �ڶ���β������һ��Ԫ��
	 * @param unknown $key
	 * @param unknown $value
	 * ���ض��г���
	 */
	public function rPush($key,$value){
		return $this->redis->rPush($key,$value);
	}

	/**
	 * �ڶ���β������һ��Ԫ�� ���key�����ڣ�ʲôҲ����
	 * @param unknown $key
	 * @param unknown $value
	 * ���ض��г���
	 */
	public function rPushx($key,$value){
		return $this->redis->rPushx($key,$value);
	}

	/**
	 * �ڶ���ͷ������һ��Ԫ��
	 * @param unknown $key
	 * @param unknown $value
	 * ���ض��г���
	 */
	public function lPush($key,$value){
		return $this->redis->lPush($key,$value);
	}

	/**
	 * �ڶ���ͷ����һ��Ԫ�� ���key�����ڣ�ʲôҲ����
	 * @param unknown $key
	 * @param unknown $value
	 * ���ض��г���
	 */
	public function lPushx($key,$value){
		return $this->redis->lPushx($key,$value);
	}

	/**
	 * ���ض��г���
	 * @param unknown $key
	 */
	public function lLen($key){
		return $this->redis->lLen($key);
	}

	/**
	 * ���ض���ָ�������Ԫ��
	 * @param unknown $key
	 * @param unknown $start
	 * @param unknown $end
	 */
	public function lRange($key,$start,$end){
		return $this->redis->lrange($key,$start,$end);
	}

	/**
	 * ���ض�����ָ��������Ԫ��
	 * @param unknown $key
	 * @param unknown $index
	 */
	public function lIndex($key,$index){
		return $this->redis->lIndex($key,$index);
	}

	/**
	 * �趨������ָ��index��ֵ��
	 * @param unknown $key
	 * @param unknown $index
	 * @param unknown $value
	 */
	public function lSet($key,$index,$value){
		return $this->redis->lSet($key,$index,$value);
	}

	/**
	 * ɾ��ֵΪvaule��count��Ԫ��
	 * PHP-REDIS��չ������˳���������˳��̫һ������֪���ǲ���bug
	 * count>0 ��β����ʼ
	 *  >0����ͷ����ʼ
	 *  =0��ɾ��ȫ��
	 * @param unknown $key
	 * @param unknown $count
	 * @param unknown $value
	 */
	public function lRem($key,$count,$value){
		return $this->redis->lRem($key,$value,$count);
	}

	/**
	 * ɾ�������ض����е�ͷԪ�ء�
	 * @param unknown $key
	 */
	public function lPop($key){
		return $this->redis->lPop($key);
	}

	/**
	 * ɾ�������ض����е�βԪ��
	 * @param unknown $key
	 */
	public function rPop($key){
		return $this->redis->rPop($key);
	}

	/*************redis�ַ�����������*****************/

	/**
	 * ����һ��key
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function set($key,$value){
		return $this->redis->set($key,$value);
	}

	/**
	 * �õ�һ��key
	 * @param unknown $key
	 */
	public function get($key){
		return $this->redis->get($key);
	}

	/**
	 * ����һ���й���ʱ���key
	 * @param unknown $key
	 * @param unknown $expire
	 * @param unknown $value
	 */
	public function setex($key,$expire,$value){
		return $this->redis->setex($key,$expire,$value);
	}


	/**
	 * ����һ��key,���key����,�����κβ���.
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function setnx($key,$value){
		return $this->redis->setnx($key,$value);
	}

	/**
	 * ��������key
	 * @param unknown $arr
	 */
	public function mset($arr){
		return $this->redis->mset($arr);
	}

	/*************redis�����򼯺ϲ�������*****************/

	/**
	 * ���ؼ���������Ԫ��
	 * @param unknown $key
	 */
	public function sMembers($key){
		return $this->redis->sMembers($key);
	}

	/**
	 * �жϼ����Ƿ����ĳ��Ԫ��
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function sIsMember($key, $value){
		return $this->redis->sIsMember($key, $value);
	}

	/**
	 * ��2�����ϵĲ
	 * @param unknown $key1
	 * @param unknown $key2
	 */
	public function sDiff($key1,$key2){
		return $this->redis->sDiff($key1,$key2);
	}

	/**
	 * ��Ӽ��ϡ����ڰ汾���⣬��չ��֧��������ӡ��������˷�װ
	 * @param unknown $key
	 * @param string|array $value
	 */
	public function sAdd($key,$value){
		if(!is_array($value))
			$arr=array($value);
		else
			$arr=$value;
		foreach($arr as $row)
			$this->redis->sAdd($key,$row);
	}

	/**
	 * �������򼯺ϵ�Ԫ�ظ���
	 * @param unknown $key
	 */
	public function scard($key){
		return $this->redis->scard($key);
	}

	/**
	 * �Ӽ�����ɾ��һ��Ԫ��
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function srem($key,$value){
		return $this->redis->srem($key,$value);
	}

	/*************redis�����������*****************/

	/**
	 * ѡ�����ݿ�
	 * @param int $dbId ���ݿ�ID��
	 * @return bool
	 */
	public function select($dbId){
		$this->dbId=$dbId;
		return $this->redis->select($dbId);
	}

	/**
	 * ��յ�ǰ���ݿ�
	 * @return bool
	 */
	public function flushDB(){
		return $this->redis->flushDB();
	}

	/**
	 * ���ص�ǰ��״̬
	 * @return array
	 */
	public function info(){
		return $this->redis->info();
	}

	/**
	 * ͬ���������ݵ�����
	 */
	public function save(){
		return $this->redis->save();
	}

	/**
	 * �첽�������ݵ�����
	 */
	public function bgSave(){
		return $this->redis->bgSave();
	}

	/**
	 * ������󱣴浽���̵�ʱ��
	 */
	public function lastSave(){
		return $this->redis->lastSave();
	}

	/**
	 * ����key,֧��*����ַ���?һ���ַ�
	 * ֻ��*����ʾȫ��
	 * @param string $key
	 * @return array
	 */
	public function keys($key){
		return $this->redis->keys($key);
	}

	/**
	 * ɾ��ָ��key
	 * @param unknown $key
	 */
	public function del($key){
		return $this->redis->del($key);
	}

	/**
	 * �ж�һ��keyֵ�ǲ��Ǵ���
	 * @param unknown $key
	 */
	public function exists($key){
		return $this->redis->exists($key);
	}

	/**
	 * Ϊһ��key�趨����ʱ�� ��λΪ��
	 * @param unknown $key
	 * @param unknown $expire
	 */
	public function expire($key,$expire){
		return $this->redis->expire($key,$expire);
	}

	/**
	 * ����һ��key���ж�ù��ڣ���λ��
	 * @param unknown $key
	 */
	public function ttl($key){
		return $this->redis->ttl($key);
	}

	/**
	 * �趨һ��keyʲôʱ����ڣ�timeΪһ��ʱ���
	 * @param unknown $key
	 * @param unknown $time
	 */
	public function exprieAt($key,$time){
		return $this->redis->expireAt($key,$time);
	}

	/**
	 * �رշ���������
	 */
	public function close(){
		return $this->redis->close();
	}

	/**
	 * �ر���������
	 */
	public static function closeAll(){
		foreach(static::$_instance as $o)
		{
			if($o instanceof self)
				$o->close();
		}
	}

	/** ���ﲻ�ر����ӣ���Ϊsessionд��������ж�������֮��
	public function __destruct(){
	return $this->redis->close();
	}
	 **/
	/**
	 * ���ص�ǰ���ݿ�key����
	 */
	public function dbSize(){
		return $this->redis->dbSize();
	}

	/**
	 * ����һ�����key
	 */
	public function randomKey(){
		return $this->redis->randomKey();
	}

	/**
	 * �õ���ǰ���ݿ�ID
	 * @return int
	 */
	public function getDbId(){
		return $this->dbId;
	}

	/**
	 * ���ص�ǰ����
	 */
	public function getAuth(){
		return $this->auth;
	}

	public function getHost(){
		return $this->host;
	}

	public function getPort(){
		return $this->port;
	}

	public function getConnInfo(){
		return array(
			'host'=>$this->host,
			'port'=>$this->port,
			'auth'=>$this->auth
		);
	}
	/*********************�������ط���************************/

	/**
	 * ���key,����һ������key���һ���ֹ���
	 * �ڴ��ڼ����key��ֵ��������ĸı䣬�ղ���Ϊkey�趨ֵ
	 * ��������ȡ��Key��ֵ��
	 * @param unknown $key
	 */
	public function watch($key){
		return $this->redis->watch($key);
	}

	/**
	 * ȡ����ǰ���Ӷ�����key��watch
	 *  EXEC ����� DISCARD �����ȱ�ִ���˵Ļ�����ô�Ͳ���Ҫ��ִ�� UNWATCH ��
	 */
	public function unwatch(){
		return $this->redis->unwatch();
	}

	/**
	 * ����һ������
	 */
	public function multi(){
		return $this->redis->multi(\Redis::MULTI);
	}

	/**
	 * ����һ������
	 * Redis::PIPELINE�ܵ�ģʽ�ٶȸ��죬��û���κα�֤ԭ�����п���������ݵĶ�ʧ
	 */
	public function pipeline(){
		return $this->redis->multi(\Redis::PIPELINE);
	}

	/**
	 * ִ��һ������
	 * �յ� EXEC ������������ִ�У���������������ִ��ʧ�ܣ������������Ȼ��ִ��
	 */
	public function exec(){
		return $this->redis->exec();
	}

	/**
	 * �ع�һ������
	 */
	public function discard(){
		return $this->redis->discard();
	}

	/**
	 * ���Ե�ǰ�����ǲ����Ѿ�ʧЧ
	 * û��ʧЧ����+PONG
	 * ʧЧ����false
	 */
	public function ping(){
		return $this->redis->ping();
	}

	public function auth($auth){
		return $this->redis->auth($auth);
	}
	/*********************�Զ���ķ���,���ڼ򻯲���************************/

	/**
	 * �õ�һ���ID��
	 * @param unknown $prefix
	 * @param unknown $ids
	 */
	public function hashAll($prefix,$ids){
		if($ids==false)
			return false;
		if(is_string($ids))
			$ids=explode(',', $ids);
		$arr=array();
		foreach($ids as $id)
		{
			$key=$prefix.'.'.$id;
			$res=$this->hGetAll($key);
			if($res!=false)
				$arr[]=$res;
		}

		return $arr;
	}

	/**
	 * ����һ����Ϣ������redis���ݿ��С�ʹ��0�ſ⡣
	 * @param string|array $msg
	 */
	public function pushMessage($lkey,$msg){
		if(is_array($msg)){
			$msg    =    json_encode($msg);
		}
		$key    =    md5($msg);

		//�����Ϣ�Ѿ����ڣ�ɾ������Ϣ���ѵ�ǰ��ϢΪ׼
		//echo $n=$this->lRem($lkey, 0, $key)."\n";
		//������������Ϣ
		$this->lPush($lkey, $key);
		$this->setex($key, 3600, $msg);
		return $key;
	}


	/**
	 * �õ�������ɾ��key������
	 * @param unknown $keys
	 * @param unknown $dbId
	 */
	public function delKeys($keys,$dbId){
		$redisInfo=$this->getConnInfo();
		$cmdArr=array(
			'redis-cli',
			'-a',
			$redisInfo['auth'],
			'-h',
			$redisInfo['host'],
			'-p',
			$redisInfo['port'],
			'-n',
			$dbId,
		);
		$redisStr=implode(' ', $cmdArr);
		$cmd="{$redisStr} KEYS \"{$keys}\" | xargs {$redisStr} del";
		return $cmd;
	}
}