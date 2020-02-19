<?php
namespace Home\Controller;
use Think\Controller;
class MimajiController extends BaseController {

    public function index(){
        addLog("","用户访问日志","访问密码机管理页面","成功");
        //字典
        $arr = ['厂家', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);

        $this->display();
    }

    /**
    * 密码机管理添加或修改
    */
    public function add(){
        $id = trim(I('get.mmj_atpid'));
        if(!empty($id)){
            $model = M('mimaji');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('mmj_atpid,mmj_devicecode,mmj_modifytime,mmj_mask,mmj_secretlevel,mmj_modelnumber,mmj_usage,mmj_modifyuser,mmj_dutydept,mmj_startusetime,mmj_roomno,mmj_mac,mmj_useman,mmj_remark,mmj_atpstatus,mmj_status,mmj_gateway,mmj_sn,mmj_type,mmj_assetsource,mmj_createuser,mmj_createtime,mmj_dutyman,mmj_net,mmj_ip,mmj_subip,mmj_submask,mmj_yxq,mmj_name,mmj_area,mmj_factory,mmj_purchasetime,mmj_usedept,mmj_assetusedept,mmj_assetdutydept,mmj_anecode,mmj_belongfloor')
                ->where("mmj_atpid='%s'", $id)
                ->find();

            //使用人
            $userId = $data['mmj_useman'];
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
            $dutuserId = $data['mmj_dutyman'];
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
            $deptId = $data['mmj_assetdutydept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $dutydept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['org_id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //使用责任单位
            $deptId = $data['mmj_assetusedept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $usedept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $usedept['id'] = $deptInfo['org_id'];
            } else {
                $usedept = [];
            }
            $this->assign('usedept', $usedept);
        }
        $arr = ['密级', '地区', '厂家', '使用状态', '资产来源', '所属网络'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);

        $this->assign('mmj_type', '密码机');
        $this->assign('data', $data);
        addLog('','用户访问日志',"访问密码机管理添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['mmj_atpid']);
        $type = trim($data['mmj_type']);

        //验证ip
        if (!empty($data['mmj_ip'])) {
            if ($this->checkAddress($data['mmj_ip'], 'IP') === false) exit(makeStandResult(-1, 'ip地址有误'));
        }
        //子ip地址
        if (!empty($data['mmj_subip'])) {
            if ($this->checkAddress($data['mmj_subip'], 'IP') === false) exit(makeStandResult(-1, '子ip地址有误'));
        }
        //子网掩码
        if (!empty($data['mmj_mask'])) {
            if ($this->checkAddress($data['mmj_mask'], 'IP') === false) exit(makeStandResult(-1, '子网掩码有误'));
        }
        //子IP地址
        if (!empty($data['mmj_gateway'])) {
            if ($this->checkAddress($data['mmj_gateway'], 'IP') === false) exit(makeStandResult(-1, '默认网关有误'));
        }

        //验证mac
        if (!empty($data['mmj_mac'])) {
            if ($this->checkAddress($data['mmj_mac'], 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        }
        //子mac地址
        if (!empty($data['mmj_submask'])) {
            if ($this->checkAddress($data['mmj_submask'], 'MAC') === false) exit(makeStandResult(-1, '子mac地址有误'));
        }

        $model = M('mimaji');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['mmj_type'] = $type ? $type : '密码机';
        $data['mmj_mac'] = strtoupper($data['mmj_mac']);
        $data['mmj_area'] = $this->getDicById($data['mmj_area'], 'dic_name'); //地区
        $data['mmj_belongfloor'] = $this->getDicLouYuById($data['mmj_belongfloor'], 'dic_name'); //楼宇
        $data['mmj_factory'] = $this->getDicById($data['mmj_factory'], 'dic_name'); //厂家
        $data['mmj_modelnumber'] = $this->getDicXingHaoById($data['mmj_modelnumber'], 'dic_name'); //型号
        if(empty($id)){
            $data['mmj_atpid'] = makeGuid();

            $data['mmj_createtime'] = $time;
            $data['mmj_createuser'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('mimaji', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('mimaji', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['mmj_modifytime'] = $time;
            $data['mmj_modifyuser'] = $user;
            $data = $model->create($data);
            $res = $model->where("mmj_atpid='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('mimaji', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('mimaji', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取密码机管理数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'mmj_devicecode,mmj_anecode,mmj_ip,mmj_subip,mmj_submask,mmj_mask,mmj_gateway,mmj_mac,mmj_name,mmj_usage,mmj_factory,mmj_modelnumber,mmj_sn,mmj_status,mmj_secretlevel,mmj_assetsource,mmj_assetdutydept,mmj_assetusedept,mmj_purchasetime,mmj_startusetime,mmj_area,mmj_belongfloor,mmj_roomno,mmj_dutyman,mmj_dutydept,mmj_useman,mmj_usedept,mmj_net,mmj_remark,mmj_yxq';
        }else{
            $filedStr = 'mmj_devicecode,mmj_anecode,mmj_ip,mmj_subip,mmj_submask,mmj_mask,mmj_gateway,mmj_mac,mmj_name,mmj_usage,mmj_factory,mmj_modelnumber,mmj_sn,mmj_status,mmj_secretlevel,mmj_assetsource,mmj_assetdutydept,mmj_assetusedept,mmj_purchasetime,mmj_startusetime,mmj_area,mmj_belongfloor,mmj_roomno,mmj_dutyman,mmj_dutydept,mmj_useman,mmj_usedept,mmj_net,mmj_remark,mmj_yxq, mmj_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['mmj_atpstatus'] = ['exp', 'IS NULL'];
        $rqDevicecode = trim($queryParam['mmj_devicecode']);
        if(!empty($rqDevicecode)) $where['mmj_devicecode'] = ['like', "%$rqDevicecode%"];
        
        $rqAnecode = trim($queryParam['mmj_anecode']);
        if(!empty($rqAnecode)) $where['mmj_anecode'] = ['like', "%$rqAnecode%"];
        
        $rqIp = trim($queryParam['mmj_ip']);
        if(!empty($rqIp)) $where['mmj_ip'] = ['like', "%$rqIp%"];
        
        $rqMac = trim($queryParam['mmj_mac']);
        if(!empty($rqMac)) $where['mmj_mac'] = ['like', "%$rqMac%"];
        
        $rqName = trim($queryParam['mmj_name']);
        if(!empty($rqName)) $where['mmj_name'] = ['like', "%$rqName%"];
        
        $rqFactory = trim($queryParam['mmj_factory']);
        if(!empty($rqFactory)) {
            $rqFactory = $this->getDicById($rqFactory, 'dic_name'); //厂家
            $where['mmj_factory'] = ['like', "%$rqFactory%"];
        }
        
        $rqModelnumber = trim($queryParam['mmj_modelnumber']);
        if(!empty($rqModelnumber)) {
            $rqModelnumber = $this->getDicXingHaoById($rqModelnumber, 'dic_name'); //型号
            $where['mmj_modelnumber'] = ['like', "%$rqModelnumber%"];
        }
        
        $rqArea = trim($queryParam['mmj_area']);
        if(!empty($rqArea)) {
            $rqArea = $this->getDicById($rqArea, 'dic_name');
            $where['mmj_area'] = ['like', "%$rqArea%"];
        }
        
        $rqBelongfloor = trim($queryParam['mmj_belongfloor']);
        if(!empty($rqBelongfloor)) {
            $rqBelongfloor = $this->getDicLouYuById($rqBelongfloor, 'dic_name');
            $where['mmj_belongfloor'] = ['like', "%$rqBelongfloor%"];
        }
        
        $rqRoomno = trim($queryParam['mmj_roomno']);
        if(!empty($rqRoomno)) $where['mmj_roomno'] = ['like', "%$rqRoomno%"];
        
        $model = M('mimaji');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['设备编码','部标编码','IP地址','子网掩码','默认网关','MAC地址','名称','主要用途','厂家','型号','列装号','使用状态','密级','资产来源','资产责任单位','使用责任单位','采购日期','启用日期','地区','楼宇','房间号','责任人','责任部门','使用人','使用部门','所属网络','备注','有效期'];
            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['mmj_area'] = !empty($v['mmj_area']) ? $this->getDicById($v['mmj_area'], 'dic_name') : '-'; //地区
//                $v['mmj_belongfloor'] = !empty($v['mmj_belongfloor']) ? $this->getDicLouYuById($v['mmj_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['mmj_factory'] = !empty($v['mmj_factory']) ? $this->getDicById($v['mmj_factory'], 'dic_name') : '-'; //厂家
//                $v['mmj_modelnumber'] = !empty($v['mmj_modelnumber']) ? $this->getDicXingHaoById($v['mmj_modelnumber'], 'dic_name') : '-'; //型号
//                $v['mmj_status'] = !empty($v['mmj_status']) ? $this->getDicById($v['mmj_status'], 'dic_name') : '-'; //状态
//                $v['mmj_secretlevel'] = !empty($v['mmj_secretlevel']) ? $this->getDicById($v['mmj_secretlevel'], 'dic_name') : '-'; //密级
//                $v['mmj_assetsource'] = !empty($v['mmj_assetsource']) ? $this->getDicById($v['mmj_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['mmj_net'] = !empty($v['mmj_net']) ? $this->getDicById($v['mmj_net'], 'dic_name') : '-'; //所属网络

                //使用人
                if (!empty($v['mmj_useman'])) {
                    $userName = D('org')->getViewPerson($v['mmj_useman']);
                    $v['mmj_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['mmj_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['mmj_useman'] = '-';
                    $v['mmj_usedept'] = '-';
                }
                //责任人
                if (!empty($v['mmj_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['mmj_dutyman']);
                    $v['mmj_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['mmj_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['mmj_dutyman'] = '-';
                    $v['mmj_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['mmj_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['mmj_assetdutydept']);
                    $v['mmj_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['mmj_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['mmj_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['mmj_assetusedept']);
                    $v['mmj_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['mmj_assetusedept'] = '-';
                }
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
                //翻译字典
//                $v['mmj_area'] = !empty($v['mmj_area']) ? $this->getDicById($v['mmj_area'], 'dic_name') : '-'; //地区
//                $v['mmj_belongfloor'] = !empty($v['mmj_belongfloor']) ? $this->getDicLouYuById($v['mmj_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['mmj_factory'] = !empty($v['mmj_factory']) ? $this->getDicById($v['mmj_factory'], 'dic_name') : '-'; //厂家
//                $v['mmj_modelnumber'] = !empty($v['mmj_modelnumber']) ? $this->getDicXingHaoById($v['mmj_modelnumber'], 'dic_name') : '-'; //型号
//                $v['mmj_status'] = !empty($v['mmj_status']) ? $this->getDicById($v['mmj_status'], 'dic_name') : '-'; //状态
//                $v['mmj_secretlevel'] = !empty($v['mmj_secretlevel']) ? $this->getDicById($v['mmj_secretlevel'], 'dic_name') : '-'; //密级
//                $v['mmj_assetsource'] = !empty($v['mmj_assetsource']) ? $this->getDicById($v['mmj_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['mmj_net'] = !empty($v['mmj_net']) ? $this->getDicById($v['mmj_net'], 'dic_name') : '-'; //所属网络

                //使用人
                if (!empty($v['mmj_useman'])) {
                    $userName = D('org')->getViewPerson($v['mmj_useman']);
                    $v['mmj_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['mmj_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['mmj_useman'] = '-';
                    $v['mmj_usedept'] = '-';
                }
                //责任人
                if (!empty($v['mmj_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['mmj_dutyman']);
                    $v['mmj_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['mmj_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['mmj_dutyman'] = '-';
                    $v['mmj_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['mmj_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['mmj_assetdutydept']);
                    $v['mmj_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['mmj_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['mmj_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['mmj_assetusedept']);
                    $v['mmj_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['mmj_assetusedept'] = '-';
                }
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    

    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.mmj_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('mimaji');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['mmj_modifytime'] = $time;
                $data['mmj_modifyuser'] = $user;
                $data['mmj_atpstatus'] = 'DEL';
                $res = $model->where("mmj_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('mimaji', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    // D('relation')->delRelation($id, 'mimaji');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('mimaji', '对象删除日志', 'delete', "删除xxx 失败", '失败');
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
        $arrField = ['密级', '资产来源', '地区', '厂家', '使用状态', '所属网络'];
        $arrDic = D('Dic')->getDicValueByName($arrField);
        //当前资产字典id
        $res = M('dic')->field('dic_id')
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='密码机'")
            ->find();
        $rqDicId = $res['dic_id'];

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');
        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');
        $changJia = $arrDic['厂家'];
        $changJiaArray = array_column($changJia, 'dic_name');
        $ziYuanLaiYuan = $arrDic['资产来源'];
        $ziYuanLaiYuanArray = array_column($ziYuanLaiYuan, 'dic_name');
        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');
        $suoShuWangLuo = $arrDic['所属网络'];
        $suoShuWangLuoArray = array_column($suoShuWangLuo, 'dic_name');

        //字段
        $fields = ['mmj_devicecode', 'mmj_anecode', 'mmj_ip', 'mmj_subip', 'mmj_submask', 'mmj_mask', 'mmj_gateway', 'mmj_mac', 'mmj_name', 'mmj_usage', 'mmj_factory', 'mmj_modelnumber', 'mmj_sn', 'mmj_status', 'mmj_secretlevel', 'mmj_assetsource', 'mmj_assetdutydept', 'mmj_assetusedept', 'mmj_purchasetime', 'mmj_startusetime', 'mmj_area', 'mmj_belongfloor', 'mmj_roomno', 'mmj_dutyman', 'mmj_useman', 'mmj_net', 'mmj_remark', 'mmj_yxq'];
        //'mmj_devicecode', 'mmj_anecode', 'mmj_ip', 'mmj_subip', 'mmj_submask', 'mmj_mask', 'mmj_gateway', 'mmj_mac', 'mmj_name', 'mmj_usage', 'mmj_factory', 'mmj_modelnumber', 'mmj_sn', 'mmj_status', 'mmj_secretlevel', 'mmj_assetsource', 'mmj_assetdutydept', 'mmj_assetusedept', 'mmj_purchasetime', 'mmj_startusetime', 'mmj_area', 'mmj_belongfloor', 'mmj_roomno', 'mmj_dutyman', 'mmj_useman', 'mmj_net', 'mmj_remark', 'mmj_yxq'
        //设备编码,部标编码,IP地址,子ip地址,子mac地址,子网掩码,默认网关,MAC地址,名称,主要用途,厂家,型号,列装号,使用状态,密级,资产来源,资产责任单位,使用责任单位,采购日期,启用日期,地区,楼宇,房间号,责任人,使用人,所属网络,备注,有效期


        $model = M('mimaji');
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
                    case 'mmj_factory': //厂家
                        $deptNameField = 'mmj_factory';
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
                            $xingHao = $this->getDicXingHaoByPid($dicId, $rqDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'mmj_modelnumber': //型号
                        $deptNameField = 'mmj_modelnumber';
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

                    case 'mmj_secretlevel': //密级
                        $deptNameField = 'mmj_secretlevel';
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
                    case 'mmj_assetsource': //资产来源
                        $deptNameField = 'mmj_assetsource';
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

                    case 'mmj_area': //地区
                        $deptNameField = 'mmj_area';
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

                    case 'mmj_belongfloor': //楼宇
                        $deptNameField = 'mmj_belongfloor';
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
                    case 'mmj_status': //使用状态
                        $deptNameField = 'mmj_status';
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
                    case 'mmj_net': //所属网络
                        $deptNameField = 'mmj_net';
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

                    case 'mmj_sn': //列装号
                        $arr[$field] = $v;
                        break;
                    case 'mmj_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'mmj_name': //名称
                        $fieldName = '名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'mmj_remark': //备注
                        $arr[$field] = $v;
                        break;

                    case 'mmj_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'mmj_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'mmj_usage': //主要用途
                        $arr[$field] = $v;
                        break;

                    case 'mmj_mac': //MAC地址
                        $fieldName = 'MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'mmj_submask': //子mac地址
                        $fieldName = '子mac地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'mmj_ip': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'mmj_subip': //子ip地址
                        $fieldName = '子ip地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'mmj_mask': //子网掩码
                        $fieldName = '子网掩码';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'mmj_gateway': //默认网关
                        $fieldName = '默认网关';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'mmj_purchasetime': //采购日期
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
                    case 'mmj_startusetime': //启用日期
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
                    case 'mmj_yxq': //有效期
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

                    case 'mmj_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'mmj_useman';

                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'mmj_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'mmj_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'mmj_assetdutydept': //资产责任单位
                        $fieldName = '资产责任单位';
                        $userNameField = 'mmj_assetdutydept';
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
                    case 'mmj_assetusedept': //使用责任单位
                        $fieldName = '使用责任单位';
                        $userNameField = 'mmj_assetusedept';
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
                $arr['mmj_createtime'] = $time;
                $arr['mmj_createuser'] = $loginUserId;
                $arr['mmj_type'] = '密码机';

                $arr['mmj_atpid'] = makeGuid();
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
}