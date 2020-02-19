<?php
namespace Home\Controller;
use Think\Controller;
class SpgrqController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","访问视频干扰器管理页面","成功");
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
    * 视频干扰器管理添加或修改
    */
    public function add(){
        $id = trim(I('get.sg_atpid'));
        if(!empty($id)){
            $model = M('spgrq');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('sg_atpid,sg_createuser,sg_dutydept,sg_modelnumber,sg_purchasetime,sg_area,sg_type,sg_usage,sg_assetusedept,sg_useman,sg_phone,sg_remark,sg_usedept,sg_belongfloor,sg_factory,sg_startusetime,sg_assetdutydept,sg_dutyman,sg_devicecode,sg_atpstatus,sg_createtime,sg_roomno,sg_anecode,sg_secretlevel,sg_lydate,sg_status,sg_modifyuser,sg_name,sg_sn,sg_modifytime')
                ->where("sg_atpid='%s'", $id)
                ->find();
            //使用人
            $userId = $data['sg_useman'];
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
            $dutuserId = $data['sg_dutyman'];
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
            $deptId = $data['sg_assetdutydept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $dutydept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['org_id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //使用责任单位
            $deptId = $data['sg_assetusedept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getOrgInfo($deptId);

                $usedept['name'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                $usedept['id'] = $deptInfo['org_id'];
            } else {
                $usedept = [];
            }
            $this->assign('usedept', $usedept);
        }
        $arr = ['密级', '地区', '厂家', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('zhuangTai', $arrDic['使用状态']);

        $this->assign('sg_type', '视频干扰器');
        $this->assign('data', $data);
        addLog('','用户访问日志',"访问视频干扰器管理添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['sg_atpid']);
        $type = trim($data['sg_type']);


        $model = M('spgrq');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['sg_type'] = $type ? $type : '视频干扰器';
        $data['sg_area'] = $this->getDicById($data['sg_area'], 'dic_name'); //地区
        $data['sg_belongfloor'] = $this->getDicLouYuById($data['sg_belongfloor'], 'dic_name'); //楼宇
        $data['sg_factory'] = $this->getDicById($data['sg_factory'], 'dic_name'); //厂家
        $data['sg_modelnumber'] = $this->getDicXingHaoById($data['sg_modelnumber'], 'dic_name'); //型号
        if(empty($id)){
            $data['sg_atpid'] = makeGuid();

            $data['sg_createtime'] = $time;
            $data['sg_createuser'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('spgrq', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('spgrq', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['sg_modifytime'] = $time;
            $data['sg_modifyuser'] = $user;
            $data = $model->create($data);
            $res = $model->where("sg_atpid='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('spgrq', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('spgrq', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 获取视频干扰器管理数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'sg_devicecode,sg_anecode,sg_name,sg_usage,sg_factory,sg_modelnumber,sg_sn,sg_status,sg_secretlevel,sg_lydate,sg_assetdutydept,sg_assetusedept,sg_purchasetime,sg_startusetime,sg_area,sg_belongfloor,sg_roomno,sg_dutyman,sg_dutydept,sg_useman,sg_usedept,sg_phone,sg_remark';
        }else{
            $filedStr = 'sg_devicecode,sg_anecode,sg_name,sg_usage,sg_factory,sg_modelnumber,sg_sn,sg_status,sg_secretlevel,sg_lydate,sg_assetdutydept,sg_assetusedept,sg_purchasetime,sg_startusetime,sg_area,sg_belongfloor,sg_roomno,sg_dutyman,sg_dutydept,sg_useman,sg_usedept,sg_phone,sg_remark, sg_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['sg_atpstatus'] = ['exp', 'IS NULL'];
        $xgDevicecode = trim($queryParam['sg_devicecode']);
        if(!empty($xgDevicecode)) $where['sg_devicecode'] = ['like', "%$xgDevicecode%"];
        
        $xgAnecode = trim($queryParam['sg_anecode']);
        if(!empty($xgAnecode)) $where['sg_anecode'] = ['like', "%$xgAnecode%"];
        
        $xgName = trim($queryParam['sg_name']);
        if(!empty($xgName)) $where['sg_name'] = ['like', "%$xgName%"];
        
        $xgFactory = trim($queryParam['sg_factory']);
        if(!empty($xgFactory)) {
            $xgFactory = $this->getDicById($xgFactory, 'dic_name'); //厂家
            $where['sg_factory'] = ['like', "%$xgFactory%"];
        }
        
        $xgModelnumber = trim($queryParam['sg_modelnumber']);
        if(!empty($xgModelnumber)) {
            $xgModelnumber = $this->getDicXingHaoById($xgModelnumber, 'dic_name'); //型号
            $where['sg_modelnumber'] = ['like', "%$xgModelnumber%"];
        }
        
        $xgStatus = trim($queryParam['sg_status']);
        if(!empty($xgStatus)) $where['sg_status'] = ['like', "%$xgStatus%"];
        
        $xgSecretlevel = trim($queryParam['sg_secretlevel']);
        if(!empty($xgSecretlevel)) $where['sg_secretlevel'] = ['like', "%$xgSecretlevel%"];
        
        $xgArea = trim($queryParam['sg_area']);
        if(!empty($xgArea)) {
            $xgArea = $this->getDicById($xgArea, 'dic_name');
            $where['sg_area'] = ['like', "%$xgArea%"];
        }
        
        $xgBelongfloor = trim($queryParam['sg_belongfloor']);
        if(!empty($xgBelongfloor)) {
            $xgBelongfloor = $this->getDicLouYuById($xgBelongfloor, 'dic_name');
            $where['sg_belongfloor'] = ['like', "%$xgBelongfloor%"];
        }
        
        $xgRoomno = trim($queryParam['sg_roomno']);
        if(!empty($xgRoomno)) $where['sg_roomno'] = ['like', "%$xgRoomno%"];
        
        $model = M('spgrq');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['设备编码','部标编码','名称','主要用途','厂家','型号','出厂编号','使用状态','密级','领用时间','资产责任单位','使用责任单位','采购日期','启用日期','地区','楼宇','房间号','责任人','责任部门','使用人','使用部门','联系方式','备注'];
            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['sg_area'] = !empty($v['sg_area']) ? $this->getDicById($v['sg_area'], 'dic_name') : '-'; //地区
//                $v['sg_belongfloor'] = !empty($v['sg_belongfloor']) ? $this->getDicLouYuById($v['sg_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['sg_factory'] = !empty($v['sg_factory']) ? $this->getDicById($v['sg_factory'], 'dic_name') : '-'; //厂家
//                $v['sg_modelnumber'] = !empty($v['sg_modelnumber']) ? $this->getDicXingHaoById($v['sg_modelnumber'], 'dic_name') : '-'; //型号
//                $v['sg_status'] = !empty($v['sg_status']) ? $this->getDicById($v['sg_status'], 'dic_name') : '-'; //状态
//                $v['sg_secretlevel'] = !empty($v['sg_secretlevel']) ? $this->getDicById($v['sg_secretlevel'], 'dic_name') : '-'; //密级

                //使用人
                if (!empty($v['sg_useman'])) {
                    $userName = D('org')->getViewPerson($v['sg_useman']);
                    $v['sg_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['sg_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sg_useman'] = '-';
                    $v['sg_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sg_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sg_dutyman']);
                    $v['sg_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['sg_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sg_dutyman'] = '-';
                    $v['sg_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['sg_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['sg_assetdutydept']);
                    $v['sg_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['sg_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['sg_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['sg_assetusedept']);
                    $v['sg_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['sg_assetusedept'] = '-';
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
//                $v['sg_area'] = !empty($v['sg_area']) ? $this->getDicById($v['sg_area'], 'dic_name') : '-'; //地区
//                $v['sg_belongfloor'] = !empty($v['sg_belongfloor']) ? $this->getDicLouYuById($v['sg_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['sg_factory'] = !empty($v['sg_factory']) ? $this->getDicById($v['sg_factory'], 'dic_name') : '-'; //厂家
//                $v['sg_modelnumber'] = !empty($v['sg_modelnumber']) ? $this->getDicXingHaoById($v['sg_modelnumber'], 'dic_name') : '-'; //型号
//                $v['sg_status'] = !empty($v['sg_status']) ? $this->getDicById($v['sg_status'], 'dic_name') : '-'; //状态
//                $v['sg_secretlevel'] = !empty($v['sg_secretlevel']) ? $this->getDicById($v['sg_secretlevel'], 'dic_name') : '-'; //密级

                //使用人
                if (!empty($v['sg_useman'])) {
                    $userName = D('org')->getViewPerson($v['sg_useman']);
                    $v['sg_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['sg_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sg_useman'] = '-';
                    $v['sg_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sg_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sg_dutyman']);
                    $v['sg_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['sg_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sg_dutyman'] = '-';
                    $v['sg_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['sg_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['sg_assetdutydept']);
                    $v['sg_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['sg_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['sg_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['sg_assetusedept']);
                    $v['sg_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['sg_assetusedept'] = '-';
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
        $ids = trim(I('post.sg_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('spgrq');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['sg_modifytime'] = $time;
                $data['sg_modifyuser'] = $user;
                $data['sg_atpstatus'] = 'DEL';
                $res = $model->where("sg_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('spgrq', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    // D('relation')->delRelation($id, 'spgrq');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('spgrq', '对象删除日志', 'delete', "删除xxx 失败", '失败');
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
        $arrField = ['密级', '地区', '厂家', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arrField);
        //当前资产字典id
        $res = M('dic')->field('dic_id')
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='视频干扰器'")
            ->find();
        $xgDicId = $res['dic_id'];

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');
        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');
        $changJia = $arrDic['厂家'];
        $changJiaArray = array_column($changJia, 'dic_name');
        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');

        //字段
        $fields = ['sg_devicecode', 'sg_anecode', 'sg_name', 'sg_usage', 'sg_factory', 'sg_modelnumber', 'sg_sn', 'sg_status', 'sg_secretlevel', 'sg_lydate', 'sg_assetdutydept', 'sg_assetusedept', 'sg_purchasetime', 'sg_startusetime', 'sg_area', 'sg_belongfloor', 'sg_roomno', 'sg_dutyman', 'sg_useman', 'sg_phone', 'sg_remark'];
        //'sg_devicecode', 'sg_anecode', 'sg_name', 'sg_usage', 'sg_factory', 'sg_modelnumber', 'sg_sn', 'sg_status', 'sg_secretlevel', 'sg_assetdutydept', 'sg_assetusedept', 'sg_purchasetime', 'sg_startusetime', 'sg_area', 'sg_belongfloor', 'sg_roomno', 'sg_dutyman', 'sg_useman', 'sg_phone', 'sg_remark'
        //设备编码,部标编码,名称,主要用途,厂家,型号,出厂编号,使用状态,密级,领用时间,资产责任单位,使用责任单位,采购日期,启用日期,地区,楼宇,房间号,责任人,使用人,联系方式,备注

        $model = M('spgrq');
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
                    case 'sg_factory': //厂家
                        $deptNameField = 'sg_factory';
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

                    case 'sg_modelnumber': //型号
                        $deptNameField = 'sg_modelnumber';
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

                    case 'sg_secretlevel': //密级
                        $deptNameField = 'sg_secretlevel';
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

                    case 'sg_area': //地区
                        $deptNameField = 'sg_area';
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

                    case 'sg_belongfloor': //楼宇
                        $deptNameField = 'sg_belongfloor';
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
                    case 'sg_status': //使用状态
                        $deptNameField = 'sg_status';
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

                    case 'sg_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'sg_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'sg_name': //名称
                        $fieldName = '名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sg_phone': //联系方式
                        $arr[$field] = $v;
                        break;
                    case 'sg_remark': //备注
                        $arr[$field] = $v;
                        break;

                    case 'sg_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'sg_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'sg_usage': //主要用途
                        $arr[$field] = $v;
                        break;

                    case 'sg_purchasetime': //采购日期
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
                    case 'sg_startusetime': //启用日期
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
                    case 'sg_lydate': //领用时间
                        $fieldName = '领用时间';
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
                    
                    case 'sg_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'sg_useman';

                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'sg_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'sg_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'sg_assetdutydept': //资产责任单位
                        $fieldName = '资产责任单位';
                        $userNameField = 'sg_assetdutydept';
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
                    case 'sg_assetusedept': //使用责任单位
                        $fieldName = '使用责任单位';
                        $userNameField = 'sg_assetusedept';
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
                $arr['sg_createtime'] = $time;
                $arr['sg_createuser'] = $loginUserId;
                $arr['sg_type'] = '密码机';

                $arr['sg_atpid'] = makeGuid();
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