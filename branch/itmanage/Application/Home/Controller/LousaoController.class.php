<?php
namespace Home\Controller;
use Think\Controller;
class LousaoController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","访问漏洞扫描设备管理页面","成功");
        //字典
        $arr = ['厂家', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);

        $this->display();
    }

    /**
    * 漏洞扫描设备管理添加或修改
    */
    public function add(){
        $id = trim(I('get.ls_atpid'));
        if(!empty($id)){
            $model = M('lousao');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('ls_atpid,ls_devicecode,ls_modifytime,ls_mask,ls_secretlevel,ls_modelnumber,ls_zssn,ls_usage,ls_modifyuser,ls_dutydept,ls_startusetime,ls_roomno,ls_mac,ls_useman,ls_remark,ls_atpstatus,ls_status,ls_gateway,ls_sn,ls_type,ls_assetsource,ls_createuser,ls_createtime,ls_dutyman,ls_net,ls_ip,ls_yxq,ls_name,ls_area,ls_factory,ls_purchasetime,ls_usedept,ls_assetusedept,ls_assetdutydept,ls_anecode,ls_belongfloor,ls_zsname')
                ->where("ls_atpid='%s'", $id)
                ->find();

            //使用人
            $userId = $data['ls_useman'];
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
            $dutuserId = $data['ls_dutyman'];
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
            $deptId = $data['ls_assetdutydept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $dutydept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['org_id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //使用责任单位
            $deptId = $data['ls_assetusedept'];
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

        $this->assign('ls_type', '漏洞扫描设备');
        $this->assign('data', $data);
        addLog('','用户访问日志',"访问漏洞扫描设备管理添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['ls_atpid']);
        $type = trim($data['ls_type']);

        //验证ip
        if (!empty($data['ls_ip'])) {
            if ($this->checkAddress($data['ls_ip'], 'IP') === false) exit(makeStandResult(-1, 'ip地址有误'));
        }
        //子网掩码
        if (!empty($data['ls_mask'])) {
            if ($this->checkAddress($data['ls_mask'], 'IP') === false) exit(makeStandResult(-1, '子网掩码有误'));
        }
        //子IP地址
        if (!empty($data['ls_gateway'])) {
            if ($this->checkAddress($data['ls_gateway'], 'IP') === false) exit(makeStandResult(-1, '默认网关有误'));
        }

        //验证mac
        if (!empty($data['ls_mac'])) {
            if ($this->checkAddress($data['ls_mac'], 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        }

        $model = M('lousao');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['ls_type'] = $type ? $type : '漏洞扫描设备';
        $data['ls_mac'] = strtoupper($data['ls_mac']);
        $data['ls_area'] = $this->getDicById($data['ls_area'], 'dic_name'); //地区
        $data['ls_belongfloor'] = $this->getDicLouYuById($data['ls_belongfloor'], 'dic_name'); //楼宇
        $data['ls_factory'] = $this->getDicById($data['ls_factory'], 'dic_name'); //厂家
        $data['ls_modelnumber'] = $this->getDicXingHaoById($data['ls_modelnumber'], 'dic_name'); //型号
        if(empty($id)){
            $data['ls_atpid'] = makeGuid();

            $data['ls_createtime'] = $time;
            $data['ls_createuser'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('lousao', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('lousao', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['ls_modifytime'] = $time;
            $data['ls_modifyuser'] = $user;
            $data = $model->create($data);
            $res = $model->where("ls_atpid='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('lousao', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('lousao', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取漏洞扫描设备管理数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'ls_devicecode,ls_anecode,ls_ip,ls_mask,ls_gateway,ls_mac,ls_name,ls_usage,ls_factory,ls_modelnumber,ls_sn,ls_status,ls_secretlevel,ls_assetsource,ls_assetdutydept,ls_assetusedept,ls_purchasetime,ls_startusetime,ls_area,ls_belongfloor,ls_roomno,ls_dutyman,ls_dutydept,ls_useman,ls_usedept,ls_net,ls_remark,ls_yxq,ls_zsname,ls_zssn';
        }else{
            $filedStr = 'ls_devicecode,ls_anecode,ls_ip,ls_mask,ls_gateway,ls_mac,ls_name,ls_usage,ls_factory,ls_modelnumber,ls_sn,ls_status,ls_secretlevel,ls_assetsource,ls_assetdutydept,ls_assetusedept,ls_purchasetime,ls_startusetime,ls_area,ls_belongfloor,ls_roomno,ls_dutyman,ls_dutydept,ls_useman,ls_usedept,ls_net,ls_remark,ls_yxq,ls_zsname,ls_zssn, ls_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['ls_atpstatus'] = ['exp', 'IS NULL'];
        $rqDevicecode = trim($queryParam['ls_devicecode']);
        if(!empty($rqDevicecode)) $where['ls_devicecode'] = ['like', "%$rqDevicecode%"];
        
        $rqAnecode = trim($queryParam['ls_anecode']);
        if(!empty($rqAnecode)) $where['ls_anecode'] = ['like', "%$rqAnecode%"];
        
        $rqIp = trim($queryParam['ls_ip']);
        if(!empty($rqIp)) $where['ls_ip'] = ['like', "%$rqIp%"];
        
        $rqMac = trim($queryParam['ls_mac']);
        if(!empty($rqMac)) $where['ls_mac'] = ['like', "%$rqMac%"];
        
        $rqName = trim($queryParam['ls_name']);
        if(!empty($rqName)) $where['ls_name'] = ['like', "%$rqName%"];
        
        $rqFactory = trim($queryParam['ls_factory']);
        if(!empty($rqFactory)) {
            $rqFactory = $this->getDicById($rqFactory, 'dic_name'); //厂家
            $where['ls_factory'] = ['like', "%$rqFactory%"];
        }
        
        $rqModelnumber = trim($queryParam['ls_modelnumber']);
        if(!empty($rqModelnumber)) {
            $rqModelnumber = $this->getDicXingHaoById($rqModelnumber, 'dic_name'); //型号
            $where['ls_modelnumber'] = ['like', "%$rqModelnumber%"];
        }
        
        $rqArea = trim($queryParam['ls_area']);
        if(!empty($rqArea)) {
            $rqArea = $this->getDicById($rqArea, 'dic_name');
            $where['ls_area'] = ['like', "%$rqArea%"];
        }
        
        $rqBelongfloor = trim($queryParam['ls_belongfloor']);
        if(!empty($rqBelongfloor)) {
            $rqBelongfloor = $this->getDicLouYuById($rqBelongfloor, 'dic_name');
            $where['ls_belongfloor'] = ['like', "%$rqBelongfloor%"];
        }
        
        $rqRoomno = trim($queryParam['ls_roomno']);
        if(!empty($rqRoomno)) $where['ls_roomno'] = ['like', "%$rqRoomno%"];
        
        $model = M('lousao');
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
//                $v['ls_area'] = !empty($v['ls_area']) ? $this->getDicById($v['ls_area'], 'dic_name') : '-'; //地区
//                $v['ls_belongfloor'] = !empty($v['ls_belongfloor']) ? $this->getDicLouYuById($v['ls_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['ls_factory'] = !empty($v['ls_factory']) ? $this->getDicById($v['ls_factory'], 'dic_name') : '-'; //厂家
//                $v['ls_modelnumber'] = !empty($v['ls_modelnumber']) ? $this->getDicXingHaoById($v['ls_modelnumber'], 'dic_name') : '-'; //型号
//                $v['ls_status'] = !empty($v['ls_status']) ? $this->getDicById($v['ls_status'], 'dic_name') : '-'; //状态
//                $v['ls_secretlevel'] = !empty($v['ls_secretlevel']) ? $this->getDicById($v['ls_secretlevel'], 'dic_name') : '-'; //密级
//                $v['ls_assetsource'] = !empty($v['ls_assetsource']) ? $this->getDicById($v['ls_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['ls_net'] = !empty($v['ls_net']) ? $this->getDicById($v['ls_net'], 'dic_name') : '-'; //所属网络

                //使用人
                if (!empty($v['ls_useman'])) {
                    $userName = D('org')->getViewPerson($v['ls_useman']);
                    $v['ls_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['ls_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['ls_useman'] = '-';
                    $v['ls_usedept'] = '-';
                }
                //责任人
                if (!empty($v['ls_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['ls_dutyman']);
                    $v['ls_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['ls_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['ls_dutyman'] = '-';
                    $v['ls_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['ls_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['ls_assetdutydept']);
                    $v['ls_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['ls_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['ls_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['ls_assetusedept']);
                    $v['ls_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['ls_assetusedept'] = '-';
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
//                $v['ls_area'] = !empty($v['ls_area']) ? $this->getDicById($v['ls_area'], 'dic_name') : '-'; //地区
//                $v['ls_belongfloor'] = !empty($v['ls_belongfloor']) ? $this->getDicLouYuById($v['ls_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['ls_factory'] = !empty($v['ls_factory']) ? $this->getDicById($v['ls_factory'], 'dic_name') : '-'; //厂家
//                $v['ls_modelnumber'] = !empty($v['ls_modelnumber']) ? $this->getDicXingHaoById($v['ls_modelnumber'], 'dic_name') : '-'; //型号
//                $v['ls_status'] = !empty($v['ls_status']) ? $this->getDicById($v['ls_status'], 'dic_name') : '-'; //状态
//                $v['ls_secretlevel'] = !empty($v['ls_secretlevel']) ? $this->getDicById($v['ls_secretlevel'], 'dic_name') : '-'; //密级
//                $v['ls_assetsource'] = !empty($v['ls_assetsource']) ? $this->getDicById($v['ls_assetsource'], 'dic_name') : '-'; //资产来源
//                $v['ls_net'] = !empty($v['ls_net']) ? $this->getDicById($v['ls_net'], 'dic_name') : '-'; //所属网络

                //使用人
                if (!empty($v['ls_useman'])) {
                    $userName = D('org')->getViewPerson($v['ls_useman']);
                    $v['ls_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['ls_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['ls_useman'] = '-';
                    $v['ls_usedept'] = '-';
                }
                //责任人
                if (!empty($v['ls_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['ls_dutyman']);
                    $v['ls_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['ls_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['ls_dutyman'] = '-';
                    $v['ls_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['ls_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['ls_assetdutydept']);
                    $v['ls_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['ls_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['ls_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['ls_assetusedept']);
                    $v['ls_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['ls_assetusedept'] = '-';
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
        $ids = trim(I('post.ls_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('lousao');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['ls_modifytime'] = $time;
                $data['ls_modifyuser'] = $user;
                $data['ls_atpstatus'] = 'DEL';
                $res = $model->where("ls_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('lousao', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
//                     D('relation')->delRelation($id, 'lousao');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('lousao', '对象删除日志', 'delete', "删除xxx 失败", '失败');
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
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='漏洞扫描设备'")
            ->find();
        $lsDicId = $res['dic_id'];

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
        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');
        $suoShuWangLuo = $arrDic['所属网络'];
        $suoShuWangLuoArray = array_column($suoShuWangLuo, 'dic_name');

        //字段
        $fields = ['ls_devicecode', 'ls_anecode', 'ls_ip', 'ls_mask', 'ls_gateway', 'ls_mac', 'ls_name', 'ls_usage', 'ls_factory', 'ls_modelnumber', 'ls_sn', 'ls_status', 'ls_secretlevel', 'ls_assetsource', 'ls_assetdutydept', 'ls_assetusedept', 'ls_purchasetime', 'ls_startusetime', 'ls_area', 'ls_belongfloor', 'ls_roomno', 'ls_dutyman', 'ls_useman', 'ls_net', 'ls_remark', 'ls_yxq', 'ls_zsname', 'ls_zssn'];
        //'ls_devicecode', 'ls_anecode', 'ls_ip', 'ls_mask', 'ls_gateway', 'ls_mac', 'ls_name', 'ls_usage', 'ls_factory', 'ls_modelnumber', 'ls_sn', 'ls_status', 'ls_secretlevel', 'ls_assetsource', 'ls_assetdutydept', 'ls_assetusedept', 'ls_purchasetime', 'ls_startusetime', 'ls_area', 'ls_belongfloor', 'ls_roomno', 'ls_dutyman', 'ls_useman', 'ls_net', 'ls_remark', 'ls_yxq', 'ls_zsname', 'ls_zssn'
        //设备编码,部标编码,IP地址,子网掩码,默认网关,MAC地址,名称,主要用途,厂家,型号,出厂编号,使用状态,密级,资产来源,资产责任单位,使用责任单位,采购日期,启用日期,地区,楼宇,房间号,责任人,使用人,所属网络,备注,有效期,证书名称,证书编号
        //        $orgModel = D('Org'); //初始化org model 查询部门id
        //        $userModel = D('User'); //初始化user model 查询用户id

        $model = M('lousao');
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
                    case 'ls_factory': //厂家
                        $deptNameField = 'ls_factory';
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
                            $xingHao = $this->getDicXingHaoByPid($dicId, $lsDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'ls_modelnumber': //型号
                        $deptNameField = 'ls_modelnumber';
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

                    case 'ls_secretlevel': //密级
                        $deptNameField = 'ls_secretlevel';
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
                    case 'ls_assetsource': //资产来源
                        $deptNameField = 'ls_assetsource';
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
                    case 'ls_assetdutydept': //资产责任单位
                        $arr[$field] = $v;
                        break;
                    case 'ls_assetusedept': //使用责任单位
                        $arr[$field] = $v;
                        break;

//                    case 'ls_assetdutydept': //资产责任单位
//                        $deptNameField ='ls_assetdutydept';
//                        $fieldName = '资产责任单位';
//                        if(!empty($v)){
//                            if(!in_array($v,$ziChanDanWei)){
//                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
//                                break;
//                            }
//                        }
//                        $arr[$deptNameField] = $v;
//                        break;
//
//                    case 'ls_assetusedept': //使用责任单位
//                        $deptNameField ='ls_assetusedept';
//                        $fieldName = '使用责任单位';
//                        if(!empty($v)){
//                            if(!in_array($v,$shiYongDanWei)){
//                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
//                                break;
//                            }
//                        }
//                        $arr[$deptNameField] = $v;
//                        break;

                    case 'ls_area': //地区
                        $deptNameField = 'ls_area';
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

                    case 'ls_belongfloor': //楼宇
                        $deptNameField = 'ls_belongfloor';
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
                    case 'ls_status': //使用状态
                        $deptNameField = 'ls_status';
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
                    case 'ls_net': //所属网络
                        $deptNameField = 'ls_net';
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

                    case 'ls_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'ls_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'ls_name': //名称
                        $fieldName = '名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'ls_remark': //备注
                        $arr[$field] = $v;
                        break;

                    case 'ls_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'ls_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'ls_usage': //主要用途
                        $arr[$field] = $v;
                        break;
                        case 'ls_zsname': //证书名称
                        $arr[$field] = $v;
                        break;
                        case 'ls_zssn': //证书编号
                        $arr[$field] = $v;
                        break;

                    case 'ls_mac': //MAC地址
                        $fieldName = 'MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'ls_ip': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'ls_mask': //子网掩码
                        $fieldName = '子网掩码';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'ls_gateway': //默认网关
                        $fieldName = '默认网关';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'ls_purchasetime': //采购日期
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
                    case 'ls_startusetime': //启用日期
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
                    case 'ls_yxq': //有效期
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

                    case 'ls_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'ls_useman';

                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'ls_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'ls_dutyman';
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
                $arr['ls_createtime'] = $time;
                $arr['ls_createuser'] = $loginUserId;
                $arr['ls_type'] = '漏洞扫描设备';

                $arr['ls_atpid'] = makeGuid();
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