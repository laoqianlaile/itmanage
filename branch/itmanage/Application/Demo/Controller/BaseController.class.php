<?php
namespace Demo\Controller;
use Think\Controller;
class BaseController extends Controller{

    public function __construct(){
        parent::__construct();
        $userId = session('user_id');

        $loginModel = D('Admin/Login');
        if (empty($userId)) $loginModel->loginOut();

        $loginModel->checkLoginIfExpire(false); //检测登录是否过期
    }

    /**
     * 该方法用于简化方法实现逻辑：如需要导入，方法后面加上withexport，无权限页面加上withnopower（不区分大小写）
     * 后续扩展在switch 中增加case分支即可
     * 具体逻辑仍需自己在同一个方法中实现
     * @param $method
     */
    public function _empty($method){
        $method = strtolower($method);
        $withIndex = strpos($method, 'with');
        if($withIndex === false){
            if (file_exists_case($this->view->parseTemplate())) {
                exit($this->display());
            }else{
                E($method . '页面未找到', 404);
            }
        }

        $split = explode('with', $method);
        switch($split[1]){
            case 'export': //导出
            case 'nopower': //无权限页面
                $this->$split[0](true);
                break;
            default:
                E($method . '页面未找到', 404);
        }
    }
    function makeGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return 'guid'.trim(com_create_guid(), '{}');
        }
        return  uniqid( '', true);
        // return 'guid'.sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    function buildSql($sql,$condition)
    {
        if($this->containString($sql," where "))
        {
            return $sql." and ".$condition;
        }
        else
        {
            return $sql." where ".$condition;
        }
    }

    function filterSqlOrderCondition($input)
    {
        $subject = strtolower($input);
        $pattern  =  '/^[qwertyuiopasdfghjklzxcvbnm_0123456789]*$/' ;
        preg_match($pattern, $subject, $matches);
        if (preg_match($pattern, $subject, $matches) == 0) {
            echo "''";
        } else {
            echo $input;
        }
    }
    function buildSqlPage($sql,$start,$length)
    {
        $oraclestart = $start;
        $oracleend = $start + $length;
        return "
Select * From
(
  Select fp.*,Rownum RN From
  (
  $sql
  ) fp
  Where Rownum<=$oracleend
)
Where RN>$oraclestart";
    }
    function containString($input,$splite)
    {
        $tmparray = explode($splite, $input);
        if (count($tmparray) > 1) {
            return true;
        } else {
            return false;
        }
    }
    function logSys($type,$ip,$userid,$username,$detail,$module,$tablename,$mainid){
        $Model = M('log');
        $data['l_atpid'] = $this->makeGuid();
        $data['l_ipaddress'] = $ip;
        $data['l_optype'] = $type;
        $data['l_optime'] = date('Y-m-d H:i:s', time());
        $data['l_opuserid'] = $userid;
        $data['l_opusername'] = $username;
        $data['l_modulename'] = $module;
        $data['l_tablename'] = $tablename;
        $data['l_detail'] = $detail;
        $data['l_mainid'] = $mainid;
        $Model->add($data);
    }
    function logUsbkey($code="",$optype="",$objecttype="",$detail=""){
        $Model = M('usbkeyhistory');
        $data['uh_atpid'] = $this->makeGuid();
        $data['uh_optime'] = date('Y-m-d H:i:s', time());
        $data['uh_opuserid'] = session('username');
        $data['uh_opuser'] = session('realusername');
        $data['uh_type'] = $optype;
        $data['uh_object'] = $objecttype;
        $data['uh_content'] = $detail;
        $data['uh_code'] = $code;
        $Model->add($data);
    }
    /**
     * @param $oldArr 数据库中查询到的原记录
     * @param $newArr 提交的新纪录
     * @return $logStr 日志详细内容
     */
    function getLogDetail($L_TABLENAME,$L_OPTYPE, $oldArr, $newArr) {
        $logStr = '';
        $filterArr = array('L_IPADDRESS','L_OPTIME','L_OPUSERID','L_OPUSERNAME');
        if($L_OPTYPE == 'add') {
            $id = $newArr['id'];
            $logStr .= 'type:add;<br />tablename:'.$L_TABLENAME.';<br />id:'.$id.';<br />';
//            data:{';
//            foreach($newArr as $k => $v) {
//                $logStr .=  $k . ':' . $v . ',<br />';
//            }
//            $logStr .= '}';
        } else if($L_OPTYPE == 'update') {
            $id = $oldArr['id'];
            $logStr .= 'type:update;<br />tablename:'.$L_TABLENAME.';<br />id:'.$id.';<br />data:{';
            foreach ($oldArr as $k => $v) {
                if (in_array($k, $filterArr)) {
                    continue;
                } else {
                    if ($v != $newArr[$k]) {
                        $logStr .= $k . ':' . $v . '->' . $newArr[$k] . ',<br />';
                    }
                }
            }
            $logStr .= '}';
        } else if($L_OPTYPE == 'delete') {
            $id = $oldArr['id'];
            $logStr .= 'type:delete;<br />tablename:'.$L_TABLENAME.';<br />id:'.$id.';<br />data:{';
            foreach($oldArr as $k => $v) {
                $logStr .=  $k . ':' . $v . ',<br />';
            }
            $logStr .= '}';
        }
        return $logStr;
    }
    function logZichansys( $tmp,$type,$table,$detail)
    {
        $Model = M('change');
        $data['bg_atpid'] = $this->makeGuid();
        $data['bg_atpstatus'] = null;
        $data['bg_atpcreatetime'] = date('Y-m-d H:i:s', time());
        $data['bg_atpcreateuser'] = session('username');
        $data['bg_atplastmodifytime'] = date('Y-m-d H:i:s', time());
        $data['bg_atplastmodifyuser'] = session('username');
        $data['bg_atpremark'] = '';
//        $data['bg_logip'] = get_client_ip();
        $data['bg_optype'] = $type;
        $data['bg_ip'] = $tmp['zd_ipaddress'];
        $data['bg_mac'] = $tmp['zd_macaddress'];
        $data['bg_detail'] = $detail;

        $data['bg_devicecode'] = $tmp['zd_devicecode'];
        $data['bg_table'] = $table;
        $data['bg_belongpersonid'] = $tmp['zd_dutyman'];
        $data['bg_mainid'] = $tmp['zd_atpid'];
        $data['bg_maintype'] =M('dictionary')->where("d_atpid='%s' and d_belongtype='资产类型'", $tmp['zd_type'])->getField('d_dictname');
        $data['bg_username'] = M('person')->where("username='%s'",$data['bg_atplastmodifyuser'])->getField('realusername');
        $data['bg_belongperson'] = M('person')->where("id='%s'",$data['bg_belongpersonid'])->getField('realusername');
        $Model->add($data);
    }

    public function assignuser(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.username id,p.realusername||'('||p.username||')--'||d.name text from  it_person p,it_depart d  where
(p.username like '%".$q."%' or p.realusername like '%".$q."%') and p.orgid = d.id";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }
    public function assigndept(){

        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  id,fullname text from
it_depart  where
fullname like '%".$q."%'";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    public function getuserdept_new($username) //通信站派工单中用户查询
    {
        $arr = array();
        $Model = M('person');
        $orgid = $Model->where("username='%s'", $username)->getField('orgid');
        if(empty($orgid)) return $arr;
        $pid=  M('depart')->where("id='%s'", $orgid)->getField('pid');
        if(empty($pid)) return $arr;
        $office = M('depart')->where("id='%s'", $orgid)->getField('name');
        $depart = M('depart')->where("id='%s'", $pid)->getField('name');
        if(empty($depart)) return $arr;
        array_push($arr, $depart);
        array_push($arr, $office);
        return $arr;
    }

    /**
     * 获取json标准结果
     * @param int $code
     * @param string $message
     * @return string
     */
    public function makeStandResult($code = 0, $message = ''){
        $result = array(
            'code' => $code,
            'message' => $message,
        );
        return json_encode($result);
    }

    /**
     * 记录操作日志
     * @param $type
     * @param $module
     * @param $content
     * @return mixed
     */
    public function recordLog($type, $module, $content,$table = '',$atpid = '',$beizhu=''){
        $optime = date('Y-m-d H:i:s',time());
        $data = array(
            'l_atpid'      => $this->makeGuid(),
            'l_optime'     => $optime,
            'l_ipaddress'  => get_client_ip(),
            'l_optype'     => $type,
            'l_opuserid'   => session('user_name'),
            'l_opusername' => session('realusername'),
            'l_modulename' => $module,
            'l_detail'     => $content,
            'l_tablename'  => $table,
            'l_mainid'     => $atpid,
            'l_beizhu'     => $beizhu
        );
        $res = M('log')->add($data);
        return $res;
    }

    /**
     * 得到webservice操作对象
     * @param string $address
     * @param string $charset
     * @param bool|false $decode
     * @return \SoapClient
     */
    public function getWebServiceObj($address = '', $charset = 'UTF-8',$decode = false){
        $client = new \SoapClient($address);
        $client->soap_defencoding = $charset;
        $client->decode_utf8 = $decode;
        $client->xml_encoding = $charset;
        return $client;
    }

    public function getfactory(){
        $Model   = M('dictionary');
        $sbtype  = I('post.sbtype');
        $options = I('post.options');
        if(!$options){
            $sbtypelist = $Model->where("d_parentid='%s'",$sbtype)->field('d_dictname,d_atpid')->select();
        }else{
            $sbtypelist = $Model->where("d_parentid='$sbtype' and d_belongtype = 'factoryinfo'")->field('d_dictname,d_atpid')->select();
        }
        echo json_encode($sbtypelist);
    }
    public function getfactoryForSEC(){
        $Model   = M('dictionary');
        $sbtype  = I('post.sbtype');
        $options = I('post.options');
        if(!$options){
            $sbtypelist = $Model->where("d_parentid='%s' and d_atpstatus is null",$sbtype)->field('d_dictname,d_atpid')->select();
        }else{
            $sbtypelist = $Model->where("d_parentid='guidF5C36989-8993-4112-9E69-20DB4D4220BB'  and d_atpstatus is null")->field('d_dictname,d_atpid')->select();
        }
        echo json_encode($sbtypelist);
    }
    public function getmodel(){
        $Model = M('dictionary');
        $factory= $_POST['factory'];
        $sbtypelist = $Model->where("d_parentid='%s' and d_atpstatus is null",$factory)->field('d_dictname,d_atpid')->select();
        echo json_encode($sbtypelist);
    }

    public function download()
    {
        $fileid=I('get.v');

        $filename=I('get.name');
        if(!file_exists( './Public/uploads/'.$fileid)) {
            echo "<script>alert('文件不存在');history.go(-1);</script>";
        }
        else
        {
            $contents=file_get_contents( './Public/uploads/'.$fileid);
            //告诉浏览器这是一个文件流格式的文件
            Header("Content-type:application/octet-stream");
            //请求范围的度量单位
            Header("Accept-Ranges:bytes");
            //Content-Length是指定包含于请求或响应中数据的字节长度
            Header("Accept-Length:".filesize( './Public/uploads/'.$fileid));
            //用来告诉浏览器，文件时可以当做附件被下载
            $filename= changeCoding($filename,'utf-8','gbk');
            Header("Content-Disposition:attachment;filename=".$filename);
            echo $contents;

        }
    }

    /*
     * 去掉多余的字段
     * 用于写日志
     * @oldmsgs 原数据(数据库查出来的)
     * @data 新数据(页面传过来的)
     * */
    public function dropDield($oldmsgs,$data){
        $diff_key = array_keys(array_diff_key($oldmsgs,$data));
        foreach ($diff_key as $key) {
            if(array_key_exists($key, $oldmsgs)) unset($oldmsgs[$key]);
        }
        return $oldmsgs;
    }

    //生成log日志文字,新数据有所改变则生成相应文字
    public function createLog($datamsg,$oldmsgs){
        $diff = array_diff($datamsg,$oldmsgs);
        $content = '';
        foreach($diff as $key=>$val){
            if(!empty($val) || !empty($datamsg[$key])){
                $oldmsgsKey = empty($oldmsgs[$key])?'空':$oldmsgs[$key];
                $dataKey = empty($datamsg[$key])?'空':$datamsg[$key];
                $content .= '将字段'.$key."：原值 ".$oldmsgsKey." 修改为 ".$dataKey.";\r\n";
            }
        }
        if(empty($content)){
            return '数据未作改变';
        }else{
            return $content;
        }
    }
}