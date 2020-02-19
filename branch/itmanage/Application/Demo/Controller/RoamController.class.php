<?php
namespace Demo\Controller;
use Think\Controller;
class RoamController extends BaseController
{
    const TABLENAME = '"ATP"."ROAMINGUSER"';
    public function index()
    {
        $this->getSelectType();
        $seclevel = D('Dictionary')->getSecretInfo();
        $area     = D('Dictionary')->getAreaInfo();
        $this->assign('ipsecretlevel',$seclevel);
        $this->assign('iparea',$area);
        $this->display();
    }

    public function getSelectType(){
        $Modle           = M();
        $results         = $Modle->query('SELECT result FROM '.self::TABLENAME.' where result is not null group by result');
        $systemnames     = $Modle->query('SELECT systemname FROM '.self::TABLENAME.' where systemname is not null group by systemname');
        $maindevicetypes = $Modle->query('SELECT maindevicetype FROM '.self::TABLENAME.' where maindevicetype is not null group by maindevicetype');
        $devicetypes     = $Modle->query('SELECT devicetype FROM '.self::TABLENAME.' where devicetype is not null group by devicetype');
        $this->assign('results',$results);
        $this->assign('systemnames',$systemnames);
        $this->assign('maindevicetypes',$maindevicetypes);
        $this->assign('devicetypes',$devicetypes);
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
        $ip_startnum = D('Ipaddressnew')->IPformat($data['ip_start']);
        $ip_endnum   = D('Ipaddressnew')->IPformat($data['ip_end']);
        if(!$ip_startnum || !$ip_endnum){
            echo $this->makeStandResult('1','IP地址错误');
            return false;
        }
        $data['ip_startnum']   = $ip_startnum;
        $data['ip_endnum']     = $ip_endnum;
        $data['ip_area']       = implode(',',$data['ip_area']);
        $data['ip_depart']     = implode(',',$data['ip_depart']);

//        $res = D('Ipaddressnew')->checkHasIPInfo(['ip_start'=>$ip_startnum,'ip_end'=>$ip_endnum]);
//        if(!$res){
//            echo $this->makeStandResult('2','IP地址段存在重复');
//            return false;
//        }

        $data['ip_atpcreateuser']     = session('username');
        $data['ip_atpcreatedatetime'] = date('Y-m-d H:i:s',time());

        $res = D('Ipaddressnew')->insertData($data);
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

    public function getdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Roam')->getRoamInfo($queryparam);
        $Result = $res[0];
        $Count = $res[1];
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function checkIfBig(){
        $queryparam = I('get.');
        $res = D('Roam')->getRoamInfo($queryparam,2);
        echo json_encode($res);
    }
    public function setRoamStatus(){
        $status = I('post.status');
        $ids    = I('post.ids');
        if(!isset($status) || empty($ids)){
            echo "<script>alert('参数缺失，请重试！');location.href='index';</script>";
            return false;
        }
        $model = M(self::TABLENAME);
        try{
            $model->startTrans();
            $ids = explode(',', $ids);
            foreach($ids as $key=>$id){
//                $data = $model->where("rowid = '".$id."'")->find();
                if($status == '漫游') {
                    // 漫游-->非漫游
                    $model->execute("update ".self::TABLENAME." set status = '已确认（漫游）',result = '漫游登录' where rowid = '".$id."'");
                }else if($status == '非漫游'){
                    // 非漫游-->漫游
                    $model->execute("update ".self::TABLENAME." set status = '已确认（非漫游）',result = '非漫游登录' where rowid = '".$id."'");
                }
            }
            $model->commit();
            echo json_encode(['error'=>0]);
        }
        catch(\Exception $e)
        {
            echo $e;
            echo json_encode(['error'=>0,'message'=>$e]);
            $model->rollback();
            return false;
        }
    }

    function changeStatus(){
        if(IS_GET){
            $rowid = I('get.id','');
            if(empty($rowid)){
                echo "<script>alert('参数缺失，请重试！');location.href='Roam/index';</script>";
                return false;
            }
            $status = M()->query("SELECT status,rowid FROM ".self::TABLENAME." WHERE rowid = '".$rowid."'");
            $this->assign("data",$status[0]);
            $this->display();
        }else{
            $rowid  = I('post.rowid','');
            $status = I('post.status','');
            if(empty($rowid) || empty($status)){
                echo json_encode(['error'=>1,'message'=>'参数缺失，请刷新重试']);
                return false;
            }
            if($status == '待核查'){
                $res = M()->execute("update ".self::TABLENAME." set status = '".$status."',result = '漫游登录' where rowid = '".$rowid."'");
            }else if($status == '已确认（漫游）'){
                $res = M()->execute("update ".self::TABLENAME." set status = '".$status."',result = '漫游登录' where rowid = '".$rowid."'");
            }else if($status == '已确认（非漫游）'){
                $res = M()->execute("update ".self::TABLENAME." set status = '".$status."',result = '非漫游登录' where rowid = '".$rowid."'");
            }
            if($res){
                echo json_encode(['error'=>0,'message'=>'修改成功！']);
                return true;
            }else{
                echo json_encode(['error'=>2,'message'=>'修改失败，请刷新页面重试！']);
                return false;
            }
        }
    }

    function zcbgDetail(){
        $ipaddress = I('get.ipaddress','');
        if(empty($ipaddress)){
            echo "<script>alert('参数缺失，请重试！');location.href='Roam/index';</script>";
            return false;
        }
        $this->assign("ipaddress",$ipaddress);
        $this->display();
    }

    function bdDetail(){
        $ipaddress = I('get.ipaddress','');
        if(empty($ipaddress)){
            echo "<script>alert('参数缺失，请重试！');location.href='Roam/index';</script>";
            return false;
        }
        $this->assign("ipaddress",$ipaddress);
        $this->display();
    }

    function getZcbgdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        if ($queryparam['offset'] == ''){
            $offset = 0;
        }else{
            $offset = $queryparam['offset'];
        }
        $limit = $queryparam['limit'];
        $where = [];
        $where[0]['l_modulename'] = ['eq','terminal'];
        $optime = date("Y-m-d H:i:s",strtotime("-90 day"));
        $where[0]['l_optime']     = ['egt',$optime];
        $where[0]['l_opuserid']   = ['neq','系统初始化'];
        $where[1][0]['l_detail']  = ['like',"%".trim($queryparam['ipaddress']).",%"];
        $where[1][1]['l_detail']  = ['like',"%".trim($queryparam['ipaddress']).";%"];
        $where[1][2]['l_detail']  = ['like',"%".trim($queryparam['ipaddress'])."，%"];
        $where[1][3]['l_detail']  = ['like',"%".trim($queryparam['ipaddress'])."。%"];
        $where[1][4]['l_detail']  = ['like',"%".trim($queryparam['ipaddress'])." %"];
        $where[1]['_logic']    = 'or';
        $order  = array($queryparam['sort']=>$queryparam['sortOrder']);
        $Result = M('Log')->where($where)->order($order)->limit($limit,$offset)->select();
        $Count  = M('Log')->field('count(*) c')->where($where)->select();
        foreach($Result as $key=>$val){
            $mainid = $val['l_bdid'];
            if(strpos($mainid,'WY/BM') !== false){
                $sql_select = "select ta.t_biaodanurl,ta.t_status,ta.t_nameid,ta.t_biaodanname from it_task ta where ta.t_rwid = '".$mainid."'";
                $taskinfo   = M()->query($sql_select);
                if($taskinfo){
                    $Result[$key] = array_merge($Result[$key],$taskinfo[0]);
                }
            }else{
                continue;
            }
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    function getBddata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $where = [];
        $optime = date("Y-m-d H:i:s",strtotime("-90 day"));
        $where[0]['t_arrivetime'] = ['egt',$optime];
        $where[0]['t_ip']         = ['eq',trim($queryparam['ipaddress'])];
        $order  = array($queryparam['sort']=>$queryparam['sortOrder']);
        $Result = M('v_task',' ')->field("t_atpid,t_arrivetime,t_ip,t_rwid,t_biaodanurl,t_status,t_nameid,t_biaodanname,t_problemtype")->where($where)->order($order)->select();
//        echo M('v_task',' ')->_sql();die;
        $Results = [];
        foreach($Result as $key=>$val){
            $formno = $val['t_rwid'];
            $node   = $val['t_problemtype'];
            $arrive = $val['t_arrivetime'];
            if(empty($Results[$formno])){
                $Results[$formno] = $val;
            }else{
                if($Results[$formno]['t_problemtype'] == '网络配置'){
                    continue;
                }else if($node == '网络配置'){
                    $Results[$formno] = $val;
                }else if($arrive>$Results[$formno]['t_arrivetime']){
                    $Results[$formno] = $val;
                }
            }
        }
        $Results = array_values($Results);
        echo json_encode(array( 'total' => count($Results),'rows' => $Results));
    }
}

