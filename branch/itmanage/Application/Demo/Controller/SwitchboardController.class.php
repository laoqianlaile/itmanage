<?php
namespace Demo\Controller;
use Think\Controller;
class SwitchboardController extends BaseController {

    public function getSWInfoByIP()
    {
        $switchip    = I('post.switchip');
        if(!$switchip) return null;
        $data        = D('SwitchnewinfoT')->getSWInfoByIP($switchip);
        $factoryInfo = D('Netdevice')->getFactory($switchip);
        $buttonInfo  = [];
        if($factoryInfo){
            $buttonInfo = D('swbat')->field('swbat_mainid,swbat_name')->where("swbat_atpstatus is null and swbat_type ='".$factoryInfo."'")->group('swbat_mainid,swbat_name')->select();
        }
        $count = count($data);
        $data[$count] = $buttonInfo;
        echo json_encode($data);
        return true;
    }

    public function configureRecordLog()
    {
        $contents  = I('post.contents');
        $atpid     = I('post.atpid');
        $parameter = I('post.parameter');
        if(!$contents || !$atpid || !$parameter) return null;
        $webserviceurl = C('SwitchConfigureWs');
        $client  = $this->getWebServiceObj($webserviceurl);
        $res     = $client->RunScript($parameter);
        $result  = $res->RunScriptResult;
        $result  = base64_decode($result);
        if(!$result){
            $this->recordLog('config', 'netdevice', $contents,'',$atpid);
        }
        echo json_encode($result);
        return true;
    }

    public function getNetDeviceByArea()
    {
        $area = I('post.area');
//        if(!$area) return null;
        $data = D('Netdevice')->getNetDeviceByArea($area);
        echo json_encode($data);
        return true;
    }

    public function getNetDeviceByBuilding()
    {
        $building = I('post.building');
        if(!$building) return null;
        $data = D('Netdevice')->getNetDeviceByBuilding($building);
        echo json_encode($data);
        return true;
    }

    public function Configure()
    {
        $areaInfo  = D('Dictionary')->getAreaInfo();
        $modelName = D('Dictionary')->getSWModelname();
        $ipaddress = D('Netdevice')->getNetDeviceByArea();
        $this->assign('areaInfo',$areaInfo);
        $this->assign('modelName',$modelName);
        $this->assign('ipaddress',$ipaddress);
        $this->display();
    }

    public function modelEdit()
    {
        $modelInfo = D('Dictionary')->getSWModelname();
        $this->assign('modelInfo',$modelInfo);
        $this->display();
    }

    public function Setmodelinfo()
    {
        $modelData = I('post.');
        $modelinfo = $modelData['modelinfo'];
        if(empty($modelData['swbat_mainid']) || empty($modelData['swbat_type']) || empty($modelData['swbat_name'])){
            echo "<script>alert('参数缺失，请重试！');location.href='modelEdit';</script>";
            return false;
        }
        try{
            M('swbat')->startTrans();
            $Model = M("swbat");
            $swbat_mainid = trim($modelData['swbat_mainid']);
            $swbat_type   = trim($modelData['swbat_type']);
            $swbat_name   = trim($modelData['swbat_name']);
            if (!empty($modelinfo)){
                $oldatpid = [];
                foreach($modelinfo as $key=>$item){
                    if($item['swbat_atpid']) $oldatpid[] = $item['swbat_atpid'];
                }
                //获取修改前原数据
                if(!empty($oldatpid)){
                    //修改的指令
                    $oldatpids = "'".implode("','",$oldatpid)."'";
                    $oldModel = $Model->field('swbat_atpid,swbat_sortno,swbat_detail,swbat_success,swbat_fail,swbat_sign_more')->where("swbat_atpid in (".$oldatpids.")")->select();
                    $oldModels = [];
                    foreach($oldModel as $k=>$v){
                        $oldModels[$v['swbat_atpid']]['swbat_sortno']    = $v['swbat_sortno'];
                        $oldModels[$v['swbat_atpid']]['swbat_detail']    = $v['swbat_detail'];
                        $oldModels[$v['swbat_atpid']]['swbat_success']   = $v['swbat_success'];
                        $oldModels[$v['swbat_atpid']]['swbat_fail']      = $v['swbat_fail'];
                        $oldModels[$v['swbat_atpid']]['swbat_sign_more'] = $v['swbat_sign_more'];
                    }
                }
                //删除的指令
                $delModel = $Model->field('swbat_atpid,swbat_detail')->where("swbat_mainid='$swbat_mainid' and swbat_type = '$swbat_type'")->select();
                $delatpid  = [];
                $delModels = [];
                if(!empty($delModel)){
                    foreach($delModel as $k=>$v){
                        $delatpid[]  = $v['swbat_atpid'];
                        $delModels[$v['swbat_atpid']] = $v['swbat_detail'];
                    }
                    $diffdel  = array_diff($delatpid,$oldatpid);
                    if(!empty($diffdel)){
                        //删除
                        $del['swbat_atpstatus'] = 'DEL';
                        foreach($diffdel as $k=>$v){
                            $Model->where("swbat_atpid='$v'")->setField($del);
                            $content = '删除指令序列信息：指令--'.$delModels[$v];
                            $this->recordLog('delete', 'model',$content,'swbat',$v);
                        }
                    }
                }
                $data = [];
                foreach($modelinfo as $key=>$item){
                    $swbat_atpid = empty($item['swbat_atpid'])?'':$item['swbat_atpid'];
                    if(!$oldModels[$swbat_atpid]){
                        $data['swbat_atpid']                 = $this->makeGuid();
                        $data['swbat_atpcreatedatetime']     = date('Y-m-d H:i:s', time());
                        $data['swbat_atpcreateuser']         = I('session.username', '');
                        $data['swbat_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                        $data['swbat_atplastmodifyuser']     = I('session.username', '');
                        $data['swbat_sortno']                = $item['swbat_sortno'];
                        $data['swbat_mainid']                = $swbat_mainid;
                        $data['swbat_name']                  = $swbat_name;
                        $data['swbat_type']                  = $swbat_type;
                        $data['swbat_detail']                = $item['swbat_detail'];
                        $data['swbat_success']               = empty($item['swbat_success'])?'null':$item['swbat_success'];
                        $data['swbat_fail']                  = empty($item['swbat_fail'])?'null':$item['swbat_fail'];
                        $data['swbat_sign_more']             = ($item['swbat_sign_more']=='need' || $item['swbat_sign_more'][0]=='need')?'need':'';
                        $Model->add($data);
                        $content = '新增指令序列信息：指令,'.$swbat_name.';模板类型,'.$swbat_type.';指令内容,'.$data['swbat_detail'].';成功结果,'.$data['swbat_success'].';失败结果,'.$data['swbat_fail'].';排序号,'.$data['swbat_sortno'].'.';
                        $this->recordLog('add', 'model',$content,'swbat',$data['swbat_atpid']);
                    }else{
                        $data['swbat_sortno']                = $item['swbat_sortno'];
                        $data['swbat_detail']                = $item['swbat_detail'];
                        $data['swbat_success']               = empty($item['swbat_success'])?'null':$item['swbat_success'];
                        $data['swbat_fail']                  = empty($item['swbat_fail'])?'null':$item['swbat_fail'];
                        $data['swbat_sign_more']             = ($item['swbat_sign_more']=='need' || $item['swbat_sign_more'][0]=='need')?'need':'';
                        $diff  = array_diff($oldModels[$swbat_atpid],$data);
                        $diffs = array_diff($data,$oldModels[$swbat_atpid]);
                        if(empty($diff) && empty($diffs)){
                            continue;
                        }else{
                            $content = '编辑指令序列信息：';
                            foreach($diff as $key=>$val){
                                $content .= $key.";".$val."-".$data[$key].";";
                            }
                            $data['swbat_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                            $data['swbat_atplastmodifyuser']     = I('session.username', '');
                            $Model->where("swbat_atpid = '$swbat_atpid'")->save($data);
                            $this->recordLog("update",'model',$content,'swbat',$swbat_atpid);
                        }
                    }
                }
                echo json_encode(['code'=>0,'message'=>'编辑指令序列集成功！']);
            }else{
                $delModel = $Model->field('swbat_atpid,swbat_detail')->where("swbat_mainid='$swbat_mainid' and swbat_type = '$swbat_type'")->select();
                //删除
                $del['swbat_atpstatus'] = 'DEL';
                foreach($delModel as $k=>$v){
                    $Model->where("swbat_atpid='".$v['swbat_atpid']."'")->setField($del);
                    $content = '删除指令序列信息：指令--'.$v['swbat_detail'];
                    $this->recordLog('delete', 'model',$content,'swbat',$v['swbat_atpid']);
                }
                echo json_encode(['code'=>0,'message'=>'删除指令序列集成功！']);
            }
            M('swbat')->commit();
        }
        catch(\Exception $e)
        {
            M('swbat')->rollback();
            echo json_encode(['code'=>1,'message'=>"error：$e"]);
        }
    }

    public function getModelData()
    {
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="select * from it_swbat du";
        $sql_count="
                select
                    count(1) c
                from it_swbat du";
        $sql_select = $this->buildSql($sql_select,"du.swbat_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.swbat_atpstatus is null");
        if(("" == $queryparam['swbat_mainid']) || ("" == $queryparam['swbat_type'])){
            echo json_encode(array( 'total' => 0,'rows' => []));
        }else{
            if ("" != $queryparam['swbat_mainid']){
                $searchcontent = trim($queryparam['swbat_mainid']);
                $sql_select = $this->buildSql($sql_select,"du.swbat_mainid = '".$searchcontent."'");
                $sql_count = $this->buildSql($sql_count,"du.swbat_mainid = '".$searchcontent."'");
            }
            if ("" != $queryparam['swbat_type']){
                $searchcontent = trim($queryparam['swbat_type']);
                $sql_select = $this->buildSql($sql_select,"du.swbat_type = '".$searchcontent."'");
                $sql_count = $this->buildSql($sql_count,"du.swbat_type = '".$searchcontent."'");
            }
            $sql_select = $sql_select . " order by du.swbat_sortno asc ";
            $Result = $Model->query($sql_select);
            $Count = $Model->query($sql_count);
            foreach($Result as $key=>$val){
                $Result[$key]['swbat_atpids'] = $val['swbat_atpid'];
            }
            echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
        }
    }

    public function getdutydept()
    {
        $Model = M('person');
        $dutyman= $_POST['dutyman'];
        $orgid = $Model->where("username='%s'",$dutyman)->getField('orgid');
        $sbtypelist = M('depart')->where("id='%s'",$orgid)->field('fullname,id')->select();
        echo json_encode($sbtypelist);
    }

    public function assignrelation()
    {
        $Model = M('dictionary');
        $data =$Model->where("d_belongtype='%s'","关联关系")->field('d_dictname,d_atpid')->select();
        $this->assign('ds_relation',$data);

    }

    public function getbuilding()
    {
        $Model = M('dictionary');
        $area= $_POST['area'];
        $buildinglist = $Model->where("d_parentid='%s'",$area)->field('d_dictname,d_atpid')->select();
        echo json_encode($buildinglist);
    }
}