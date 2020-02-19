<?php
namespace Server\Controller;
use Think\Controller;
use Think\Exception;

//该控制器提供服务,类似于抽象类或接口
class ServerController extends Controller\WebServiceController{
    public function tastNewCreate($formbody){}
    public function formstatus($formtype,$formid,$nodename,$subformstatus){}
    public function updateequipment($formbody){}
    public function macData($mac){}
    public function ipAddressdata($miji,$username,$area,$louyu){}
    // public function ipAddressdata(){}
    public function ipdata($ip1,$ip2,$is){}
}

//下面控制器具体实现
class ServerImplementController  extends Controller{

    public function formstatus($formtype,$formid,$nodename,$subformstatus){
        $taskModel = M('it_task');
        $time      = date('Y-m-d H:i:s');
        $status    = '2';
        $operator  = '表单系统';
        $detail    = '更新了表单号为:'.$formid.'的运维任务单状态信息,节点为:'.$nodename.';';
        try{
            $taskModel->startTrans();
            // it_task表修改
            $taskData                            = [];
            $taskData['t_bdstatus']              = $subformstatus;
            $taskData['t_biaodanname']           = $formtype;
            $taskData['t_atplastmodifyuser']     = $operator;
            $taskData['t_status']                = $status;
            $taskData['t_atplastmodifydatetime'] = $time;
            $taskModel->where("t_rwtype='BD' and t_rwid ='".$formid."'")->setField($taskData);
            $tl_taskid = $taskModel->field("max(t_taskid)")->where("t_rwid ='$formid'")->group("t_rwid")->getField('max(t_taskid)');
            if($tl_taskid){
                // it_taskdetail表新增
                $taskInfo                   = $taskModel->where("t_taskid ='"."$tl_taskid"."'")->find();
                $taskDetail                 = [];
                $taskDetail['tl_atpid']     = makeGuid();
                $taskDetail['tl_taskid']    = $tl_taskid;
                $taskDetail['tl_person']    = $taskInfo['t_person'];
                $taskDetail['tl_solvetime'] = $time;
                $taskDetail['tl_tasktype']  = $taskInfo['t_tasktype'];
                $taskDetail['tl_process']   = '一线完成：'.$nodename;
                M('it_taskdetail')->add($taskDetail);
            }else{
                E("任务单号".$formid."不存在");
            }
            // 记录日志it_log
            recordLog('update','表单驱动-更新任务单',$detail,'it_task',$formid);
            // 记webService日志
            writeWebLog('formstatusSuccess',['formtype'=>$formtype,'formid'=>$formid,'nodename'=>$nodename,'formstatus'=>$subformstatus]);
            $taskModel->commit();
            return "success";
        }catch(Exception $e){
            $taskModel->rollback();
            writeWebLog('formstatusFail',['errorMessage'=>$e,'formtype'=>$formtype,'formid'=>$formid,'nodename'=>$nodename,'formstatus'=>$subformstatus]);
            return "fail:$e";
        }
    }

//    public function tastNewCreate($formtype,$formid,$formbody,$isCreate,$nodename,$formtaskid){
    public function tastNewCreate($formbody){
        $data = $this->xmlToArrays($formbody);
        writeWebLog('taskcreateSuccess', ['formtype' => $data['formtype'],'data' => $data]);
        $isCreate = $data['isCreate'];
        if($isCreate == '是') {
            $taskModel = M('it_task');
            $detail = '新建了表单号为:' . $data['FormNo'] . '的运维任务单，节点为:' . $data['NodeName'] . ';';
            $time = date('Y-m-d H:i:s');
            $operator = '表单系统';
            $tasktype = '一线';
            $taskid = $this->GetWorkID($data['FormNo']);
            $formtype  = $data['formtype'];
            $formid  = $data['FormNo'];
            $BDSID = $data['BDSID'];
            $nodename = $data['NodeName'];
            $BindID = $data['BindID'];
            $ip = empty($data['IP']) ? $data['OldIP'] : $data['IP'];
            $mac = empty($data['MAC']) ? $data['OldMac'] : $data['MAC'];
            $url = "http://10.78.86.18:8088/portal/bice/bice_jsp/aws_opentask_log.jsp?createuser=".$data['ApplyMan']."&taskid=".$BDSID."&bindid=".$BindID."&openstate=2";
            $name = $this->FindPersonID($data['ApplyMan']);
            $taskData = [];
            $taskData['t_atpid'] = makeGuid();
            $taskData['t_arrivetime'] = $time;
            $taskData['t_rwid'] = $data['FormNo'];
            $taskData['t_problemtype'] = $data['NodeName'];
            $taskData['t_rwtype'] = 'BD';
            $taskData['t_status'] = '0';
            $taskData['t_taskid'] = $taskid;
            $taskData['t_tasktype'] = $tasktype;
            $taskData['t_name'] =  $name['realusername'];
            $taskData['t_depart'] = $data['ApplyDept'];
            $taskData['t_office'] = $data['ApplyOffice'];
            $taskData['t_phone'] = $data['PhoneNo'];
            $taskData['t_biaodanurl'] = $url;
            $taskData['t_biaodanname'] = $data['formtype'];
            $taskData['t_nameid'] = $data['ApplyMan'];
            $taskData['t_atpcreateuser'] = $operator;
            $taskData['t_atpcreatedatetime'] = $time;
            $taskData['t_bdstatus'] = $data['FormStatus'];
            $taskData['t_ip'] = $ip;
            $taskData['t_mac'] = $mac;
//            $taskData['t_createuserid'] = $data['createuserid'];
//            $taskData['t_createusername'] = $data['createusername'];
            $taskData['t_area'] = $data['jharea'];
            $taskData['t_belongfloor'] = $data['buildingname'];
            $taskData['t_roomno'] = $data['roomno'];
            try {
                $taskModel->startTrans();
                $taskModel->add($taskData);

                $taskDetail = [];
                $taskDetail['tl_atpid'] = makeGuid();
                $taskDetail['tl_taskid'] = $taskid;
                $taskDetail['tl_person'] = '系统';
                $taskDetail['tl_solvetime'] = $time;
                $taskDetail['tl_tasktype'] = $tasktype;
                $taskDetail['tl_process'] = '审批表单流入建立工单：' . $nodename;
                M('it_taskdetail')->add($taskDetail);
//                // 记录日志it_log
                recordLog('add', '表单驱动-创建任务单', $detail, 'it_task', $formid);
//                // 记webService日志
                writeWebLog('taskcreatesSuccess', ['formtype' => $formtype, 'formid' => $formid, 'nodename' => $nodename, 'formbody' => $formbody]);
                $taskModel->commit();
                return "success";
            } catch (\Exception $e) {
                $taskModel->rollBack();
                writeWebLog('taskcreateFail', ['errorMessage' => $e, 'formtype' => $formtype, 'formid' => $formid, 'nodename' => $nodename, 'formbody' => $formbody]);
                return "fail:$e";
            }
        }else{
            return "is null";
        }
    }

    function updateequipment($formbody)
    {
        $time                   = date('Y-m-d H:i:s');
        $operator               = '表单系统';
        $atpid                  = makeGuid();
        $atpid_sk               = makeGuid();
        $model                  = M('it_terminal');
        $data                   = $this->xmlequipmentToArrays($formbody);
        $formtype               = $data['BillType'];
        $formid                 = $data['FormNo'];
        $nodename               = $data['NodeName'];
        $equipmenttype_sbsk     = '154112'; //刷卡器类型
        $AssetType              = $data['AssetType'];
        $region                 = $data['jharea'];
        $building               = $data['buildingname'];

        $mac                    = $this->ConverMAC($data['macaddress']);
        $oldmac                 = $this->ConverMAC($data['OldMac']); //测试用机变更入网时用
        $skmacaddress           = $this->ConverMAC($data['SKMAC']); //设备变更入网时用
        $oldskmacaddress        = $this->ConverMAC($data['OldSKMAC']); //设备变更入网时用

        $ip                     = $this->ConverMAC($data['ipaddress']);
        $oldip                  = $this->ConverMAC($data['OldIP']); //测试用机变更入网时用
        $skipaddress                   = $this->ConverMAC($data['SKIP']); //设备变更入网时用
        $oldskipaddress                = $this->ConverMAC($data['OldSKIP']); //设备变更入网时用

//        $newmac                 = $this->ConverMAC($data['newmac']); //测试用机变更入网时用
//        $newmacaddress          = $this->ConverMAC($data['newmacaddress']); //计算机、设备变更入网时用
//        $oldmacaddress          = $this->ConverMAC($data['oldmacaddress']); //计算机、设备变更入网时用
//        $newskmacaddress        = $this->ConverMAC($data['newskmacaddress']); //设备变更入网时用
        $secret                 = $data['SecretLevel']; //密级
//        $equipmenttype_sb       = $this->FindBDID($data['type'], '涉密设备入网单'); //查找设备入网类型
//        $equipmenttype_computer   = $this->FindID("涉密网计算机", "equipmenttype"); //涉密网计算机类型
//        $equipmenttype_ceshi     = $this->FindID("测试用机", "equipmenttype"); //测试用机类型
//        $equipmenttype_gongyong   = $this->FindID("公共用机", "equipmenttype"); //公共用机计算机类型
        $deptids                 = $this->FindPersonID($data['UseMan']);

        $deptid                 = empty($deptids) ? '' : $deptids['orgid'];//使用人部门ID
        $usename                = empty($deptids) ? '' : $deptids['realusername'];//使用人
        $usedeptname            = empty($deptids) ? '' : $deptids['fullname'];//使用人部门


        $deptname               = $this->FindPersonID($data['DutyMan']);
        $dutyDeptid             = empty($deptname) ? '' : $deptname['orgid']; //责任部门id
        $dutyname               = empty($deptname) ? '' : $deptname['realusername']; //责任人
        $deptname               = empty($deptname) ? '' : $deptname['fullname']; //责任人部门
        $factoryinfo            = $data['factory'];
        $modelnumber            = $data['model'];
//
//        $deptid_d               = $this->FindPersonID($data['chargepersonid']);
//        $deptid_d               = empty($deptid_d) ? '' : $deptid_d['orgid']; //无applyname时的使用人部门ID
//        $deptname_d             = $this->FindPersonID($data['chargepersonid']);
//        $deptname_d             = empty($deptname_d) ? '' : $deptname_d['fullname'];//无applyname时的使用人部门
//        $newip_final            = empty($data['newip']) ? $data['oldip'] : $data['newip'];//防止出现变更时未换IP
//        $newmac_final           = empty($newmac) ? $oldmac : $newmac;//防止出现变更时未换MAC
//        $newipaddress_final     = empty($data['newipaddress']) ? $data['oldipaddress'] : $data['newipaddress'];//防止出现变更时未换IP
//        $newmacaddress_final    = empty($newmacaddress) ? $oldmacaddress : $newmacaddress;//防止出现变更时未换MAC
//        $newskipaddress_final   = empty($data['newskipaddress']) ? $data['oldskipaddress'] : $data['newskipaddress'];//防止出现变更时未换刷卡器IP
        $newskmacaddress_final  = empty($newskmacaddress) ? $oldskmacaddress : $newskmacaddress;//防止出现变更时未换刷卡器MAC
        if ($data['isisolate'] == '0') $data['isisolate'] = '是';
        if ($data['isisolate'] == '1') $data['isisolate'] = '否';
        if ($data['isinstalljammer'] == '0') $data['isinstalljammer'] = '是';
        if ($data['isinstalljammer'] == '1') $data['isinstalljammer'] = '否';
        $status = '在用';
//        $data['computername']   = empty($data['computername']) ? $data['usbkeyid'] : $data['computername']; //避免部分表单使用usbkeyid作为computername

        $terminalData = [];
        $terminalData['zd_atpid']           = $atpid;
        $terminalData['zd_type']            = $AssetType  ;
        $terminalData['zd_devicecode']      = $data['machineno'];
        $terminalData['zd_name']            = $data['AssetName'];
        $terminalData['zd_ipaddress']       = $ip;
        $terminalData['zd_macaddress']      = $mac;
        $terminalData['zd_area']            = $region;
        $terminalData['zd_belongfloor']     = $building;
        $terminalData['zd_roomno']          = $data['roomno'];
        $terminalData['zd_osinstalltime']   = $data['Osinstalltime'];
        $terminalData['zd_harddiskseq']     = $data['Harddiskseq'];
        $terminalData['zd_bdid']            = $formid;
        $terminalData['zd_dutyman']         = $data['DutyMan'];
        $terminalData['zd_dutymanname']    = $dutyname;
        $terminalData['zd_useman']          = $data['UseMan'];
        $terminalData['zd_username']        = $usename;
        $terminalData['zd_dutydeptid']      = $dutyDeptid;
        $terminalData['zd_dutydeptname']    = $deptname;
        $terminalData['zd_usedeptid']       = $deptid;
        $terminalData['zd_usedeptname']     = $usedeptname;
        $terminalData['zd_secretlevel']     = $secret;
        $terminalData['zd_atpcreateuser']   = $operator;
        $terminalData['zd_atpcreatetime']   = $time;
        $terminalData['zd_isisolate']       = $data['isisolate'];
        $terminalData['zd_isinstalljammer'] = $data['isinstalljammer'];
        $terminalData['zd_display']         = $data['reason'];
        $terminalData['zd_status']          = $status;
        $delData = [];
        $delData['zd_atpstatus']            = 'DEL';
        $delData['zd_atplastmodifyuser']    = $operator;
        $delData['zd_atplastmodifytime']    = $time;
        $delData['zd_mainid']               = $terminalData['zd_atpid'];
        $delData['zd_bdid_del']               = $formid;
        try {
            $model->startTrans();
            if ($formtype == '110') { //涉密网计算机入网

                $terminalData['zd_factoryname'] = $factoryinfo;
                $terminalData['zd_modelnumber'] = $modelnumber;
                $terminalData['zd_bdtype']       = '入网';
                $terminalData['zd_display']      = $data['reason'];
                $detail = '表单驱动-更新资产信息-涉密网计算机入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';

                $model->add($terminalData);
                recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                M("it_ipbase")->where("ipb_address='" . $data['ipaddress'] . "'")->setField(['ipb_status' => 2]);
            }else if ($formtype == '119') { //涉密网设备入网，增加刷卡器ip
                //增加除刷卡器外的其他设备的资产
                $terminalData['zd_type']            = $AssetType;
                $terminalData['zd_devicecode']      = $data['machineno'];
                $terminalData['zd_memo']            = $data['reason'];
                unset($terminalData['zd_name']);
                unset($terminalData['zd_osinstalltime']);
                unset($terminalData['zd_harddiskseq']);
                $terminalData['zd_dutyman']         = $data['DutyMan'];
                $terminalData['zd_dutymanname']     = $dutyname;
                $terminalData['zd_useman']          = $data['UseMan'];
                $terminalData['zd_username']        = $usename;
                $terminalData['zd_dutydeptid']      = $dutyDeptid;
                $terminalData['zd_dutydeptname']    = $deptname;
                $terminalData['zd_usedeptid']       = $deptid;
                $terminalData['zd_usedeptname']     = $usedeptname;
                $terminalData['zd_bdtype']          = '入网';
                $terminalData['zd_display']      = $data['reason'];
                $model->add($terminalData);
                $detail = '表单驱动-更新资产信息-涉密网设备入网;mac为:' . $mac . ',ip为:' . $ip . ',设备类型ID为:' . $AssetType . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                M("it_ipbase")->where("ipb_address='" . $data['ipaddress'] . "'")->setField(['ipb_status' => 2]);

                if ($data['SKIP'] <> '' || $skmacaddress <> '') //判断是否有刷卡器
                {
                    $skData                       = $terminalData;
                    $skData['zd_atpid']           = $atpid_sk;
                    $skData['zd_type']            = $equipmenttype_sbsk;
                    $skData['zd_devicecode']      = $data['machineno'];
                    $skData['zd_memo']            = $data['reason'];
                    unset($skData['zd_name']);
                    $skData['zd_ipaddress']       = $data['SKIP'];
                    $skData['zd_macaddress']      = $skmacaddress;
                    unset($skData['zd_osinstalltime']);
                    unset($skData['zd_harddiskseq']);
                    $skData['zd_dutyman']         = $data['DutyMan'];
                    $skData['zd_dutymanname']     = $dutyname;
                    $skData['zd_useman']          = $data['UseMan'];
                    $skData['zd_username']        = $usename;
                    $skData['zd_dutydeptid']      = $dutyDeptid;
                    $skData['zd_dutydeptname']    = $deptname;
                    $skData['zd_usedeptid']       = $deptid;
                    $skData['zd_usedeptname']     = $usedeptname;
                    $skData['zd_bdtype']          = '入网';
                    $terminalData['zd_display']      = $data['reason'];
                    //日志中的detail拼写
                    $detail_sk = '表单驱动-更新资产信息-涉密网设备(刷卡器)入网;mac为:' . $skmacaddress . ',ip为:' . $data['SKIP'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                    M("it_ipbase")->where("ipb_address='" . $data['SKIP'] . "'")->setField(['ipb_status' => 2]);
                    $model->add($skData);
                    recordLog('add', 'it_terminal', $detail_sk, 'it_terminal', $skData['zd_atpid'], $formid);
                }
            } else if ($formtype == '18') { //涉密网计算机新增入网
//                $factoryinfo_c = $this->FindID($data['factory'], "factoryinfo", $AssetType  );
//                $factoryinfo   = ($factoryinfo_c == 'notfind') ? $data['factory'] : $factoryinfo_c;
//                $modelnumber_c = $this->FindID($data['model'], "modelnumber", $factoryinfo);
//                $modelnumber   = ($modelnumber_c == 'notfind') ? $data['model'] : $modelnumber_c;

                $terminalData = [];
                $terminalData['zd_factoryname']     = $factoryinfo;
                $terminalData['zd_modelnumber']     = $modelnumber;
                $terminalData['zd_bdtype']          = '入网';

                $model->add($terminalData);
                $detail = '表单驱动-更新资产信息-新增涉密网计算机入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                M("it_ipbase")->where("ipb_address='" . $data['ipaddress'] . "'")->setField(['ipb_status' => 2]);
            } else if ($formtype == '501') { //测试用机入网
//                $factoryinfo_c = $this->FindID($data['factory'], "factoryinfo", $AssetType  );
//                $factoryinfo   = ($factoryinfo_c == 'notfind') ? $data['factory'] : $factoryinfo_c;
//                $modelnumber_c = $this->FindID($data['model'], "modelnumber", $factoryinfo);
//                $modelnumber   = ($modelnumber_c == 'notfind') ? $data['model'] : $modelnumber_c;

                $terminalData['zd_type']            = $AssetType  ;
                $terminalData['zd_memo']            = $data['reason'];
                unset($terminalData['zd_name']);
                $terminalData['zd_factoryname']     = $factoryinfo;
                $terminalData['zd_modelnumber']     = $modelnumber;
                $terminalData['zd_bdtype']          = '入网';
                $terminalData['zd_display']      = $data['reason'];

                $model->add($terminalData);
                $detail = '表单驱动-更新资产信息-测试用机入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                M("it_ipbase")->where("ipb_address='" . $data['ipaddress'] . "'")->setField(['ipb_status' => 2]);
            } else if ($formtype == '116') { //公共用机入网
//                $factoryinfo_c = $this->FindID($data['factory'], "factoryinfo", $AssetType  );
//                $factoryinfo   = ($factoryinfo_c == 'notfind') ? $data['factory'] : $factoryinfo_c;
//                $modelnumber_c = $this->FindID($data['model'], "modelnumber", $factoryinfo);
//                $modelnumber   = ($modelnumber_c == 'notfind') ? $data['model'] : $modelnumber_c;

                $terminalData['zd_type']            = $AssetType  ;
                $terminalData['zd_memo']            = $data['reason'];
                unset($terminalData['zd_name']);
                $terminalData['zd_factoryname']     = $factoryinfo;
                $terminalData['zd_modelnumber']     = $modelnumber;
                $terminalData['zd_dutyman']         = $data['DutyMan'];
                $terminalData['zd_dutymanname']     = $dutyname;
                $terminalData['zd_useman']          = $data['UseMan'];
                $terminalData['zd_username']        = $usename;
                $terminalData['zd_dutydeptid']      = $dutyDeptid;
                $terminalData['zd_dutydeptname']    = $deptname;
                $terminalData['zd_usedeptid']       = $deptid;
                $terminalData['zd_usedeptname']     = $usedeptname;
                $terminalData['zd_bdtype']          = '入网';
                $terminalData['zd_display']      = $data['reason'];

                $model->add($terminalData);
                $detail = '表单驱动-更新资产信息-公共用机入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                M("it_ipbase")->where("ipb_address='" . $data['ipaddress'] . "'")->setField(['ipb_status' => 2]);
            } else if ($formtype == '15') { //涉密网计算机变更入网
//                $factoryinfo_c = $this->FindID($data['factory'], "factoryinfo", $AssetType  );
//                $factoryinfo = ($factoryinfo_c == 'notfind') ? $data['factory'] : $factoryinfo_c;
//                $modelnumber_c = $this->FindID($data['model'], "modelnumber", $factoryinfo);
//                $modelnumber = ($modelnumber_c == 'notfind') ? $data['model'] : $modelnumber_c;

                $terminalData['zd_ipaddress']       = $ip;;
                $terminalData['zd_macaddress']      = $mac;
                $terminalData['zd_factoryname']     = $factoryinfo;
                $terminalData['zd_modelnumber']     = $modelnumber;
                $terminalData['zd_bdtype']          = '变更';
                $terminalData['zd_display']      = $data['reason'];
                if ($oldmac <> '') //获取更改前的数据比较变化
                {
                    $oldData = M("it_terminal")->where("zd_atpstatus is null and zd_macaddress ='".$oldmac."'")->find();
                    $detail  = $this->compareOldData($oldData,$terminalData);
                    recordLog('update', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                    $delData['zd_bdtype'] = '变更';
                    $model->where("zd_macaddress='" . $oldmac . "' and zd_atpstatus is null")->setField($delData); // 写入标志位DEL，同时将mainid写入更新数据的atpid
                }else{
                    $detail = '表单驱动-更新资产信息-涉密网计算机入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                    recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                }

                if($ip <> $data['OldIP']){
                    M("it_ipbase")->where("ipb_address='".$data['OldIP']."'")->setField(['ipb_status' => null]);//释放旧ip地址
                    M("it_ipbase")->where("ipb_address='".$ip."'")->setField(['ipb_status' => 2]);//更新新ip地址
                }
                $model->add($terminalData);// 增加资产
            } else if ($formtype == '308')
            {  //涉密网设备变更入网，以ip为判定条件,mac可能为空
                //增加刷卡器资产
                $skData                       = $terminalData;
                $skData['zd_atpid']           = $atpid_sk;
                $skData['zd_type']            = $equipmenttype_sbsk;
                $skData['zd_memo']            = $data['reason'];
                unset($skData['zd_name']);
                $skData['zd_ipaddress']       = $skipaddress;
                $skData['zd_macaddress']      = $skmacaddress;
                $skData['zd_dutyman']         = $data['DutyMan'];
                $skData['zd_dutymanname']     = $dutyname;
                $skData['zd_useman']          = $data['UseMan'];
                $skData['zd_username']        = $usename;
                $skData['zd_dutydeptid']      = $dutyDeptid;
                $skData['zd_dutydeptname']    = $deptname;
                $skData['zd_usedeptid']       = $deptid;
                $skData['zd_usedeptname']     = $usedeptname;
                $skData['zd_bdtype']          = '变更';
                $skData['zd_display']      = $data['reason'];
                unset($skData['zd_osinstalltime']);
                unset($skData['zd_harddiskseq']);

                // 处理刷卡器数据
                if(!empty($skipaddressl) || !empty($skmacaddress))
                {
                    $oldatpid = $this->FindTerminalatpID($data['OldSKMAC'], $data['OldSKIP']);
                    if($oldatpid){ // 删除旧刷卡器资产
                        $oldSkData = M("it_terminal")->where("zd_atpid  ='$oldatpid'")->find();
                        $detailSk  = $this->compareOldData($oldSkData,$skData);
                        if(!empty($detailSk)){
                            recordLog('update', 'it_terminal', $detailSk, 'it_terminal', $skData['zd_atpid'], $formid);
                            // 删除旧刷卡器资产
                            $delData['zd_bdtype'] = '变更';
                            $delSkData              = $delData;
                            $delSkData['zd_mainid'] = $skData['zd_atpid'];
                            $model->where("zd_atpid='" . $oldatpid . "' and zd_atpstatus is null")->setField($delSkData);
                            $model->add($skData);// 增加资产
                        }
                    }else{
                        $detail_sk = '表单驱动-更新资产信息-涉密网设备(刷卡器)入网;mac为:' . $skmacaddress . ',ip为:' . $data['SKIP'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                        recordLog('add', 'it_terminal', $detail_sk, 'it_terminal', $skData['zd_atpid'], $formid);
                        $model->add($skData);// 增加资产
                    }
                    if($skipaddress != $data['OldSKIP']){
                        M("it_ipbase")->where("ipb_address='".$data['OldSKIP']."'")->setField(['ipb_status' => null]);// 释放旧ip地址
                        M("it_ipbase")->where("ipb_address='".$skipaddress."'")->setField(['ipb_status' => 2]);// 更新新ip地址
                    }
                }

                // 除刷卡器外的其他设备的资产
                $terminalData['zd_type']            = $AssetType;
                $terminalData['zd_memo']            = $data['reason'];
                unset($terminalData['zd_name']);
                $terminalData['zd_ipaddress']       = $ip;
                $terminalData['zd_macaddress']      = $mac;
                unset($terminalData['zd_osinstalltime']);
                unset($terminalData['zd_harddiskseq']);
                $terminalData['zd_dutyman']         = $data['DutyMan'];
                $terminalData['zd_dutymanname']     = $dutyname;
                $terminalData['zd_useman']          = $data['UseMan'];
                $terminalData['zd_username']        = $usename;
                $terminalData['zd_dutydeptid']      = $dutyDeptid;
                $terminalData['zd_dutydeptname']    = $deptname;
                $terminalData['zd_usedeptid']       = $deptid;
                $terminalData['zd_usedeptname']     = $usedeptname;
                $terminalData['zd_skipaddress']     = $skipaddress;
                $terminalData['zd_skmacaddress']    = $skmacaddress;
                $terminalData['zd_bdtype']          = '变更';
                $terminalData['zd_display']      = $data['reason'];

                // 处理其他资产数据
                if(!empty($ip) || !empty($mac))
                {
                    $oldatpid = $this->FindTerminalatpID($data['OldMac'], $data['OldIP']);
                    if($oldatpid){ // 删除旧刷卡器资产
                        $oldData = M("it_terminal")->where("zd_atpid  ='$oldatpid'")->find();
                        $detail  = $this->compareOldData($oldData,$terminalData);
                        if(!empty($detail)){
                            recordLog('update', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                            // 删除旧刷卡器资产
                            $delData['zd_bdtype'] = '变更';
                            $model->where("zd_atpid='" . $oldatpid . "' and zd_atpstatus is null")->setField($delData);
                            $model->add($terminalData);// 增加资产
                        }
                    }else{
                        $detail = '表单驱动-更新资产信息-涉密网设备入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备类型ID为:' . $AssetType . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                        recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                        $model->add($terminalData);// 增加资产
                    }
                    if($ip != $data['OldIP']){
                        M("it_ipbase")->where("ipb_address='".$data['OldIP']."'")->setField(['ipb_status' => null]);// 释放旧ip地址
                        M("it_ipbase")->where("ipb_address='".$ip."'")->setField(['ipb_status' => 2]);// 更新新ip地址
                    }
                }
            } else if ($formtype == '502') { //测试用机变更入网
//                $factoryinfo_c = $this->FindID($data['factory'], "factoryinfo", $AssetType  );
//                $factoryinfo = ($factoryinfo_c == 'notfind') ? $data['factory'] : $factoryinfo_c;
//                $modelnumber_c = $this->FindID($data['model'], "modelnumber", $factoryinfo);
//                $modelnumber = ($modelnumber_c == 'notfind') ? $data['model'] : $modelnumber_c;

                $terminalData['zd_type']            = $AssetType  ;
                $terminalData['zd_memo']            = $data['reason'];
                unset($terminalData['zd_name']);
                $terminalData['zd_ipaddress']       = $ip;;
                $terminalData['zd_macaddress']      = $mac;
                $terminalData['zd_factoryname']     = $factoryinfo;
                $terminalData['zd_modelnumber']     = $modelnumber;
                $terminalData['zd_bdtype']          = '变更';
                $terminalData['zd_display']      = $data['reason'];

                if ($oldmac <> '') //获取更改前的数据比较变化
                {
                    $oldData = M("it_terminal")->where("zd_atpstatus is null and zd_macaddress ='".$oldmac."'")->find();
                    $detail  = $this->compareOldData($oldData,$terminalData);
                    recordLog('update', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                    $delData['zd_bdtype'] = '变更';
                    $model->where("zd_macaddress='" . $oldmac . "' and zd_atpstatus is null")->setField($delData); // 写入标志位DEL，同时将mainid写入更新数据的atpid
                }else{
                    $detail = '表单驱动-更新资产信息-测试用机入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                    recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                }
                if($ip <> $data['OldIP']){
                    M("it_ipbase")->where("ipb_address='".$data['OldIP']."'")->setField(['ipb_status' => null]);//释放旧ip地址
                    M("it_ipbase")->where("ipb_address='".$ip."'")->setField(['ipb_status' => 2]);//更新新ip地址
                }
                $model->add($terminalData);// 增加资产
            } else if ($formtype == '117')
            { //公共用机变更入网
//                $factoryinfo_c = $this->FindID($data['factory'], "factoryinfo", $AssetType  );
//                $factoryinfo = ($factoryinfo_c == 'notfind') ? $data['factory'] : $factoryinfo_c;
//                $modelnumber_c = $this->FindID($data['model'], "modelnumber", $factoryinfo);
//                $modelnumber = ($modelnumber_c == 'notfind') ? $data['model'] : $modelnumber_c;

                $terminalData['zd_type']            = $AssetType  ;
                $terminalData['zd_memo']            = $data['reason'];
                unset($terminalData['zd_name']);
                $terminalData['zd_ipaddress']       = $ip;
                $terminalData['zd_macaddress']      = $mac;
                $terminalData['zd_factoryname']     = $factoryinfo;
                $terminalData['zd_modelnumber']     = $modelnumber;
                $terminalData['zd_dutyman']         = $data['DutyMan'];
                $terminalData['zd_dutymanname']     = $dutyname;
                $terminalData['zd_useman']          = $data['UseMan'];
                $terminalData['zd_username']        = $usename;
                $terminalData['zd_dutydeptid']      = $dutyDeptid;
                $terminalData['zd_dutydeptname']    = $deptname;
                $terminalData['zd_usedeptid']       = $deptid;
                $terminalData['zd_usedeptname']     = $usedeptname;
                $terminalData['zd_bdtype']          = '变更';
                $terminalData['zd_display']      = $data['reason'];
                if ($data['OldMac'] <> '')  //获取更改前的数据比较变化
                {
                    $oldmac = $data['OldMac'];
                    $oldData = M("it_terminal")->where("zd_atpstatus is null and zd_macaddress ='".$oldmac."'")->find();
                    $detail  = $this->compareOldData($oldData,$terminalData);
                    recordLog('update', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                    $delData['zd_bdtype'] = '变更';
                    $model->where("zd_macaddress='" . $oldmac . "' and zd_atpstatus is null")->setField($delData); // 写入标志位DEL，同时将mainid写入更新数据的atpid
                }else{
                    $detail = '表单驱动-更新资产信息-公共用机入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ',设备编号为:' . $data['machineno'] . ',所在地区为:' . $data['jharea'] . ',所在楼宇为:' . $data['buildingname'] . ',所在房间号为:' . $data['roomno'] . ';';
                    recordLog('add', 'it_terminal', $detail, 'it_terminal', $terminalData['zd_atpid'], $formid);
                }
                if($ip <> $data['OldIP']){
                    M("it_ipbase")->where("ipb_address='".$data['OldIP']."'")->setField(['ipb_status' => null]);//释放旧ip地址
                    M("it_ipbase")->where("ipb_address='".$ip."'")->setField(['ipb_status' => 2]);//更新新ip地址
                }
                $model->add($terminalData);// 增加资产
            }else if ($formtype == '21' || $formtype == '122' || $formtype == '503' || $formtype == '20')
            {   //涉密网计算机、涉密网设备、测试设备、公共用机撤销入网
                if ($mac <> '') {
                    $oldatpid    = $this->FindTerminalatpID($mac, $data['ipaddress']);
                    $oldatpid_sk = $this->FindTerminalatpID($skmacaddress, $data['SKIP']);

                    $detail = '表单驱动-更新资产信息-终端撤销入网;mac为:' . $mac . ',ip为:' . $data['ipaddress'] . ';';
                    $detail_sk = '表单驱动-更新资产信息-终端撤销入网;刷卡器mac为:' . $skmacaddress . ',ip为:' . $data['SKIP'] . ';';

                    //删除刷卡器
                    $delData['zd_mainid'] = null;
                    $delData['zd_bdtype'] = '撤网';
                    if ($formtype == '122') {  //判断是否为打印机撤销入网单
                        if ($AssetType == '154111') {  //判断是否为打印机和刷卡器撤销入网单
                            if ($skmacaddress <> '' || $data['SKIP'] <> '') //判断是否有刷卡器
                            {
                                M("it_ipbase")->where("ipb_address='".$data['SKIP']."'")->setField(['ipb_status' => null]);//释放旧ip地址
                                $model->where("zd_macaddress='" . $skmacaddress . "' and zd_atpstatus is null")->setField($delData); // 写入标志位DEL
                                recordLog('del', 'it_terminal', $detail_sk, 'it_terminal', $oldatpid_sk, $formid);
                            }
                        }
                    }
                    M("it_ipbase")->where("ipb_address='".$data['ipaddress']."'")->setField(['ipb_status' => null]);//释放旧ip地址
                    $model->where("zd_macaddress='" . $mac . "' and zd_atpstatus is null")->setField($delData); // 写入标志位DEL
                    recordLog('del', 'it_terminal', $detail, 'it_terminal', $oldatpid, $formid);
                }
            }
            $model->commit();
            writeWebLog('updateequipmentSuccess',['formtype'=>$formtype,'formid'=>$formid,'nodename'=>$nodename,'formbody'=>$formbody]);
            return 'success';
        }catch (Exception $e){
            $model->rollback();
            writeWebLog('updateequipmentFail',['errorMessage'=>$e,'formtype'=>$formtype,'formid'=>$formid,'nodename'=>$nodename,'formbody'=>$formbody]);
            return "fail:$e";
        }
    }

//检验mac地址在运维系统数据库中
//输入：mac,格式为xxxx.xxxx.xxxx
//输出：1:mac已存在，0:mac不存在

    public function macData($mac){
        $mac = strtolower($mac);
        $model = M('it_terminal');
        $res = $model->where("lower(zd_macaddress) = '%s' and zd_atpstatus is null",$mac)->getField('zd_atpid');
        if(!empty($res)){
            writeWebLog('itTerminalSuccess',['zd_atpid'=>$res]);
            return 1;
        }else{
            writeWebLog('itTerminalError',['macaddress'=>$mac,'model'=>$model->getLastSql()]);
            return 0;
        }
    }

// 根据密级、用户账号、设备所在地区、设备所在楼宇信息，分配ip地址
// 输入：密级、用户账号、设备所在地区、设备所在楼宇4个参数
// 输出：ip地址或null
    public function ipAddressdata($miji,$username,$area,$louyu){
    // public function ipAddressdata(){
        // $miji='机密';
        // $username='hq-mapei';
        // $area='白石桥';
        // $louyu='白石桥食堂';

        writeWebLog('it_ipbase',['miji'=>$miji,'username'=>$username,'area'=>$area,'building'=>$louyu]);
        $model = M('it_ipaddress');
        try{
            $model->startTrans();
            $baseModel = M('it_ipbase');
            $username = str_replace('-','\\',$username);
            $depart = D('Home/org')->getDeptId($username);
            $area = $this->getDicId($area);
            $louyu = $this->getLyDicId($louyu);
            $where['ip_secret_level'] = ['eq',$miji];
            $where['ip_depart'] = ['like',"%$depart%"];
            $where['ip_area'] = ['like',"%$louyu%"];
            $where['ip_parea'] = ['eq',$area];
            $id = $model->field('ip_atpid,ip_mask,ip_gateway')->where($where)->find();
            $ipBase = $baseModel->where("ipb_ipid = '%s' and ipb_status is null",$id['ip_atpid'])->order('ipb_addressnum asc')->getField('ipb_address');
            if(!empty($ipBase)){
                $data['ipb_status'] = 1;
                $baseModel->where("ipb_address = '%s'",$ipBase)->save($data);

                writeWebLog('itIpbasedSuccess',['ipaddress'=>$ipBase,'ipb_status'=>'1']);
                $model->commit();
                return $ipBase.'-'.$id['ip_mask'].'-'.$id['ip_gateway'];
            }else{
                writeWebLog('itIpbasedError',['miji'=>$miji,'ip_depart'=>$depart,'parea'=>$area,'area'=>$louyu,'model'=>$model->getLastSql(),'baseModel'=>$baseModel->getLastSql()]);
                $model->commit();
                return 'error';
            }
    } catch (\Exception $e) {
            $model->rollBack();
            writeWebLog('itIpbasedFail',['result'=>'error','detail'=>$e]);
            return "fail:$e";
          }
    }

//确认ip地址是否被投入使用，会更新地址库中ip地址的使用状态信息，ip更新为null，ip2更新为2
//输入：ip，ip2地址，is为是否退回，is为“否”表示ip2参数无效，仅更新ip的状态，is为“是”表示ip2有效，更新ip、ip2的状态 ip为第一次自动分配的地址，在地址库中状态为“预分配”；ip2为第二次确定使用的地址，在地址库中状态为“已使用”
//输出：无 
    public function ipdata($ip1,$ip2,$is){
        writeWebLog('it_ipbasee',['ip1'=>$ip1,'ip2'=>$ip2,'is'=>$is]);
        $model = M('it_ipbase');
        try{
            $model->startTrans();
            if($is == '否'){
                if(!empty($ip1)){
                    $data['ipb_status'] = '';
                    $model->where("ipb_address = '%s'",$ip1)->save($data);
                }
                writeWebLog('itIpbasesSuccess',['ipaddress'=>$ip1,'ipb_status'=>'','is'=>$is]);
                $model->commit();
                return 'success';
            } else if($is == '是'){
                if(!empty($ip1) || !empty($ip2)){
                    if(!empty($ip1)){
                        $data['ipb_status'] = '';
                        $model->where("ipb_address = '%s'",$ip1)->save($data);
                    }
                    if(!empty($ip2)){
                        D('Home/Ip')->ipTo2($ip2);
                    }
                }
                writeWebLog('itIpbasesSuccess',['ipaddress1'=>$ip1,'ipaddress2'=>$ip2,'ipb_status1'=>'','ipb_status2'=>'2','is'=>$is]);
                $model->commit();
                return 'success';
            }else{
                writeWebLog('itIpbasesError',['ipaddress1'=>$ip1,'ipaddress2'=>$ip2,'is'=>$is]);
                $model->commit();
                return 'error';
            }
        } catch (\Exception $e) {
            $model->rollBack();
            writeWebLog('itIpbasesFail',['result'=>'error','detail'=>$e]);
            return "fail:$e";
        }
    }

    public function getDicId($name){
        $model = M('dic');
        $id = $model->where("dic_name = '%s'",$name)->getField('dic_id');
        return $id;
    }
    public function getLyDicId($name){
        $model = M('dic_louyu');
        $id = $model->where("dic_name = '%s'",$name)->getField('dic_id');
        return $id;
    }


    /**
     * 获取工单号
     * @param $bdid - 任务单号
     * @return string
     */
    function GetWorkID($bdid)
    {
        $tmpdate    = date("Ymd", time());
        $arrayid_bd = M('it_task')->field("t_taskid")->where("t_rwid ='".$bdid."'")->find();
        if (empty($arrayid_bd)) {
            $arrayid = M('it_task')->field("t_taskid")->where("t_taskid like '%IT-XZ-".$tmpdate."%'")->order("t_taskid desc")->find();
            if (empty($arrayid)) {
                $workid = 'IT-XZ-' . $tmpdate . '-001';
            } else {
                $tmp    = explode("-", $arrayid['t_taskid']);
                $num    = sprintf("%03d",($tmp[3] +1));
                $workid = 'IT-XZ-' . $tmpdate . '-' . $num;
            }
        }else{
            $workid = $arrayid_bd['t_taskid'];
        }
        return  $workid;
    }

     /**
     * xml 转换为 Array
     * @param $xmlFile
     * @return array
     */
    function xmlToArrays($xmlFile)
    {
        $data = json_decode($xmlFile, true);
        $UserDept = $this->FindPersonID($data['ApplyMan']);
        $dept = $this->removeStr($UserDept['fullname']);
        $xml_array = [];
        $xml_array['FormNo']         = $data['FormNo'];
        $xml_array['formtype']         = $data['BillType'];
        $xml_array['isCreate']         = $data['IsCreate'];
        $xml_array['NodeName']         = $data['NodeName'];
        $xml_array['BindID']         = $data['BindID'];
        $xml_array['BDSID']         = $data['BDSID'];
        $xml_array['FormStatus']   = $data['FormStatus'];
        $xml_array['PhoneNo']       = $data['PhoneNo'];
        $xml_array['ApplyMan']      = $data['ApplyMan'];
        $xml_array['ApplyTime']      = $data['ApplyTime'];
        $xml_array['ApplyDept']      = $dept;
        $xml_array['ApplyOffice']   = $UserDept['name'];
        $xml_array['IP']              = $data['IP'];
        $xml_array['MAC']             = $data['MAC'];
        $xml_array['roomno']           = isset($data['RoomNo'])?$data['RoomNo']:'';
        $xml_array['jharea']             = isset($data['JHAREA'])?$data['JHAREA']:'';
        $xml_array['buildingname']    = isset($data['BuildingName'])?$data['BuildingName']:'';
        foreach($xml_array as $k=>$v){
            if(empty($v)){
                $xml_array[$k] = '';
            }else{
                $xml_array[$k] = trim($v);
            }
        }
        return $xml_array ;
    }



    //去掉
    public function removeStr($str){
        if(mb_strstr($str,'五院本级') !== false){
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

        $name = explode('-',$name);
        $name = $name[count($name)-1];
        return $name;
    }

    /**
     * 通过mac或者ip查找atpid
     * @param $mac
     * @param $ip
     * @return string
     */
    function FindTerminalatpID($mac,$ip){
        $zd_atpid = '';
        $model = M('it_terminal');
        if(!empty($mac)){
            $mac = strtoupper($mac);
            $zd_atpid = $model->where("upper(zd_macaddress)='%s' and zd_atpstatus is null",$mac)->getField("zd_atpid");
        }else if(!empty($ip)){
            $zd_atpid = $model->where("zd_ipaddress='%s' and zd_atpstatus is null",$ip)->getField("zd_atpid");
        }
        return $zd_atpid;
    }

    /**
     * 查找人员表,$arrayid['ORGID'],$arrayid['FULLNAME']
     * @param $userid -域账户名
     * @return mixed|string
     */
    function FindPersonID($userid){
        if($userid==''){
            return "";
        } else {
            $userid = str_replace('-','\\',$userid);
            $arrayid = M('it_person p')->field("p.orgid,d.fullname,p.realusername,d.name")->join("inner join it_depart d on p.orgid = d.id")->where("p.domainusername='".$userid."'")->find();
            if (empty($arrayid)) {
                return "notfind";
            } else {
                return $arrayid;
            }
        }
    }
    /**
     * 查找配置文件，表单系统传递的字段（设备入网类型）为自编的值，存入配置文件，查询后可得到字典表ID
     * @param $id
     * @param $type
     * @return string
     */
    function FindBDID($id,$type){
        if (($id == '')||($id == '0')){
            $id = 0;
        }
        $equipmentType = C('EQUIPMENT_TYPE')[$type];
        if (empty($equipmentType) || empty($equipmentType[$id])) {
            return "notfind";
        } else {
            return $equipmentType[$id];
        }
    }
    /**
     * MAC地址转换
     * @param $mac
     * @return string
     */
    function ConverMAC($mac)
    {
        $macarray = explode('-',$mac);
        if(count($macarray) == 6) {
            $macaddress = strtoupper($macarray[0].$macarray[1].'.'.$macarray[2].$macarray[3].'.'.$macarray[4].$macarray[5]);
            return  $macaddress;
        }else{
            $macaddress=strtoupper($mac);
            return $macaddress;
        }
    }
    /**
     * 查字典表ID
     * @param $name -字典名称
     * @param $type -字典类型
     * @param string $pid -字典pid
     * @return string
     */
    function FindID($name,$type,$pid = '') //查找字典表
    {
        if ($name == '') return "";
        if($pid == ''){
            $arrayid = M('dictionary')->field("d_atpid")->where("d_dictname = '".$name."' and d_belongtype = '".$type."'")->find();
        }else{
            $arrayid = M('dictionary')->field("d_atpid")->where("d_dictname = '".$name."' and d_belongtype = '".$type."' and d_parentid='" .$pid. "'")->find();
        }
        if(empty($arrayid)){
            return "notfind";
        }else{
            return $arrayid['d_atpid'];
        }
    }

    /**
     * 新旧数据比较，返回日志的详细信息
     * @param $oldData
     * @param $data
     * @return string
     */
    function compareOldData($oldData,$data){
        $compareInfo      = C('COMPAREFIELD');
        $compareField     = array_keys($compareInfo);
        $detail           = '';
        foreach($compareField as $field){
            if(empty($oldData[$field]) && empty($data[$field])) continue;
            if($field == 'zd_useman'){
                if($oldData['zd_useman'] != $data['zd_useman']){
                    $detail .= $compareInfo[$field]."原值“".$oldData['zd_useman']."--".$oldData['zd_username']."--".$oldData['zd_usedeptname']."”修改成“".$data['zd_useman']."--".$data['zd_username']."--".$data['zd_usedeptname']."”，";
                }
            }else if($field == 'zd_dutyman'){
                if($oldData['zd_dutyman'] != $data['zd_dutyman']){
                    $detail .= $compareInfo[$field]."原值“".$oldData['zd_dutyman']."--".$oldData['zd_dutymanname']."--".$oldData['zd_dutydeptname']."”修改成“".$data['zd_dutyman']."--".$data['zd_dutymanname']."--".$data['zd_dutydeptname']."”，";
                }
            }else if($oldData[$field] != $data[$field]){
                $detail .= $compareInfo[$field]."字段原值“".$oldData[$field]."”修改成“".$data[$field]."”，";
            }
        }
        return $detail;
    }

    /**
     * 转换XML格式为数组
     * @param $xmlFile
     * @return array
     */
    function xmlequipmentToArrays($xmlFile)
    {
        $data                          = json_decode($xmlFile,true);
        $xml_array                     = [];
        $xml_array['FormNo']          = $data['FormNo'];
        $xml_array['BillType']        = $data['BillType'];
        $xml_array['NodeName']        = $data['NodeName'];
        $xml_array['UseMan']          = isset($data['UseMan'])?$data['UseMan']:'';
        $xml_array['DutyMan']         = isset($data['DutyMan'])?$data['DutyMan']:'';
        $xml_array['reason']          = isset($data['Reason'])?$data['Reason']:''; //设备、公共机、测试机入网原因字段,存到
        $xml_array['AssetType']       = isset($data['AssetType'])?$data['AssetType']:'';
        $xml_array['machineno']       = isset($data['DeviceCode'])?$data['DeviceCode']:'';
        $xml_array['AssetName']       = isset($data['AssetName'])?$data['AssetName']:'';
        $xml_array['ipaddress']       = isset($data['IP'])?$data['IP']:'';
        $xml_array['macaddress']      = isset($data['MAC'])?$data['MAC']:'';
        $xml_array['jharea']           = isset($data['JHAREA'])?$data['JHAREA']:'';
        $xml_array['buildingname']    = isset($data['BuildingName'])?$data['BuildingName']:'';
        $xml_array['roomno']           = isset($data['RoomNo'])?$data['RoomNo']:'';
        $xml_array['factory']          = isset($data['FactoryName'])?$data['FactoryName']:'';
        $xml_array['model']            = isset($data['ModelNumber'])?$data['ModelNumber']:'';
        $xml_array['Harddiskseq']     = isset($data['Harddiskseq'])?$data['Harddiskseq']:'';
        $xml_array['Osinstalltime']   = isset($data['Osinstalltime'])?$data['Osinstalltime']:'';
        $xml_array['SecretLevel']     = isset($data['SecretLevel'])?$data['SecretLevel']:'';
        $xml_array['isisolate']        = isset($data['isisolate'])?$data['isisolate']:'';
        $xml_array['isinstalljammer'] = isset($data['isinstalljammer'])?$data['isinstalljammer']:'';
        $xml_array['SKIP']              = isset($data['SKIP'])?$data['SKIP']:'';
        $xml_array['SKMAC']             = isset($data['SKMAC'])?$data['SKMAC']:'';
        $xml_array['OldIP']             = isset($data['OldIP'])?$data['OldIP']:'';
        $xml_array['OldMac']            = isset($data['OldMac'])?$data['OldMac']:'';
        $xml_array['OldSKIP']           = isset($data['OldSKIP'])?$data['OldSKIP']:'';
        $xml_array['OldSKMAC']          = isset($data['OldSKMAC'])?$data['OldSKMAC']:'';

        foreach($xml_array as $k=>$v){
            if(empty($v)){
                $xml_array[$k] = '';
            }else if(!is_array($v)){
                $xml_array[$k] = trim($v);
            }
        }
        return $xml_array ;
    }
}