<?php
namespace Home\Controller;
use Think\Controller;
class FirewallController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","访问防火墙和防毒墙管理页面","成功");
        //字典
        $arr = ['厂家', '地区'];
        $factory = D('Dic')->getFactoryList('防毒墙');
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('changJia', $factory);
        $this->assign('diQu', $arrDic['地区']);

        $this->display();
    }

    /**
    * 防火墙和防毒墙管理添加或修改
    */
    public function add(){
        $id = trim(I('get.fw_atpid'));
        if(!empty($id)){
            $model = M('firewall');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('fw_devicecode,fw_anecode,fw_ip,fw_mask,fw_gateway,fw_mac,fw_name,fw_usage,fw_factory,fw_modelnumber,fw_sn,fw_status,fw_secretlevel,fw_assetsource,fw_assetdutydept,fw_assetusedept,fw_purchasetime,fw_startusetime,fw_area,fw_belongfloor,fw_dutyman,fw_useman,fw_roomno,fw_net,fw_remark,fw_yxq,fw_atpid,fw_type')
                ->where("fw_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //使用人
            $userId = $data['fw_useman'];
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
            $dutuserId = $data['fw_dutyman'];
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
//            //资产责任单位
//            $deptId = $data['fw_assetdutydept'];
//            if (!empty($deptId)) {
//                $deptInfo = D('org')->getOrgInfo($deptId);
//
//                $dutydept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
//                $dutydept['id'] = $deptInfo['org_id'];
//            } else {
//                $dutydept = [];
//            }
//            $this->assign('dutydept', $dutydept);
//
//            //使用责任单位
//            $deptId = $data['fw_assetusedept'];
//            if (!empty($deptId)) {
//                $deptInfo = D('org')->getOrgInfo($deptId);
//
//                $usedept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
//                $usedept['id'] = $deptInfo['org_id'];
//            } else {
//                $usedept = [];
//            }
//            $this->assign('usedept', $usedept);
        }
        $arr = ['密级', '地区', '厂家', '使用状态(防火墙和防毒墙)', '资产来源', '所属网络','资产责任单位','使用责任单位'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $factory = D('Dic')->getFactoryList('防火墙_防毒墙');

        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $factory);
        $this->assign('zhuangTai', $arrDic['使用状态(防火墙和防毒墙)']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);
        $this->assign('zcDept', $arrDic['资产责任单位']);
        $this->assign('syDept', $arrDic['使用责任单位']);

        $this->assign('fw_type', '防火墙和防毒墙');
        $this->assign('data', $data);
        addLog('','用户访问日志',"访问防火墙和防毒墙管理添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['fw_atpid']);
        $type = trim($data['fw_type']);

        //验证ip
        if (!empty($data['fw_ip'])) {
            if ($this->checkAddress($data['fw_ip'], 'IP') === false) exit(makeStandResult(-1, 'ip地址有误'));
        }
        //子网掩码
        if (!empty($data['fw_mask'])) {
            if ($this->checkAddress($data['fw_mask'], 'IP') === false) exit(makeStandResult(-1, '子网掩码有误'));
        }
        //子IP地址
        if (!empty($data['fw_gateway'])) {
            if ($this->checkAddress($data['fw_gateway'], 'IP') === false) exit(makeStandResult(-1, '默认网关有误'));
        }

        //验证mac
        if (!empty($data['fw_mac'])) {
            if ($this->checkAddress($data['fw_mac'], 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        }

        $model = M('firewall');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['fw_type'] = $type ? $type : '防火墙和防毒墙';
        $data['fw_mac'] = strtoupper($data['fw_mac']);
        $data['fw_area'] = $this->getDicById($data['fw_area'], 'dic_name'); //地区
        $data['fw_belongfloor'] = $this->getDicLouYuById($data['fw_belongfloor'], 'dic_name'); //楼宇

        $data['fw_factory'] = $this->getDicFactort($data['fw_factory'], 'dic_name'); //厂家
        $data['fw_modelnumber'] = $this->getDicXingHaoById($data['fw_modelnumber'], 'dic_name'); //型号
        if(empty($id)){
            $model->startTrans();
            try {
                $ip = explode(',',$data['fw_ip']);
                //验证ip是否已被使用
                $Fx = D('ip')->addIpCs($ip,$data['fw_status']);
                if($Fx != 'success'){
                    exit(makeStandResult(-1, $Fx.'IP地址已被使用'));
                }

                $data['fw_atpid'] = makeGuid();

                $data['fw_createtime'] = $time;
                $data['fw_createuser'] = $user;
                $data = $model->create($data);
                $res = $model->add($data);
                $model->commit();
                addLog('firewall', '对象添加日志', '添加主键为'.$data['fw_atpid'].  '成功','成功',$data['fw_atpid']);
                exit(makeStandResult(1,'添加成功'));
            } catch (\Exception $e) {
                $model->rollback();
                addLog('firewall', '对象添加日志',  '添加主键为'.$data['fw_atpid'].  '失败', '失败',$data['fw_atpid']);
                exit(makeStandResult(-1,'添加失败'));
            }
//            if(empty($res)){
//                // 修改日志
//                addLog('firewall', '对象添加日志',  '添加主键为'.$data['fw_atpid'].  '失败', '失败',$data['fw_atpid']);
//                exit(makeStandResult(-1,'添加失败'));
//            }else{
//                // 修改日志
//                addLog('firewall', '对象添加日志', '添加主键为'.$data['fw_atpid'].  '成功','成功',$data['fw_atpid']);
//                exit(makeStandResult(1,'添加成功'));
//            }
        }else{
            $model->startTrans();
            try {
                $sevList = $model->where("fw_atpid = '%s'",$id)->find();
                $subipsUp = explode(';',$sevList['fw_ip']);
                $nowIp = explode(';',$data['fw_ip']);
                //验证ip是否已被使用
                $Fx = D('ip')->saveIpCs($nowIp,$subipsUp,$data['fw_status']);
                if($Fx != 'success'){
                    exit(makeStandResult(-1, $Fx.'IP地址已被使用'));
                }

                $list = session('list');
                $content = LogContent($data,$list);

                $data['fw_modifytime'] = $time;
                $data['fw_modifyuser'] = $user;
                $data = $model->create($data);
                $res = $model->where("fw_atpid='%s'", $id)->save($data);
                $model->commit();
                // 修改日志
                if(!empty($content)) {
                    addLog('firewall', '对象修改日志', $content, '成功', $id);
                }
                exit(makeStandResult(1,'修改成功'));
            } catch (\Exception $e) {
                $model->rollback();
                // 修改日志
                addLog('firewall', '对象修改日志', '修改主键为'.$id. '失败', '失败',$id);
                exit(makeStandResult(-1,'修改失败'));
            }
//            if(empty($res)){
//                // 修改日志
//                addLog('firewall', '对象修改日志', '修改主键为'.$id. '失败', '失败',$id);
//                exit(makeStandResult(-1,'修改失败'));
//            }else{
//                // 修改日志
//                if(!empty($content)) {
//                    addLog('firewall', '对象修改日志', $content, '成功', $id);
//                }
//                exit(makeStandResult(1,'修改成功'));
//            }
        }
    }    

    /**
     * 获取防火墙和防毒墙管理数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'fw_devicecode,fw_anecode,fw_ip,fw_mask,fw_gateway,fw_mac,fw_name,fw_usage,fw_factory,fw_modelnumber,fw_sn,fw_status,fw_secretlevel,fw_assetsource,fw_assetdutydept,fw_assetusedept,fw_purchasetime,fw_startusetime,fw_area,fw_belongfloor,fw_roomno,fw_dutyman,fw_dutydept,fw_useman,fw_usedept,fw_net,fw_remark,fw_yxq';
        }else{
            $filedStr = 'fw_devicecode,fw_anecode,fw_ip,fw_mask,fw_gateway,fw_mac,fw_name,fw_usage,fw_factory,fw_modelnumber,fw_sn,fw_status,fw_secretlevel,fw_assetsource,fw_assetdutydept,fw_assetusedept,fw_purchasetime,fw_startusetime,fw_area,fw_belongfloor,fw_roomno,fw_dutyman,fw_dutydept,fw_useman,fw_usedept,fw_net,fw_remark,fw_yxq, fw_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['fw_atpstatus'] = ['exp', 'IS NULL'];
        $rqDevicecode = trim($queryParam['fw_devicecode']);
        if(!empty($rqDevicecode)) $where['fw_devicecode'] = ['like', "%$rqDevicecode%"];
        
        $rqAnecode = trim($queryParam['fw_anecode']);
        if(!empty($rqAnecode)) $where['fw_anecode'] = ['like', "%$rqAnecode%"];
        
        $rqIp = trim($queryParam['fw_ip']);
        if(!empty($rqIp)) $where['fw_ip'] = ['like', "%$rqIp%"];
        
        $rqMac = trim($queryParam['fw_mac']);
        if(!empty($rqMac)) $where['fw_mac'] = ['like', "%$rqMac%"];
        
        $rqName = trim($queryParam['fw_name']);
        if(!empty($rqName)) $where['fw_name'] = ['like', "%$rqName%"];
        
        $rqFactory = trim($queryParam['fw_factory']);
        if(!empty($rqFactory)) {
            $rqFactory = $this->getDicById($rqFactory, 'dic_name'); //厂家
            $where['fw_factory'] = ['like', "%$rqFactory%"];
        }
        
        $rqModelnumber = trim($queryParam['fw_modelnumber']);
        if(!empty($rqModelnumber)) {
            $rqModelnumber = $this->getDicXingHaoById($rqModelnumber, 'dic_name'); //型号
            $where['fw_modelnumber'] = ['like', "%$rqModelnumber%"];
        }
        
        $rqArea = trim($queryParam['fw_area']);
        if(!empty($rqArea)) {
            $rqArea = $this->getDicById($rqArea, 'dic_name');
            $where['fw_area'] = ['like', "%$rqArea%"];
        }
        
        $rqBelongfloor = trim($queryParam['fw_belongfloor']);
        if(!empty($rqBelongfloor)) {
            $rqBelongfloor = $this->getDicLouYuById($rqBelongfloor, 'dic_name');
            $where['fw_belongfloor'] = ['like', "%$rqBelongfloor%"];
        }
        
        $rqRoomno = trim($queryParam['fw_roomno']);
        if(!empty($rqRoomno)) $where['fw_roomno'] = ['like', "%$rqRoomno%"];
        
        $model = M('firewall');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['设备编码','部标编码','IP地址','子网掩码','默认网关','MAC地址','名称','主要用途','厂家','型号','出厂编号','使用状态','密级','资产来源','资产责任单位','使用责任单位','采购日期','启用日期','地区','楼宇','房间号','责任人','责任部门','使用人','使用部门','所属网络','备注','有效期'];
            foreach ($data as $k => &$v) {
                //使用人
                if (!empty($v['fw_useman'])) {
                    $userName = D('org')->getViewPerson($v['fw_useman']);
                    $v['fw_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['fw_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['fw_useman'] = '-';
                    $v['fw_usedept'] = '-';
                }
                //责任人
                if (!empty($v['fw_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['fw_dutyman']);
                    $v['fw_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['fw_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['fw_dutyman'] = '-';
                    $v['fw_dutydept'] = '-';
                }
//                //资产责任单位
//                if (!empty($v['fw_assetdutydept'])) {
//                    $deptInfo = D('org')->getOrgInfo($v['fw_assetdutydept']);
//                    $v['fw_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
//                } else {
//                    $v['fw_assetdutydept'] = '-';
//                }
//
//                //使用责任单位
//                if (!empty($v['fw_assetusedept'])) {
//                    $deptInfo = D('org')->getOrgInfo($v['fw_assetusedept']);
//                    $v['fw_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
//                } else {
//                    $v['fw_assetusedept'] = '-';
//                }
            }
            if($count <= 0){
              exit(makeStandResult(-1, '没有要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach ($data as $k => &$v) {
                //使用人
                if (!empty($v['fw_useman'])) {
                    $userName = D('org')->getViewPerson($v['fw_useman']);
                    $v['fw_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['fw_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['fw_useman'] = '-';
                    $v['fw_usedept'] = '-';
                }
                //责任人
                if (!empty($v['fw_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['fw_dutyman']);
                    $v['fw_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['fw_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['fw_dutyman'] = '-';
                    $v['fw_dutydept'] = '-';
                }
//                //资产责任单位
//                if (!empty($v['fw_assetdutydept'])) {
//                    $deptInfo = D('org')->getOrgInfo($v['fw_assetdutydept']);
//                    $v['fw_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
//                } else {
//                    $v['fw_assetdutydept'] = '-';
//                }
//
//                //使用责任单位
//                if (!empty($v['fw_assetusedept'])) {
//                    $deptInfo = D('org')->getOrgInfo($v['fw_assetusedept']);
//                    $v['fw_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
//                } else {
//                    $v['fw_assetusedept'] = '-';
//                }
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    

    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.fw_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('firewall');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['fw_modifytime'] = $time;
                $data['fw_modifyuser'] = $user;
                $data['fw_atpstatus'] = 'DEL';
                $res = $model->where("fw_atpid='%s'", $id)->save($data);
                $ip = $model->where("fw_atpid='%s'", $id)->getField('fw_ip');
                if ($res) {
                    // 修改日志
                    D('ip')->DelIpCs([$ip]);
                    addLog('firewall', '对象删除日志',  "删除主键为".$id."成功", '成功',$id);
                    //删除关联关系
                    // D('relation')->delRelation($id, 'firewall');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('firewall', '对象删除日志',  "删除主键为".$ids."失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
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
        $arrField = ['密级', '资产来源', '地区', '厂家', '使用状态(防火墙和防毒墙)', '所属网络','资产责任单位','使用责任单位'];
        $arrDic = D('Dic')->getDicValueByName($arrField);
        //当前资产字典id
//        $res = M('dic')->field('dic_id')
//            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='防火墙和防毒墙'")
//            ->find();
//        $rqDicId = $res['dic_id'];

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');
        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');
        $changJia = D('Dic')->getFactoryList('防火墙_防毒墙');
        $changJiaArray = array_column($changJia, 'dic_name');
        $ziYuanLaiYuan = $arrDic['资产来源'];
        $ziYuanLaiYuanArray = array_column($ziYuanLaiYuan, 'dic_name');
        $zhuangTai = $arrDic['使用状态(防火墙和防毒墙)'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');
        $suoShuWangLuo = $arrDic['所属网络'];
        $suoShuWangLuoArray = array_column($suoShuWangLuo, 'dic_name');
        $dutydept = $arrDic['资产责任单位'];
        $dutydeptArray = array_column($dutydept, 'dic_name');
        $usedept = $arrDic['使用责任单位'];
        $usedeptArray = array_column($usedept, 'dic_name');

        //字段
        $fields = ['fw_devicecode', 'fw_anecode', 'fw_ip', 'fw_mask', 'fw_gateway', 'fw_mac', 'fw_name', 'fw_usage', 'fw_factory', 'fw_modelnumber', 'fw_sn', 'fw_status', 'fw_secretlevel', 'fw_assetsource', 'fw_assetdutydept', 'fw_assetusedept', 'fw_purchasetime', 'fw_startusetime', 'fw_area', 'fw_belongfloor', 'fw_roomno', 'fw_dutyman', 'fw_useman', 'fw_net', 'fw_remark', 'fw_yxq'];
        //'fw_devicecode', 'fw_anecode', 'fw_ip', 'fw_mask', 'fw_gateway', 'fw_mac', 'fw_name', 'fw_usage', 'fw_factory', 'fw_modelnumber', 'fw_sn', 'fw_status', 'fw_secretlevel', 'fw_assetsource', 'fw_assetdutydept', 'fw_assetusedept', 'fw_purchasetime', 'fw_startusetime', 'fw_area', 'fw_belongfloor', 'fw_roomno', 'fw_dutyman', 'fw_useman', 'fw_net', 'fw_remark', 'fw_yxq'

        $model = M('firewall');
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
                    case 'fw_factory': //厂家
                        $deptNameField = 'fw_factory';
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
                            $xingHao = $this->getDicXingHaoByPid($dicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'fw_modelnumber': //型号
                        $deptNameField = 'fw_modelnumber';
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

                    case 'fw_secretlevel': //密级
                        $deptNameField = 'fw_secretlevel';
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
                    case 'fw_assetsource': //资产来源
                        $deptNameField = 'fw_assetsource';
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
                    
                    case 'fw_area': //地区
                        $deptNameField = 'fw_area';
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
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'fw_belongfloor': //楼宇
                        $deptNameField = 'fw_belongfloor';
                        $fieldName = '楼宇';
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
                    case 'fw_status': //使用状态
                        $deptNameField = 'fw_status';
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
                    case 'fw_net': //所属网络
                        $deptNameField = 'fw_net';
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

                    case 'fw_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'fw_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'fw_name': //名称
                        $fieldName = '名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'fw_remark': //备注
                        $arr[$field] = $v;
                        break;

                    case 'fw_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'fw_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'fw_usage': //主要用途
                        $arr[$field] = $v;
                        break;

                    case 'fw_mac': //MAC地址
                        $fieldName = 'MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'fw_ip': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'fw_mask': //子网掩码
                        $fieldName = '子网掩码';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'fw_gateway': //默认网关
                        $fieldName = '默认网关';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'fw_purchasetime': //采购日期
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
                    case 'fw_startusetime': //启用日期
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
                    case 'fw_yxq': //有效期
                        $fieldName = '有效期';
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

                    case 'fw_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'fw_useman';
                        if (!empty($v)) {
                            $userInfo = D('org')->getUserNames($v);
                            if (empty($userInfo)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                                break;
                            }
                            $arr[$userNameField] = $userInfo['domainusername'];
                        }else{
                            $arr[$userNameField] = '';
                        }

                        break;
                    case 'fw_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'fw_dutyman';
                        if (!empty($v)) {
                            $userInfo = D('org')->getUserNames($v);
                            if (empty($userInfo)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                                break;
                            }
                            $arr[$userNameField] = $userInfo['domainusername'];
                        }else{
                            $arr[$userNameField] = '';
                        }
                            break;
                        case
                            'fw_assetdutydept': //密级
                        $deptNameField = 'fw_assetdutydept';
                        $fieldName = '资产责任单位';
                        if (!empty($v)) {
                            if (!in_array($v, $dutydeptArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $dutydeptArray);
                            }
                            $dicId = $dutydept[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'fw_assetusedept': //密级
                        $deptNameField = 'fw_assetusedept';
                        $fieldName = '使用责任单位';
                        if (!empty($v)) {
                            if (!in_array($v, $usedeptArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $usedeptArray);
                            }
                            $dicId = $usedept[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['fw_createtime'] = $time;
                $arr['fw_createuser'] = $loginUserId;
                $arr['fw_type'] = '防火墙和防毒墙';

                $arr['fw_atpid'] = makeGuid();
            }
            $initTables[] = $arr;
        }

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $key => $value) {
                    $res = $model->add($value);
                    if (!empty($res)) $successNum += 1;
                    $ip = explode(',',$value['fw_ip']);
                    //验证ip是否已被使用
                    $Fx = D('ip')->addIpCs($ip,$value['fw_status']);
                    $num =$key+1;
                    if($Fx != 'success'){
                        exit(makeStandResult(-1, '第'.$num.'行IP地址：'.$Fx.'IP地址已被使用'));
                    }
                }
                $failNum = $exportNum - $successNum;
                addLog('firewall', '对象导入日志',  '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
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
}