<?php
namespace Cron\Controller;
use Think\Controller;
use Think\Exception;

class SyncMainDataController extends Controller{


    /**
     * 同步主数据内部组织
     */
    // public function syncOrg(){
    //     echo date('Y-m-d H:i:s').'开始同步部门主数据<br/>'.PHP_EOL;
    //     $webservice = getWebServiceObj('http://10.78.72.96:8989/pdservice?wsdl');
    //     $mainOrg = $webservice->GetData(['pvName' => 'CDC_PV_501SUPERSYSTEMCP_ORG', 'pwd' => '39114311BE982D2CE054D4C9EF06663E']);
    //     if(!empty($mainOrg->GetDataResult)){
    //         addLog('org', '部门主数据同步日志','获取部门主数据失败'.$mainOrg->GetDataResult, '失败');
    //         exit('数据获取失败：'.$mainOrg->GetDataResult);
    //     }
    //     set_time_limit(0);
    //     ini_set('memory_limit', '500M');
    //     $mainOrg = json_decode($mainOrg->retJson, true);
    //     //以主数据sid为键值，初始化一个数组
    //     $initMainOrg = [];
    //     foreach($mainOrg as $key=>$value){
    //         $initMainOrg[$value['ID']] = $value;
    //     }
    //     unset($mainOrg);

    //     //读取部门表数据
    //     $orgModel = M('org');
    //     $depts = $orgModel->field("org_sid,org_id")->select();

    //     //以部门表主数据sid字段为键值，初始化一个数组
    //     $initDepts = [];
    //     foreach($depts as $key=>$value){
    //         $initDepts[$value['org_sid']] = $value; //部门表 主数据id => 部门条目信息
    //     }
    //     unset($depts);

    //     $addCount = 0;
    //     $updateCount = 0;
    //     //遍历主数据，对比部门表，有则更新，无则新增
    //     foreach($initMainOrg as $key=>$value){
    //         $sid = $key; //主数据id
    //         $arr = [
    //             'org_name' => $value['NAME'],
    //             'org_fullname' => $value['FULLNAME'],
    //             'org_isavaliable' => intval($value['ISAVAILABLE']) == 1 ? '启用': '禁用',
    //             'org_fullnum' => $value['SORTNUM']
    //         ];
    //         if(isset($initDepts[$sid])){
    //             $arr['org_lastmodifytime'] = time();
    //             $arr['org_lastmodifyuser'] = 'system';
    //             $orgModel->where("org_sid = '%s'", $sid)->save($arr);
    //             $updateCount++;
    //         }else{
    //             $arr['org_id'] = makeGuid();
    //             $arr['org_createuser'] = 'system';
    //             $arr['org_createtime'] = time();
    //             $arr['org_sid'] = $sid;
    //             $orgModel->add($arr);
    //             $addCount++;

    //             //新增数据插入到数组中，避免二次查询数据库
    //             $initDepts[$sid] = [
    //                 'org_sid' => $sid,
    //                 'org_id' => $arr['org_id']
    //             ];
    //         }
    //     }
    //     //根据sid找到部门表与主数据的差集，删除
    //     $notExistMainDataDept = array_keys(array_diff_key($initDepts, $initMainOrg));
    //     $orgModel->where(['org_sid' => ['in', $notExistMainDataDept]])->delete();

    //     //遍历部门表数据,更新org_pid字段
    //     foreach($initDepts as $key=>$value){
    //         $sid = $value['org_sid'];
    //         $mainDataPid = $initMainOrg[$sid]['PID']; //以sid取得该部门对应的主数据pid
    //         $orgPid = $initDepts[$mainDataPid]['org_id']; //再以主数据id映射当前部门表的org_id
    //         $orgModel->where("org_sid = '%s'", $sid)->setField('org_pid', $orgPid);
    //     }
    //     addLog('org', '部门主数据同步日志','部门主数据同步成功，此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据', '成功');
    //     exit(date('Y-m-d H:i:s').'同步数据成功!此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据');
    // }

    /**
     * 同步主数据用户
     */
//     public function syncUser(){
//         echo date('Y-m-d H:i:s').'开始同步人员主数据'.PHP_EOL;
//         $webservice = getWebServiceObj('http://10.78.72.96:8989/pdservice?wsdl');
//         $mainOrg = $webservice->GetData(['pvName' => 'CDC_PV_501SUPERSYSTEMCP_USER', 'pwd' => '39114311BE982D2CE054D4C9EF06663E']);
//         if(!empty($mainOrg->GetDataResult)){
//             addLog('org', '人员主数据同步日志','获取人员主数据失败'.$mainOrg->GetDataResult, '失败');
//             exit('数据获取失败：'.$mainOrg->GetDataResult);
//         }
//         set_time_limit(0);
//         ini_set('memory_limit', '500M');
//         $mainUser = json_decode($mainOrg->retJson, true);
// //        $mainUser = [json_decode($mainOrg->retJson, true)[0]];
//         $initMainUser = [];
//         foreach($mainUser as $key=>$value){
//             $initMainUser[$value['ID']] = $value;
//         }
//         unset($mainUser);

//         //读取用户表数据
//         $userModel = M('sysuser');
//         $users = $userModel->where("user_issystem='否' and user_sid is not null")->field("user_id,user_sid")->select();

//         $initUsers = [];
//         foreach($users as $key=>$value){
//             $initUsers[$value['user_sid']] = $value;
//         }
//         unset($users);

//         $addCount = 0;
//         $updateCount = 0;

//         //遍历主数据，对比用户表，有则更新，无则新增
//         $orgModel = M('org');

//         //主数据密级 映射按顺序为 核心,重要,一般,无
//         $secretMap = [
//             '绝密' => '4',
//             '机密' => '3',
//             '秘密' => '2',
//             '非密' => '1'
//         ];

//         foreach($initMainUser as $key=>$value){
//             $sid = $key; //主数据id
//             $orgId = $orgModel->where("org_sid = '%s'", $value['ORGID'])->field('org_id')->getField('org_id');
//             $arr = [
//                 'user_name' => $value['DOMAINUSERNAME'],
//                 'user_realusername' => $value['REALUSERNAME'],
//                 'user_secretlevel' => isset($secretMap[$value['SECRETLEVEL']]) ?  $secretMap[$value['SECRETLEVEL']]: 1 ,
//                 'user_orgid' => $orgId,
//                 'user_enable' => intval($value['ISAVAILABLE']) == 1 ? '启用': '禁用'
//             ];
//             if(isset($initUsers[$sid])){
//                 $arr['user_secretlevelcode']=md5($initUsers[$sid]['user_id'].$arr['user_secretlevel']);
//                 $arr['user_lastmodifytime'] = time();
//                 $arr['user_lastmodifyuser'] = 'system';
//                 $userModel->where("user_sid = '$sid'")->save($arr);
//                 $updateCount++;
//             }else{
//                 $arr['user_id'] = makeGuid();
//                 $arr['user_createuser'] = 'system';
//                 $arr['user_createtime'] = time();
//                 $arr['user_sid'] = $sid;
//                 $arr['user_password'] = md5(C('PWD_SALT').md5('Guanli'.date('Y')));
//                 $arr['user_secretlevelcode']=md5($arr['user_id'].$arr['user_secretlevel']);

//                 $userModel->add($arr);
//                 $addCount++;
//             }
//         }
//         $disableIds = [];
//         $disableCount = 0;
//         //二次遍历，禁用人员账号
//         foreach($initUsers as $key=>$value){
//             if(!isset($initMainUser[$value['user_sid']]))
//             {
//                 $disableIds[] = $value['user_sid'];
//                 $disableCount++;
//             }
//         }

//         if(!empty($disableIds)) {
//             $userModel->where(['user_sid'=>['in',$disableIds]])->setField('user_enable','禁用');
//         }

//         addLog('org', '人员主数据同步日志','人员主数据同步成功，此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据', '成功');
//         exit(date('Y-m-d H:i:s').'同步数据成功!此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据');
//     }


    //同步运维4.0系统人员信息  it_person
    public function TbPerson(){
        echo date('Y-m-d H:i:s').'开始同步人员主数据'.PHP_EOL;
        $webservice = getWebServiceObj('http://10.78.72.96:8989/pdservice?wsdl');
        $mainOrg = $webservice->GetData(['pvName' => 'CDC_PV_WYYW4_USER', 'pwd' => '39114311BE982D2CE054D4C9EF06663E']);
        if(!empty($mainOrg->GetDataResult)){
            addLog('org', '人员主数据同步日志','获取人员主数据失败'.$mainOrg->GetDataResult, '失败');
            exit('数据获取失败：'.$mainOrg->GetDataResult);
        }
        set_time_limit(0);
        ini_set('memory_limit', '500M');
        $mainUser = json_decode($mainOrg->retJson, true);
        //读取用户表数据
        $userModel = M('it_person');
        $addCount = 0;
        $updateCount = 0;

        foreach($mainUser as $key=>$value){
            $users = $userModel->where("id = '%s'",$value['ID'])->field("id")->select();
            if($users){
                $data['domainusername'] = $value['DOMAINUSERNAME'];
                $data['username'] = $value['USERNAME'];
                $data['realusername'] = $value['REALUSERNAME'];
                $data['domainname'] = $value['DOMAINNAME'];
                $data['secretlevel'] = $value['SECRETLEVEL'];
                $data['orgid'] = $value['ORGID'];
                $data['isavailable'] = $value['ISAVAILABLE'];
                $data['groupname'] = $value['GROUPNAME'];
                $userModel->where("id = '%s'",$value['ID'])->save($data);
                $updateCount++;
            }else{
                $data['id'] = $value['ID'];
                $data['domainusername'] = $value['DOMAINUSERNAME'];
                $data['username'] = $value['USERNAME'];
                $data['realusername'] = $value['REALUSERNAME'];
                $data['domainname'] = $value['DOMAINNAME'];
                $data['secretlevel'] = $value['SECRETLEVEL'];
                $data['orgid'] = $value['ORGID'];
                $data['isavailable'] = $value['ISAVAILABLE'];
                $data['groupname'] = $value['GROUPNAME'];
                $userModel->add($data);
                $addCount++;
            }
        }
        addLog('person', '人员主数据同步日志','人员主数据同步成功，此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据', '成功');
        exit(date('Y-m-d H:i:s').'同步数据成功!此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据');
    }


    //同步运维4.0系统部门信息   it_depart
    public function TbDepart(){
        echo date('Y-m-d H:i:s').'开始同步部门主数据<br/>'.PHP_EOL;
        $webservice = getWebServiceObj('http://10.78.72.96:8989/pdservice?wsdl');
        $mainOrg = $webservice->GetData(['pvName' => 'CDC_PV_WYYW4_ORG', 'pwd' => '39114311BE982D2CE054D4C9EF06663E']);
        if(!empty($mainOrg->GetDataResult)){
            addLog('org', '部门主数据同步日志','获取部门主数据失败'.$mainOrg->GetDataResult, '失败');
            exit('数据获取失败：'.$mainOrg->GetDataResult);
        }
        set_time_limit(0);
        ini_set('memory_limit', '500M');
        $mainOrg = json_decode($mainOrg->retJson, true);

        //读取部门表数据
        $orgModel = M('it_depart');

        $addCount = 0;
        $updateCount = 0;
        //遍历主数据，对比部门表，有则更新，无则新增
        foreach($mainOrg as $key=>$value){
            $org = $orgModel->where("id = '%s'",$value['ID'])->field("id")->select();
            if($org){
                $data['name'] = $value['NAME'];
                $data['pid'] = $value['PID'];
                $data['fullname'] = $value['FULLNAME'];
                $data['fullnum'] = $value['FULLNUM'];
                $data['isavailable'] = $value['ISAVAILABLE'];
                $data['rootorgname'] = $value['ROOTORGNAME'];
                $data['rootorgid'] = $value['ROOTORGID'];
                $orgModel->where("id = '%s'",$value['ID'])->save($data);
                $updateCount++;
            }else{
                $data['id'] = $value['ID'];
                $data['name'] = $value['NAME'];
                $data['pid'] = $value['PID'];
                $data['fullname'] = $value['FULLNAME'];
                $data['fullnum'] = $value['FULLNUM'];
                $data['isavailable'] = $value['ISAVAILABLE'];
                $data['rootorgname'] = $value['ROOTORGNAME'];
                $data['rootorgid'] = $value['ROOTORGID'];
                $orgModel->add($data);
                $addCount++;
            }

        }
        addLog('org', '部门主数据同步日志','部门主数据同步成功，此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据', '成功');
        exit(date('Y-m-d H:i:s').'同步数据成功!此次同步新增'.$addCount.'条数据,更新'.$updateCount.'条数据');
    }
}