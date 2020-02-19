<?php
namespace Demo\Model;
use Think\Model;
class TaskModel extends BaseModel {
    public function getBDData($queryparam,$type = 0)
    {
        $map = [];
        if($type == 0){
            if ($queryparam['offset'] == ''){
                $offset = 0;
            }else{
                $offset = $queryparam['offset'];
            }
            $limit = $queryparam['limit'];
        }
        $map[0]['t_rwtype'] = ['eq',"BD"];
        if ("" != $queryparam['bdh']){
            $searchcontent = trim($queryparam['bdh']);
            $map[0][0]['t_rwid']   = ['like',"%".$searchcontent."%"];
            $map[0][0]['t_taskid'] = ['like',"%".$searchcontent."%"];
            $map[0][0]['_logic']   = 'or';
        }
        if ("" != $queryparam['dutydept']){
            $searchcontent = trim($queryparam['dutydept']);
            $searchcontent = D('depart')->getDepartment($searchcontent);
            $map[0][1]['t_depart'] = ['like',"%".$searchcontent."%"];
            $map[0][1]['t_office'] = ['like',"%".$searchcontent."%"];
            $map[0][1]['_logic']   = 'or';
        }
        if ("" != $queryparam['isstatus']){
            $searchcontent = trim($queryparam['isstatus']);
            $map[0]['t_status'] = ['like',"%".$searchcontent."%"];
        }
        if ("" != $queryparam['begintime']){
            $searchcontent = trim($queryparam['begintime']);
            $map[0][0]['t_arrivetime'] = ['gt',$searchcontent];
        }
        if ("" != $queryparam['endtime']){
            $searchcontent = trim($queryparam['endtime']);
            $map[0][1]['t_arrivetime'] = ['lt',$searchcontent];
        }
        if ("" != $queryparam['ip']){
            $searchcontent = trim($queryparam['ip']);
            $map[0]['t_ip'] = ['like',"%".$searchcontent."%"];
        }
        if ("" != $queryparam['mac']){
            $searchcontent = strtoupper(trim($queryparam['mac']));
            $map[0]['t_mac'] = ['like',"%".$searchcontent."%"];
        }
        if ("" != $queryparam['dutyman']){
            $searchcontent = trim($queryparam['dutyman']);
            $map[0]['t_nameid'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['createuser']){
            $searchcontent = trim($queryparam['createuser']);
            $map[0]['t_createuserid'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['bdname']){
            $searchcontent = trim($queryparam['bdname']);
            $map[0]['t_biaodanname'] = ['eq',$searchcontent];
        }


        $filed = ",min(zd_devicecode) as zd_devicecode,
                  min(zd_factoryname) as zd_factoryname,
                  min(zd_modelnumber) as zd_modelnumber,
                  min(zd_secretlevel) as zd_secretlevel,
                  min(zd_usedeptname) as zd_usedeptname";
        $join = "left join (
                select
                min(zd_macaddress) as zd_macaddress $filed
                from it_terminal
                where zd_atpstatus is null
                group by UPPER(REGEXP_REPLACE(zd_macaddress,'[^\da-zA-Z]'))
            ) zd on UPPER(substr(REGEXP_REPLACE(t_mac,'[^\da-zA-Z]'),0,12)) = UPPER(REGEXP_REPLACE(zd.zd_macaddress,'[^\da-zA-Z]'))";

        $task = M('task')->where($map);

        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'bdname') $queryparam['sort'] = 't_biaodanname';
            $order = array($queryparam['sort']=>$queryparam['sortOrder']);
            if($type == 0){
                $Result = $task->order($order)->limit($limit,$offset)->select();
            }else{
                $Result = $task->join($join)->order($order)->select();
            }
        } else {
            if($type == 0){
                $Result = $task->order("t_atpid desc nulls last")->limit($limit,$offset)->select();
            }else{
                $Result = $task->join($join)->order("t_atpid desc nulls last")->select();
            }
        }
        $Count  = M('task')->field('count(*) c')->where($map)->select();
//        echo $task->_sql();die;

        foreach($Result as $key=> &$value){
            switch($value['t_status']){
                case "0":
                    $value['statusprint'] ="未处理";
                    break;
                case "1":
                    $value['statusprint'] ="处理中";
                    break;
                case "2":
                    $value['statusprint'] ="已完成";
                    break;
            }
            switch($value['t_biaodanname']){
                case "110":
                    $value['bdname'] ="涉密网计算机入网";
                    break;
                case "501":
                    $value['bdname'] ="涉密网测试用机入网";
                    break;
                case "119":
                    $value['bdname'] ="涉密网设备入网";
                    break;
                case "15":
                    $value['bdname'] ="涉密网计算机变更入网";
                    break;
                case "502":
                    $value['bdname'] ="涉密网测试用机变更入网";
                    break;
                case "308":
                    $value['bdname'] ="涉密网设备变更入网";
                    break;
                case "21":
                    $value['bdname'] ="涉密网计算机撤销入网";
                    break;
                case "503":
                    $value['bdname'] ="涉密网测试用机撤销入网";
                    break;
                case "122":
                    $value['bdname'] ="涉密网设备撤销入网";
                    break;
                case "18":
                    $value['bdname'] ="涉密网计算机新增入网申请";
                    break;
                case "105":
                    $value['bdname'] ="总体部涉密计算机特殊需求申请";
                    break;
                case "115":
                    $value['bdname'] ="涉密网计算机安全防护特殊需求申请";
                    break;
                case "118":
                    $value['bdname'] ="总体部涉密人员密级审定表";
                    break;
                case "134":
                    $value['bdname'] ="涉密网应用系统帐号申请";
                    break;
                case "135":
                    $value['bdname'] ="涉密网公共机登录权限申请";
                    break;
                case "136":
                    $value['bdname'] ="涉密网公共机登录权限撤销申请";
                    break;
                case "137":
                    $value['bdname'] ="涉密网测试用机域账号申请";
                    break;
                case "138":
                    $value['bdname'] ="涉密网测试用机域账号撤销申请";
                    break;
                case "506":
                    $value['bdname'] ="UsbKey管理表";
                    break;
                case "508":
                    $value['bdname'] ="临时域账号申请";
                    break;
                case "526":
                    $value['bdname'] ="综合管理层涉密计算机处理审批登记";
                    break;
                default:
                    $value['bdname'] ="-";

            }
            //申请人姓名根据t_nameid查
            if(!empty($value['t_nameid'])){
                $userid = trim($value['t_nameid']);
                $Result[$key]['t_name'] = getRealusername($userid);
            }
            //处理人姓名
            if($value['t_person'] != ''){
                if($value['t_person'] != '系统'){
                    $personid = trim($value['t_person']);
                    $Result[$key]['t_personname'] = getRealusername($personid);
                }else{
                    $Result[$key]['t_personname'] = '系统';
                }
            }else{
                $Result[$key]['t_personname'] = '';
            }
            $value['taskidurl'] ="<a onclick=\"opentaskdetail('".$value['t_taskid']."')\" style='color:DarkBlue;'>".$value['t_taskid'];
            $value['bdidurl'] ="<a onclick=\"openbddetail('".$value['t_rwid']."')\" style='color:DarkRed;'>".$value['t_rwid'];
        }
        return [$Result,$Count];
    }
}