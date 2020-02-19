<?php
namespace Home\Controller;
use Think\Controller;
class RuqinController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","访问入侵检测设备管理页面","成功");
        //字典
        $arr = ['厂家', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);

        $this->display();
    }

    /**
    * 入侵检测设备管理添加或修改
    */
    public function add(){
        $id = trim(I('get.rq_atpid'));
        if(!empty($id)){
            $model = M('ruqin');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('rq_atpid,rq_devicecode,rq_modifytime,rq_mask,rq_secretlevel,rq_modelnumber,rq_zssn,rq_usage,rq_modifyuser,rq_dutydept,rq_startusetime,rq_roomno,rq_mac,rq_useman,rq_remark,rq_atpstatus,rq_status,rq_gateway,rq_sn,rq_type,rq_assetsource,rq_createuser,rq_createtime,rq_dutyman,rq_net,rq_ip,rq_yxq,rq_name,rq_area,rq_factory,rq_purchasetime,rq_usedept,rq_assetusedept,rq_assetdutydept,rq_anecode,rq_belongfloor,rq_zsname')
                ->where("rq_atpid='%s'", $id)
                ->find();

            //使用人
            $userId = $data['rq_useman'];
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
            $dutuserId = $data['rq_dutyman'];
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
            $deptId = $data['rq_assetdutydept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $dutydept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['org_id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //使用责任单位
            $deptId = $data['rq_assetusedept'];
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

        $this->assign('rq_type', '入侵检测设备');
        $this->assign('data', $data);
        addLog('','用户访问日志',"访问入侵检测设备管理添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['rq_atpid']);
        $type = trim($data['rq_type']);

        //验证ip
        if (!empty($data['rq_ip'])) {
            if ($this->checkAddress($data['rq_ip'], 'IP') === false) exit(makeStandResult(-1, 'ip地址有误'));
        }
        //子网掩码
        if (!empty($data['rq_mask'])) {
            if ($this->checkAddress($data['rq_mask'], 'IP') === false) exit(makeStandResult(-1, '子网掩码有误'));
        }
        //子IP地址
        if (!empty($data['rq_gateway'])) {
            if ($this->checkAddress($data['rq_gateway'], 'IP') === false) exit(makeStandResult(-1, '默认网关有误'));
        }

        //验证mac
        if (!empty($data['rq_mac'])) {
            if ($this->checkAddress($data['rq_mac'], 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        }

        $model = M('ruqin');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['rq_type'] = $type ? $type : '入侵检测设备';
        $data['rq_mac'] = strtoupper($data['rq_mac']);
        $data['rq_area'] = $this->getDicById($data['rq_area'], 'dic_name'); //地区
        $data['rq_belongfloor'] = $this->getDicLouYuById($data['rq_belongfloor'], 'dic_name'); //楼宇
        $data['rq_factory'] = $this->getDicById($data['rq_factory'], 'dic_name'); //厂家
        $data['rq_modelnumber'] = $this->getDicXingHaoById($data['rq_modelnumber'], 'dic_name'); //型号
        if(empty($id)){
            $data['rq_atpid'] = makeGuid();

            $data['rq_createtime'] = $time;
            $data['rq_createuser'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('ruqin', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('ruqin', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['rq_modifytime'] = $time;
            $data['rq_modifyuser'] = $user;
            $data = $model->create($data);
            $res = $model->where("rq_atpid='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('ruqin', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('ruqin', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取入侵检测设备管理数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'rq_devicecode,rq_anecode,rq_ip,rq_mask,rq_gateway,rq_mac,rq_name,rq_usage,rq_factory,rq_modelnumber,rq_sn,rq_status,rq_secretlevel,rq_assetsource,rq_assetdutydept,rq_assetusedept,rq_purchasetime,rq_startusetime,rq_area,rq_belongfloor,rq_roomno,rq_dutyman,rq_dutydept,rq_useman,rq_usedept,rq_net,rq_remark,rq_yxq,rq_zsname,rq_zssn';
        }else{
            $filedStr = 'rq_devicecode,rq_anecode,rq_ip,rq_mask,rq_gateway,rq_mac,rq_name,rq_usage,rq_factory,rq_modelnumber,rq_sn,rq_status,rq_secretlevel,rq_assetsource,rq_assetdutydept,rq_assetusedept,rq_purchasetime,rq_startusetime,rq_area,rq_belongfloor,rq_roomno,rq_dutyman,rq_dutydept,rq_useman,rq_usedept,rq_net,rq_remark,rq_yxq,rq_zsname,rq_zssn, rq_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['rq_atpstatus'] = ['exp', 'IS NULL'];
        $rqDevicecode = trim($queryParam['rq_devicecode']);
        if(!empty($rqDevicecode)) $where['rq_devicecode'] = ['like', "%$rqDevicecode%"];
        
        $rqAnecode = trim($queryParam['rq_anecode']);
        if(!empty($rqAnecode)) $where['rq_anecode'] = ['like', "%$rqAnecode%"];
        
        $rqIp = trim($queryParam['rq_ip']);
        if(!empty($rqIp)) $where['rq_ip'] = ['like', "%$rqIp%"];
        
        $rqMac = trim($queryParam['rq_mac']);
        if(!empty($rqMac)) $where['rq_mac'] = ['like', "%$rqMac%"];
        
        $rqName = trim($queryParam['rq_name']);
        if(!empty($rqName)) $where['rq_name'] = ['like', "%$rqName%"];
        
        $rqFactory = trim($queryParam['rq_factory']);
        if(!empty($rqFactory)) {
            $rqFactory = $this->getDicById($rqFactory, 'dic_name'); //地区
            $where['rq_factory'] = ['like', "%$rqFactory%"];
        }
        
        $rqModelnumber = trim($queryParam['rq_modelnumber']);
        if(!empty($rqModelnumber)) {
            $rqModelnumber = $this->getDicXingHaoById($rqModelnumber, 'dic_name');
            $where['rq_modelnumber'] = ['like', "%$rqModelnumber%"];
        }
        
        $rqArea = trim($queryParam['rq_area']);
        if(!empty($rqArea)) {
            $rqArea = $this->getDicById($rqArea, 'dic_name'); //地区
            $where['rq_area'] = ['like', "%$rqArea%"];
        }
        
        $rqBelongfloor = trim($queryParam['rq_belongfloor']);
        if(!empty($rqBelongfloor)) {
            $rqBelongfloor = $this->getDicLouYuById($rqBelongfloor, 'dic_name');
            $where['rq_belongfloor'] = ['like', "%$rqBelongfloor%"];
        }
        
        $rqRoomno = trim($queryParam['rq_roomno']);
        if(!empty($rqRoomno)) $where['rq_roomno'] = ['like', "%$rqRoomno%"];
        
        $model = M('ruqin');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['设备编码','部标编码','IP地址','子网掩码','默认网关','MAC地址','名称','主要用途','厂家','型号','出厂编号','使用状态','密级','资产来源','资产责任单位','使用责任单位','采购日期','启用日期','地区','楼宇','房间号','责任人','责任部门','使用人','使用部门','所属网络','备注','有效期','证书名称','证书编号'];
            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['rq_area'] = !empty($v['rq_area']) ? $this->getDicById($v['rq_area'], 'dic_name') : '-'; //地区
//                $v['rq_belongfloor'] = !empty($v['rq_belongfloor']) ? $this->getDicLouYuById($v['rq_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['rq_factory'] = !empty($v['rq_factory']) ? $this->getDicById($v['rq_factory'], 'dic_name') : '-'; //厂家
//                $v['rq_modelnumber'] = !empty($v['rq_modelnumber']) ? $this->getDicXingHaoById($v['rq_modelnumber'], 'dic_name') : '-'; //型号
//                $v['rq_status'] = !empty($v['rq_status']) ? $this->getDicById($v['rq_status'], 'dic_name') : '-'; //状态
//                $v['rq_secretlevel'] = !empty($v['rq_secretlevel']) ? $this->getDicById($v['rq_secretlevel'], 'dic_name') : '-'; //密级
//                $v['rq_assetsource'] = !empty($v['rq_assetsource']) ? $this->getDicById($v['rq_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['rq_net'] = !empty($v['rq_net']) ? $this->getDicById($v['rq_net'], 'dic_name') : '-'; //所属网络

                //使用人
                if (!empty($v['rq_useman'])) {
                    $userName = D('org')->getViewPerson($v['rq_useman']);
                    $v['rq_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['rq_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['rq_useman'] = '-';
                    $v['rq_usedept'] = '-';
                }
                //责任人
                if (!empty($v['rq_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['rq_dutyman']);
                    $v['rq_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['rq_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['rq_dutyman'] = '-';
                    $v['rq_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['rq_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['rq_assetdutydept']);
                    $v['rq_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['rq_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['rq_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['rq_assetusedept']);
                    $v['rq_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['rq_assetusedept'] = '-';
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
//                $v['rq_area'] = !empty($v['rq_area']) ? $this->getDicById($v['rq_area'], 'dic_name') : '-'; //地区
//                $v['rq_belongfloor'] = !empty($v['rq_belongfloor']) ? $this->getDicLouYuById($v['rq_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['rq_factory'] = !empty($v['rq_factory']) ? $this->getDicById($v['rq_factory'], 'dic_name') : '-'; //厂家
//                $v['rq_modelnumber'] = !empty($v['rq_modelnumber']) ? $this->getDicXingHaoById($v['rq_modelnumber'], 'dic_name') : '-'; //型号
//                $v['rq_status'] = !empty($v['rq_status']) ? $this->getDicById($v['rq_status'], 'dic_name') : '-'; //状态
//                $v['rq_secretlevel'] = !empty($v['rq_secretlevel']) ? $this->getDicById($v['rq_secretlevel'], 'dic_name') : '-'; //密级
//                $v['rq_assetsource'] = !empty($v['rq_assetsource']) ? $this->getDicById($v['rq_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['rq_net'] = !empty($v['rq_net']) ? $this->getDicById($v['rq_net'], 'dic_name') : '-'; //所属网络

                //使用人
                if (!empty($v['rq_useman'])) {
                    $userName = D('org')->getViewPerson($v['rq_useman']);
                    $v['rq_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['rq_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['rq_useman'] = '-';
                    $v['rq_usedept'] = '-';
                }
                //责任人
                if (!empty($v['rq_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['rq_dutyman']);
                    $v['rq_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['rq_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['rq_dutyman'] = '-';
                    $v['rq_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['rq_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['rq_assetdutydept']);
                    $v['rq_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['rq_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['rq_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['rq_assetusedept']);
                    $v['rq_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['rq_assetusedept'] = '-';
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
        $ids = trim(I('post.rq_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('ruqin');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['rq_modifytime'] = $time;
                $data['rq_modifyuser'] = $user;
                $data['rq_atpstatus'] = 'DEL';
                $res = $model->where("rq_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('ruqin', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    // D('relation')->delRelation($id, 'ruqin');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('ruqin', '对象删除日志', 'delete', "删除xxx 失败", '失败');
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
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='入侵检测设备'")
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
        $fields = ['rq_devicecode', 'rq_anecode', 'rq_ip', 'rq_mask', 'rq_gateway', 'rq_mac', 'rq_name', 'rq_usage', 'rq_factory', 'rq_modelnumber', 'rq_sn', 'rq_status', 'rq_secretlevel', 'rq_assetsource', 'rq_assetdutydept', 'rq_assetusedept', 'rq_purchasetime', 'rq_startusetime', 'rq_area', 'rq_belongfloor', 'rq_roomno', 'rq_dutyman', 'rq_useman', 'rq_net', 'rq_remark', 'rq_yxq', 'rq_zsname', 'rq_zssn'];
        //'rq_devicecode', 'rq_anecode', 'rq_ip', 'rq_mask', 'rq_gateway', 'rq_mac', 'rq_name', 'rq_usage', 'rq_factory', 'rq_modelnumber', 'rq_sn', 'rq_status', 'rq_secretlevel', 'rq_assetsource', 'rq_assetdutydept', 'rq_assetusedept', 'rq_purchasetime', 'rq_startusetime', 'rq_area', 'rq_belongfloor', 'rq_roomno', 'rq_dutyman', 'rq_useman', 'rq_net', 'rq_remark', 'rq_yxq', 'rq_zsname', 'rq_zssn'
        //设备编码,部标编码,IP地址,子网掩码,默认网关,MAC地址,名称,主要用途,厂家,型号,出厂编号,使用状态,密级,资产来源,资产责任单位,使用责任单位,采购日期,启用日期,地区,楼宇,房间号,责任人,使用人,所属网络,备注,有效期,证书名称,证书编号

        $model = M('ruqin');
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
                    case 'rq_factory': //厂家
                        $deptNameField = 'rq_factory';
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

                    case 'rq_modelnumber': //型号
                        $deptNameField = 'rq_modelnumber';
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

                    case 'rq_secretlevel': //密级
                        $deptNameField = 'rq_secretlevel';
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
                    case 'rq_assetsource': //资产来源
                        $deptNameField = 'rq_assetsource';
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
                    
                    case 'rq_area': //地区
                        $deptNameField = 'rq_area';
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

                    case 'rq_belongfloor': //楼宇
                        $deptNameField = 'rq_belongfloor';
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
                    case 'rq_status': //使用状态
                        $deptNameField = 'rq_status';
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
                    case 'rq_net': //所属网络
                        $deptNameField = 'rq_net';
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

                    case 'rq_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'rq_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'rq_name': //名称
                        $fieldName = '名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'rq_remark': //备注
                        $arr[$field] = $v;
                        break;

                    case 'rq_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'rq_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'rq_usage': //主要用途
                        $arr[$field] = $v;
                        break;
                        case 'rq_zsname': //证书名称
                        $arr[$field] = $v;
                        break;
                        case 'rq_zssn': //证书编号
                        $arr[$field] = $v;
                        break;

                    case 'rq_mac': //MAC地址
                        $fieldName = 'MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'rq_ip': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'rq_mask': //子网掩码
                        $fieldName = '子网掩码';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'rq_gateway': //默认网关
                        $fieldName = '默认网关';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'rq_purchasetime': //采购日期
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
                    case 'rq_startusetime': //启用日期
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
                    case 'rq_yxq': //有效期
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

                    case 'rq_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'rq_useman';

                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'rq_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'rq_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'rq_assetdutydept': //资产责任单位
                        $fieldName = '资产责任单位';
                        $userNameField = 'rq_assetdutydept';
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
                    case 'rq_assetusedept': //使用责任单位
                        $fieldName = '使用责任单位';
                        $userNameField = 'rq_assetusedept';
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
                $arr['rq_createtime'] = $time;
                $arr['rq_createuser'] = $loginUserId;
                $arr['rq_type'] = '入侵检测设备';

                $arr['rq_atpid'] = makeGuid();
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