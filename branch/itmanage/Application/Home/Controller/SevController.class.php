<?php

namespace Home\Controller;

use Think\Controller;

class SevController extends BaseController
{
    //服务器管理
    public function index()
    {
        addLog("", "用户访问日志", "访问服务器资产管理界面页面", "成功");

        //字典
        $arr = ['密级', '地区','使用状态（物理服务器）','资产来源','设备类型（物理）','操作系统','是否','分类(物理服务器)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $factory = D('Dic')->getFactoryList('服务器');
        $this->assign('changJia', $factory);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('status', $arrDic['使用状态（物理服务器）']);
        $this->assign('zichan', $arrDic['资产来源']);
        $this->assign('sbType', $arrDic['设备类型（物理）']);
        $this->assign('caoZuoXiTong', $arrDic['操作系统']);
        $this->assign('is', $arrDic['是否']);
        $this->assign('type', $arrDic['分类(物理服务器)']);

        $this->display();
    }

    //虚拟服务器
    public function virtual()
    {
        addLog("", "用户访问日志",  "访问服务器资产管理界面页面", "成功");

        //字典
        $arr = ['密级', '地区','使用状态(虚拟服务器)','操作系统','是否','分类(虚拟服务器)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('status', $arrDic['使用状态(虚拟服务器)']);
        $this->assign('caoZuoXiTong', $arrDic['操作系统']);
        $this->assign('is', $arrDic['是否']);
        $this->assign('types', $arrDic['分类(虚拟服务器)']);
        $this->display();
    }

    /**
     * 服务器资产管理界面添加或修改
     */
    public function add()
    {
        $id = trim(I('get.sev_atpid'));
        $Objtype = trim(I('get.type'));
        if (!empty($id)) {
            $yxstatus = trim(I('get.yxstatus'));
            $model = M('it_sev');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('sev_atpid,sev_ip,sev_powernum,sev_toptype,sev_kvmnum,sev_directstorage,sev_disksn,sev_mask,sev_devicecode,sev_hostip,sev_mac,sev_net,sev_wwnno,sev_ilopass,sev_purchasetime,sev_dutyman,sev_disknum,sev_startusetime,sev_gateway,sev_modelnumber,sev_hbanum,sev_type,sev_useman,sev_kvmsw,sev_fsip,sev_subip,sev_powerport,sev_cpunum,sev_belongfloor,sev_submac,sev_roomno,sev_osinstalltime,sev_sn,sev_factory,sev_status,sev_cabloc,sev_app,sev_assetusedept,sev_os,sev_anecode,sev_area,sev_iloip,sev_assetsource,sev_ilomac,sev_remark,sev_name,sev_secretlevel,sev_assetdutydept,sev_swip,sev_swinterface,sev_cab,sev_dutydept,sev_usedept,sev_powergl,sev_isyuan,sev_iszhongxin')
                ->where("sev_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //使用人
            $userId = $data['sev_useman'];
            if (!empty($userId)) {
                $userName = D('org')->getViewPerson($userId);
                $userMan['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $userMan['username'] = $userName['username'];

                $userDept = $this->removeStr($userName['orgfullname']); //去掉字符串
            } else {
                $userMan = [];
                $userDept = '';
            }
            $this->assign('userman', $userMan);
            $this->assign('userDept', $userDept);

            //责任人
            $dutuserId = $data['sev_dutyman'];
            if (!empty($dutuserId)) {
                $dutuserName = D('org')->getViewPerson($dutuserId);
                $dutuser['name'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')--' . $dutuserName['orgname'];
                $dutuser['username'] = $dutuserName['username'];

                $dutyDept = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
            } else {
                $dutuser = [];
                $dutyDept = '';
            }
            $this->assign('dutuser', $dutuser);
            $this->assign('dutyDept', $dutyDept);

            //资产责任单位
            $deptId = $data['sev_assetdutydept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getDepartId($deptId);
                $dutydept['name'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //使用责任单位
            $deptId = $data['sev_assetusedept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getDepartId($deptId);

                $usedept['name'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                $usedept['id'] = $deptInfo['id'];
            } else {
                $usedept = [];
            }
            $this->assign('usedept', $usedept);

            //光纤交换机IP地址
//            $relation = D("relation")->getViewRelationInfo($id, 'it_netdevice');
//            $sev_fsip = '';
//            foreach ($relation as $v) {
//                $sev_fsip .= '(' . $v['r_ip'] . '),';
//            }
//            $sev_fsip = substr($sev_fsip, 0, -1);
//            $this->assign('sev_fsip', $sev_fsip);
//            //隐藏的id值
//            $sev_fsip_id = implode(',', array_column($relation, 'r_id'));
//            // dump($sev_fsip_id);die;
//            $this->assign('sev_fsip_id', $sev_fsip_id);

            //机柜
//            $relation = D("relation")->getViewRelationInfo($id, 'jigui');
//            $sev_cab = '';
//            foreach ($relation as $v) {
//                $sev_cab .= '(' . $v['r_name'] . '),';
//            }
//            $sev_cab = substr($sev_cab, 0, -1);
//            $this->assign('sev_cab', $sev_cab);
//            //隐藏的id值
//            $sev_cab_id = implode(',', array_column($relation, 'r_id'));
//            $this->assign('sev_cab_id', $sev_cab_id);

            //应用系统
//            $relation = D("relation")->getViewRelationInfo($id, 'it_application');
//            $sev_app = '';
//            foreach ($relation as $v) {
//                $sev_app .= '(' . $v['r_name'] . '),';
//            }
//            $sev_app = substr($sev_app, 0, -1);
//            $this->assign('sev_app', $sev_app);
//            //隐藏的id值
//            $sev_app_id = implode(',', array_column($relation, 'r_id'));
//            // dump($sev_app_id);die;
//            $this->assign('sev_app_id', $sev_app_id);
//
//            //服务器
//            $relation = D("relation")->getViewRelationInfo($id, 'it_sev');
//            $sev_hostip = '';
//            foreach ($relation as $v) {
//                $sev_hostip .= '(' . $v['r_ip'] . '),';
//            }
//            $sev_hostip = substr($sev_hostip, 0, -1);
//            $this->assign('sev_hostip', $sev_hostip);
//            //隐藏的id值
//            $sev_hostip_id = implode(',', array_column($relation, 'r_id'));
//            $this->assign('sev_hostip_id', $sev_hostip_id);

//            $ip = explode(';',$data['sev_ip']);
            $this->assign('yxStatus', $yxstatus);
            $list = M('it_relationx i')->field('rw_detail,rlx_useage')
                ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
                ->where("rlx_zyid = '%s' and rlx_atpstatus is null and rw_atpstatus is null",$id)
                ->select();
            $detail = '';
            foreach($list as $v){
                $detail.=$v['rw_detail'].'-'.$v['rlx_useage'].',';
            }
            $this->assign('detail',$detail);

        }

        $arr = ['密级', '地区', '厂家','分类(物理服务器)', '使用状态（物理服务器）','是否', '资产来源', '操作系统', '所属网络','设备类型（物理）','资产责任单位','使用责任单位'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $factory = D('Dic')->getFactoryList('服务器');

        $this->assign('Objtype', $Objtype);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $factory);
        $this->assign('zhuangTai', $arrDic['使用状态（物理服务器）']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);
        $this->assign('caoZuoXiTong', $arrDic['操作系统']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);
        $this->assign('sbType', $arrDic['设备类型（物理）']);
        $this->assign('zcDept', $arrDic['资产责任单位']);
        $this->assign('syDept', $arrDic['使用责任单位']);
        $this->assign('type', $arrDic['分类(物理服务器)']);
        $this->assign('is', $arrDic['是否']);

//        $this->assign('sev_type', '物理服务器');
        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问服务器资产管理界面添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 服务器资产管理界面添加或修改
     */
    public function virtualAdd()
    {
        $id = trim(I('get.sevv_atpid'));
        $Objtype = trim(I('get.type'));
        if (!empty($id)) {
            $model = M('it_sevv');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('sevv_name,sevv_ip,sevv_subip,sevv_toptype,sevv_mac,sevv_submac,sevv_os,sevv_osinstalltime,sevv_status,sevv_startusetime,sevv_useman,sevv_secretlevel,sevv_hostip,sevv_directstorage,sevv_supportdrift,sevv_cpunum,sevv_memory,sevv_type,sevv_mask,sevv_gateway,sevv_app,sevv_dutyman,sevv_atpid,sevv_remark,sevv_dutydept,sevv_usedept,sevv_isyuan,sevv_iszhongxin')
                ->where("sevv_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //使用人
            $userId = $data['sevv_useman'];
            if (!empty($userId)) {
                $userName = D('org')->getViewPerson($userId);
                $userMan['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $userMan['username'] = $userName['username'];

                $userDept = $this->removeStr($userName['orgfullname']); //去掉字符串
            } else {
                $userMan = [];
                $userDept = '';
            }
            $this->assign('userman', $userMan);
            $this->assign('userDept', $userDept);

            //责任人
            $dutuserId = $data['sevv_dutyman'];
            if (!empty($dutuserId)) {
                $dutuserName = D('org')->getViewPerson($dutuserId);
                $dutuser['name'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')--' . $dutuserName['orgname'];
                $dutuser['username'] = $dutuserName['username'];

                $userDept = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
            } else {
                $dutuser = [];
                $userDept = '';
            }
            $this->assign('dutuser', $dutuser);
            $this->assign('dutyDept', $userDept);

            //应用系统
            $relation = D("relation")->getViewRelationInfo($id, 'it_application');
            $sevv_app = '';
            foreach ($relation as $v) {
                $sevv_app .= '(' . $v['r_name'] . '),';
            }
            $sevv_app = substr($sevv_app, 0, -1);
            $this->assign('sevv_app', $sevv_app);
            //隐藏的id值
            $sevv_app_id = implode(',', array_column($relation, 'r_id'));
            // dump($sevv_app_id);die;
            $this->assign('sevv_app_id', $sevv_app_id);

            //服务器
            $relation = D("relation")->getViewRelationInfo($id, 'it_sev');
            $sevv_hostip = '';
            foreach ($relation as $v) {
                $sevv_hostip .= '(' . $v['r_ip'] . '),';
            }
            $sevv_hostip = substr($sevv_hostip, 0, -1);
            $this->assign('sevv_hostip', $sevv_hostip);
            //隐藏的id值
            $sevv_hostip_id = implode(',', array_column($relation, 'r_id'));
            $this->assign('sevv_hostip_id', $sevv_hostip_id);

            $list = M('it_relationx i')->field('rw_detail,rlx_useage')
                ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
                ->where("rlx_zyid = '%s' and rlx_atpstatus is null and rw_atpstatus is null",$id)
                ->select();
            $detail = '';
            foreach($list as $v){
                $detail.=$v['rw_detail'].'-'.$v['rlx_useage'].',';
            }
            $this->assign('detail',$detail);
        }


        $arr = ['密级', '使用状态(虚拟服务器)', '操作系统','设备类型（虚拟）','是否','分类(虚拟服务器)'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('Objtype', $Objtype);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态(虚拟服务器)']);
        $this->assign('caoZuoXiTong', $arrDic['操作系统']);
        $this->assign('sbType', $arrDic['设备类型（虚拟）']);
        $this->assign('is', $arrDic['是否']);
        $this->assign('type', $arrDic['分类(虚拟服务器)']);
        $this->assign('sevv_type', '虚拟服务器');
        $this->assign('data', $data);

        addLog('', '用户访问日志',"访问服务器资产管理界面添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['sev_atpid']);
        //验证ip
        if (!empty($data['sev_ip'])) {
            if ($this->checkAddress($data['sev_ip'], 'IP') === false) exit(makeStandResult(-1, 'ip地址有误'));
        }
        //子IP地址
        if (!empty($data['sev_subip'])) {
            $sev_subips  = str_replace(',',';',$data['sev_subip']);
            $sev_subips = explode(';',$sev_subips);
            foreach($sev_subips as $ip){
                if ($this->checkAddress($ip, 'IP') === false) exit(makeStandResult(-1, '子IP地址有误'));
            }
        }
        //光纤交换机IP地址
        if (!empty($data['sev_fsip'])) {
            $sev_fsips  = str_replace(',',';',$data['sev_fsip']);
            $sev_fsips = explode(';',$sev_fsips);
            foreach($sev_fsips as $ip){
                if ($this->checkAddress($ip, 'IP') === false) exit(makeStandResult(-1, '光纤交换机IP地址有误'));
            }
        }
        //交换机IP地址
        if (!empty($data['sev_swip'])) {
            $sev_swips  = str_replace(',',';',$data['sev_swip']);
            $sev_swips = explode(';',$sev_swips);
            foreach($sev_swips as $ip){
                if ($this->checkAddress($ip, 'IP') === false) exit(makeStandResult(-1, '交换机IP地址有误'));
            }
        }
        //iLoIP地址有误
        if (!empty($data['sev_iloip'])) {
            if ($this->checkAddress($data['sev_iloip'], 'IP') === false) exit(makeStandResult(-1, 'iLoIP地址有误'));
        }
        //KVM交换机有误
        if (!empty($data['sev_kvmsw'])) {
            if ($this->checkAddress($data['sev_kvmsw'], 'IP') === false) exit(makeStandResult(-1, 'KVM交换机有误'));
        }
        //默认网关有误
        if (!empty($data['sev_gateway'])) {
            if ($this->checkAddress($data['sev_gateway'], 'IP') === false) exit(makeStandResult(-1, '默认网关有误'));
        }

        //验证mac
        if (!empty($data['sev_mac'])) {
            if ($this->checkAddress($data['sev_mac'], 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        }
        //子mac地址有误
        if (!empty($data['sev_submac'])) {
            $sev_submacs  = str_replace(',',';',$data['sev_submac']);
            $sev_submacs = explode(';',$sev_submacs);
            foreach($sev_submacs as $ip) {
                if ($this->checkAddress($ip, 'MAC') === false) exit(makeStandResult(-1, '子mac地址有误'));
            }
        }
        //iLoMAC地址有误
        if (!empty($data['sev_ilomac'])) {
            if ($this->checkAddress($data['sev_ilomac'], 'MAC') === false) exit(makeStandResult(-1, 'iLoMAC地址有误'));
        }


     //验证硬盘数量
        $sev_disksn = $data['sev_disksn'];
        if (!empty($sev_disksn)) {
            if ($data['sev_disknum'] != count(explode(';', $sev_disksn))) {
                exit(makeStandResult(-1, '硬盘数量与硬盘序列号不符合，请以 ; 隔开'));
            }
            if($data['sev_disknum'] <= 0){
                exit(makeStandResult(-1, '硬盘数量必须大于0'));
            }
        }

        //验证HBA口数
        $sev_wwnno = $data['sev_wwnno'];
        if (!empty($sev_wwnno)) {
            if ($data['sev_hbanum'] != count(explode(';', $sev_wwnno))) {
                exit(makeStandResult(-1, 'HBA口数量与WWN号不符合，请以 ; 隔开'));
            }
            if($data['sev_hbanum'] <= 0){
                exit(makeStandResult(-1, 'HBA口数必须大于0'));
            }
        }

        //验证电源数量
        $sev_powernum = $data['sev_powernum'];
        if(!empty($sev_powernum)) {
            if ($sev_powernum <= 0) {
                exit(makeStandResult(-1, '电源数量必须大于0'));
            }
        }

        //验证电源功率数量
        $sev_powergl = $data['sev_powergl'];
        if(!empty($sev_powergl)) {
            if ($sev_powergl <= 0) {
                exit(makeStandResult(-1, '电源功率数量必须大于0'));
            }
        }

        //验证电源功率数量
        $sev_powergl = $data['sev_powergl'];
        if(!empty($sev_powergl)) {
            if ($sev_powergl <= 0) {
                exit(makeStandResult(-1, '电源功率数量必须大于0'));
            }
        }

        //验证cpu数量
        $sev_cpunum = $data['sev_cpunum'];
        if(!empty($sev_cpunum)){
            if($sev_cpunum <= 0){
                exit(makeStandResult(-1, 'CPU数量必须大于0'));
            }
        }

        //验证内存数量
        $sev_memory = $data['sev_memory'];
        if(!empty($sev_memory)){
            if($sev_memory <= 0){
                exit(makeStandResult(-1, '内存数量必须大于0'));
            }
        }

        $sev_subips  = str_replace(',',';',$data['sev_subip']);
        $subips = explode(';',$sev_subips);
        $ipIlop = [$data['sev_ip'],$data['sev_iloip']];
        $ip = array_merge($subips,$ipIlop);
        $ip = array_filter($ip);
        $ips = array_count_values($ip);
        foreach($ips as $key =>$val){
            if($val > 1){
                exit(makeStandResult(-1, '页面中填写的'.$key.'重复，请修改后提交'));
            }
        }

        $model = M('it_sev');
        $modelSub = M('subelement');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容

        $data['sev_mac'] = strtoupper($data['sev_mac']);
        $data['sev_submac'] = strtoupper($data['sev_submac']);
        $data['sev_ilomac'] = strtoupper($data['sev_ilomac']);
        $data['sev_area'] = $this->getDicById($data['sev_area'], 'dic_name'); //地区
        $data['sev_belongfloor'] = $this->getDicLouYuById($data['sev_belongfloor'], 'dic_name'); //楼宇
        $data['sev_factory'] = $this->getDicFactort($data['sev_factory'], 'dic_name'); //厂家
        $data['sev_modelnumber'] = $this->getDicXingHaoById($data['sev_modelnumber'], 'dic_name'); //型号
        $data['sev_dutydept'] = D('org')->getDeptId($data['sev_dutyman']);
        $data['sev_usedept'] = D('org')->getDeptId($data['sev_useman']);
        if (empty($id)) {
            $model->startTrans();
            try {
                //验证地址是否已被使用
                $Fx = D('ip')->addIpCs($ip, $data['sev_status']);
                if ($Fx != 'success') {
                    exit(makeStandResult(-1, $Fx . 'IP地址已被使用'));
                }
                $data['sev_atpid'] = makeGuid();
                //若子IP不为空，拆分子IP将相应信息写入subelement表中
                if (!empty($data['sev_subip'])) {
                    $subIP = str_replace(';', ',', $data['sev_subip']);
                    $subIP = explode(',', $subIP);
                    foreach ($subIP as $v) {
                        $list['sub_content'] = $v;
                        $list['sub_type'] = $data['sev_type'];
                        $list['sub_pid'] = $data['sev_atpid'];
                        $list['sub_num'] = ip2long($v);
                        $list['sub_field'] = 'sev_subip';
                        $modelSub->add($list);
                    }
                }
                //若子mac不为空，拆分子mac将相应信息写入subelement表中
                if (!empty($data['sev_submac'])) {
                    $submac = str_replace(';', ',', $data['sev_submac']);
                    $submac = explode(',', $submac);
                    foreach ($submac as $mac) {
                        $arr['sub_content'] = $mac;
                        $arr['sub_type'] = $data['sev_type'];
                        $arr['sub_pid'] = $data['sev_atpid'];
                        $arr['sub_field'] = 'sev_submac';
                        $modelSub->add($arr);
                    }
                }
                $data['sev_atpcreatetime'] = $time;
                $data['sev_atpcreateuser'] = $user;
                $data = $model->create($data);
                $res = $model->add($data);
                $model->commit();
                addLog('it_sev', '对象添加日志', '添加主键为'.$data['sev_atpid']. '成功', '成功',$data['sev_atpid']);
                exit(makeStandResult(1, '添加成功'));
            } catch (\Exception $e) {
                $model->rollback();
                addLog('it_sev', '对象添加日志', '添加主键为'.$data['sev_atpid']. '失败', '失败',$data['sev_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            }
        } else {
            $model->startTrans();
            try {
                $sevList = $model->where("sev_atpid = '%s'",$id)->find();
                $subipsUp  = str_replace(',',';',$sevList['sev_subip']);
                $subipsUp = explode(';',$subipsUp);
                $ipIlopUp = [$sevList['sev_ip'],$sevList['sev_iloip']];
                $ipUp = array_merge($subipsUp,$ipIlopUp);
                $ipUp = array_filter($ipUp);
                //验证ip是否已被使用
                $Fx = D('ip')->saveIpCs($ip,$ipUp,$data['sev_status']);
                if($Fx != 'success'){
                    exit(makeStandResult(-1, $Fx.'IP地址已被使用'));
                }

                $modelSub->where("sub_pid = '%s'",$data['sev_atpid'])->delete();
                //若子IP不为空，拆分子IP将相应信息写入subelement表中
                if(!empty($data['sev_subip'])){
                    $subIP  = str_replace(';',',',$data['sev_subip']);
                    $subIP = explode(',',$subIP);
                    foreach($subIP as $v){
                        $list['sub_content'] = $v;
                        $list['sub_type'] = $data['sev_type'];
                        $list['sub_pid']  = $data['sev_atpid'];
                        $list['sub_num'] = ip2long($v);
                        $list['sub_field'] = 'sev_subip';
                        $modelSub->add($list);
                    }
                }
                //若子mac不为空，拆分子mac将相应信息写入subelement表中
                if(!empty($data['sev_submac'])){
                    $submac  = str_replace(';',',',$data['sev_submac']);
                    $submac = explode(',',$submac);
                    foreach($submac as $mac){
                        $arr['sub_content'] = $mac;
                        $arr['sub_type'] = $data['sev_type'];
                        $arr['sub_pid']  = $data['sev_atpid'];
                        $arr['sub_field'] = 'sev_submac';
                        $modelSub->add($arr);
                    }
                }

                $data = $model->create($data);
                $lists = session('list');
                $content = LogContent($data,$lists);
                $data['sev_atpmodifytime'] = $time;
                $data['sev_atpmodifyuser'] = $user;
                $res = $model->where("sev_atpid='%s'", $id)->save($data);
                $model->commit();
                if(!empty($content)) {
                    addLog('it_sev', '对象修改日志', $content, '成功', $id);
                }
                exit(makeStandResult(1, '修改成功'));
            } catch (\Exception $e) {
                $model->rollback();
                addLog('it_sev', '对象修改日志', '主键为'.$id. '失败', '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            }
        }
    }

    /**
     * 虚拟数据添加、修改
     */
    public function addXnData()
    {
        $data = I('post.');
        $id = trim($data['sevv_atpid']);
        //验证ip
        if (!empty($data['sevv_ip'])) {
            if ($this->checkAddress($data['sevv_ip'], 'IP') === false) exit(makeStandResult(-1, 'ip地址有误'));
        }

        //子IP地址
        if (!empty($data['sevv_subip'])) {
            $sev_subips  = str_replace(',',';',$data['sevv_subip']);
            $sev_subips = explode(';',$sev_subips);
            foreach($sev_subips as $ip){
                if ($this->checkAddress($ip, 'IP') === false) exit(makeStandResult(-1, '子IP地址有误'));
            }
        }

        //子网掩码
        if (!empty($data['sevv_mask'])) {
            if ($this->checkAddress($data['sevv_mask'], 'IP') === false) exit(makeStandResult(-1, '子网掩码有误'));
        }
        //默认网关有误
        if (!empty($data['sevv_gateway'])) {
            if ($this->checkAddress($data['sevv_gateway'], 'IP') === false) exit(makeStandResult(-1, '默认网关有误'));
        }

        //验证mac
        if (!empty($data['sevv_mac'])) {
            if ($this->checkAddress($data['sevv_mac'], 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        }

        if(empty($data['sevv_status'])){
            exit(makeStandResult(-1, '使用状态不能为空'));
        }

        //验证cpu数量
        $sev_cpunum = $data['sevv_cpunum'];
        if(!empty($sev_cpunum)){
            if($sev_cpunum <= 0){
                exit(makeStandResult(-1, 'CPU数量必须大于0'));
            }
        }

        //验证内存数量
        $sev_memory = $data['sevv_memory'];
        if(!empty($sev_memory)){
            if($sev_memory <= 0){
                exit(makeStandResult(-1, '内存数量必须大于0'));
            }
        }

        //验证硬盘数量
        $sev_memory = $data['sevv_disk'];
        if(!empty($sev_memory)){
            if($sev_memory <= 0){
                exit(makeStandResult(-1, '硬盘数量必须大于0'));
            }
        }


        $sev_subips  = str_replace(',',';',$data['sevv_subip']);
        $subips = explode(';',$sev_subips);
        $ipIlop = [$data['sevv_ip']];
        $ip = array_merge($subips,$ipIlop);
        $ip = array_filter($ip);
        $ips = array_count_values($ip);
        foreach($ips as $key =>$val){
            if($val > 1){
                exit(makeStandResult(-1, '页面中填写的'.$key.'重复，请修改后提交'));
            }
        }

        $model = M('it_sevv');
        $modelSub = M('subelement');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容

        $data['sevv_mac'] = strtoupper($data['sevv_mac']);
        $data['sevv_dutydept'] = D('org')->getDeptId($data['sevv_dutyman']);
        $data['sevv_usedept'] = D('org')->getDeptId($data['sevv_useman']);
        if (empty($id)) {
            $model->startTrans();
            try {
                $Fx = D('ip')->addIpCs($ip,$data['sevv_status']);
                if($Fx != 'success'){
                    exit(makeStandResult(-1, $Fx.'IP地址已被使用'));
                }
                $data['sevv_atpid'] = makeGuid();

                //若子IP不为空，拆分子IP将相应信息写入subelement表中
                if(!empty($data['sevv_subip'])){
                    $subIP  = str_replace(';',',',$data['sevv_subip']);
                    $subIP = explode(',',$subIP);
                    foreach($subIP as $v){
                        $list['sub_content'] = $v;
                        $list['sub_type'] = $data['sevv_type'];
                        $list['sub_pid']  = $data['sevv_atpid'];
                        $list['sub_num'] = ip2long($v);
                        $list['sub_field'] = 'sevv_subip';
                        $modelSub->add($list);
                    }
                }
                //若子mac不为空，拆分子mac将相应信息写入subelement表中
                if(!empty($data['sevv_submac'])){
                    $submac  = str_replace(';',',',$data['sevv_submac']);
                    $submac = explode(',',$submac);
                    foreach($submac as $mac){
                        $arr['sub_content'] = $mac;
                        $arr['sub_type'] = $data['sevv_type'];
                        $arr['sub_pid']  = $data['sevv_atpid'];
                        $arr['sub_field'] = 'sevv_submac';
                        $modelSub->add($arr);
                    }
                }

                $data['sevv_atpcreatetime'] = $time;
                $data['sevv_atpcreateuser'] = $user;
                $data = $model->create($data);
                $res = $model->add($data);
                $model->commit();
                addLog('it_sevv', '对象添加日志',  '添加主键为'.$data['sevv_atpid'] .  '成功', '成功',$data['sevv_atpid']);
                exit(makeStandResult(1, '添加成功'));
            } catch (\Exception $e) {
                $model->rollback();
                // 修改日志
                addLog('it_sevv', '对象添加日志', '添加主键为'.$data['sevv_atpid'] . '失败', '失败',$data['sevv_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            }

        } else {
            $model->startTrans();
            try {
                $sevList = $model->where("sevv_atpid = '%s'",$id)->find();
                $subipsUp  = str_replace(',',';',$sevList['sevv_subip']);
                $subipsUp = explode(';',$subipsUp);
                $ipIlopUp = [$sevList['sevv_ip']];
                $ipUp = array_merge($subipsUp,$ipIlopUp);
                $ipUp = array_filter($ipUp);
                $Fx = D('ip')->saveIpCs($ip,$ipUp,$data['sevv_status']);
                if($Fx != 'success'){
                    exit(makeStandResult(-1, $Fx.'IP地址已被使用'));
                }

                $modelSub->where("sub_pid = '%s'",$data['sevv_atpid'])->delete();
                //若子IP不为空，拆分子IP将相应信息写入subelement表中
                if(!empty($data['sevv_subip'])){
                    $subIP  = str_replace(';',',',$data['sevv_subip']);
                    $subIP = explode(',',$subIP);
                    foreach($subIP as $v){
                        $list['sub_content'] = $v;
                        $list['sub_type'] = $data['sevv_type'];
                        $list['sub_pid']  = $data['sevv_atpid'];
                        $list['sub_num'] = ip2long($v);
                        $list['sub_field'] = 'sevv_subip';
                        $modelSub->add($list);
                    }
                }
                //若子mac不为空，拆分子mac将相应信息写入subelement表中
                if(!empty($data['sevv_submac'])){
                    $submac  = str_replace(';',',',$data['sevv_submac']);
                    $submac = explode(',',$submac);
                    foreach($submac as $mac){
                        $arr['sub_content'] = $mac;
                        $arr['sub_type'] = $data['sevv_type'];
                        $arr['sub_pid']  = $data['sevv_atpid'];
                        $arr['sub_field'] = 'sevv_submac';
                        $modelSub->add($arr);
                    }
                }

                $data = $model->create($data);
                $list = session('list');
                $content = LogContent($data,$list);
                $data['sevv_atpmodifytime'] = $time;
                $data['sevv_atpmodifyuser'] = $user;
                $res = $model->where("sevv_atpid='%s'", $id)->save($data);
                $model->commit();
                if(!empty($content)) {
                    addLog('it_sevv', '对象修改日志', $content, '成功', $id);
                }
                exit(makeStandResult(1, '修改成功'));
            } catch (\Exception $e) {
                $model->rollback();
                // 修改日志
                addLog('it_sevv', '对象修改日志','修改主键为'.$id . '失败', '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            }
        }
    }

    /**
     * 获取服务器资产管理界面数据
     */
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'sev_atpid,sev_name,sev_ip,sev_mac,sev_type,sev_belongfloor,sev_roomno,sev_useman,sev_dutyman,sev_remark,sev_factory,sev_modelnumber,sev_sn,sev_status,sev_secretlevel,sev_assetsource,sev_assetdutydept,sev_assetusedept,sev_purchasetime,sev_startusetime,sev_disknum,sev_disksn,sev_cpunum,sev_hbanum,sev_wwnno,sev_fsip,sev_fsinterface,sev_powernum,sev_powerport,sev_powergl,sev_area,sev_cab,sev_cabloc,sev_app,sev_kvmsw,sev_kvmnum,sev_dutydept,sev_usedept,sev_os,sev_osinstalltime,sev_swinterface,sev_net,sev_ilopass,sev_directstorage,sev_swip,sev_devicecode,sev_anecode,sev_mask,sev_gateway,sev_subip,sev_submac,sev_iloip,sev_ilomac,sev_isyuan,sev_iszhongxin,sev_toptype';
        } else {
            $filedStr = 'sev_name,sev_ip,sev_mac,sev_type,sev_belongfloor,sev_roomno,sev_useman,sev_dutyman,sev_remark,sev_factory,sev_modelnumber,sev_sn,sev_status,sev_secretlevel,sev_assetsource,sev_assetdutydept,sev_assetusedept,sev_purchasetime,sev_startusetime,sev_disknum,sev_disksn,sev_cpunum,sev_hbanum,sev_wwnno,sev_fsip,sev_fsinterface,sev_powernum,sev_powerport,sev_powergl,sev_area,sev_cab,sev_cabloc,sev_kvmsw,sev_kvmnum,sev_dutydept,sev_usedept,sev_app,sev_os,sev_osinstalltime,sev_swinterface,sev_net,sev_ilopass,sev_hostip,sev_directstorage,sev_swip,sev_devicecode,sev_anecode,sev_mask,sev_gateway,sev_subip,sev_submac,sev_iloip,sev_ilomac, sev_atpid,sev_isyuan,sev_iszhongxin,sev_toptype';
            $queryParam = I('put.');
        }
        //过滤方法这里统一为trim，请根据实际需求更改
        $sev_devicecode = strtolower(trim($queryParam['sev_devicecode']));
        if (!empty($sev_devicecode)) $where['lower(sev_devicecode)'] = ['like', "%$sev_devicecode%"];

        $sev_anecode = strtolower(trim($queryParam['sev_anecode']));
        if (!empty($sev_anecode)) $where['lower(sev_anecode)'] = ['like', "%$sev_anecode%"];

        $sevType = trim($queryParam['sev_type']);
        if (!empty($sevType)) $where['sev_type'] = ['eq', $sevType];

        $Factory = trim($queryParam['sev_factory']);
        if (!empty($Factory)) {
            $Factory = $this->getFactoryById($Factory, 'dic_name') ; //厂家
            $where['sev_factory'] = ['like', "%$Factory%"];
        }

        $netModel = trim($queryParam['sev_modelnumber']);
        if (!empty($netModel)) {
            $netModel = $this->getDicXingHaoById($netModel, 'dic_name') ; //型号
            $where['sev_modelnumber'] = ['like', "%$netModel%"];
        }

        $sev_sn = strtolower(trim($queryParam['sev_sn']));
        if (!empty($sev_sn)) $where['lower(sev_sn)'] = ['like', "%$sev_sn%"];


        $sevIp = trim($queryParam['sev_ip']);
        if (!empty($sevIp)) {
            $where[0]['sev_ip'] = ['like', "%$sevIp%"];
            $where[0]['sev_subip'] = ['like', "%$sevIp%"];
            $where[0]['sev_iloip'] = ['like', "%$sevIp%"];
            $where[0]['_logic'] = 'OR';
        }

        $sevMac = strtolower(trim($queryParam['sev_mac']));
        if (!empty($sevMac)) {
            $where[1]['lower(sev_mac)'] = ['like', "%$sevMac%"];
            $where[1]['lower(sev_submac)'] = ['like', "%$sevMac%"];
            $where[1]['lower(sev_ilomac)'] = ['like', "%$sevMac%"];
            $where[1]['_logic'] = 'OR';
        }

        $sev_yxstatus = trim($queryParam['sev_yxstatus']);
        if(!empty($sev_yxstatus)){
            $Ipmodel = M('scanserver');
            $list = $Ipmodel->query("select x.* from scanserver x,
(
       SELECT max(ss_atpcreatedatetime) ss_atpcreatedatetime, ss_ipaddress
                    FROM scanserver
                    GROUP BY ss_ipaddress
) y
                    where x.ss_ipaddress = y.ss_ipaddress and x.ss_atpcreatedatetime=y.ss_atpcreatedatetime and x.ss_status = 1");
            $ips = removeArrKey($list,'ss_ipaddress');
            $ipArr = array_chunk($ips,1000);
            $count = count($ipArr)-1;
            if($sev_yxstatus == 1){
                for($i=0;$i<= $count;$i++){
                    $where[2][$i]['sev_ip'] = ['in',$ipArr[$i]];
                }
                $where[2]['_logic'] = 'or';
            }else{
                for($i=0;$i<= $count;$i++){
                    $where[3][2]['sev_ip'][$i] = ['not in',$ipArr[$i]];
                }
                $where[3][2]['_logic'] = 'AND';

                $where[3]['sev_ip'] = ['exp','is null'];
                $where[3]['_logic'] = 'OR';
            }
        }

        $sev_atpstatus = trim($queryParam['sev_atpstatus']);
        if (!empty($sev_atpstatus)) $where['sev_status'] = ['like', "%$sev_atpstatus%"];

        $sevDutyman = trim($queryParam['sev_dutyman']);
        if (!empty($sevDutyman)) $where['sev_dutyman'] = ['like', "%$sevDutyman%"];

        $sevDutydept = trim($queryParam['sev_dutydept']);
        if (!empty($sevDutydept)) {
            $sql = "select id from it_depart start with id= '$sevDutydept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['sev_dutydept'] = ['in', $ids];
        }
        $sevUseman = trim($queryParam['sev_useman']);
        if (!empty($sevUseman)) $where['sev_useman'] = ['like', "%$sevUseman%"];

        $sevUsedept = trim($queryParam['sev_usedept']);
        if (!empty($sevUsedept)) {
            $sql = "select id from it_depart start with id= '$sevUsedept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['sev_usedept'] = ['in', $ids];
        }

        $sevSecretlevel = trim($queryParam['sev_secretlevel']);
        if (!empty($sevSecretlevel)) $where['sev_secretlevel'] = ['like', "%$sevSecretlevel%"];

        $sev_isyuan = trim($queryParam['sev_isyuan']);
        if (!empty($sev_isyuan)) $where['sev_isyuan'] = ['like', "%$sev_isyuan%"];

        $sev_iszhongxin = trim($queryParam['sev_iszhongxin']);
        if (!empty($sev_iszhongxin)) $where['sev_iszhongxin'] = ['like', "%$sev_iszhongxin%"];

        $sevArea = trim($queryParam['sev_area']);
        if (!empty($sevArea)) {
            $sevArea = $this->getDicById($sevArea, 'dic_name') ; //地区
            $where['sev_area'] = ['like', "%$sevArea%"];
        }

        $sevBelongfloor = trim($queryParam['sev_belongfloor']);
        if (!empty($sevBelongfloor)) {
            $sevBelongfloor = $this->getDicLouYuById($sevBelongfloor, 'dic_name') ; //楼宇
            $where['sev_belongfloor'] = ['like', "%$sevBelongfloor%"];
        }

        $sev_cab = strtolower(trim($queryParam['sev_cab']));
        if (!empty($sev_cab)) $where['lower(sev_cab)'] = ['like', "%$sev_cab%"];

        $sev_cabloc = strtolower(trim($queryParam['sev_cabloc']));
        if (!empty($sev_cabloc)) $where['lower(sev_cabloc)'] = ['like', "%$sev_cabloc%"];

        $sev_toptype = strtolower(trim($queryParam['sev_toptype']));
        if (!empty($sev_toptype)) $where['sev_toptype'] = ['like', "%$sev_toptype%"];

        $sev_assetsource = trim($queryParam['sev_assetsource']);
        if (!empty($sev_assetsource)) $where['sev_assetsource'] = ['like', "%$sev_assetsource%"];

        $sev_os = trim($queryParam['sev_os']);
        if (!empty($sev_os)) $where['sev_os'] = ['like', "%$sev_os%"];

        $sev_fsip = trim($queryParam['sev_fsip']);
        if (!empty($sev_fsip)) $where['sev_fsip'] = ['like', "%$sev_fsip%"];

        $sev_directstorage = trim($queryParam['sev_directstorage']);
        if (!empty($sev_directstorage)) $where['sev_directstorage'] = ['like', "%$sev_directstorage%"];

        $where['sev_atpstatus'] = ['exp', 'IS NULL'];
        $sev_sx = trim($queryParam['sev_sx']);
        if (!empty($sev_sx)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '物理服务器'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');


            $rlxArr = array_chunk($rlx_zyids,1000);
            $count = count($rlxArr)-1;
            if($sev_sx == 1){
                for($i=0;$i<= $count;$i++){
                    $where[5][$i]['sev_atpid'] = ['in',$rlxArr[$i]];
                }
                $where[5]['_logic'] = 'or';
            }else{
                for($i=0;$i<= $count;$i++){
                    $where[6]['sev_atpid'][$i] = ['not in',$rlxArr[$i]];
                }
                $where[6]['_logic'] = 'AND';
            }


//            if($sev_sx == '1'){
//                $where['sev_atpid'] = ['in', $rlx_zyids];
//            }else{
//                $where['sev_atpid'] = ['not in', $rlx_zyids];
//            }
        }
        $model = M('it_sev');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if ($isExport) {
            set_time_limit(0);
            $data = $obj->select();

            $header = ['主数据ID','服务器名称', 'IP地址', 'MAC地址','设备类型', '楼宇', '房间号', '使用人', '责任人', '备注', '厂家', '型号', '出厂编号', '使用状态', '密级', '资产来源', '资产责任单位', '使用责任单位', '采购日期', '启用日期', '硬盘块数', '硬盘序列号', 'CPU颗数', 'HBA口数', 'WWN号', '光纤交换机IP地址', '光纤交换机端口','电源数量', '电源连接端口', '电源功率','地区', '机柜', '机位','服务名称', 'KVM交换机', 'KVM口', '责任部门', '使用部门', '操作系统', '操作系统安装时间', '交换机端口', '所属网络', 'iLo密码提示', '直连存储', '交换机IP', '设备编码', '部标编码', '子网掩码', '默认网关', '子IP地址', '子MAC地址', 'iLoIP地址', 'iLoMAC地址','是否院管','是否中心管','分类','运行状态'];

            foreach ($data as $k => &$v) {
                //使用人
                if (!empty($v['sev_useman'])) {
                    $userName = D('org')->getViewPerson($v['sev_useman']);
                    $v['sev_useman'] = $userName['realusername'];

                    //使用人部门
                    $v['sev_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sev_useman'] = '-';
                    $v['sev_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sev_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sev_dutyman']);
                    $v['sev_dutyman'] = $userName['realusername'];
                    $v['sev_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                }

                //资产责任单位
//                if (!empty($v['sev_assetdutydept'])) {
//                    $deptInfo = D('org')->getDepartId($v['sev_assetdutydept']);
//                    $v['sev_assetdutydept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
//                } else {
//                    $v['sev_assetdutydept'] = '-';
//                }

                //使用责任单位
//                if (!empty($v['sev_assetusedept'])) {
//                    $deptInfo = D('org')->getDepartId($v['sev_assetusedept']);
//                    $v['sev_assetusedept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
//                } else {
//                    $v['sev_assetusedept'] = '-';
//                }
                $ip = explode(';',$v['sev_ip']);
                $serverModel = M('scanserver');
                $status = [];
                foreach($ip as $val){
                    $status[] = $serverModel->field('ss_status')->where("ss_ipaddress = '%s'",$val)->order('ss_atpcreatedatetime desc')->limit(1)->find();
                }
                $status =removeArrKey($status,'ss_status');
                if(in_array('1',$status)){
                    $data[$k]['ss_status'] = '开机';
                }else{
                    $data[$k]['ss_status'] = '关机';
                }

                //光纤交换机IP地址
//                $relation = D("relation")->getViewRelationInfo($v['sev_atpid'], $v['sev_fsip']);
//                $sev_fsip = '';
//                foreach ($relation as $val) {
//                    $sev_fsip .= '(' . $val['r_ip'] . '),';
//                }
//                $sev_fsip = substr($sev_fsip, 0, -1);
//                $data[$k]['sev_fsip']  = $sev_fsip;


                //机柜
//                $relation = D("relation")->getViewRelationInfo($v['sev_atpid'], 'jigui');
//                $sev_cab = '';
//                foreach ($relation as $val) {
//                    $sev_cab .= '(' . $val['r_name'] . '),';
//                }
//                $sev_cab = substr($sev_cab, 0, -1);
//                $data[$k]['sev_cab']  = $sev_cab;



                //应用系统
//                $relation = D("relation")->getViewRelationInfo($v['sev_atpid'], 'it_application');
//                $sev_app = '';
//                foreach ($relation as $val) {
//                    $sev_app .= '(' . $val['r_name'] . '),';
//                }
//                $sev_app = substr($sev_app, 0, -1);
//                $data[$k]['sev_app']  = $sev_app;
//
//                //服务器
//                $relation = D("relation")->getViewRelationInfo($v['sev_atpid'], 'it_sev');
//                $sev_hostip = '';
//                foreach ($relation as $val) {
//                    $sev_hostip .= '(' . $val['r_ip'] . '),';
//                }
//                $sev_hostip = substr($sev_hostip, 0, -1);
//                $data[$k]['sev_hostip']  = $sev_hostip;

//                unset($data[$k]['sev_atpid']);

            }
//            print_r($data);die;
            if ($count <= 0) {
                exit(makeStandResult(-1, '没有要导出的数据'));
            } else if ($count > 1000) {
                csvExport($header, $data, true);
            } else {
                excelExport($header, $data, true);
            }
        } else {
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
//            print_r($model->getLastSql());die;
            //            var_dump($data);die;
            //            $sql = $obj->getLastSql();

            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['sev_area'] = !empty($v['sev_area']) ? $this->getDicById($v['sev_area'], 'dic_name') : '-'; //地区
//                $v['sev_belongfloor'] = !empty($v['sev_belongfloor']) ? $this->getDicLouYuById($v['sev_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['sev_factory'] = !empty($v['sev_factory']) ? $this->getDicById($v['sev_factory'], 'dic_name') : '-'; //厂家
//                $v['sev_modelnumber'] = !empty($v['sev_modelnumber']) ? $this->getDicXingHaoById($v['sev_modelnumber'], 'dic_name') : '-'; //型号
//                $v['sev_status'] = !empty($v['sev_status']) ? $this->getDicById($v['sev_status'], 'dic_name') : '-'; //状态
//                $v['sev_secretlevel'] = !empty($v['sev_secretlevel']) ? $this->getDicById($v['sev_secretlevel'], 'dic_name') : '-'; //密级
//                $v['sev_assetsource'] = !empty($v['sev_assetsource']) ? $this->getDicById($v['sev_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['sev_os'] = !empty($v['sev_os']) ? $this->getDicById($v['sev_os'], 'dic_name') : '-'; //操作系统
//                $v['sev_net'] = !empty($v['sev_net']) ? $this->getDicById($v['sev_net'], 'dic_name') : '-'; //所属网络

                $ip = explode(';',$v['sev_ip']);
                $serverModel = M('scanserver');
                $status = [];
                foreach($ip as $val){
                    $status[] = $serverModel->field('ss_status')->where("ss_ipaddress = '%s'",$val)->order('ss_atpcreatedatetime desc')->limit(1)->find();
                }
                $status =removeArrKey($status,'ss_status');
                if(in_array('1',$status)){
                    $data[$k]['ss_status'] = '开机';
                }else{
                    $data[$k]['ss_status'] = '关机';
                }
                //使用人
                if (!empty($v['sev_useman'])) {
                    $userName = D('org')->getViewPerson($v['sev_useman']);
                    $v['sev_useman'] = $userName['realusername'];

                    //使用人部门
                    $v['sev_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sev_useman'] = '-';
                    $v['sev_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sev_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sev_dutyman']);
                    $v['sev_dutyman'] = $userName['realusername'];

                    //责任人部门
                    $v['sev_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sev_dutyman'] = '-';
                    $v['sev_dutydept'] = '-';
                }
                //资产责任单位
//                if (!empty($v['sev_assetdutydept'])) {
//                    $deptInfo = D('org')->getDepartId($v['sev_assetdutydept']);
//                    $v['sev_assetdutydept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
//                } else {
//                    $v['sev_assetdutydept'] = '-';
//                }
//
//                //使用责任单位
//                if (!empty($v['sev_assetusedept'])) {
//                    $deptInfo = D('org')->getDepartId($v['sev_assetusedept']);
//                    $v['sev_assetusedept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
//                } else {
//                    $v['sev_assetusedept'] = '-';
//                }
                //光纤交换机关联关系
//                $netAll = D("relation")->getViewRelationInfo($v['sev_atpid'], 'it_netdevice');
//                if (!empty($netAll)) {
//                    $v['sev_fsip'] = '';
//                    foreach ($netAll as $val) {
//                        $v['sev_fsip'] .= '(' . $val['r_ip'] . '),';
//                    }
//                    $v['sev_fsip'] = substr($v['sev_fsip'], 0, -1);
//                } else {
//                    $v['sev_fsip'] = '-';
//                }
                //机柜关联关系
//                $jgAll = D("relation")->getViewRelationInfo($v['sev_atpid'], 'jigui');
//                if (!empty($jgAll)) {
//                    $v['sev_cab'] = '';
//                    foreach ($jgAll as $val) {
//                        $v['sev_cab'] .= '(' . $val['r_name'] . '),';
//                    }
//                    $v['sev_cab'] = substr($v['sev_cab'], 0, -1);
//                } else {
//                    $v['sev_cab'] = '-';
//                }
                //应用系统关联关系
//                $appAll = D("relation")->getViewRelationInfo($v['sev_atpid'], 'it_application');
//                if (!empty($appAll)) {
//                    $v['sev_app'] = '';
//                    foreach ($appAll as $val) {
//                        $v['sev_app'] .= '(' . $val['r_name'] . '),';
//                    }
//                    $v['sev_app'] = substr($v['sev_app'], 0, -1);
//                } else {
//                    $v['sev_app'] = '-';
//                }
                //服务器关联关系
                $jgAll = D("relation")->getViewRelationInfo($v['sev_atpid'], 'it_sev');
                if (!empty($jgAll)) {
                    $v['sev_hostip'] = '';
                    foreach ($jgAll as $val) {
                        $v['sev_hostip'] .= '(' . $val['r_ip'] . '),';
                    }
                    $v['sev_hostip'] = substr($v['sev_hostip'], 0, -1);
                } else {
                    $v['sev_hostip'] = '-';
                }

                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['sev_atpid'])->count();
                $data[$k]['sxCount'] = $rlxCount;
            }
            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }

    /**
     * 获取虚拟服务器资产管理界面数据
     */
    public function getXnData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'sevv_atpid,sevv_name,sevv_type,sevv_ip,sevv_subip,sevv_mac,sevv_submac,sevv_secretlevel,sevv_dutyman,sevv_dutydept,sevv_useman,sevv_usedept,sevv_status,sevv_startusetime,sevv_remark,sevv_directstorage,sevv_app,sevv_memory,sevv_hostip,sevv_supportdrift,sevv_cpunum,sevv_mask,sevv_gateway,sevv_osinstalltime,sevv_os,sevv_isyuan,sevv_iszhongxin,sevv_toptype';
        } else {
            $filedStr = 'sevv_name,sevv_ip,sevv_subip,sevv_mac,sevv_startusetime,sevv_submac,sevv_secretlevel,sevv_dutyman,sevv_useman,sevv_status,sevv_remark,sevv_directstorage,sevv_app,sevv_memory,sevv_hostip,sevv_supportdrift,sevv_mask,sevv_gateway,sevv_cpunum,sevv_osinstalltime,sevv_os,sevv_atpid,sevv_type,sevv_isyuan,sevv_iszhongxin,sevv_toptype';
            $queryParam = I('put.');
        }
        //过滤方法这里统一为trim，请根据实际需求更改
//        $sevType = isset($_GET['sevv_type']) ? $_GET['sevv_type'] : $_POST['sevv_type'];
        $where['sevv_atpstatus'] = ['exp', 'IS NULL'];

        $sevIp = trim($queryParam['sevv_ip']);
        if (!empty($sevIp)) {
            $where[0]['sevv_ip'] = ['like', "%$sevIp%"];
            $where[0]['sevv_subip'] = ['like', "%$sevIp%"];
            $where[0]['_logic'] = 'OR';
        }

        $sevMac = strtolower(trim($queryParam['sevv_mac']));
        if (!empty($sevMac)) {
            $where[1]['lower(sevv_mac)'] = ['like', "%$sevMac%"];
            $where[1]['lower(sevv_submac)'] = ['like', "%$sevMac%"];
            $where[1]['_logic'] = 'OR';
        }

        $sevv_os = trim($queryParam['sevv_os']);
        if (!empty($sevv_os)) $where['sevv_os'] = ['like', "%$sevv_os%"];

        $sevv_status = trim($queryParam['sevv_status']);
        if (!empty($sevv_status)) $where['sevv_status'] = ['like', "%$sevv_status%"];

        $sevUseman = trim($queryParam['sevv_useman']);
        if (!empty($sevUseman)) $where['sevv_useman'] = ['like', "%$sevUseman%"];


        $sevv_toptype = strtolower(trim($queryParam['sevv_toptype']));
        if (!empty($sevv_toptype)) $where['sevv_toptype'] = ['like', "%$sevv_toptype%"];

        $sevv_isyuan = trim($queryParam['sevv_isyuan']);
        if (!empty($sevv_isyuan)) $where['sevv_isyuan'] = ['like', "%$sevv_isyuan%"];

        $sevv_iszhongxin = trim($queryParam['sevv_iszhongxin']);
        if (!empty($sevv_iszhongxin)) $where['sevv_iszhongxin'] = ['like', "%$sevv_iszhongxin%"];

        $sevUsedept = trim($queryParam['sevv_usedept']);
        if (!empty($sevUsedept)) {
            $sql = "select id from it_depart start with id= '$sevUsedept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['sevv_usedept'] = ['in', $ids];
        }

        $sevdutyman = trim($queryParam['sevv_dutyman']);
        if (!empty($sevdutyman)) $where['sevv_dutyman'] = ['like', "%$sevdutyman%"];

        $sevdutydept = trim($queryParam['sevv_dutydept']);
        if (!empty($sevUsedept)) {
            $sql = "select id from it_depart start with id= '$sevdutydept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['sevv_dutydept'] = ['in', $ids];
        }

        $sevv_jq = trim($queryParam['sevv_jq']);
        if (!empty($sevv_jq)) $where['sevv_jq'] = ['like', "%$sevv_jq%"];

        $sevSecretlevel = trim($queryParam['sevv_secretlevel']);
        if (!empty($sevSecretlevel)) $where['sevv_secretlevel'] = ['like', "%$sevSecretlevel%"];

        $directstorage = trim($queryParam['sevv_directstorage']);
        if (!empty($directstorage)) $where['sevv_directstorage'] = ['like', "%$directstorage%"];

        $supportdrift = trim($queryParam['sevv_supportdrift']);
        if (!empty($supportdrift)) $where['sevv_supportdrift'] = ['like', "%$supportdrift%"];

//        $sevDutyman = trim($queryParam['sevv_dutyman']);
//        if (!empty($sevDutyman)) $where['sevv_dutyman'] = ['like', "%$sevDutyman%"];

        $sev_yxstatus = trim($queryParam['sevv_yxstatus']);
        if(!empty($sev_yxstatus)){
            $Ipmodel = M('scanserver');
            $list = $Ipmodel->query("select x.* from scanserver x,
(
       SELECT max(ss_atpcreatedatetime) ss_atpcreatedatetime, ss_ipaddress
                    FROM scanserver
                    GROUP BY ss_ipaddress
) y
                    where x.ss_ipaddress = y.ss_ipaddress and x.ss_atpcreatedatetime=y.ss_atpcreatedatetime and x.ss_status = 1");
            $ips = removeArrKey($list,'ss_ipaddress');
            $ipArr = array_chunk($ips,1000);
            $count = count($ipArr)-1;
            if($sev_yxstatus == 1){
                for($i=0;$i<= $count;$i++){
                    $where[2][$i]['sevv_ip'] = ['in',$ipArr[$i]];
                }
                $where[2]['_logic'] = 'or';
            }else{
                for($i=0;$i<= $count;$i++){
                    $where[3][2]['sevv_ip'][$i] = ['not in',$ipArr[$i]];
                }
                $where[3][2]['_logic'] = 'AND';

                $where[3]['sevv_ip'] = ['exp','is null'];
                $where[3]['_logic'] = 'OR';
            }
        }

        $sevv_sx = trim($queryParam['sevv_sx']);
        if (!empty($sevv_sx)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '虚拟服务器'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');

            $rlxArr = array_chunk($rlx_zyids,1000);
            $count = count($rlxArr)-1;
            if($sevv_sx == 1){
                for($i=0;$i<= $count;$i++){
                    $where[5][$i]['sevv_atpid'] = ['in',$rlxArr[$i]];
                }
                $where[5]['_logic'] = 'or';
            }else{
                for($i=0;$i<= $count;$i++){
                    $where[6]['sevv_atpid'][$i] = ['not in',$rlxArr[$i]];
                }
                $where[6]['_logic'] = 'AND';
            }
        }

        $model = M('it_sevv');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if ($isExport) {
            set_time_limit(0);
            $data = $obj->select();
//            print_r($data);die;
            $header = ['主数据ID','服务器名称','设备类型', 'IP地址','子IP地址', 'MAC地址','子MAC地址', '密级',  '责任人','责任部门', '使用人','使用部门', '使用状态','上线/下线日期', '备注', '直连存储', '内置服务', '内存', '宿主机IP','支持漂移','CPU', '子网掩码', '默认网关', '操作系统安装时间', '操作系统','是否院管','是否中心管','分类','运行状态'];

            foreach ($data as $k => &$v) {
                //使用人
                if (!empty($v['sevv_useman'])) {
                    $userName = D('org')->getViewPerson($v['sevv_useman']);
                    $v['sevv_useman'] = $userName['realusername'];

                    //使用人部门
                    $v['sevv_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sevv_useman'] = '-';
                    $v['sevv_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sevv_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sevv_dutyman']);
                    $v['sevv_dutyman'] = $userName['realusername'];

                    //责任人部门
                    $v['sevv_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sevv_dutyman'] = '-';
                    $v['sevv_dutydept'] = '-';
                }
                //资产责任单位
//                if (!empty($v['sevv_assetdutydept'])) {
//                    $deptInfo = D('org')->getDepartId($v['sevv_assetdutydept']);
//                    $v['sevv_assetdutydept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
//                } else {
//                    $v['sevv_assetdutydept'] = '-';
//                }

                //使用责任单位
//                if (!empty($v['sevv_assetusedept'])) {
//                    $deptInfo = D('org')->getDepartId($v['sevv_assetusedept']);
//                    $v['sevv_assetusedept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
//                } else {
//                    $v['sevv_assetusedept'] = '-';
//                }
                $ip = explode(';',$v['sevv_ip']);
                $serverModel = M('scanserver');
                $status = [];
                foreach($ip as $val){
                    $status[] = $serverModel->field('ss_status')->where("ss_ipaddress = '%s'",$val)->order('ss_atpcreatedatetime desc')->limit(1)->find();
                }
                $status =removeArrKey($status,'ss_status');
                if(in_array('1',$status)){
                    $data[$k]['ss_status'] = '开机';
                }else{
                    $data[$k]['ss_status'] = '关机';
                }

                //光纤交换机IP地址
//                $relation = D("relation")->getViewRelationInfo($v['sevv_atpid'], $v['sevv_fsip']);
//                $sevv_fsip = '';
//                foreach ($relation as $val) {
//                    $sevv_fsip .= '(' . $val['r_ip'] . '),';
//                }
//                $sevv_fsip = substr($sevv_fsip, 0, -1);
//                $data[$k]['sevv_fsip']  = $sevv_fsip;


                //机柜
//                $relation = D("relation")->getViewRelationInfo($v['sevv_atpid'], 'jigui');
//                $sevv_cab = '';
//                foreach ($relation as $val) {
//                    $sevv_cab .= '(' . $val['r_name'] . '),';
//                }
//                $sevv_cab = substr($sevv_cab, 0, -1);
//                $data[$k]['sevv_cab']  = $sevv_cab;



                //应用系统
//                $relation = D("relation")->getViewRelationInfo($v['sevv_atpid'], 'it_application');
//                $sevv_app = '';
//                foreach ($relation as $val) {
//                    $sevv_app .= '(' . $val['r_name'] . '),';
//                }
//                $sevv_app = substr($sevv_app, 0, -1);
//                $data[$k]['sevv_app']  = $sevv_app;
                $list = M('it_relationx i')->field('rw_detail,rlx_useage')
                    ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
                    ->where("rlx_zyid = '%s' and rlx_atpstatus is null and rw_atpstatus is null",$v['sevv_atpid'])
                    ->select();
                $detail = '';
                foreach($list as $val){
                    $detail.=$val['rw_detail'].'-'.$val['rlx_useage'].',';
                }
                $data[$k]['sevv_app'] = $detail;

                //服务器
                $relation = D("relation")->getViewRelationInfo($v['sevv_atpid'], 'it_sev');
                $sevv_hostip = '';
                foreach ($relation as $val) {
                    $sevv_hostip .= '(' . $val['r_ip'] . '),';
                }
                $sevv_hostip = substr($sevv_hostip, 0, -1);
                $data[$k]['sevv_hostip']  = $sevv_hostip;

//                unset($data[$k]['sevv_atpid']);

            }
//            print_r($data);die;
            if ($count <= 0) {
                exit(makeStandResult(-1, '没有要导出的数据'));
            } else if ($count > 1000) {
                csvExport($header, $data, true);
            } else {
                excelExport($header, $data, true);
            }
        } else {
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            //            var_dump($data);die;
            //            $sql = $obj->getLastSql();

            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['sevv_area'] = !empty($v['sev_area']) ? $this->getDicById($v['sev_area'], 'dic_name') : '-'; //地区
//                $v['sev_belongfloor'] = !empty($v['sev_belongfloor']) ? $this->getDicLouYuById($v['sev_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['sev_factory'] = !empty($v['sev_factory']) ? $this->getDicById($v['sev_factory'], 'dic_name') : '-'; //厂家
//                $v['sev_modelnumber'] = !empty($v['sev_modelnumber']) ? $this->getDicXingHaoById($v['sev_modelnumber'], 'dic_name') : '-'; //型号
//                $v['sev_status'] = !empty($v['sev_status']) ? $this->getDicById($v['sev_status'], 'dic_name') : '-'; //状态
//                $v['sev_secretlevel'] = !empty($v['sev_secretlevel']) ? $this->getDicById($v['sev_secretlevel'], 'dic_name') : '-'; //密级
//                $v['sev_assetsource'] = !empty($v['sev_assetsource']) ? $this->getDicById($v['sev_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['sev_os'] = !empty($v['sev_os']) ? $this->getDicById($v['sev_os'], 'dic_name') : '-'; //操作系统
//                $v['sev_net'] = !empty($v['sev_net']) ? $this->getDicById($v['sev_net'], 'dic_name') : '-'; //所属网络

                $ip = explode(';',$v['sevv_ip']);
                $serverModel = M('scanserver');
                $status = [];
                foreach($ip as $val){
                    $status[] = $serverModel->field('ss_status')->where("ss_ipaddress = '%s'",$val)->order('ss_atpcreatedatetime desc')->limit(1)->find();
                }
                $status =removeArrKey($status,'ss_status');
                if(in_array('1',$status)){
                    $data[$k]['ss_status'] = '开机';
                }else{
                    $data[$k]['ss_status'] = '关机';
                }
                //使用人
                if (!empty($v['sevv_useman'])) {
                    $userName = D('org')->getViewPerson($v['sevv_useman']);
                    $v['sevv_useman'] = $userName['realusername'] ;

                    //使用人部门
                    $v['sevv_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sevv_useman'] = '-';
                    $v['sevv_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sevv_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sevv_dutyman']);
                    $v['sevv_dutyman'] = $userName['realusername'] ;

                    //责任人部门
                    $v['sevv_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sevv_dutyman'] = '-';
                    $v['sevv_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['sevv_assetdutydept'])) {
                    $deptInfo = D('org')->getDepartId($v['sevv_assetdutydept']);
                    $v['sevv_assetdutydept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                } else {
                    $v['sevv_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['sevv_assetusedept'])) {
                    $deptInfo = D('org')->getDepartId($v['sevv_assetusedept']);
                    $v['sevv_assetusedept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                } else {
                    $v['sevv_assetusedept'] = '-';
                }
                //光纤交换机关联关系
                $netAll = D("relation")->getViewRelationInfo($v['sevv_atpid'], 'it_netdevice');
                if (!empty($netAll)) {
                    $v['sevv_fsip'] = '';
                    foreach ($netAll as $val) {
                        $v['sevv_fsip'] .= '(' . $val['r_ip'] . '),';
                    }
                    $v['sevv_fsip'] = substr($v['sevv_fsip'], 0, -1);
                } else {
                    $v['sevv_fsip'] = '-';
                }
                //机柜关联关系
                $jgAll = D("relation")->getViewRelationInfo($v['sevv_atpid'], 'jigui');
                if (!empty($jgAll)) {
                    $v['sevv_cab'] = '';
                    foreach ($jgAll as $val) {
                        $v['sevv_cab'] .= '(' . $val['r_name'] . '),';
                    }
                    $v['sevv_cab'] = substr($v['sevv_cab'], 0, -1);
                } else {
                    $v['sevv_cab'] = '-';
                }
                //应用系统关联关系
//                $appAll = D("relation")->getViewRelationInfo($v['sevv_atpid'], 'it_application');
//                if (!empty($appAll)) {
//                    $v['sevv_app'] = '';
//                    foreach ($appAll as $val) {
//                        $v['sevv_app'] .= '(' . $val['r_name'] . '),';
//                    }
//                    $v['sevv_app'] = substr($v['sevv_app'], 0, -1);
//                } else {
//                    $v['sevv_app'] = '-';
//                }
                //服务器关联关系
                $jgAll = D("relation")->getViewRelationInfo($v['sevv_atpid'], 'it_sev');
                if (!empty($jgAll)) {
                    $v['sevv_hostip'] = '';
                    foreach ($jgAll as $val) {
                        $v['sevv_hostip'] .= '(' . $val['r_ip'] . '),';
                    }
                    $v['sevv_hostip'] = substr($v['sevv_hostip'], 0, -1);
                } else {
                    $v['sevv_hostip'] = '-';
                }

                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['sevv_atpid'])->count();
                $data[$k]['sxCount'] = $rlxCount;
            }
            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }

    public function read(){
        $ipaddress = I('get.ipaddress');
        $this->assign('ipaddress',$ipaddress);
        $this->display();
    }

    public function getIpData(){
        $queryParam = json_decode(file_get_contents("php://input"), true);
        $where = [];
        $ipaddress = $queryParam['ipaddress'];
        $ipaddress = explode(';',$ipaddress);
        if(!empty($ipaddress)) $where['ss_ipaddress'] = ['in',$ipaddress];
        $model = M('scanserver');
        $data = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->where($where)->count();
        foreach($data as $k => $value){
            if($value['ss_status']=='1'){
                $data[$k]['ss_status'] = '开机';
            }else{
                $data[$k]['ss_status'] = '关机';
            }
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.sev_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        $arr = explode(',', $ids);
        $model = M('it_sev');

        $where['sev_atpid'] = ['in',$arr];
        $ipList = $model->where($where)->select();
        $ip = removeArrKey($ipList,'sev_ip');
        $subip = removeArrKey($ipList,'sev_subip');
        $iloip = removeArrKey($ipList,'sev_iloip');
        $ips = array_merge($ip,$iloip);
        foreach($subip as $val){
            $subipSon  = str_replace(',',';',$val);
            $subipSon = explode(';',$subipSon);
            $ips = array_merge($ips,$subipSon);
        }
        $ips =array_unique(array_filter($ips));
        D('ip')->DelIpCs($ips);

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['sev_atpmodifytime'] = $time;
                $data['sev_atpmodifyuser'] = $user;
                $data['sev_atpstatus'] = 'DEL';
                $res = $model->where("sev_atpid='%s'", $id)->save($data);
                $list['rlx_atpstatus'] = 'DEL';
                M('it_relationx')->where("rlx_zyid = '%s'",$id)->save($list);
                if ($res) {
                    // 修改日志
                    addLog('it_sev', '对象删除日志',  "删除主键为".$id."成功", '成功',$id);
                    D('relation')->delRelation($id, 'it_sev');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('it_sev', '对象删除日志',  "删除主键为".$ids."失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    /**
     * 删除数据
     */
    public function delVirData()
    {
        $ids = trim(I('post.sevv_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('it_sevv');

        $where['sevv_atpid'] = ['in',$arr];
        $ipList = $model->where($where)->select();
        $ips = removeArrKey($ipList,'sevv_ip');
        $subip = removeArrKey($ipList,'sevv_subip');
        foreach($subip as $val){
            $subipSon  = str_replace(',',';',$val);
            $subipSon = explode(';',$subipSon);
            $ips = array_merge($ips,$subipSon);
        }
        $ips =array_unique(array_filter($ips));
        D('ip')->DelIpCs($ips);

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['sevv_atpmodifytime'] = $time;
                $data['sevv_atpmodifyuser'] = $user;
                $data['sevv_atpstatus'] = 'DEL';
                $res = $model->where("sevv_atpid='%s'", $id)->save($data);
                $list['rlx_atpstatus'] = 'DEL';
                M('it_relationx')->where("rlx_zyid = '%s'",$id)->save($list);
                if ($res) {
                    // 修改日志
                    addLog('it_sevv', '对象删除日志',  "删除主键为".$id."成功", '成功',$id);
                    D('relation')->delRelation($id, 'it_sevv');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('it_sevv', '对象删除日志',  "删除主键为".$ids."失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    //ajax验证ip查重
    public function ajaxIpCheck()
    {
        $ip = I('post.sev_ip');
        $res = D('sev')->sevIpChecking($ip);
        if ($res) {
            exit(makeStandResult(-1, '输入的ip重复'));
        } else {
            exit(makeStandResult(1, '成功'));
        }
    }

    public function getSonip(){
        $ip = I('post.sev_ip');
        $ips = explode('.',$ip);
        $Zip = $ips[0].'.'.$ips[1].'.'.$ips[2].'.'.'254';
        if($Zip){
            exit(makeStandResult(1, $Zip));
        }else{

        }exit(makeStandResult(-1, 'error'));
    }

    /**
     * 批量增加
     */
    public function saveCopyTables()
    {
        $receiveData = $_POST;
        $head = explode(',', $receiveData['head']);
        $data = $receiveData['data'];
        if (empty($data)) exit(makeStandResult(-1, '未接收到数据'));
        $data = json_decode($data, true);

        $reduce = 0;
        if ($head[0] == '序号') {
            foreach ($data as &$value) {
                unset($value[0]);
            }
            unset($value);
            $reduce = 1;
        }

        $time = date('Y-m-d H:i:s');
        $loginUserId = session('user_id');

        //字典
        $arrField = ['密级', '资产来源', '地区', '厂家', '操作系统', '所属网络', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arrField);
        //当前资产字典id
        $res = M('dic')->field('dic_id')
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='物理服务器'")
            ->find();
        $sevDicId = $res['dic_id'];

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');
        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');
        $ziYuanLaiYuan = $arrDic['资产来源'];
        $ziYuanLaiYuanArray = array_column($ziYuanLaiYuan, 'dic_name');
        $caoZuoXiTong = $arrDic['操作系统'];
        $caoZuoXiTongArray = array_column($caoZuoXiTong, 'dic_name');
        $suoShuWangLuo = $arrDic['所属网络'];
        $suoShuWangLuoArray = array_column($suoShuWangLuo, 'dic_name');
        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');

        //字段
        $fields = ['sev_factory', 'sev_modelnumber', 'sev_sn', 'sev_status', 'sev_secretlevel', 'sev_assetsource', 'sev_assetdutydept', 'sev_assetusedept', 'sev_purchasetime', 'sev_startusetime', 'sev_disknum', 'sev_disksn', 'sev_cpunum', 'sev_hbanum', 'sev_wwnno', 'sev_powernum', 'sev_powerport','sev_powergl', 'sev_area', 'sev_belongfloor', 'sev_roomno', 'sev_cabloc', 'sev_kvmsw', 'sev_kvmnum', 'sev_dutyman', 'sev_dutydept', 'sev_useman', 'sev_usedept', 'sev_os', 'sev_osinstalltime', 'sev_swinterface', 'sev_net', 'sev_ilopass', 'sev_remark', 'sev_directstorage',  'sev_name', 'sev_swip', 'sev_devicecode', 'sev_anecode', 'sev_ip', 'sev_mask', 'sev_gateway', 'sev_subip', 'sev_mac', 'sev_submac', 'sev_iloip', 'sev_ilomac'];


        $model = M('it_sev');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);

        foreach ($data as $key => $value) {

            $lineNum = $key + 1; //表格行号
            $arr = [];
            foreach ($value as $k => $v) {
                $field = $fields[$k - $reduce];
                switch ($field) {
                    case 'sev_secretlevel': //密级
                        $deptNameField = 'sev_secretlevel';
                        $fieldName = '密级';
                        if (!empty($v)) {
                            if (!in_array($v, $miJiArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $miJiArray);
                            }
                            $dicId = $miJi[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'sev_assetsource': //资产来源
                        $deptNameField = 'sev_assetsource';
                        $fieldName = '资产来源';
                        if (!empty($v)) {
                            if (!in_array($v, $ziYuanLaiYuanArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $ziYuanLaiYuanArray);
                            }
                            $dicId = $ziYuanLaiYuan[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    
                        
                    case 'sev_area': //地区
                        $deptNameField = 'sev_area';
                        $fieldName = '地区';
                        if (!empty($v)) {
                            if (!in_array($v, $diQuArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $diQuArray);
                            }
                            $dicId = $diQu[$k]['dic_id'];
                            $dicName = $diQu[$k]['dic_name'];
                            $arr[$deptNameField] = $dicName;
                            //楼宇
                            $diQuData = $v;
                            //查字典
                            $louYu = $this->getDicLouYuByPid($dicId);
                            $louYuArray = array_column($louYu, 'dic_name');
                            //                            var_dump($dicId);die;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'sev_belongfloor': //楼宇
                        $deptNameField = 'sev_belongfloor';
                        $fieldName = '楼宇';
                        //                        var_dump($v);die;
                        if (!empty($diQuData)) {
                            if (!empty($v)) {
                                if (!in_array($v, $louYuArray)) {
                                    $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                    break;
                                } else {
                                    $k = array_search($v, $louYuArray);
                                }
                                $arr[$deptNameField] = $louYu[$k]['dic_name'];
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        } else {
                            if (!empty($v)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 需要填写地区<br>";
                                break;
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        }
                        break;

                    case 'sev_os': //操作系统
                        $deptNameField = 'sev_os';
                        $fieldName = '操作系统';
                        if (!empty($v)) {
                            if (!in_array($v, $caoZuoXiTongArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $caoZuoXiTongArray);
                            }
                            $dicId = $caoZuoXiTong[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'sev_net': //所属网络
                        $deptNameField = 'sev_net';
                        $fieldName = '所属网络';
                        if (!empty($v)) {
                            if (!in_array($v, $suoShuWangLuoArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $suoShuWangLuoArray);
                            }
                            $dicId = $suoShuWangLuo[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'sev_status': //使用状态
                        $deptNameField = 'sev_status';
                        $fieldName = '使用状态';
                        if (!empty($v)) {
                            if (!in_array($v, $zhuangTaiArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $zhuangTaiArray);
                            }
                            $dicId = $zhuangTai[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;


                    case 'sev_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'sev_disknum': //硬盘块数
                        $arr[$field] = $v;
                        break;
                    case 'sev_disksn': //硬盘序列号
                        $arr[$field] = $v;
                        break;
                    case 'sev_hbanum': //HBA口数
                        $arr[$field] = $v;
                        break;
                    case 'sev_wwnno': //WWN号
                        $arr[$field] = $v;
                        break;
                    case 'sev_cpunum': //CPU颗数
                        $arr[$field] = $v;
                        break;
                    case 'sev_powernum': //电源数量
                        $arr[$field] = $v;
                        break;
                    case 'sev_powerport': //电源连接端口
                        $arr[$field] = $v;
                        break;
                    case 'sev_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'sev_cabloc': //机位
                        $arr[$field] = $v;
                        break;
                    case 'sev_kvmnum': //KVM口
                        $arr[$field] = $v;
                        break;
                    case 'sev_swinterface': //交换机端口
                        $arr[$field] = $v;
                        break;
                    case 'sev_ilopass': //iLo密码提示
                        $arr[$field] = $v;
                        break;
                    case 'sev_remark': //备注
                        $arr[$field] = $v;
                        break;
                    case 'sev_directstorage': //直连存储
                        $arr[$field] = $v;
                        break;
//                    case 'sev_supportdrift': //支持漂移
//                        $arr[$field] = $v;
//                        break;
                    case 'sev_name': //服务器名称
                        $fieldName = '服务器名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_swip': //交换机IP
                        $arr[$field] = $v;
                        break;
                    case 'sev_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'sev_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'sev_usedept': //使用部门
                        $arr[$field] = $v;
                        break;
                    case 'sev_dutydept': //责任部门
                        $arr[$field] = $v;
                        break;


                    case 'sev_mac': //MAC地址
                        $fieldName = 'MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} MAC格式不对<br>";
                                break;
                            }
                        } else {
                            $error .= "第{$lineNum} 行 {$fieldName} MAC地址不能为空<br>";
                            break;
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_submac': //子MAC地址
                        $fieldName = '子MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} MAC格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_ilomac': //iLoMAC地址
                        $fieldName = 'iLoMAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} MAC格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'sev_kvmsw': //KVM交换机
                        $fieldName = 'KVM交换机';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_ip': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                            //ip查重
                            $res = D('sev')->sevIpChecking($v);
                            if ($res) {
                                $error .= "第{$lineNum} 行 {$fieldName} ip地址重复<br>";
                                break;
                            }
                        } else {
                            $error .= "第{$lineNum} 行 {$fieldName} IP地址不能为空<br>";
                            break;
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_mask': //子网掩码
                        $fieldName = '子网掩码';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_gateway': //默认网关
                        $fieldName = '默认网关';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_subip': //子IP地址
                        $fieldName = '子IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sev_iloip': //iLoIP地址
                        $fieldName = 'iLoIP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'sev_purchasetime': //采购日期
                        $fieldName = '采购日期';
                        if (!empty($v)) {
                            $strTime = strtotime($v);
                            if ($strTime === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        } else {
                            $arr[$field] = '';
                        }
                        break;
                    case 'sev_startusetime': //启用日期
                        $fieldName = '启用日期';
                        if (!empty($v)) {
                            $strTime = strtotime($v);
                            if ($strTime === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        } else {
                            $arr[$field] = '';
                        }
                        break;
                    case 'sev_osinstalltime': //操作系统安装时间
                        $fieldName = '操作系统安装时间';
                        if (!empty($v)) {
                            $strTime = strtotime($v);
                            if ($strTime === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        } else {
                            $arr[$field] = '';
                        }
                        break;


                    case 'sev_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'sev_useman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'sev_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'sev_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'sev_assetdutydept': //资产责任单位
                        $fieldName = '资产责任单位';
                        $userNameField = 'sev_assetdutydept';
                        if(!empty($v)){
                            $orgId = D('org')->getOrgId($v);
                            if (empty($orgId)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写单位全称)<br>";
                                break;
                            }
                            $arr[$userNameField] = $orgId;
                        }else{
                            $arr[$field] = $v;
                        }
                        break;
                    case 'sev_assetusedept': //使用责任单位
                        $fieldName = '使用责任单位';
                        $userNameField = 'sev_assetusedept';
                        if(!empty($v)){
                            $orgId = D('org')->getOrgId($v);
                            if (empty($orgId)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写单位全称)<br>";
                                break;
                            }
                            $arr[$userNameField] = $orgId;
                        }else{
                            $arr[$field] = $v;
                        }
                        break;

                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['sev_atpcreatetime'] = $time;
                $arr['sev_atpcreateuser'] = $loginUserId;
                $arr['sev_type'] = '物理服务器';

                $arr['sev_atpid'] = makeGuid();
            }
            $initTables[] = $arr;
        }

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    $res = $model->add($value);
                    if (!empty($res)) $successNum += 1;
                }
                $failNum = $exportNum - $successNum;
                addLog('it_sev', '对象导入日志', '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
                $model->commit();
                exit(makeStandResult(1, '添加成功'));
            } else {
                exit(makeStandResult(-1, $error));
            }
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '添加失败'));
        }
    }

    /**
     * 批量增加
     */
    public function virtulaSaveCopyTables()
    {
        $receiveData = $_POST;
        $head = explode(',', $receiveData['head']);
        $data = $receiveData['data'];
        if (empty($data)) exit(makeStandResult(-1, '未接收到数据'));
        $data = json_decode($data, true);

        $reduce = 0;
        if ($head[0] == '序号') {
            foreach ($data as &$value) {
                unset($value[0]);
            }
            unset($value);
            $reduce = 1;
        }

        $time = date('Y-m-d H:i:s');
        $loginUserId = session('user_id');

        //字典
        $arrField = ['密级', '操作系统', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arrField);

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');

        $caoZuoXiTong = $arrDic['操作系统'];
        $caoZuoXiTongArray = array_column($caoZuoXiTong, 'dic_name');

        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');
        //字段
        $fields = ['sevv_name', 'sevv_app', 'sevv_ip', 'sevv_subip','sevv_mask', 'sevv_gateway', 'sevv_mac','sevv_submac', 'sevv_os', 'sevv_osinstalltime', 'sevv_status', 'sevv_hostip', 'sevv_useman', 'sevv_usedept', 'sevv_secretlevel', 'sevv_directstorage','sevv_supportdrift','sevv_cpunum', 'sevv_swip', 'sevv_remark'];

        //        $orgModel = D('Org'); //初始化org model 查询部门id
        //        $userModel = D('User'); //初始化user model 查询用户id

        $model = M('it_sevv');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);

        foreach ($data as $key => $value) {

            $lineNum = $key + 1; //表格行号
            $arr = [];
            foreach ($value as $k => $v) {
                $field = $fields[$k - $reduce];
                switch ($field) {

                    case 'sevv_secretlevel': //密级
                        $deptNameField = 'sevv_secretlevel';
                        $fieldName = '密级';
                        //                        var_dump($changJiaArray);die;
                        if (!empty($v)) {
                            if (!in_array($v, $miJiArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $miJiArray);
                            }
                            //                            var_dump($miJi[$k]['dic_id']);die;
                            $dicId = $miJi[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'sevv_os': //操作系统
                        $deptNameField = 'sevv_os';
                        $fieldName = '操作系统';
                        if (!empty($v)) {
                            if (!in_array($v, $caoZuoXiTongArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $caoZuoXiTongArray);
                            }
                            $dicId = $caoZuoXiTong[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'sevv_status': //使用状态
                        $deptNameField = 'sevv_status';
                        $fieldName = '使用状态';
                        if (!empty($v)) {
                            if (!in_array($v, $zhuangTaiArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $zhuangTaiArray);
                            }
                            $dicId = $zhuangTai[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'sevv_cpunum': //CPU颗数
                        $arr[$field] = $v;
                        break;
                    case 'sevv_app': //内置服务
                        $arr[$field] = $v;
                        break;
                    case 'sevv_remark': //备注
                        $arr[$field] = $v;
                        break;
                    case 'sevv_directstorage': //直连存储
                        $arr[$field] = $v;
                        break;
                    case 'sevv_supportdrift': //支持漂移
                        $arr[$field] = $v;
                        break;
                    case 'sevv_name': //服务器名称
                        $fieldName = '服务器名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 服务器名称不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sevv_swip': //交换机IP
                        $arr[$field] = $v;
                        break;
                    case 'sevv_usedept': //使用部门
                        $arr[$field] = $v;
                        break;


                    case 'sevv_mac': //MAC地址
                        $fieldName = 'MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} MAC格式不对<br>";
                                break;
                            }
                        } else {
                            $error .= "第{$lineNum} 行 {$fieldName} MAC地址不能为空<br>";
                            break;
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sevv_submac': //MAC地址
                        $fieldName = '子MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} MAC格式不对<br>";
                                break;
                            }
                        }
//                        else {
//                            $error .= "第{$lineNum} 行 {$fieldName} 子MAC地址不能为空<br>";
//                            break;
//                        }
                        $arr[$field] = $v;
                        break;
                    case 'sevv_hostip': //宿主机IP
                        $fieldName = '宿主机IP';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sevv_ip': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        } else {
                            $error .= "第{$lineNum} 行 {$fieldName} IP地址不能为空<br>";
                            break;
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sevv_subip': //子IP地址
                        $fieldName = '子IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
//                        else {
//                            $error .= "第{$lineNum} 行 {$fieldName} 子IP地址不能为空<br>";
//                            break;
//                        }
                        $arr[$field] = $v;
                        break;
                    case 'sevv_mask': //子网掩码
                        $fieldName = '子网掩码';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sevv_gateway': //默认网关
                        $fieldName = '默认网关';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'sevv_osinstalltime': //操作系统安装时间
                        $fieldName = '操作系统安装时间';
                        if (!empty($v)) {
                            $strTime = strtotime($v);
                            if ($strTime === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        } else {
                            $arr[$field] = '';
                        }
                        break;


                    case 'sevv_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'sevv_useman';

                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;

                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['sevv_atpcreatetime'] = $time;
                $arr['sevv_atpcreateuser'] = $loginUserId;

                $arr['sevv_atpid'] = makeGuid();
            }
            $initTables[] = $arr;
        }

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    $res = $model->add($value);
                    if (!empty($res)) $successNum += 1;
                }
                $failNum = $exportNum - $successNum;
                addLog('it_sevv', '对象导入日志',  '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
                $model->commit();
                exit(makeStandResult(1, '添加成功'));
            } else {
                exit(makeStandResult(-1, $error));
            }
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '添加失败'));
        }
    }

    public function detail(){
        $detail = I('get.detail');
        $detail =explode(',',$detail);
        $count = count($detail);
        unset($detail[$count-1]);
        $this->assign('detail',$detail);
        $this->display();
    }

    /**
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $powers = D('Admin/RefinePower')->getViewPowers('SevRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }
}
