<?php
namespace Demo\Controller;
use Think\Controller;
class IPController extends BaseController
{

    public function index()
    {
        $seclevel = D('Dictionary')->getSecretInfo();
        $area     = D('Dictionary')->getAreaInfo();
        $this->assign('ipsecretlevel',$seclevel);
        $this->assign('iparea',$area);
        $this->display();
    }
    public function add(){
        $seclevel = D('Dictionary')->getSecretInfo();
        $area     = D('Dictionary')->getAreaInfo();
        $this->assign('ipsecretlevel',$seclevel);
        $this->assign('iparea',$area);
        $this->display();
    }

    public function Search(){
        $this->display();
    }

    public function IPcheck()
    {
        $data = I('post.');
        $ip_startnum = D('Ipaddress')->IPformat($data['ip_start']);
        $ip_endnum   = D('Ipaddress')->IPformat($data['ip_end']);
        if(!$ip_startnum || !$ip_endnum){
            echo $this->makeStandResult('1','IP地址错误');
            return false;
        }
        $data['ip_startnum']   = $ip_startnum;
        $data['ip_endnum']     = $ip_endnum;
        $data['ip_area']       = implode(',',$data['ip_area']);
        $data['ip_depart']     = implode(',',$data['ip_depart']);

        // 检查IP地址段是否存在重复
//        $res = D('Ipaddress')->checkHasIPInfo(['ip_start'=>$ip_startnum,'ip_end'=>$ip_endnum]);
//        if(!$res){
//            echo $this->makeStandResult('2','IP地址段存在重复');
//            return false;
//        }

        $data['ip_atpcreateuser']     = session('username');
        $data['ip_atpcreatedatetime'] = date('Y-m-d H:i:s',time());

        $res = D('Ipaddress')->insertData($data);
        if(!$res){
            echo $this->makeStandResult('3','插入数据失败，请稍后重试！');
            return false;
        }else{
            //记录日志
            $this->recordLog("add",'IP',"IP地址段：".$data['ip_start']."-".$data['ip_end']."；子网掩码：".$data['ip_mask']."；网关：".$data['ip_gateway']."；VLAN号：".$data['ip_vlan_no']."；密级：".$data['ip_secret_name']."；地区：".$data['ip_areaname']."；楼宇：".$data['ip_departname']."；用途：".$data['ip_purpose']."；",'ipaddress',$res);
            echo $this->makeStandResult('0','');
            return true;
        }
    }

    public function IPcheckEdit(){
        $data = I('post.');
        $data['ip_area']       = implode(',',$data['ip_area']);
        $data['ip_depart']     = implode(',',$data['ip_depart']);

        $data['ip_atplastmodifyuser']     = session('username');
        $data['ip_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());

        $res = D('Ipaddress')->editData($data);
        if(!$res){
            echo $this->makeStandResult('3','修改数据失败，请稍后重试！');
            return false;
        }else if($res == 'error'){
            echo $this->makeStandResult('0','');
            return true;
        }else{
            //记录日志
            $diff1 = $res[0];
            $diff2 = $res[1];
            $diff  = array_diff($diff1,$diff2);
            $content = '';
            foreach($diff as $key=>$val){
                $content .= $key."：".$val."-".$diff2[$key]."；";
            }
            $this->recordLog("update",'IP',$content,'ipaddress',$data['ip_atpid']);
            echo $this->makeStandResult('0','');
            return true;
        }
    }

    public function getdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Ipaddress')->getIPInfo($queryparam);
        $Result = $res[0];
        $Count = $res[1];
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function getrealname($username){
        $realusername =M('person')->where("username='%s'",$username)->getField('realusername');
        return $realusername;
    }

    public function getbuilding(){
        $area= I('post.area');
        $buildinglist = D('Dictionary')->getBuildingByPID($area);
        echo json_encode($buildinglist);
    }


    /**
     * 部门结构弹出页
     */
    function getDeptTree(){
        $this->display();
    }

    /**
     * 部门结构弹出页数据获取
     */
    function getdepttreedata(){
        $treename = I('get.treename','');
        if($treename == ''){
            $deptinfo = M('depart')->field('id,name,pid')->select();
        }else{
            $deptinfo = M('depart')->where("name like '%".$treename."%'")->field('id,name,pid')->select();
        }
        $depttree = json_encode($deptinfo);
        echo $depttree;
        return true;
    }

    function baseInfonuse(){
        $ipid      = I('get.ipid');
        $usestatus = I('get.status');
        $num       = D('Ipaddress')->getBaseNumByID($ipid);
        $usenum    = $num['use'];
        $notusenum = $num['notuse'];
        $sumnum    = $usenum+$notusenum;
        $this->assign('ipid',$ipid);
        $this->assign('usestatus',$usestatus);
        $this->assign('usestatus',$usestatus);
        $this->assign('usenum',$usenum);
        $this->assign('notusenum',$notusenum);
        $this->assign('sumnum',$sumnum);
        $this->display();
    }

    function getnuseBaseData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Ipaddress')->getIPBaseInfonuse($queryparam);
        $Result = $res[0];
        $Count  = $res[1];
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    function baseInfouse(){
        $ipid      = I('get.ipid');
        $usestatus = I('get.status');
        $num       = D('Ipaddress')->getBaseNumByID($ipid);
        $usenum    = $num['use'];
        $notusenum = $num['notuse'];
        $sumnum    = $usenum+$notusenum;
        $this->assign('ipid',$ipid);
        $this->assign('usestatus',$usestatus);
        $this->assign('usestatus',$usestatus);
        $this->assign('usenum',$usenum);
        $this->assign('notusenum',$notusenum);
        $this->assign('sumnum',$sumnum);
        $this->display();
    }

    /*
     * 未使用IP状态修改页面
     */
    function editIpInfo(){
        $ipbid = I('get.id');
        $baseInfo = D('Ipaddress')->getBaseInfoById($ipbid);
        $this->assign('baseInfo',$baseInfo);
        $this->display();
    }

    /*
     * 修改Base状态
     */
    function editBaseStatus(){
        $params   = I('post.');
        $status   = I('post.status');
        $ipbid    = I('post.ipbid');
        $res = D('Ipaddress')->changeBaseStatus($params);
        if($res == 'error'){
            echo $this->makeStandResult('0');
            return true;
        }else if($res){
            $ipbid = $res[0];
            $res   = $res[1];
            //记录日志
            foreach($ipbid as $key=>$val){
                if($status == ''){
                    $this->recordLog("update",'IP',"IP地址".$res."；状态修改：预分配-未使用",'ipbase',$val['ipb_atpid']);
                }else{
                    $this->recordLog("update",'IP',"IP地址".$res."；状态修改：未使用-预分配",'ipbase',$val['ipb_atpid']);
                }
            }
            echo $this->makeStandResult('0');
            return true;
        }else{
            echo $this->makeStandResult('1','执行失败请稍后重试!');
            return true;
        }
    }

    function getuseBaseData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Ipaddress')->getIPBaseInfouse($queryparam);
        $Result = $res[0];
        $Count  = $res[1];
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    function getSearchData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Ipaddress')->getSearchData($queryparam);
        $Result = $res[0];
        $Count  = $res[1];
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    function getSearchDatas(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Ipaddress')->getSearchDatas($queryparam);
        $Result = $res[0];
        $Count  = $res[1];
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    /*
     * 删除数据
     */
    function deleteData(){
        $ids = I('post.ids');
//        print_r($ids);die;
        if($ids == ''){
            echo $this->makeStandResult('1','执行失败请稍后重试!');
            return true;
        }
        $res = D('Ipaddress')->deleteAddressData($ids);
        if(!$res){
            echo $this->makeStandResult('2','执行失败请稍后重试!');
            return true;
        }else{
            //记录日志
            $this->recordLog("delete",'IP',"IP地址段：".$res['ip_start']."-".$res['ip_end']."；",'ipaddress',$ids);
            echo $this->makeStandResult('0','');
            return true;
        }
    }

    /*
     * 编辑数据
     */
    function editData(){
        $ipid = I('get.ids');
        if(empty($ipid)){
            echo "<script>alert('请选择一行记录！');location.href='index';</script>";
            return false;
        }
        $seclevel = D('Dictionary')->getSecretInfo();
        $area     = D('Dictionary')->getAreaInfo();
        $ipInfo   = D('Ipaddress')->getAddressInfoById($ipid);
        $this->assign('ipsecretlevel',$seclevel);
        $this->assign('iparea',$area);
        if(!empty($ipInfo['ip_area'])){
            $tmp = D('Dictionary')->getAreaInfoByIds($ipInfo['ip_area']);
            $ipInfo['ip_areainfo'] = json_encode($tmp);
        }
        if(!empty($ipInfo['ip_depart'])){
            $tmp = D('Depart')->getDeptInfoByIds($ipInfo['ip_depart']);
            $ipInfo['ip_deptinfo'] = json_encode($tmp);
        }
        $this->assign('ipInfo',$ipInfo);
        $this->display();
    }


    function select(){
        $seclevel = D('Dictionary')->getSecretInfo();
        $area     = D('Dictionary')->getAreaInfo();
        $this->assign('ipsecretlevel',$seclevel);
        $this->assign('iparea',$area);
        $this->display();
    }

    function addsel(){
        $data =  I('post.');
        $bumen=$data['bumen'];
        $iparea=$data['iparea'];
        $ipbuilding=$data['ipbuilding'];
        $password=$data['password'];
        $list=D('Ipaddress')->getSelect($bumen,$iparea,$ipbuilding,$password);
        $lists=D('Ipaddress')->updates($list);
        if($lists){
            echo $list;
        }else{
            echo 'error';
        }

    }

    function save(){
        $data = I('post.');
        $ip=$data['ip'];
        // $status=$data['status'];
        $sta=D('Ipaddress')->getStatus($ip);
        $status=$sta[0]['ipb_status'];
        if($status==1){
            $sql="update IT_IPBASE set ipb_status='2' where ipb_address='"."$ip"."'";
            $res=M('ipbase')->execute($sql);
            if($res){
                echo "success";
            }
        }else{
            $sql="update IT_IPBASE set ipb_status='' where ipb_address='"."$ip"."'";
            $res=M('ipbase')->execute($sql);
            if($res){
                echo "successed";
            }
        }
    }
}

