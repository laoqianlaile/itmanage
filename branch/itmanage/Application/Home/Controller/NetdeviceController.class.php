<?php

namespace Home\Controller;

use Think\Controller;

class NetdeviceController extends BaseController
{
    //交换机管理
    public function index()
    {
        $arr = ['密级', '使用状态', '所属网络', '厂家', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);
        addLog("", "用户访问日志", "访问交换机管理页面", "成功");
        $this->display();
    }

    //光纤交换机
    public function opticalfiber()
    {
        $arr = ['密级', '使用状态', '所属网络', '厂家', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);

        addLog("", "用户访问日志", "访问交换机管理页面", "成功");
        $this->display();
    }

    /**
     * 交换机管理添加或修改
     */
    public function add()
    {
        $id = trim(I('get.net_atpid'));

        if (!empty($id)) {
            $model = M('it_netdevice');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model->field('net_atpid,net_name,net_repairtype,net_daobaodate,net_weibaodate,net_upswitch,net_loginport,net_managehost,net_building,net_atpcreateuser,net_configinfo,net_room,net_adminpass,net_loginuser,net_jiwei,net_iscore,net_manage,net_iso,net_portdetail,net_mainid,net_isscan,net_atpcreatedatetime,net_loginpass,net_useman,net_net,net_memo,net_anecode,net_jigui,net_portnum,net_mask,net_atpstatus,net_factory,net_atplastmodifyuser,net_logserver,net_caigoudate,net_ipaddress,net_globalpass,net_dutydept,net_usage,net_dutyman,net_source,net_dutydeptid,net_type,net_code,net_secretlevel,net_sn,net_privacybook,net_devicebook,net_area,net_upport,net_usedept,net_enabledate,net_protocol,net_gateway,net_status,net_model,net_atplastmodifydatetime')->where("net_atpid='%s'", $id)->find();

            //使用人
            $userId = $data['net_useman'];
            //            dump($userid);die;
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
            $dutuserId = $data['net_dutyman'];
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
        }

        $arr = ['密级', '使用状态', '资产来源', '所属网络', '地区', '厂家'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $arrDic['厂家']);

        $this->assign('net_type', '交换机');
        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问交换机管理添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 交换机管理添加或修改
     */
    public function opticalfiberAdd()
    {
        $id = trim(I('get.net_atpid'));
        if (!empty($id)) {
            $model = M('it_netdevice');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model->field('net_atpid,net_upswitch,net_loginport,net_managehost,net_building,net_atpcreateuser,net_configinfo,net_room,net_adminpass,net_loginuser,net_jiwei,net_iscore,net_manage,net_iso,net_portdetail,net_mainid,net_isscan,net_atpcreatedatetime,net_loginpass,net_useman,net_net,net_memo,net_anecode,net_jigui,net_portnum,net_mask,net_atpstatus,net_factory,net_atplastmodifyuser,net_logserver,net_caigoudate,net_ipaddress,net_globalpass,net_dutydept,net_usage,net_dutyman,net_source,net_dutydeptid,net_type,net_code,net_secretlevel,net_sn,net_privacybook,net_devicebook,net_area,net_upport,net_usedept,net_enabledate,net_protocol,net_gateway,net_status,net_model,net_atplastmodifydatetime')->where("net_atpid='%s'", $id)->find();

            //使用人
            $userId = $data['net_useman'];
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
            $dutuserId = $data['net_dutyman'];
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

            //机柜
            $relation = D("relation")->getViewRelationInfo($id, 'jigui');
            // dump($relation);die;
            $net_jigui = '';
            foreach ($relation as $v) {
                $net_jigui .= '(' . $v['r_name'] . '),';
            }
            $net_jigui = substr($net_jigui, 0, -1);
            $this->assign('net_jigui', $net_jigui);
            //隐藏的id值
            $net_jigui_id = implode(',', array_column($relation, 'r_id'));
            // dump($net_jigui_id);die;
            $this->assign('net_jigui_id', $net_jigui_id);
        }

        $arr = ['密级', '使用状态', '资产来源', '所属网络', '厂家', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);


        $this->assign('net_type', '光纤交换机');
        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问交换机管理添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['net_atpid']);
        $type = trim($data['net_type']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('it_netdevice');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $data['net_isscan'] = intval($data['net_isscan']); //是否扫描
        $data['net_iscore'] = intval($data['net_iscore']); //是否核心
        $data['net_area'] = $this->getDicById($data['net_area'], 'dic_name'); //地区
        $data['net_building'] = $this->getDicLouYuById($data['net_building'], 'dic_name'); //楼宇
        $data['net_factory'] =  $this->getDicById($data['net_factory'], 'dic_name') ; //厂家
        $data['net_model'] = $this->getDicXingHaoById($data['net_model'], 'dic_name'); //型号
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容

        //IP地址
        if (!empty($data['net_ipaddress'])) {
            if ($this->checkAddress($data['net_ipaddress'], 'IP') === false) exit(makeStandResult(-1, 'IP地址有误'));
        } else {
            exit(makeStandResult(-1, 'IP地址不能为空'));
        }
        //子网掩码
        if (!empty($data['net_mask'])) {
            if ($this->checkAddress($data['net_mask'], 'IP') === false) exit(makeStandResult(-1, '子网掩码有误'));
        }
        //默认网关
        if (!empty($data['net_gateway'])) {
            if ($this->checkAddress($data['net_gateway'], 'IP') === false) exit(makeStandResult(-1, '默认网关有误'));
        }

        //登录用户密码 如果未填就自动生成
        $data['net_loginuser'] = empty($data['net_loginuser']) ? 'yunwei' : $data['net_loginuser'];
        //验证 生成密码
        $data['net_loginpass'] = $this->createLoginPass($data['net_loginpass'], $data['net_ipaddress']);

        $data['net_type'] = $type ? $type : '交换机';
        if (empty($id)) {
            $netJiguiId = explode(',', $data['net_jigui_id']);

            $data = $model->create($data);
            $data['net_atpid'] = makeGuid();
            $data['net_atpcreatedatetime'] = $time;
            $data['net_atpcreateuser'] = $user;

            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('it_netdevice', '对象添加日志', 'add', '添加xxx' . '失败', '失败');
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('it_netdevice', '对象添加日志', 'add', '添加xxx' . '成功', '成功');
                $this->changeRelationJigui($data['net_atpid'], '', $data['net_ipaddress'], '光纤交换机', 'it_netdevice', $netJiguiId);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $netJiguiId = explode(',', $data['net_jigui_id']);

            $data = $model->create($data);
            $data['net_atplastmodifydatetime'] = $time;
            $data['net_atplastmodifyuser'] = $user;

            $res = $model->where("net_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('it_netdevice', '对象修改日志', 'update', '修改xxx' . '失败', '失败');
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
                addLog('it_netdevice', '对象修改日志', 'update', '修改xxx' . '成功', '成功');
                $this->changeRelationJigui($data['net_atpid'], '', $data['net_ipaddress'], '光纤交换机', 'it_netdevice', $netJiguiId);
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }

    /**
     * 获取交换机管理数据
     */
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'net_name,net_type,net_ipaddress,net_factory,net_model,net_area,net_building,net_room,net_status,net_isscan,net_anecode,net_jigui,net_jiwei,net_portnum,net_mask,net_gateway,net_usage,net_protocol,net_managehost,net_logserver,net_upswitch,net_upport,net_loginport,net_loginuser,net_loginpass,net_globalpass,net_adminpass,net_enabledate,net_secretlevel,net_code,net_devicebook,net_privacybook,net_sn,net_caigoudate,net_source,net_dutydept,net_net,net_iso,net_memo,net_iscore,net_configinfo,net_dutyman,net_manage,net_portdetail,net_useman,net_usedept,net_repairtype,net_daobaodate,net_weibaodate';
        } else {
            $filedStr = 'net_name,net_type,net_ipaddress,net_factory,net_model,net_area,net_building,net_room,net_status,net_isscan,net_anecode,net_jigui,net_jiwei,net_portnum,net_mask,net_gateway,net_usage,net_protocol,net_managehost,net_logserver,net_upswitch,net_upport,net_loginport,net_loginuser,net_loginpass,net_globalpass,net_adminpass,net_enabledate,net_secretlevel,net_code,net_devicebook,net_privacybook,net_sn,net_caigoudate,net_source,net_dutydept,net_net,net_iso,net_memo,net_iscore,net_configinfo,net_dutyman,net_manage,net_portdetail,net_useman,net_usedept,net_repairtype,net_daobaodate,net_weibaodate,net_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
//        $netType = isset($_GET['net_type']) ? $_GET['net_type'] : $_POST['net_type'];
        $netType = $queryParam['net_type'];
        $where['net_type'] = ['eq', $netType];
        $where['net_atpstatus'] = ['exp', 'IS NULL'];
        $netIpaddress = trim($queryParam['net_ipaddress']);
        if (!empty($netIpaddress)) $where['net_ipaddress'] = ['like', "%$netIpaddress%"];

        $netFactory = trim($queryParam['net_factory']);
        if (!empty($netFactory)) {
            $netFactory = $this->getDicById($netFactory, 'dic_name') ; //厂家
            $where['net_factory'] = ['like', "%$netFactory%"];
        }

        $netModel = trim($queryParam['net_model']);
        if (!empty($netModel)) {
            $netModel = $this->getDicXingHaoById($netModel, 'dic_name') ; //型号
            $where['net_model'] = ['like', "%$netModel%"];
        }

        $netArea = trim($queryParam['net_area']);
        if (!empty($netArea)) {
            $netArea =  $this->getDicById($netArea, 'dic_name') ; //地区
            $where['net_area'] = ['like', "%$netArea%"];
        }

        $netBuilding = trim($queryParam['net_building']);
        if (!empty($netBuilding)) {
            $netBuilding = $this->getDicLouYuById($netBuilding, 'dic_name') ; //楼宇
            $where['net_building'] = ['like', "%$netBuilding%"];
        }

        $netRoom = trim($queryParam['net_room']);
        if (!empty($netRoom)) $where['net_room'] = ['like', "%$netRoom%"];

        $netUsage = trim($queryParam['net_usage']);
        if (!empty($netUsage)) $where['net_usage'] = ['like', "%$netUsage%"];

        $netProtocol = trim($queryParam['net_protocol']);
        if (!empty($netProtocol)) $where['net_protocol'] = ['like', "%$netProtocol%"];

        $netStatus = trim($queryParam['net_status']);
        if (!empty($netStatus)) $where['net_status'] = ['like', "%$netStatus%"];

        $netSecretlevel = trim($queryParam['net_secretlevel']);
        if (!empty($netSecretlevel)) $where['net_secretlevel'] = ['like', "%$netSecretlevel%"];

        $netAnecode = trim($queryParam['net_anecode']);
        if (!empty($netAnecode)) $where['net_anecode'] = ['like', "%$netAnecode%"];

        $netSn = trim($queryParam['net_sn']);
        if (!empty($netSn)) $where['net_sn'] = ['like', "%$netSn%"];

        $netDutydept = trim($queryParam['net_dutydept']);
        if (!empty($netDutydept)) $where['net_dutydept'] = ['like', "%$netDutydept%"];

        $netNet = trim($queryParam['net_net']);
        if (!empty($netNet)) $where['net_net'] = ['like', "%$netNet%"];

        $netDutyman = trim($queryParam['net_dutyman']);
        if (!empty($netDutyman)) $where['net_dutyman'] = ['like', "%$netDutyman%"];

        $model = M('it_netdevice');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
        ->where($where);
        //            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if ($isExport) {
            $data = $obj->select();
            $header = ['设备名称', '设备类型', 'IP地址', '厂家', '型号', '地区', '楼宇', '房间号', '状态', '是否扫描', '设备编码', '机柜', '机位', '端口数', '子网掩码', '默认网关', '用途', '登录方式', '远程管理主机', '日志服务器', '上联交换机', '上联端口', '登录端口', '登录用户', '登录密码', '全局模式密码', '特权密码', '启用日期', '密级', '部标编码', '仪设台账', '保密台账', '出厂编号', '采购日期', '资产来源', '责任部门', '所属网络', '版本', '备注', '是否核心', '配件信息', '责任人', '管理方式', '端口详情', '使用人', '使用人部门', '维修状态', '到保日期', '维保日期'];
            foreach ($data as $k => &$v) {
                $v['net_isscan'] = $v['net_isscan'] == 1 ? '扫描' : '不扫描';
                //翻译字典
//                $v['net_area'] = !empty($v['net_area']) ? $this->getDicById($v['net_area'], 'dic_name') : '-'; //地区
//                $v['net_building'] = !empty($v['net_building']) ? $this->getDicLouYuById($v['net_building'], 'dic_name') : '-'; //楼宇
//                $v['net_factory'] = !empty($v['net_factory']) ? $this->getDicById($v['net_factory'], 'dic_name') : '-'; //厂家
//                $v['net_model'] = !empty($v['net_model']) ? $this->getDicXingHaoById($v['net_model'], 'dic_name') : '-'; //型号
//                $v['net_status'] = !empty($v['net_status']) ? $this->getDicById($v['net_status'], 'dic_name') : '-'; //使用状态
//                $v['net_secretlevel'] = !empty($v['net_secretlevel']) ? $this->getDicById($v['net_secretlevel'], 'dic_name') : '-'; //密级
//                $v['net_source'] = !empty($v['net_source']) ? $this->getDicById($v['net_source'], 'dic_name') : '-'; //资产来源
//                $v['net_net'] = !empty($v['net_net']) ? $this->getDicById($v['net_net'], 'dic_name') : '-'; //所属网络

                //使用人
                $userId = $v['net_useman'];
                if (!empty($userId)) {
                    $userName = D('org')->getViewPerson($userId);
                    $v['net_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['net_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['net_useman'] = '-';
                    $v['net_usedept'] = '-';
                }
                //责任人
                $dutuserId = $v['net_dutyman'];
                if (!empty($dutuserId)) {
                    $dutuserName = D('org')->getViewPerson($dutuserId);
                    $v['net_dutyman'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')';

                    //责任人部门
                    $v['net_dutydept'] = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
                } else {
                    $v['net_dutyman'] = '-';
                    $v['net_dutydept'] = '-';
                }
            }

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

            foreach ($data as $k => &$v) {
                $v['net_isscan'] = $v['net_isscan'] == 1 ? '扫描' : '不扫描';
                //翻译字典
//                $v['net_area'] = !empty($v['net_area']) ? $this->getDicById($v['net_area'], 'dic_name') : '-'; //地区
//                $v['net_building'] = !empty($v['net_building']) ? $this->getDicLouYuById($v['net_building'], 'dic_name') : '-'; //楼宇
//                $v['net_factory'] = !empty($v['net_factory']) ? $this->getDicById($v['net_factory'], 'dic_name') : '-'; //厂家
//                $v['net_model'] = !empty($v['net_model']) ? $this->getDicXingHaoById($v['net_model'], 'dic_name') : '-'; //型号
//                $v['net_status'] = !empty($v['net_status']) ? $this->getDicById($v['net_status'], 'dic_name') : '-'; //使用状态
//                $v['net_secretlevel'] = !empty($v['net_secretlevel']) ? $this->getDicById($v['net_secretlevel'], 'dic_name') : '-'; //密级
//                $v['net_source'] = !empty($v['net_source']) ? $this->getDicById($v['net_source'], 'dic_name') : '-'; //资产来源
//                $v['net_net'] = !empty($v['net_net']) ? $this->getDicById($v['net_net'], 'dic_name') : '-'; //所属网络

                //使用人
                $userId = $v['net_useman'];
                if (!empty($userId)) {
                    $userName = D('org')->getViewPerson($userId);
                    $v['net_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['net_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['net_useman'] = '-';
                    $v['net_usedept'] = '-';
                }

                //责任人
                $dutuserId = $v['net_dutyman'];
                if (!empty($dutuserId)) {
                    $dutuserName = D('org')->getViewPerson($dutuserId);
                    $v['net_dutyman'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')';

                    //责任人部门
                    $v['net_dutydept'] = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
                } else {
                    $v['net_dutyman'] = '-';
                    $v['net_dutydept'] = '-';
                }
                //机柜关联关系
                $jgAll = D("relation")->getViewRelationInfo($v['net_atpid'], 'jigui');
                if (!empty($jgAll)) {
                    $v['net_jigui'] = '';
                    foreach ($jgAll as $val) {
                        $v['net_jigui'] .= '(' . $val['r_name'] . '),';
                    }
                    $v['net_jigui'] = substr($v['net_jigui'], 0, -1);
                } else {
                    $v['net_jigui'] = '-';
                }
            }
            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }

    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.net_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('it_netdevice');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['net_atplastmodifydatetime'] = $time;
                $data['net_atplastmodifyuser'] = $user;
                $data['net_atpstatus'] = 'DEL';
                $res = $model->where("net_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('it_netdevice', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    D('relation')->delRelation($id, 'it_netdevice');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('it_netdevice', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    /**
     * 设置扫描数据
     */
    public function updataInScan()
    {
        $id = trim(I('post.net_atpid'));
        if (empty($id)) exit(makeStandResult(-1, '参数缺少'));
        $where = [];
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
            $where['net_atpid'] = ['in', $id];
        } else {
            $where['net_atpid'] = ['eq', $id];
        }
        //0为不扫描 1为扫描
        $flag = trim(I('post.flag'));
        if ($flag == 'true') {
            $data['net_isscan'] = '1';
        } else {
            $data['net_isscan'] = '0';
        }
        //        var_dump($flag);die;

        $model = M('it_netdevice');
        $res = $model->where($where)->save($data);
        if ($res) {
            // 修改日志
            addLog('it_netdevice', '设置扫描日志', 'delete', "设置扫描xxx 成功", '成功');
            exit(makeStandResult(1, '设置成功'));
        } else {
            // 修改日志
            addLog('it_netdevice', '设置扫描日志', 'delete', "设置扫描xxx 失败", '失败');
            exit(makeStandResult(1, '设置成功'));
        }
    }

    //生成密码
    public function createLoginPass($loginpass, $ip = null)
    {
        if ($ip === null) return $loginpass;
        if (empty($loginpass)) {
            $pass = 'Castinfo45888' . substr($ip, strrpos($ip, '.') + 1);
        } else {
            $pass = $loginpass;
        }
        return $pass;
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
        $arrField = ['密级', '资产来源', '地区', '厂家', '所属网络', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arrField);
        //当前资产字典id
        $res = M('dic')->field('dic_id')
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='交换机'")
            ->find();
        $netDicId = $res['dic_id'];

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');
        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');
        $changJia = $arrDic['厂家'];
        $changJiaArray = array_column($changJia, 'dic_name');
        $ziYuanLaiYuan = $arrDic['资产来源'];
        $ziYuanLaiYuanArray = array_column($ziYuanLaiYuan, 'dic_name');
        $suoShuWangLuo = $arrDic['所属网络'];
        $suoShuWangLuoArray = array_column($suoShuWangLuo, 'dic_name');
        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');

        //字段
        $fields = ['net_name', 'net_anecode', 'net_sn', 'net_ipaddress', 'net_isscan', 'net_status', 'net_factory', 'net_model', 'net_secretlevel', 'net_area', 'net_building', 'net_room', 'net_upport', 'net_net', 'net_iso', 'net_usage', 'net_iscore', 'net_repairtype', 'net_protocol', 'net_manage', 'net_caigoudate', 'net_daobaodate', 'net_weibaodate', 'net_enabledate', 'net_devicebook', 'net_privacybook', 'net_source', 'net_loginport', 'net_loginuser', 'net_loginpass', 'net_dutyman', 'net_code', 'net_configinfo', 'net_memo'];
        //'net_name', 'net_anecode', 'net_sn', 'net_ipaddress', 'net_isscan', 'net_status', 'net_factory', 'net_model', 'net_secretlevel', 'net_area', 'net_building', 'net_room', 'net_upport', 'net_net', 'net_iso', 'net_usage', 'net_iscore', 'net_repairtype', 'net_protocol', 'net_manage', 'net_caigoudate', 'net_daobaodate', 'net_weibaodate', 'net_enabledate', 'net_devicebook', 'net_privacybook', 'net_source', 'net_loginport', 'net_loginuser', 'net_loginpass', 'net_dutyman', 'net_code', 'net_configinfo', 'net_memo'
        //设备名称,设备编码,出厂编号,IP地址,是否扫描,使用状态,厂家,型号,密级,地区,楼宇,房间号,上联端口,所属网络,版本,用途,是否核心,维修状态,登录方式,管理方式,采购日期,到保日期,维保日期,启用日期,仪设台账,保密台账,资产来源,登录用户,登录密码,责任人,部标编码,配件信息,备注

        $model = M('it_netdevice');
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
                    case 'net_factory': //厂家
                        $deptNameField = 'net_factory';
                        $fieldName = '厂家';
                        if (!empty($v)) {
                            if (!in_array($v, $changJiaArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $changJiaArray);
                            }
                            $dicId = $changJia[$k]['dic_id'];
                            $dicName = $changJia[$k]['dic_name'];
                            $arr[$deptNameField] = $dicName;
                            //型号
                            $xingHaoData = $v;

                            //查字典
                            $xingHao = $this->getDicXingHaoByPid($dicId, $netDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'net_model': //型号
                        $deptNameField = 'net_model';
                        $fieldName = '型号';
                        if (!empty($xingHaoData)) {
                            if (!empty($v)) {
                                if (!in_array($v, $xingHaoArray)) {
                                    $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                    break;
                                } else {
                                    $k = array_search($v, $xingHaoArray);
                                }
                                $arr[$deptNameField] = $xingHao[$k]['dic_name'];
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        } else {
                            if (!empty($v)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 需要填写厂家<br>";
                                break;
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        }
                        break;

                    case 'net_secretlevel': //密级
                        $deptNameField = 'net_secretlevel';
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
                    case 'net_source': //资产来源
                        $deptNameField = 'net_source';
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

                    case 'net_area': //地区
                        $deptNameField = 'net_area';
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

                    case 'net_building': //楼宇
                        $deptNameField = 'net_building';
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
                    case 'net_net': //所属网络
                        $deptNameField = 'net_net';
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
                    case 'net_status': //使用状态
                        $deptNameField = 'net_status';
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

                    case 'net_isscan': //是否扫描
                        $fieldName = '是否扫描';
                        if (!empty($v)) {
                            if ($v == '是') {
                                $arr[$field] = 1;
                            } elseif ($v == '否') {
                                $arr[$field] = 0;
                            } else {
                                $error .= "第{$lineNum} 行 {$fieldName} 请填写是或否<br>";
                                break;
                            }
                        } else {
                            $arr[$field] = $v;
                        }
                        break;
                    case 'net_iscore': //是否核心
                        $fieldName = '是否核心';
                        if (!empty($v)) {
                            if ($v == '是') {
                                $arr[$field] = 1;
                            } elseif ($v == '否') {
                                $arr[$field] = 0;
                            } else {
                                $error .= "第{$lineNum} 行 {$fieldName} 请填写是或否<br>";
                            }
                        } else {
                            $arr[$field] = $v;
                        }
                        break;

                    case 'net_ipaddress': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $ip = $v;
                        $arr[$field] = $v;
                        break;

                    case 'net_room': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'net_anecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'net_usage': //用途
                        $arr[$field] = $v;
                        break;
                    case 'net_protocol': //登录方式
                        $arr[$field] = $v;
                        break;
                    case 'net_upport': //上联端口
                        $arr[$field] = $v;
                        break;
                    case 'net_loginuser': //登录用户
                        //登录用户密码 如果未填就自动生成
                        $arr[$field] = !empty($v) ? $v : 'yunwei';
                        break;
                    case 'net_loginpass': //登录密码
                        //验证 生成密码
                        $arr[$field] = !empty($v) ? $v : $this->createLoginPass($v, $ip);
                        break;
                    case 'net_code': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'net_devicebook': //仪设台账
                        $arr[$field] = $v;
                        break;
                    case 'net_privacybook': //保密台账
                        $arr[$field] = $v;
                        break;
                    case 'net_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'net_iso': //版本
                        $arr[$field] = $v;
                        break;
                    case 'net_memo': //备注
                        $arr[$field] = $v;
                        break;
                    case 'net_configinfo': //配件信息
                        $arr[$field] = $v;
                        break;
                    case 'net_name': //设备名称
                        $fieldName = '设备名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'net_repairtype': //维修状态
                        $arr[$field] = $v;
                        break;

                    case 'net_caigoudate': //采购日期
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
                    case 'net_enabledate': //启用日期
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
                    case 'net_daobaodate': //到保日期
                        $fieldName = '到保日期';
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
                    case 'net_weibaodate': //维保日期
                        $fieldName = '维保日期';
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

                    case 'net_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'net_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                        //责任部门,net_dutydept

                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['net_atpcreatetime'] = $time;
                $arr['net_atpcreateuser'] = $loginUserId;
                $arr['net_type'] = '交换机';

                $arr['net_atpid'] = makeGuid();
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
                addLog('todo', '对象导入日志', 'add', '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
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
    public function opticalSaveCopyTables()
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
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='光纤交换机'")
            ->find();
        $netDicId = $res['dic_id'];

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');
        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');
        $changJia = $arrDic['厂家'];
        $changJiaArray = array_column($changJia, 'dic_name');
        $ziYuanLaiYuan = $arrDic['资产来源'];
        $ziYuanLaiYuanArray = array_column($ziYuanLaiYuan, 'dic_name');
        //        $ziChanDanWei = $arrDic['资产责任单位'];
        //        $shiYongDanWei = $arrDic['使用责任单位'];
        $caoZuoXiTong = $arrDic['操作系统'];
        $caoZuoXiTongArray = array_column($caoZuoXiTong, 'dic_name');
        $suoShuWangLuo = $arrDic['所属网络'];
        $suoShuWangLuoArray = array_column($suoShuWangLuo, 'dic_name');
        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');

        //字段
        $fields = ['net_ipaddress', 'net_factory', 'net_model', 'net_area', 'net_building', 'net_room', 'net_status', 'net_isscan', 'net_anecode', 'net_jiwei', 'net_portnum', 'net_mask', 'net_gateway', 'net_usage', 'net_protocol', 'net_managehost', 'net_logserver', 'net_upswitch', 'net_upport', 'net_loginport', 'net_loginuser', 'net_loginpass', 'net_globalpass', 'net_adminpass', 'net_enabledate', 'net_secretlevel', 'net_code', 'net_devicebook', 'net_privacybook', 'net_sn', 'net_caigoudate', 'net_source', 'net_net', 'net_iso', 'net_memo', 'net_iscore', 'net_configinfo', 'net_dutyman', 'net_manage', 'net_portdetail', 'net_useman'];
        //IP地址,厂家,型号,地区,楼宇,房间号,状态,是否扫描,设备编码,机位,端口数,子网掩码,默认网关,用途,登录方式,远程管理主机,日志服务器,上联交换机,上联端口,登录端口,登录用户,登录密码,全局模式密码,特权密码,启用日期,密级,部标编码,仪设台账,保密台账,出厂编号,采购日期,资产来源,所属网络,版本,备注,是否核心,配件信息,责任人,管理方式,端口详情,使用人

        $model = M('it_netdevice');
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
                    case 'net_factory': //厂家
                        $deptNameField = 'net_factory';
                        $fieldName = '厂家';
                        if (!empty($v)) {
                            if (!in_array($v, $changJiaArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $changJiaArray);
                            }
                            $dicId = $changJia[$k]['dic_id'];
                            $arr[$deptNameField] = $dicId;
                            //型号
                            $xingHaoData = $v;
                            //查字典
                            $xingHao = $this->getDicXingHaoByPid($dicId, $netDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'net_model': //型号
                        $deptNameField = 'net_model';
                        $fieldName = '型号';
                        if (!empty($xingHaoData)) {
                            if (!empty($v)) {
                                if (!in_array($v, $xingHaoArray)) {
                                    $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                    break;
                                } else {
                                    $k = array_search($v, $xingHaoArray);
                                }
                                $arr[$deptNameField] = $xingHao[$k]['dic_id'];
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        } else {
                            if (!empty($v)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 需要填写厂家<br>";
                                break;
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        }
                        break;

                    case 'net_secretlevel': //密级
                        $deptNameField = 'net_secretlevel';
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
                            $dicId = $miJi[$k]['dic_id'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'net_source': //资产来源
                        $deptNameField = 'net_source';
                        $fieldName = '资产来源';
                        if (!empty($v)) {
                            if (!in_array($v, $ziYuanLaiYuanArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $ziYuanLaiYuanArray);
                            }
                            $dicId = $ziYuanLaiYuan[$k]['dic_id'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'net_area': //地区
                        $deptNameField = 'net_area';
                        $fieldName = '地区';
                        if (!empty($v)) {
                            if (!in_array($v, $diQuArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $diQuArray);
                            }
                            $dicId = $diQu[$k]['dic_id'];
                            $arr[$deptNameField] = $dicId;
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

                    case 'net_building': //楼宇
                        $deptNameField = 'net_building';
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
                                $arr[$deptNameField] = $louYu[$k]['dic_id'];
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
                    case 'net_net': //所属网络
                        $deptNameField = 'net_net';
                        $fieldName = '所属网络';
                        if (!empty($v)) {
                            if (!in_array($v, $suoShuWangLuoArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $suoShuWangLuoArray);
                            }
                            $dicId = $suoShuWangLuo[$k]['dic_id'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'net_status': //使用状态
                        $deptNameField = 'net_status';
                        $fieldName = '使用状态';
                        if (!empty($v)) {
                            if (!in_array($v, $zhuangTaiArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $zhuangTaiArray);
                            }
                            $dicId = $zhuangTai[$k]['dic_id'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'net_isscan': //是否扫描
                        $fieldName = '是否扫描';
                        if (!empty($v)) {
                            if ($v == '是') {
                                $arr[$field] = 1;
                            } elseif ($v == '否') {
                                $arr[$field] = 0;
                            } else {
                                $error .= "第{$lineNum} 行 {$fieldName} 请填写是或否<br>";
                                break;
                            }
                        } else {
                            $arr[$field] = $v;
                        }
                        break;
                    case 'net_iscore': //是否核心
                        $fieldName = '是否核心';
                        if (!empty($v)) {
                            if ($v == '是') {
                                $arr[$field] = 1;
                            } elseif ($v == '否') {
                                $arr[$field] = 0;
                            } else {
                                $error .= "第{$lineNum} 行 {$fieldName} 请填写是或否<br>";
                            }
                        } else {
                            $arr[$field] = $v;
                        }
                        break;


                    case 'net_ipaddress': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $ip = $v;
                        $arr[$field] = $v;
                        break;
                    case 'net_mask': //子网掩码
                        $fieldName = '子网掩码';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'net_gateway': //默认网关
                        $fieldName = '默认网关';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} IP格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;


                    case 'net_room': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'net_anecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'net_jiwei': //机位
                        $arr[$field] = $v;
                        break;
                    case 'net_portnum': //端口数
                        $arr[$field] = $v;
                        break;
                    case 'net_usage': //用途
                        $arr[$field] = $v;
                        break;
                    case 'net_protocol': //登录方式
                        $arr[$field] = $v;
                        break;
                    case 'net_managehost': //远程管理主机
                        $arr[$field] = $v;
                        break;
                    case 'net_logserver': //日志服务器
                        $arr[$field] = $v;
                        break;
                    case 'net_upswitch': //上联交换机
                        $arr[$field] = $v;
                        break;
                    case 'net_upport': //上联端口
                        $arr[$field] = $v;
                        break;
                    case 'net_loginport': //登录端口
                        $arr[$field] = $v;
                        break;
                    case 'net_loginuser': //登录用户
                        //登录用户密码 如果未填就自动生成
                        $arr[$field] = !empty($v) ? $v : 'yunwei';
                        break;
                    case 'net_loginpass': //登录密码
                        //验证 生成密码
                        $arr[$field] = !empty($v) ? $v : $this->createLoginPass($v, $ip);
                        break;
                    case 'net_globalpass': //全局模式密码
                        $arr[$field] = $v;
                        break;
                    case 'net_adminpass': //特权密码
                        $arr[$field] = $v;
                        break;
                    case 'net_code': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'net_devicebook': //仪设台账
                        $arr[$field] = $v;
                        break;
                    case 'net_privacybook': //保密台账
                        $arr[$field] = $v;
                        break;
                    case 'net_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'net_iso': //版本
                        $arr[$field] = $v;
                        break;
                    case 'net_memo': //备注
                        $arr[$field] = $v;
                        break;
                    case 'net_configinfo': //配件信息
                        $arr[$field] = $v;
                        break;
                    case 'net_manage': //管理方式
                        $arr[$field] = $v;
                        break;
                    case 'net_portdetail': //端口详情
                        $arr[$field] = $v;
                        break;

                    case 'net_caigoudate': //采购日期
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
                    case 'net_enabledate': //启用日期
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


                    case 'net_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'net_useman';

                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                        //使用人部门,net_usedept
                    case 'net_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'net_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                        //责任部门,net_dutydept

                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['net_atpcreatetime'] = $time;
                $arr['net_atpcreateuser'] = $loginUserId;
                $arr['net_type'] = '光纤交换机';

                $arr['net_atpid'] = makeGuid();
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
                addLog('todo', '对象导入日志', 'add', '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
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
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $powers = D('Admin/RefinePower')->getViewPowers('NetdeviceRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }
}
