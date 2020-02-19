<?php

namespace Home\Controller;

use Think\Controller;

class DianyuanController extends BaseController
{

    public function index()
    {
        $arr = ['密级', '使用状态(电源机柜)', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $factory = D('Dic')->getFactoryList('电源');

        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态(电源机柜)']);
        $this->assign('changJia', $factory);
        $this->assign('diQu', $arrDic['地区']);
        addLog("", "用户访问日志", "访问电源管理页面", "成功");
        $this->display();
    }

    /**
     * 电源管理添加或修改
     */
    public function add()
    {
        $id = trim(I('get.dy_atpid'));
        if (!empty($id)) {
            $model = M('dianyuan');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('dy_atpid,dy_roomno,dy_jg,dy_useman,dy_remark,dy_dutydept,dy_factory,dy_dutyman,dy_type,dy_devicecode,dy_belongfloor,dy_area,dy_name,dy_anecode,dy_modelnumber,dy_usedept,dy_status,dy_purchasetime,dy_startusetime')
                ->where("dy_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //使用人
            $userId = $data['dy_useman'];
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
            $dutuserId = $data['dy_dutyman'];
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

            if(!empty($data['dy_jg'])){
                $ids = explode(',',$data['dy_jg']);
                $wheres['jg_atpid'] = ['in',$ids];
                $name = M('jigui')->field('jg_name,jg_atpid')->where($wheres)->select();
                $this->assign('jg_name', $name);
            }

        }
        $arr = ['密级', '使用状态(电源机柜)', '地区','设备类型(电源)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
<<<<<<< .mine

=======
        $factory = D('Dic')->getFactoryList('电源');
>>>>>>> .r34237
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态(电源机柜)']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('dy_type', $arrDic['设备类型(电源)']);
        $this->assign('changJia', $factory);
        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问电源管理添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['dy_atpid']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('dianyuan');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容

        $data['dy_jg'] = implode(',',$data['dy_jg']);
        $data['dy_dutydept'] = D('org')->getDeptId($data['dy_dutyman']);
        $data['dy_usedept'] = D('org')->getDeptId($data['dy_useman']);
        $data['dy_area'] = $this->getDicById($data['dy_area'], 'dic_name') ; //地区
        $data['dy_belongfloor'] = $this->getDicLouYuById($data['dy_belongfloor'], 'dic_name'); //楼宇
        $data['dy_factory'] = $this->getDicFactort($data['dy_factory'], 'dic_name') ; //厂家
        $data['dy_modelnumber'] = $this->getDicXingHaoById($data['dy_modelnumber'], 'dic_name'); //型号
        if (empty($id)) {
            $data['dy_atpid'] = makeGuid();
            $data['dy_createtime'] = $time;
            $data['dy_createuser'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('dianyuan', '对象添加日志', 'add', '添加主键为' . $data['dy_atpid'], '失败',$data['dy_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('dianyuan', '对象添加日志', 'add', '添加主键为' . $data['dy_atpid'], '成功',$data['dy_atpid']);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {

            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);
            $data['dy_modifytime'] = $time;
            $data['dy_modifyuser'] = $user;
//            print_r($data);die;
            $res = $model->where("dy_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('dianyuan', '对象修改日志', 'update', '修改主键为' . $id, '失败');
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
                if(!empty($content)){
                    addLog('dianyuan', '对象修改日志',  $content , '成功',$id);
                }
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }

    /**
     * 获取电源管理数据
     */
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'dy_devicecode,dy_anecode,dy_type,dy_name,dy_factory,dy_modelnumber,dy_status,dy_purchasetime,dy_startusetime,dy_area,dy_belongfloor,dy_roomno,dy_dutyman,dy_dutydept,dy_useman,dy_usedept,dy_jg,dy_remark';
        } else {
            $filedStr = 'dy_devicecode,dy_anecode,dy_type,dy_name,dy_factory,dy_modelnumber,dy_status,dy_purchasetime,dy_startusetime,dy_area,dy_belongfloor,dy_roomno,dy_dutyman,dy_dutydept,dy_useman,dy_usedept,dy_jg,dy_remark, dy_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $where['dy_atpstatus'] = ['exp', 'IS NULL'];
        $dyDevicecode = trim($queryParam['dy_devicecode']);
        if (!empty($dyDevicecode)) $where['dy_devicecode'] = ['like', "%$dyDevicecode%"];

        $dyAnecode = trim($queryParam['dy_anecode']);
        if (!empty($dyAnecode)) $where['dy_anecode'] = ['like', "%$dyAnecode%"];

        $dyName = trim($queryParam['dy_name']);
        if (!empty($dyName)) $where['dy_name'] = ['like', "%$dyName%"];

        $dy_dutydept = trim($queryParam['dy_dutydept']);
        if (!empty($dy_dutydept)) $where['dy_dutydept'] = ['like', "%$dy_dutydept%"];

        $dy_purchasetime = trim($queryParam['dy_purchasetime']);
        if (!empty($dy_purchasetime)) $where['dy_purchasetime'] = ['like', "%$dy_purchasetime%"];

        $dy_startusetime = trim($queryParam['dy_startusetime']);
        if (!empty($dy_startusetime)) $where['dy_startusetime'] = ['like', "%$dy_startusetime%"];

        $dy_usedept = trim($queryParam['dy_usedept']);
        if (!empty($dy_usedept)) $where['dy_usedept'] = ['like', "%$dy_usedept%"];

        $dy_dutyman = trim($queryParam['dy_dutyman']);
        if (!empty($dy_dutyman)) $where['dy_dutyman'] = ['like', "%$dy_dutyman%"];

        $dy_useman = trim($queryParam['dy_useman']);
        if (!empty($dy_useman)) $where['dy_useman'] = ['like', "%$dy_useman%"];

        $dy_jg = trim($queryParam['dy_jg']);
        if (!empty($dy_jg)) $where['dy_jg'] = ['like', "%$dy_jg%"];

        $dyFactory = trim($queryParam['dy_factory']);
        if (!empty($dyFactory)) {
            $dyFactory = $this->getDicFactort($dyFactory, 'dic_name') ; //厂家
            $where['dy_factory'] = ['like', "%$dyFactory%"];
        }

        $dyModelnumber = trim($queryParam['dy_modelnumber']);
        if (!empty($dyModelnumber)) {
            $dyModelnumber = $this->getDicXingHaoById($dyModelnumber, 'dic_name');
            $where['dy_modelnumber'] = ['like', "%$dyModelnumber%"];
        }

        $dyStatus = trim($queryParam['dy_status']);
        if (!empty($dyStatus)) $where['dy_status'] = ['like', "%$dyStatus%"];

        $dyArea = trim($queryParam['dy_area']);
        if (!empty($dyArea)){
            $dyArea = $this->getDicById($dyArea, 'dic_name') ; //地区
            $where['dy_area'] = ['like', "%$dyArea%"];
        }

        $dyBelongfloor = trim($queryParam['dy_belongfloor']);
        if (!empty($dyBelongfloor)) {
            $dyBelongfloor = $this->getDicLouYuById($dyBelongfloor, 'dic_name');
            $where['dy_belongfloor'] = ['like', "%$dyBelongfloor%"];
        }

        $dyRoomno = trim($queryParam['dy_roomno']);
        if (!empty($dyRoomno)) $where['dy_roomno'] = ['like', "%$dyRoomno%"];

        $model = M('dianyuan');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if ($isExport) {
            $data = $obj->select();
            foreach ($data as $k => &$v) {

                //使用人
                $userId = $v['dy_useman'];
                if (!empty($userId)) {
                    $userName = D('org')->getViewPerson($userId);
                    $v['dy_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['dy_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['dy_useman'] = '-';
                    $v['dy_usedept'] = '-';
                }

                //责任人
                $dutuserId = $v['dy_dutyman'];
                if (!empty($dutuserId)) {
                    $dutuserName = D('org')->getViewPerson($dutuserId);
                    $v['dy_dutyman'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')';

                    //责任人部门
                    $v['dy_dutydept'] = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
                } else {
                    $v['dy_dutyman'] = '-';
                    $v['dy_dutydept'] = '-';
                }
                if(!empty($v['dy_jg'])){
                    $ids = explode(',',$v['dy_jg']);
                    $wheres['jg_atpid'] = ['in',$ids];
                    $name = M('jigui')->field('jg_name')->where($wheres)->select();
                    $names = removeArrKey($name,'jg_name');
                    $data[$k]['dy_jg'] = implode(',',$names);
                }

            }
            $header = ['设备编码', '部标编码', '设备类型','名称', '厂家', '型号', '使用状态','采购日期','启用日期', '地区', '楼宇', '房间号', '责任人', '责任部门', '使用人', '使用部门', '机柜', '备注'];
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

                //使用人
                $userId = $v['dy_useman'];
                if (!empty($userId)) {
                    $userName = D('org')->getViewPerson($userId);
                    $v['dy_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['dy_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['dy_useman'] = '-';
                    $v['dy_usedept'] = '-';
                }

                //责任人
                $dutuserId = $v['dy_dutyman'];
                if (!empty($dutuserId)) {
                    $dutuserName = D('org')->getViewPerson($dutuserId);
                    $v['dy_dutyman'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')';

                    //责任人部门
                    $v['dy_dutydept'] = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
                } else {
                    $v['dy_dutyman'] = '-';
                    $v['dy_dutydept'] = '-';
                }
                if(!empty($v['dy_jg'])){
                    $ids = explode(',',$v['dy_jg']);
                    $wheres['jg_atpid'] = ['in',$ids];
                    $name = M('jigui')->field('jg_name')->where($wheres)->select();
                    $names = removeArrKey($name,'jg_name');
                    $data[$k]['dy_jg'] = implode(',',$names);
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
        $ids = trim(I('post.dy_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('dianyuan');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['dy_modifytime'] = $time;
                $data['dy_modifyuser'] = $user;
                $data['dy_atpstatus'] = 'DEL';
                $res = $model->where("dy_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('dianyuan', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    D('relation')->delRelation($id, 'dianyuan');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('dianyuan', '对象删除日志', 'delete', "删除xxx 失败", '失败');
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
        $arr = ['地区', '厂家', '使用状态(电源机柜)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        //当前资产字典id
        $res = M('dic')->field('dic_id')
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='电源'")
            ->find();
        $dyDicId = $res['dic_id'];

        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');
        $changJia = D('Dic')->getFactoryList('电源');
        $changJiaArray = array_column($changJia, 'dic_name');
        $zhuangTai = $arrDic['使用状态(电源机柜)'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');

        //字段
        $fields = ['dy_devicecode', 'dy_anecode', 'dy_type','dy_name', 'dy_factory', 'dy_modelnumber', 'dy_status','dy_purchasetime','dy_startusetime', 'dy_area', 'dy_belongfloor', 'dy_roomno', 'dy_dutyman', 'dy_useman', 'dy_remark'];
        //设备编码,部标编码,名称,主要用途,厂家,型号,使用状态,地区,楼宇,房间号,责任人,使用人,备注
        $model = M('dianyuan');
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

                    case 'dy_status': //使用状态
                        $deptNameField = 'dy_status';
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
                    case 'dy_factory': //厂家
                        $deptNameField = 'dy_factory';
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
                            $xingHao = $this->getDicXingHaoByPid($dicId, $dyDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'dy_modelnumber': //型号
                        $deptNameField = 'dy_modelnumber';
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
                    case 'dy_area': //地区
                        $deptNameField = 'dy_area';
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

                    case 'dy_belongfloor': //楼宇
                        $deptNameField = 'dy_belongfloor';
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
                    case 'dy_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'dy_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'dy_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'dy_remark': //备注
                        $arr[$field] = $v;
                        break;
                    case 'dy_name': //名称
                        $fieldName = '名称';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 名称不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;

                    case 'dy_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'dy_dutyman';
                        $userInfo = D('org')->getUserNames($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'dy_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'dy_useman';
                        $userInfo = D('org')->getUserNames($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['dy_createtime'] = $time;
                $arr['dy_createuser'] = $loginUserId;
                $arr['dy_type'] = '电源';

                $arr['dy_atpid'] = makeGuid();
            }
            $initTables[] = $arr;
        }
        //        dump($error);die;
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    $value['dy_dutydept'] = D('org')->getDeptId($value['dy_dutyman']);
                    $value['dy_usedept'] = D('org')->getDeptId($value['dy_useman']);
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
        $powers = D('Admin/RefinePower')->getViewPowers('DianRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }
}
