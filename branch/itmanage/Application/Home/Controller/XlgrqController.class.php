<?php
namespace Home\Controller;
use Think\Controller;
class XlgrqController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","访问线路干扰器管理页面","成功");
        //字典
        $arr = ['厂家', '地区', '密级', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态']);

        $this->display();
    }

    /**
    * 线路干扰器管理添加或修改
    */
    public function add(){
        $id = trim(I('get.xg_atpid'));
        if(!empty($id)){
            $model = M('xlgrq');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('xg_atpid,xg_createuser,xg_dutydept,xg_modelnumber,xg_purchasetime,xg_area,xg_type,xg_usage,xg_assetusedept,xg_zyd,xg_useman,xg_remark,xg_usedept,xg_belongfloor,xg_factory,xg_startusetime,xg_assetdutydept,xg_dutyman,xg_devicecode,xg_atpstatus,xg_ls,xg_createtime,xg_swinterface,xg_roomno,xg_anecode,xg_secretlevel,xg_cyd,xg_status,xg_ip,xg_modifyuser,xg_assetsource,xg_name,xg_sn,xg_modifytime')
                ->where("xg_atpid='%s'", $id)
                ->find();
            //使用人
            $userId = $data['xg_useman'];
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
            $dutuserId = $data['xg_dutyman'];
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
            $deptId = $data['xg_assetdutydept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $dutydept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['org_id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //使用责任单位
            $deptId = $data['xg_assetusedept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $usedept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $usedept['id'] = $deptInfo['org_id'];
            } else {
                $usedept = [];
            }
            $this->assign('usedept', $usedept);
        }
        $arr = ['密级', '地区', '厂家', '使用状态', '资产来源'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);

        $this->assign('xg_type', '线路干扰器');
        $this->assign('data', $data);
        addLog('','用户访问日志',"访问线路干扰器管理添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['xg_atpid']);
        $type = trim($data['xg_type']);

        //验证ip
        if (!empty($data['xg_ip'])) {
            if ($this->checkAddress($data['xg_ip'], 'IP') === false) exit(makeStandResult(-1, '串扰器交换机地址有误'));
        }
        //验证路数
        if(!empty($data['xg_ls'])){
            if($data['xg_ls'] <= 0) exit(makeStandResult(-1, '路数有误，请输入正整数'));
        }

        $model = M('xlgrq');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['xg_type'] = $type ? $type : '线路干扰器';
        $data['xg_ls'] = intval($data['xg_ls']);
        $data['xg_area'] = $this->getDicById($data['xg_area'], 'dic_name'); //地区
        $data['xg_belongfloor'] = $this->getDicLouYuById($data['xg_belongfloor'], 'dic_name'); //楼宇
        $data['xg_factory'] = $this->getDicById($data['xg_factory'], 'dic_name'); //厂家
        $data['xg_modelnumber'] = $this->getDicXingHaoById($data['xg_modelnumber'], 'dic_name'); //型号
        if(empty($id)){
            $data['xg_atpid'] = makeGuid();

            $data['xg_createtime'] = $time;
            $data['xg_createuser'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('xlgrq', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('xlgrq', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['xg_modifytime'] = $time;
            $data['xg_modifyuser'] = $user;
            $data = $model->create($data);
            $res = $model->where("xg_atpid='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('xlgrq', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('xlgrq', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 获取线路干扰器管理数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'xg_devicecode,xg_anecode,xg_ip,xg_zyd,xg_cyd,xg_ls,xg_name,xg_usage,xg_factory,xg_modelnumber,xg_sn,xg_status,xg_secretlevel,xg_assetsource,xg_assetdutydept,xg_assetusedept,xg_purchasetime,xg_startusetime,xg_area,xg_belongfloor,xg_roomno,xg_dutyman,xg_dutydept,xg_useman,xg_usedept,xg_swinterface,xg_remark';
        }else{
            $filedStr = 'xg_devicecode,xg_anecode,xg_ip,xg_zyd,xg_cyd,xg_ls,xg_name,xg_usage,xg_factory,xg_modelnumber,xg_sn,xg_status,xg_secretlevel,xg_assetsource,xg_assetdutydept,xg_assetusedept,xg_purchasetime,xg_startusetime,xg_area,xg_belongfloor,xg_roomno,xg_dutyman,xg_dutydept,xg_useman,xg_usedept,xg_swinterface,xg_remark, xg_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['xg_atpstatus'] = ['exp', 'IS NULL'];
        $xgDevicecode = trim($queryParam['xg_devicecode']);
        if(!empty($xgDevicecode)) $where['xg_devicecode'] = ['like', "%$xgDevicecode%"];
        
        $xgAnecode = trim($queryParam['xg_anecode']);
        if(!empty($xgAnecode)) $where['xg_anecode'] = ['like', "%$xgAnecode%"];
        
        $xgIp = trim($queryParam['xg_ip']);
        if(!empty($xgIp)) $where['xg_ip'] = ['like', "%$xgIp%"];
        
        $xgName = trim($queryParam['xg_name']);
        if(!empty($xgName)) $where['xg_name'] = ['like', "%$xgName%"];
        
        $xgFactory = trim($queryParam['xg_factory']);
        if(!empty($xgFactory)) {
            $xgFactory = $this->getDicById($xgFactory, 'dic_name'); //厂家
            $where['xg_factory'] = ['like', "%$xgFactory%"];
        }
        
        $xgModelnumber = trim($queryParam['xg_modelnumber']);
        if(!empty($xgModelnumber)) {
            $xgModelnumber = $this->getDicXingHaoById($xgModelnumber, 'dic_name'); //型号
            $where['xg_modelnumber'] = ['like', "%$xgModelnumber%"];
        }
        
        $xgStatus = trim($queryParam['xg_status']);
        if(!empty($xgStatus)) $where['xg_status'] = ['like', "%$xgStatus%"];
        
        $xgSecretlevel = trim($queryParam['xg_secretlevel']);
        if(!empty($xgSecretlevel)) $where['xg_secretlevel'] = ['like', "%$xgSecretlevel%"];
        
        $xgArea = trim($queryParam['xg_area']);
        if(!empty($xgArea)) {
            $xgArea = $this->getDicById($xgArea, 'dic_name');
            $where['xg_area'] = ['like', "%$xgArea%"];
        }
        
        $xgBelongfloor = trim($queryParam['xg_belongfloor']);
        if(!empty($xgBelongfloor)) {
            $xgBelongfloor = $this->getDicLouYuById($xgBelongfloor, 'dic_name');
            $where['xg_belongfloor'] = ['like', "%$xgBelongfloor%"];
        }
        
        $xgRoomno = trim($queryParam['xg_roomno']);
        if(!empty($xgRoomno)) $where['xg_roomno'] = ['like', "%$xgRoomno%"];
        
        $model = M('xlgrq');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['设备编码','部标编码','串扰器交换机地址','最远端','次远端','路数','名称','主要用途','厂家','型号','出厂编号','使用状态','密级','资产来源','资产责任单位','使用责任单位','采购日期','启用日期','地区','楼宇','房间号','责任人','责任部门','使用人','使用部门','交换机端口','备注'];
            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['xg_area'] = !empty($v['xg_area']) ? $this->getDicById($v['xg_area'], 'dic_name') : '-'; //地区
//                $v['xg_belongfloor'] = !empty($v['xg_belongfloor']) ? $this->getDicLouYuById($v['xg_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['xg_factory'] = !empty($v['xg_factory']) ? $this->getDicById($v['xg_factory'], 'dic_name') : '-'; //厂家
//                $v['xg_modelnumber'] = !empty($v['xg_modelnumber']) ? $this->getDicXingHaoById($v['xg_modelnumber'], 'dic_name') : '-'; //型号
//                $v['xg_status'] = !empty($v['xg_status']) ? $this->getDicById($v['xg_status'], 'dic_name') : '-'; //状态
//                $v['xg_secretlevel'] = !empty($v['xg_secretlevel']) ? $this->getDicById($v['xg_secretlevel'], 'dic_name') : '-'; //密级
//                $v['xg_assetsource'] = !empty($v['xg_assetsource']) ? $this->getDicById($v['xg_assetsource'], 'dic_name') : '-'; //资产来源

                //使用人
                if (!empty($v['xg_useman'])) {
                    $userName = D('org')->getViewPerson($v['xg_useman']);
                    $v['xg_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['xg_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['xg_useman'] = '-';
                    $v['xg_usedept'] = '-';
                }
                //责任人
                if (!empty($v['xg_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['xg_dutyman']);
                    $v['xg_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['xg_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['xg_dutyman'] = '-';
                    $v['xg_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['xg_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['xg_assetdutydept']);
                    $v['xg_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['xg_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['xg_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['xg_assetusedept']);
                    $v['xg_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['xg_assetusedept'] = '-';
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
//                $v['xg_area'] = !empty($v['xg_area']) ? $this->getDicById($v['xg_area'], 'dic_name') : '-'; //地区
//                $v['xg_belongfloor'] = !empty($v['xg_belongfloor']) ? $this->getDicLouYuById($v['xg_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['xg_factory'] = !empty($v['xg_factory']) ? $this->getDicById($v['xg_factory'], 'dic_name') : '-'; //厂家
//                $v['xg_modelnumber'] = !empty($v['xg_modelnumber']) ? $this->getDicXingHaoById($v['xg_modelnumber'], 'dic_name') : '-'; //型号
//                $v['xg_status'] = !empty($v['xg_status']) ? $this->getDicById($v['xg_status'], 'dic_name') : '-'; //状态
//                $v['xg_secretlevel'] = !empty($v['xg_secretlevel']) ? $this->getDicById($v['xg_secretlevel'], 'dic_name') : '-'; //密级
//                $v['xg_assetsource'] = !empty($v['xg_assetsource']) ? $this->getDicById($v['xg_assetsource'], 'dic_name') : '-'; //资产来源

                //使用人
                if (!empty($v['xg_useman'])) {
                    $userName = D('org')->getViewPerson($v['xg_useman']);
                    $v['xg_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['xg_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['xg_useman'] = '-';
                    $v['xg_usedept'] = '-';
                }
                //责任人
                if (!empty($v['xg_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['xg_dutyman']);
                    $v['xg_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['xg_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['xg_dutyman'] = '-';
                    $v['xg_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['xg_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['xg_assetdutydept']);
                    $v['xg_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['xg_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['xg_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['xg_assetusedept']);
                    $v['xg_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['xg_assetusedept'] = '-';
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
        $ids = trim(I('post.xg_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('xlgrq');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['xg_modifytime'] = $time;
                $data['xg_modifyuser'] = $user;
                $data['xg_atpstatus'] = 'DEL';
                $res = $model->where("xg_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('xlgrq', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    // D('relation')->delRelation($id, 'xlgrq');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('xlgrq', '对象删除日志', 'delete', "删除xxx 失败", '失败');
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
        $arrField = ['密级', '资产来源', '地区', '厂家', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arrField);
        //当前资产字典id
        $res = M('dic')->field('dic_id')
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='线路干扰器'")
            ->find();
        $xgDicId = $res['dic_id'];

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

        //字段
        $fields = ['xg_devicecode', 'xg_anecode', 'xg_ip', 'xg_zyd', 'xg_cyd', 'xg_ls', 'xg_name', 'xg_usage', 'xg_factory', 'xg_modelnumber', 'xg_sn', 'xg_status', 'xg_secretlevel', 'xg_assetsource', 'xg_assetdutydept', 'xg_assetusedept', 'xg_purchasetime', 'xg_startusetime', 'xg_area', 'xg_belongfloor', 'xg_roomno', 'xg_dutyman', 'xg_useman', 'xg_swinterface', 'xg_remark'];
        //'xg_devicecode', 'xg_anecode', 'xg_ip', 'xg_zyd', 'xg_cyd', 'xg_ls', 'xg_name', 'xg_usage', 'xg_factory', 'xg_modelnumber', 'xg_sn', 'xg_status', 'xg_secretlevel', 'xg_assetsource', 'xg_assetdutydept', 'xg_assetusedept', 'xg_purchasetime', 'xg_startusetime', 'xg_area', 'xg_belongfloor', 'xg_roomno', 'xg_dutyman', 'xg_useman', 'xg_swinterface', 'xg_remark'
        //设备编码,部标编码,串扰器交换机地址,最远端,次远端,路数,名称,主要用途,厂家,型号,出厂编号,使用状态,密级,资产来源,资产责任单位,使用责任单位,采购日期,启用日期,地区,楼宇,房间号,责任人,使用人,交换机端口,备注


        $model = M('xlgrq');
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
                    case 'xg_factory': //厂家
                        $deptNameField = 'xg_factory';
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
                            $xingHao = $this->getDicXingHaoByPid($dicId, $xgDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'xg_modelnumber': //型号
                        $deptNameField = 'xg_modelnumber';
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

                    case 'xg_secretlevel': //密级
                        $deptNameField = 'xg_secretlevel';
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
                    case 'xg_assetsource': //资产来源
                        $deptNameField = 'xg_assetsource';
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

                    case 'xg_area': //地区
                        $deptNameField = 'xg_area';
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

                    case 'xg_belongfloor': //楼宇
                        $deptNameField = 'xg_belongfloor';
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
                    case 'xg_status': //使用状态
                        $deptNameField = 'xg_status';
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

                    case 'xg_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'xg_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'xg_name': //名称
                        $fieldName = '名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'xg_remark': //备注
                        $arr[$field] = $v;
                        break;

                    case 'xg_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'xg_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'xg_usage': //主要用途
                        $arr[$field] = $v;
                        break;
                    case 'xg_zyd': //最远端
                        $arr[$field] = $v;
                        break;
                    case 'xg_cyd': //次远端
                        $arr[$field] = $v;
                        break;
                    case 'xg_ls': //路数
                    $fieldName = '路数';
                        if(!empty($v)){
                            if($v <= 0){
                                $error .= "第{$lineNum} 行 {$fieldName} 填正整数<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'xg_swinterface': //交换机端口
                        $arr[$field] = $v;
                        break;

                    case 'xg_ip': //串扰器交换机地址
                        $fieldName = '串扰器交换机地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;

                    case 'xg_purchasetime': //采购日期
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
                    case 'xg_startusetime': //启用日期
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
                    
                    case 'xg_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'xg_useman';

                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'xg_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'xg_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'xg_assetdutydept': //资产责任单位
                        $fieldName = '资产责任单位';
                        $userNameField = 'xg_assetdutydept';
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
                    case 'xg_assetusedept': //使用责任单位
                        $fieldName = '使用责任单位';
                        $userNameField = 'xg_assetusedept';
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
                $arr['xg_createtime'] = $time;
                $arr['xg_createuser'] = $loginUserId;
                $arr['xg_type'] = '密码机';

                $arr['xg_atpid'] = makeGuid();
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