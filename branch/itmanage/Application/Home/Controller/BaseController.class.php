<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller{

    public function __construct(){
        parent::__construct();
        $userId = session('user_id');

        $loginModel = D('Admin/Login');
        if (empty($userId)) $loginModel->loginOut();

        $loginModel->checkLoginIfExpire(false); //检测登录是否过期
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

    function containString($input,$splite)
    {
        $tmparray = explode($splite, $input);
        if (count($tmparray) > 1) {
            return true;
        } else {
            return false;
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


    public function getfactoryForSEC(){
        $Model   = M('it_dictionary');
        $sbtype  = I('post.sbtype');
        $options = I('post.options');
        if(!$options){
            $sbtypelist = $Model->where("d_parentid='%s' and d_atpstatus is null",$sbtype)->field('d_dictname,d_atpid')->select();
        }else{
            $sbtypelist = $Model->where("d_parentid='guidF5C36989-8993-4112-9E69-20DB4D4220BB'  and d_atpstatus is null")->field('d_dictname,d_atpid')->select();
        }
        echo json_encode($sbtypelist);
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

    /*
     * home模块下需要用到的方法
     *
     * */

    //验证地址
    public function checkAddress($str='',$type = 'IP'){
        switch($type){
            case 'IP':
                return filter_var($str,FILTER_VALIDATE_IP)?$str:false;
                break;
            case 'MAC':
                return filter_var($str,FILTER_VALIDATE_MAC)?$str:false;
                break;
            default:
                return false;
                break;
        }
    }

    public function assignusered(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.user_id id,p.user_realusername||'('||p.user_name||','||o.org_name||')' text from  sysuser p inner join org o on o.org_id = p.user_orgid where
                    (p.user_name like '%".$q."%' or p.user_realusername like '%".$q."%') and p.user_issystem = '否' and user_enable = '启用' and user_isdelete = 0";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    public function assignJigui(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.jg_atpid id,p.jg_name text from  jigui p  where
                    p.jg_name like '%".$q."%'  and jg_atpstatus is null";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    //根据字典id获取字典
    public function getDicById($id,$field = ''){
        if(empty($id)) return false;
        $model = M('dic');
        $data = $model
            ->where("dic_status = '0' and dic_id = '%s' ", $id )
            ->find();
        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }

    public function getDicFactort($id,$field = ''){
        if(empty($id)) return false;
        $model = M('dic_factory');
        $data = $model
            ->where("dic_status = '0' and dic_id = '%s' ", $id )
            ->find();
        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }

      //根据厂家字典id获取字典
    public function getFactoryById($id,$field = ''){
        if(empty($id)) return false;
        $model = M('dic_factory');
        $data = $model
            ->where("dic_status = '0' and dic_id = '%s' ", $id )
            ->find();
        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }

    //查字典pid
    public function getDicByPid($pid){
        $data = M('dic')
            ->field('dic_id,dic_name,dic_value,dic_type')
            ->where("dic_status=0 and dic_pid='%s'", $pid)
            ->order('dic_order')
            ->select();
        return $data;
    }
    //查字典name
    public function getDicByName($name,$pid =''){
        if(!empty($pid)) $where['dic_pid'] = ['eq',$pid];
        $where['dic_name'] = ['like',$name];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic')
            ->field('dic_id,dic_name,dic_value,dic_pid,dic_type')
            ->where($where)
            ->order('dic_order')
            ->select();
        return $data;
    }
    //查字典type
    public function getDicByType($type){
        $data = M('dic')
            ->field('dic_id,dic_name,dic_value,dic_pid,dic_type')
            ->where("dic_status=0 and dic_type='%s'", $type)
            ->order('dic_order')
            ->select();
        return $data;
    }
    //查楼宇字典
    public function getDicLouYuById($id,$field = ''){
        if(empty($id)) return false;
        $where['dic_id'] = ['eq',$id];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic_louyu')
            ->where($where)
            ->order('dic_order')
            ->find();

        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }
    //查型号字典pid
    public function getDicLouYuByPid($pid=''){
        if(!empty($pid)) $where['dic_pid'] = ['eq',$pid];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic_louyu')
            ->where($where)
            ->order('dic_order')
            ->select();
        return $data;
    }
    //查型号字典
    public function getDicXingHaoById($id,$field = ''){
        if(empty($id)) return false;
        $where['dic_id'] = ['eq',$id];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic_xinghao')
            ->where($where)
            ->order('dic_order')
            ->find();

        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }
    //查型号字典pid
    public function getDicXingHaoByPid($pid='',$pid2=''){
        if(!empty($pid)) $where['dic_type'] = ['eq',$pid];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic_xinghao')
            ->where($where)
            ->order('dic_order')
            ->select();
        return $data;
    }

    /**
     * 判断是否催在关联数据
     */
    public function isGldel(){
        $ids = trim(I('post.zy_id'));
        $ids = explode(',',$ids);
        $where['rlx_zyid']= ['in',$ids];
        $where['rlx_atpstatus'] = ['exp','is null'];
        $model = M('it_relationx');
        $res = $model->where($where)->select();
        if(!empty($res)){
            exit(makeStandResult(1, '存在关联'));
        }else{
            exit(makeStandResult(-1, '不存在关联'));
        }

    }
    
    //去掉
    public function removeStr($str){
        if(mb_strstr($str,'综合管理层') !== false){
            //去掉 -五院本级-中国航天科技集团公司第五研究院
            $name = mb_substr($str,0,-27);
        } else if(mb_strstr($str,'五院本级') !== false){
            //去掉 -五院本级-中国航天科技集团公司第五研究院
            $name = mb_substr($str,0,-21);
        }else{
            //去掉 -中国航天科技集团公司第五研究院
            if(mb_strstr($str,'中国航天科技集团公司第五研究院') !== false){
                $name = mb_substr($str,0,-16);
            }else{
                return $str;
            }
        }
        return implode('-',array_reverse(explode('-',$name)));
    }


    //改变交换机关联关系
    public function changeRelationNet($sevId, $sevName, $ip, $type, $table, $ids)
    {
        if(!empty($ids)){
            //改动前数据
            $oldArr = D("relation")->getViewRelationInfo($sevId, 'it_netdevice');
            // $oldArr = D("relation")->getRelationSevByNet($sevId);
            $oldIds = array_column($oldArr, 'r_id');
            //var_dump(array_diff($oldIds,$ids));die;
            $model = M('it_relation');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $time = date('Y-m-d H:i:s', time());
            $user = session('user_id');
    
            //添加
            $netModel = M('it_netdevice');
            foreach (array_diff($ids, $oldIds) as $id) {
                if (!empty($id)) {
                    //查两侧id是否都存在
                    $src = D('relation')->checkRelationAllId($id, $sevId);
                    //如果为false 则跳过当前循环
                    if (!$src) continue;
    
                    $netInfo = $netModel->field('net_ipaddress,net_type')
                        ->where("net_atpid='%s'", $id)
                        ->find();
    
                    $data['rl_atpid'] = makeGuid();
                    $data['rl_atpcreatetime'] = $time;
                    $data['rl_atpcreateuser'] = $user;
                    $data['rl_cmainid'] = $sevId; //设备atpid(主）
                    $data['rl_cname'] = $sevName; //设备名称（主）
                    $data['rl_cip'] = $ip; //设备ip（主）
                    $data['rl_ctype'] = $type; //关联设备类型（主）
                    $data['rl_ctable'] = $table; //C的table名
                    // $data['rl_relation'] = $relation;//关联关系ID
                    $data['rl_rmainid'] = $id; //设备atpid（从）
                    $data['rl_rname'] = ''; //设备名称(从)
                    $data['rl_rip'] = $netInfo['net_ipaddress']; //设备ip(从)
                    $data['rl_rtype'] = $netInfo['net_type']; //关联设备类型（从）
                    $data['rl_rtable'] = 'it_netdevice'; //R的table名
                    $data = $model->create($data);
                    $model->add($data);
                }
            }
            //删除
            foreach (array_diff($oldIds, $ids) as $k => $id) {
                if (!empty($id)) {
                    $model = M('it_relation');
                    $model
                        ->where("rl_atpid='%s'", $oldArr[$k]['rl_atpid'])
                        ->delete();
                }
            }
        }
    }
    
    //改变机柜关联关系
    public function changeRelationJigui($sevId, $sevName, $ip, $type, $table, $ids)
    {
        if(!empty($ids)){
            //改动前数据
            $oldArr = D("relation")->getViewRelationInfo($sevId, 'jigui');
            $oldIds = array_column($oldArr, 'r_id');
            $model = M('it_relation');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $time = date('Y-m-d H:i:s', time());
            $user = session('user_id');
    
            //添加
            $jgModel = M('jigui');
            foreach (array_diff($ids, $oldIds) as $id) {
                if (!empty($id)) {
                    //查两侧id是否都存在
                    $src = D('relation')->checkRelationAllId($id, $sevId);
                    //如果为false 则跳过当前循环
                    if (!$src) continue;
    
                    $jgInfo = $jgModel->field('jg_name')
                        ->where("jg_atpid='%s'", $id)
                        ->find();
    
                    $data['rl_atpid'] = makeGuid();
                    $data['rl_atpcreatetime'] = $time;
                    $data['rl_atpcreateuser'] = $user;
                    $data['rl_cmainid'] = $sevId; //设备atpid(主）
                    $data['rl_cname'] = $sevName; //设备名称（主）
                    $data['rl_cip'] = $ip; //设备ip（主）
                    $data['rl_ctype'] = $type; //关联设备类型（主）
                    $data['rl_ctable'] = $table; //C的table名
                    // $data['rl_relation'] = $relation;//关联关系ID
                    $data['rl_rmainid'] = $id; //设备atpid（从）
                    $data['rl_rname'] = $jgInfo['jg_name']; //设备名称(从)
                    $data['rl_rip'] = ''; //设备ip(从)
                    $data['rl_rtype'] = '机柜'; //关联设备类型（从）
                    $data['rl_rtable'] = 'jigui'; //R的table名
                    $data = $model->create($data);
                    $model->add($data);
                }
            }
            //删除
            foreach (array_diff($oldIds, $ids) as $k => $id) {
                if (!empty($id)) {
                    $model = M('it_relation');
                    $model
                        ->where("rl_atpid='%s'", $oldArr[$k]['rl_atpid'])
                        ->delete();
                }
            }
        }
    }
    
    //改变应用系统关联关系
    public function changeRelationApp($sevId, $sevName, $ip, $type, $table, $ids)
    {
        if(!empty($ids)){
            //改动前数据
            $oldArr = D("relation")->getViewRelationInfo($sevId, 'it_application');
            // $oldArr = D("relation")->getRelationSevByNet($sevId);
            $oldIds = array_column($oldArr, 'r_id');
            //var_dump(array_diff($oldIds,$ids));die;
            $model = M('it_relation');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $time = date('Y-m-d H:i:s', time());
            $user = session('user_id');
    
            //添加
            $appModel = M('it_application');
            foreach (array_diff($ids, $oldIds) as $id) {
                if (!empty($id)) {
                    //查两侧id是否都存在
                    $src = D('relation')->checkRelationAllId($id, $sevId);
                    //如果为false 则跳过当前循环
                    if (!$src) continue;
    
                    $appInfo = $appModel->field('app_name')
                        ->where("app_atpid='%s'", $id)
                        ->find();
    
                    $data['rl_atpid'] = makeGuid();
                    $data['rl_atpcreatetime'] = $time;
                    $data['rl_atpcreateuser'] = $user;
                    $data['rl_cmainid'] = $sevId; //设备atpid(主）
                    $data['rl_cname'] = $sevName; //设备名称（主）
                    $data['rl_cip'] = $ip; //设备ip（主）
                    $data['rl_ctype'] = $type; //关联设备类型（主）
                    $data['rl_ctable'] = $table; //C的table名
                    // $data['rl_relation'] = $relation;//关联关系ID
                    $data['rl_rmainid'] = $id; //设备atpid（从）
                    $data['rl_rname'] = $appInfo['app_name']; //设备名称(从)
                    $data['rl_rip'] = ''; //设备ip(从)
                    $data['rl_rtype'] = ''; //关联设备类型（从）
                    $data['rl_rtable'] = 'it_application'; //R的table名
                    $data = $model->create($data);
                    $model->add($data);
                }
            }
            //删除
            foreach (array_diff($oldIds, $ids) as $k => $id) {
                if (!empty($id)) {
                    $model = M('it_relation');
                    $model
                        ->where("rl_atpid='%s'", $oldArr[$k]['rl_atpid'])
                        ->delete();
                }
            }
        }
    }

    //改变应用系统关联关系
    public function changeRelationFire($sevId, $sevName, $ip, $type, $table, $ids)
    {
        if(!empty($ids)){
            //改动前数据
            $oldArr = D("relation")->getViewRelationInfo($sevId, 'firewall');
            // $oldArr = D("relation")->getRelationSevByNet($sevId);
            $oldIds = array_column($oldArr, 'r_id');
            //var_dump(array_diff($oldIds,$ids));die;
            $model = M('it_relation');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $time = date('Y-m-d H:i:s', time());
            $user = session('user_id');

            //添加
            $fireModel = M('firewall');
            foreach (array_diff($ids, $oldIds) as $id) {
                if (!empty($id)) {
                    //查两侧id是否都存在
                    $src = D('relation')->checkRelationAllId($id, $sevId);
                    //如果为false 则跳过当前循环
                    if (!$src) continue;

                    $appInfo = $fireModel->field('fw_name')
                        ->where("fw_atpid='%s'", $id)
                        ->find();

                    $data['rl_atpid'] = makeGuid();
                    $data['rl_atpcreatetime'] = $time;
                    $data['rl_atpcreateuser'] = $user;
                    $data['rl_cmainid'] = $sevId; //设备atpid(主）
                    $data['rl_cname'] = $sevName; //设备名称（主）
                    $data['rl_cip'] = $ip; //设备ip（主）
                    $data['rl_ctype'] = $type; //关联设备类型（主）
                    $data['rl_ctable'] = $table; //C的table名
                    // $data['rl_relation'] = $relation;//关联关系ID
                    $data['rl_rmainid'] = $id; //设备atpid（从）
                    $data['rl_rname'] = $appInfo['fw_name']; //设备名称(从)
                    $data['rl_rip'] = ''; //设备ip(从)
                    $data['rl_rtype'] = ''; //关联设备类型（从）
                    $data['rl_rtable'] = 'firewall'; //R的table名
                    $data = $model->create($data);
                    $model->add($data);
                }
            }
            //删除
            foreach (array_diff($oldIds, $ids) as $k => $id) {
                if (!empty($id)) {
                    $model = M('it_relation');
                    $model
                        ->where("rl_atpid='%s'", $oldArr[$k]['rl_atpid'])
                        ->delete();
                }
            }
        }
    }
    
    //改变服务器关联关系
    public function changeRelationSev($sevId, $sevName, $ip, $type, $table, $ids)
    {
        if(!empty($ids)){
            //改动前数据
            $oldArr = D("relation")->getViewRelationInfo($sevId, 'it_sev');
            // $oldArr = D("relation")->getRelationSevByNet($sevId);
            $oldIds = array_column($oldArr, 'r_id');
            //var_dump(array_diff($oldIds,$ids));die;
            $model = M('it_relation');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $time = date('Y-m-d H:i:s', time());
            $user = session('user_id');
    
            //添加
            $sevModel = M('it_sev');
            foreach (array_diff($ids, $oldIds) as $id) {
                if (!empty($id)) {
                    //查两侧id是否都存在
                    $src = D('relation')->checkRelationAllId($id, $sevId);
                    //如果为false 则跳过当前循环
                    if (!$src) continue;
    
                    $sevInfo = $sevModel->field('sev_name,sev_ip,sev_type')
                        ->where("sev_atpid='%s'", $id)
                        ->find();
    
                    $data['rl_atpid'] = makeGuid();
                    $data['rl_atpcreatetime'] = $time;
                    $data['rl_atpcreateuser'] = $user;
                    $data['rl_cmainid'] = $sevId; //设备atpid(主）
                    $data['rl_cname'] = $sevName; //设备名称（主）
                    $data['rl_cip'] = $ip; //设备ip（主）
                    $data['rl_ctype'] = $type; //关联设备类型（主）
                    $data['rl_ctable'] = $table; //C的table名
                    // $data['rl_relation'] = $relation;//关联关系ID
                    $data['rl_rmainid'] = $id; //设备atpid（从）
                    $data['rl_rname'] = $sevInfo['sev_name']; //设备名称(从)
                    $data['rl_rip'] = $sevInfo['sev_ip']; //设备ip(从)
                    $data['rl_rtype'] = $sevInfo['sev_type']; //关联设备类型（从）
                    $data['rl_rtable'] = 'it_sev'; //R的table名
                    $data = $model->create($data);
                    $model->add($data);
                }
            }
            //删除
            foreach (array_diff($oldIds, $ids) as $k => $id) {
                if (!empty($id)) {
                    $model = M('it_relation');
                    $model
                        ->where("rl_atpid='%s'", $oldArr[$k]['rl_atpid'])
                        ->delete();
                }
            }
        }
    }

    public function getbuilding(){
        $area  = I('post.area');
        $data = D('dic')->getDicLouYu($area);
        echo json_encode($data);
    }

    public function getmodel(){
        $factory  = I('post.factory');
        $data = D('dic')->getDicXingHao($factory);
        echo json_encode($data);
    }

    public function getfactory(){
        $sbtype  = I('post.sbtype');
        $sbtypelist=D('dic')->getFactoryList($sbtype);
        echo json_encode($sbtypelist);
    }

    
}