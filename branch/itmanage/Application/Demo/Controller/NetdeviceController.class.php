<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class NetdeviceController extends BaseController {


    public function addrelation(){
        $id = I('get.id');
        $this->assign('relationid',$id);
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assignrelation();
        $this->display();
    }

    public function getRelationdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $Model = M();
        $sql_select="select * from it_relation where rl_cmainid ='".$id."' and rl_atpstatus is null";
        $Result = $Model->query($sql_select);

        foreach($Result as $key=> $value){
            $rl                       = $value['rl_relation'];
            $cmainid                  = $value['rl_cmainid'];
            $rmainid                  = $value['rl_rmainid'];
            $type                     = $value['rl_rtype'];
            $Result[$key]['ip']       = $value['rl_rname'];
            //关联关系
            $Result[$key]['relation'] = M("dictionary")->where("d_atpid='%s'and d_belongtype='关联关系'",$rl)->getField("d_dictname");
            //关联资产类型
            $Result[$key]['type']     = M("dictionary")->where("d_atpid='%s'and d_belongtype='equipmenttype'",$type)->getField("d_dictname");
            //当前资产名称
            $Result[$key]['cipaddress'] = M("netdevice")->where("netdevice_atpid='%s'",$cmainid)->getField("netdevice_ipaddress");
            //关联资产名称
            $ripaddress                 = M("netdevice")->where("netdevice_atpid='%s'",$rmainid)->getField("netdevice_ipaddress");
            $Result[$key]['ripaddress'] = $ripaddress;
            //关联资产IP
//            $Result[$key]['toripaddress'] = "<a onclick = toRelation('".$rmainid."')>".$ripaddress."</a>";
            $Result[$key]['toripaddress'] = $ripaddress;
        }
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function getterminaldata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype'
                 ";
        $sql_count="
                select
                    count(1) c
                from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype'";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.zd_atpstatus is null");


        if ("" != $queryparam['rlsbbm']){
            $searchcontent = trim($queryparam['rlsbbm']);
            $sql_select = $this->buildSql($sql_select,"du.zd_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_devicecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['rlipaddess']){
            $searchcontent = trim($queryparam['rlipaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_ipaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_ipaddress like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['rlmacaddess']){
            $searchcontent = trim($queryparam['rlmacaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_macaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_macaddress like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['rlsbtype']){
            $searchcontent = trim($queryparam['rlsbtype']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_type ='".$searchcontent."'");
        }
        if ('zd_atpsort' != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpsort asc,du.zd_macaddress asc nulls last ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));

    }
    public function getBinddata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $macupper = M('terminal')->where("zd_atpid='%s'",$id)->getField('zd_macaddress');
        $mac = strtolower($macupper);
        $Result = M('switchnewinfoT')->where("sw_macaddress='%s'",$mac)->select();
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function getBindolddata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $macupper = M('terminal')->where("zd_atpid='%s'",$id)->getField('zd_macaddress');
        $mac = strtolower($macupper);
        $Result = M('switcholdinfoT')->where("sw_macaddress='%s'",$mac)->order('sw_atplastmodifydatetime asc nulls last')->select();
        $newC   = M('switchnewinfoT')->where("sw_macaddress='%s'",$mac)->count();
        $Result = array_slice($Result,$newC);
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function getLogdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $Model = M();
        $sql_select="
                select l.*,n.*,d.d_dictname d_dictname from it_log l,it_netdevice n,it_dictionary d where l.l_mainid = n.netdevice_atpid and n.netdevice_type=d.d_atpid and  d.d_belongtype='equipmenttype' and l.l_mainid ='".$id."'";
//        echo $sql_select;die;
        $Result = $Model->query($sql_select);
        foreach($Result as $key=>$value) {
            $optype = $value['l_optype'];
            switch ($optype) {
                case 'add':
                    $Result[$key]['optypename'] = '新增';
                    break;
                case 'del':
                    $Result[$key]['optypename'] = '删除';
                    break;
                case 'delete':
                    $Result[$key]['optypename'] = '删除';
                    break;
                case 'update':
                    $Result[$key]['optypename'] = '修改';
                    break;
                case 'print':
                    $Result[$key]['optypename'] = '打印';
                    break;
                default:
                    $Result[$key]['optypename'] = '';
                    break;
            }
        }
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function detailForm(){
        $id = I('get.id');
        if ($id) {
            $Model = M("terminal");
            $data = $Model->where("zd_atpid='%s'", array($id))->find();
            //责任部门根据zd_dutydeptid取值
            if(!empty($data['zd_dutydeptid'])){
                $deptinfo = D('Depart')->getDeptInfoByIds($data['zd_dutydeptid']);
                if($deptinfo) $data['zd_dutydeptname'] = $deptinfo[0]['fullname'];
            }
            if ($data) {
                $this->assign('data', $data);
            }
            $depid = $data['zd_dutydeptid'];
            $userid = $data['zd_useman'];
            $dutyuserid = $data['zd_dutyman'];
            if($depid){
                $data2 = M('depart')->where("id='%s'", $depid)->select();
                $this->assign('dutydeptname',$data2);
            }
            $data3 = M('person')->where("username='%s'", $userid)->select();
            $this->assign('username',$data3);
            $data4 = M('person')->where("username='%s'", $dutyuserid )->select();
            $this->assign('dutymanname',$data4);

        }
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }
    public function getdutydept(){
        $Model = M('person');
        $dutyman= $_POST['dutyman'];
        $orgid = $Model->where("username='%s'",$dutyman)->getField('orgid');
        $sbtypelist = M('depart')->where("id='%s'",$orgid)->field('fullname,id')->select();
        echo json_encode($sbtypelist);
    }


    public function NDsubmit(){
        $Model = M('netdevice');
        $data = $Model->create();
        try{
            if(null==$data['netdevice_atpid']){
                $data['netdevice_atpid'] = $this->makeGuid();
                $data['netdevice_type']  = '154118';
                $data['netdevice_atpcreatedatetime'] = date('Y-m-d H:i:s', time());;
                $data['netdevice_atpcreateuser'] = I('session.username', '');
                $content                  = '';
                foreach($data as $key=>$val){
                    if(!empty($val)) $content .= $key.":".$val.";";
                }
                $Model->add($data);
                if($data['netdevice_ipaddress']){
                    $ipbid  = $data['netdevice_ipaddress'];
                    $status = '2';
                    $res    = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
                    if(!$res){
                        $this->ajaxReturn("IP地址状态修改失败！");
                        return true;
                    }
                }
                $this->recordLog('add', 'netdevice',$content,'netdevice',$data['netdevice_atpid']);
                $this->ajaxReturn("success");
            }else{
                $oldmsgs = $Model->where("netdevice_atpid='%s'",array($data['netdevice_atpid']))->find();
                $data['netdevice_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                $data['netdevice_atplastmodifyuser'] = I('session.username', '');
                $Model->where("netdevice_atpid='%s'",array($data['netdevice_atpid']))->save($data);
                $diff  = array_diff($oldmsgs,$data);
                $content = '';
                foreach($diff as $key=>$val){
                    if(($val !='') && ($data[$key] != '') && ($key != 'netdevice_atplastmodifydatetime') && ($key != 'netdevice_atplastmodifyuser')) $content .= $key."：".$val."-".$data[$key]."；";
                }
                //修改ip地址使用状态
                if(!empty($diff['netdevice_ipaddress'])){
                    $ipbid  = $diff['netdevice_ipaddress'];
                    $status = null;
                    $res    = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
                    if(!$res){
                        $this->ajaxReturn("error");
                        return true;
                    }else if($res) {
                        if($data['netdevice_ipaddress']){
                            $ipbid  = $data['netdevice_ipaddress'];
                            $status = '2';
                            $res    = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
                            if(!$res){
                                $this->ajaxReturn("IP地址状态修改失败！");
                                return true;
                            }
                        }
                    }
                }
                $this->recordLog('update', 'netdevice',$content,'netdevice',$data['netdevice_atpid']);
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
            echo $e;
        }
    }

    public function submitrealtion(){
        $mainid = I('post.mainid');
        $relationid = I('post.relationid');
        $rlrelation = I('post.rlrelation');
        $rlatpid = $this->makeGuid();
        $mainipaddress = M('netdevice')->where("netdevice_atpid='%s'",$mainid )->getField('netdevice_ipaddress');
        $data = M('terminal')->where("zd_atpid='%s'",$relationid )->find();
        $rip =$data['zd_ipaddress'];
        $rtype =$data['zd_type'];
        $date =date('Y-m-d H:i:s', time());
        $userman = I('session.username','');;
        $sql ="insert into it_relation(rl_atpid,rl_cmainid,rl_cname,rl_rmainid,rl_relation,rl_rname,rl_rtype,rl_rltime,rl_rluser) values('".
            $rlatpid."','".$mainid."','".$mainipaddress."','".$relationid."','".$rlrelation."','". $rip."','".
            $rtype."','".$date."','".$userman."')";
        $this->recordLog('add', 'relation','关联资产;rl_cmainid:'.$mainipaddress.';rl_rmainid:'.$rip.';','relation',$rlatpid);
        try{
            M()->execute($sql);
        }
        catch (\Exception $e){
            echo $e;
        }
    }

    public function assignrelation(){
        $Model = M('dictionary');
        $data =$Model->where("d_belongtype='%s'","关联关系")->field('d_dictname,d_atpid')->select();
        $this->assign('ds_relation',$data);

    }

    public function getbuilding(){
    $Model = M('dictionary');
    $area= $_POST['area'];
    $buildinglist = $Model->where("d_parentid='%s'",$area)->field('d_dictname,d_atpid')->select();
    echo json_encode($buildinglist);
}

    public function NetDeviceindex(){
        $Datas  = D('Dictionary')->assignsbtype();
        $usages = M('netdevice')->field('netdevice_usage')->group('netdevice_usage')->select();
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assign('nettype',$Datas[5]);
        $this->assign('usages',$usages);
        $this->display();
    }

    public function NetDeviceadd(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assign('nettype',$Datas[5]);
        $this->display();
    }

    public function getNDData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('netdevice')->getNDData($queryparam);
        $Result = $res[0];
        $Count = $res[1];
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function NDedit(){
        $id = I('get.id');
        if ($id) {
            $Model = M("netdevice");
            $data = $Model->where("netdevice_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }
        }
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assign('nettype',$Datas[5]);
        $this->display('NetDeviceedit');
    }

    public function NDdel()
    {
        try {
            $ids = I('post.ids');
            $array = explode(',', $ids);
            if ($array && count($array) > 0) {
                $Model = M("netdevice");
                foreach ($array as $id) {
                    $data    = $Model->where("netdevice_atpid='%s'", $id)->find();
                    $data['netdevice_atpstatus'] = 'DEL';
                    $data['netdevice_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
                    $data['netdevice_atplastmodifyuser'] = I('session.username','');
                    $Model->where("netdevice_atpid='%s'", $id)->save($data);
                    //修改ip地址使用状态
                    if(!empty($data['netdevice_ipaddress'])){
                        $ipbid  = $data['zd_ipaddress'];
                        $status = null;
                        D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
                    }
                    $this->recordLog('delete', 'netdevice','删除交换机台账信息;ip地址:'.$data['netdevice_ipaddress'].';mac地址:'.$data['netdevice_mask'].';','netdevice',$id);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }

    public function setScanStatus(){
        $isscan = I('post.isscan');
        $ids    = I('post.ids');
        if(!isset($isscan) || empty($ids)){
            echo "<script>alert('参数缺失，请重试！');location.href='NetDeviceindex';</script>";
            return false;
        }
        try{
            M('netdevice')->startTrans();
            $ids = explode(',', $ids);
            foreach($ids as $key=>$id){
                $data = M('netdevice')->where("netdevice_atpid = '".$id."'")->find();
                // if(($isscan == '0') && ($data['netdevice_isscan'] == '1')){
                if($isscan == '0') {
                    // 扫描-->不扫描
                    M('netdevice')->where("netdevice_atpid = '".$id."'")->setField(['netdevice_isscan'=>$isscan]); 
                    $this->recordLog('update', 'netdevice','批量修改扫描状态：扫描-->不扫描;ip地址:'.$data['netdevice_ipaddress'].';mac地址:'.$data['netdevice_mask'].';','netdevice',$id);
                // }else if(($isscan == '1') && ($data['netdevice_isscan'] == '0')){
                }else if($isscan == '1'){
                    // 不扫描-->扫描
                    M('netdevice')->where("netdevice_atpid = '".$id."'")->setField(['netdevice_isscan'=>$isscan]); 
                    $this->recordLog('update', 'netdevice','批量修改扫描状态：不扫描-->扫描;ip地址:'.$data['netdevice_ipaddress'].';mac地址:'.$data['netdevice_mask'].';','netdevice',$id);
                }
            }
            M('netdevice')->commit();
            echo json_encode(['error'=>0]);
        }
        catch(\Exception $e)
        {
            echo json_encode(['error'=>0,'message'=>$e]);
            M('netdevice')->rollback();
            return false;
        }
    }
}