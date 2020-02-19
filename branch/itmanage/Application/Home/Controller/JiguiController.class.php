<?php

namespace Home\Controller;

use Think\Controller;

class JiguiController extends BaseController
{

    public function index()
    {
        $arr = ['使用状态(电源机柜)', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $factory = D('Dic')->getFactoryList('机柜');

        $this->assign('zhuangTai', $arrDic['使用状态(电源机柜)']);
        $this->assign('changJia', $factory);
        $this->assign('diQu', $arrDic['地区']);
        addLog("", "用户访问日志", "访问机柜管理页面", "成功");
        $this->display();
    }

    /**
     * 机柜管理添加或修改
     */
    public function add()
    {

        $id = trim(I('get.jg_atpid'));
        if (!empty($id)) {
            $model = M('jigui');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model->field('jg_atpid,jg_type,jg_anecode,jg_modelnumber,jg_factory,jg_useman,jg_area,jg_roomno,jg_status,jg_dutyman,jg_belongfloor,jg_remark,jg_dutydept,jg_usedept,jg_devicecode,jg_name,jg_purchadsetime,jg_startusetime')
                ->where("jg_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //使用人
            $userId = $data['jg_useman'];
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
            $dutuserId = $data['jg_dutyman'];
            if (!empty($dutuserId)) {
                $dutuserName = D('org')->getViewPerson($dutuserId);
                $dutuser['name'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')--' . $dutuserName['orgname'];
                $dutuser['username'] = $dutuserName['username'];

                $dutyDept = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
            } else {
                $dutuser = [];
                $dutyDept = '';
            }
            // var_dump($dutuser);die;
            $this->assign('dutuser', $dutuser);
            $this->assign('dutyDept', $dutyDept);
        }
        $arr = ['使用状态(电源机柜)', '地区', '设备类型(机柜)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $factory = D('Dic')->getFactoryList('机柜');
        $this->assign('zhuangTai', $arrDic['使用状态(电源机柜)']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $factory);
        $this->assign('jg_type', $arrDic['设备类型(机柜)']);
        $this->assign('data', $data);

        addLog('', '用户访问日志',"访问机柜管理添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['jg_atpid']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('jigui');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容

        $data['jg_dutydept'] = D('org')->getDeptId($data['jg_dutyman']);
        $data['jg_usedept'] = D('org')->getDeptId($data['jg_useman']);
        $data['jg_area'] = $this->getDicById($data['jg_area'], 'dic_name'); //地区
        $data['jg_belongfloor'] =  $this->getDicLouYuById($data['jg_belongfloor'], 'dic_name'); //楼宇
        $data['jg_factory'] = $this->getDicFactort($data['jg_factory'], 'dic_name'); //厂家
        $data['jg_modelnumber'] = $this->getDicXingHaoById($data['jg_modelnumber'], 'dic_name'); //型号
        if (empty($id)) {
            $data['jg_atpid'] = makeGuid();
            $data['jg_createtime'] = $time;
            $data['jg_createuser'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('jigui', '对象添加日志', 'add', '添加主键为' . $data['jg_atpid'], '失败',$data['jg_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('jigui', '对象添加日志', 'add',  '添加主键为' . $data['jg_atpid'], '成功',$data['jg_atpid']);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);
            $data['jg_modifytime'] = $time;
            $data['jg_modifyuser'] = $user;

            $res = $model->where("jg_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('jigui', '对象修改日志', 'update', '修改主键为' . $id, '失败');
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
                if(!empty($content)){
                    addLog('jigui', '对象修改日志',  $content , '成功',$id);
                }
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }

    /**
     * 获取机柜管理数据
     */
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'jg_devicecode,jg_anecode,jg_type,jg_name,jg_factory,jg_modelnumber,jg_status,jg_purchadsetime,jg_startusetime,jg_area,jg_belongfloor,jg_roomno,jg_dutyman,jg_dutydept,jg_useman,jg_usedept,jg_remark';
        } else {
            $filedStr = 'jg_devicecode,jg_anecode,jg_type,jg_name,jg_factory,jg_modelnumber,jg_status,jg_purchadsetime,jg_startusetime,jg_area,jg_belongfloor,jg_roomno,jg_dutyman,jg_dutydept,jg_useman,jg_usedept,jg_remark, jg_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['jg_atpstatus'] = ['exp', 'IS NULL'];
        $jgDevicecode = trim($queryParam['jg_devicecode']);
        if (!empty($jgDevicecode)) $where['jg_devicecode'] = ['like', "%$jgDevicecode%"];

        $jgAnecode = trim($queryParam['jg_anecode']);
        if (!empty($jgAnecode)) $where['jg_anecode'] = ['like', "%$jgAnecode%"];

        $jgName = trim($queryParam['jg_name']);
        if (!empty($jgName)) $where['jg_name'] = ['like', "%$jgName%"];

        $jgFactory = trim($queryParam['jg_factory']);
        if (!empty($jgFactory)) {
            $jgFactory = $this->getDicFactort($jgFactory, 'dic_name');
            $where['jg_factory'] = ['like', "%$jgFactory%"];
        }

        $jg_dutydept = trim($queryParam['jg_dutydept']);
        if (!empty($jg_dutydept)) $where['jg_dutydept'] = ['like', "%$jg_dutydept%"];

        $jg_purchasetime = trim($queryParam['jg_purchasetime']);
        if (!empty($jg_purchasetime)) $where['jg_purchadsetime'] = ['like', "%$jg_purchasetime%"];

        $jg_startusetime = trim($queryParam['jg_startusetime']);
        if (!empty($jg_startusetime)) $where['jg_startusetime'] = ['like', "%$jg_startusetime%"];

        $jg_usedept = trim($queryParam['jg_usedept']);
        if (!empty($jg_usedept)) $where['jg_usedept'] = ['like', "%$jg_usedept%"];

        $jg_dutyman = trim($queryParam['jg_dutyman']);
        if (!empty($jg_dutyman)) $where['jg_dutyman'] = ['like', "%$jg_dutyman%"];

        $jg_useman = trim($queryParam['jg_useman']);
        if (!empty($jg_useman)) $where['jg_useman'] = ['like', "%$jg_useman%"];

        $jgModelnumber = trim($queryParam['jg_modelnumber']);
        if (!empty($jgModelnumber)) {
            $jgModelnumber = $this->getDicXingHaoById($jgModelnumber, 'dic_name');
            $where['jg_modelnumber'] = ['like', "%$jgModelnumber%"];
        }

        $jgStatus = trim($queryParam['jg_status']);
        if (!empty($jgStatus)) $where['jg_status'] = ['like', "%$jgStatus%"];

        $jgArea = trim($queryParam['jg_area']);
        if (!empty($jgArea)) {
            $jgArea = $this->getDicById($jgArea, 'dic_name');
            $where['jg_area'] = ['like', "%$jgArea%"];
        }

        $jgBelongfloor = trim($queryParam['jg_belongfloor']);
        if (!empty($jgBelongfloor)) {
            $jgBelongfloor = $this->getDicLouYuById($jgBelongfloor, 'dic_name');
            $where['jg_belongfloor'] = ['like', "%$jgBelongfloor%"];
        }

        $jgRoomno = trim($queryParam['jg_roomno']);
        if (!empty($jgRoomno)) $where['jg_roomno'] = ['like', "%$jgRoomno%"];

        $model = M('jigui');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
             ->order("$queryParam[sort] $queryParam[sortOrder]");

        if ($isExport) {
            $data = $obj->select();
            foreach ($data as $k => &$v) {
                //使用人
                $userId = $v['jg_useman'];
                if (!empty($userId)) {
                    $userName = D('org')->getViewPerson($userId);
                    $v['jg_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['jg_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['jg_useman'] = '-';
                    $v['jg_usedept'] = '-';
                }

                //责任人
                $dutuserId = $v['jg_dutyman'];
                if (!empty($dutuserId)) {
                    $dutuserName = D('org')->getViewPerson($dutuserId);
                    $v['jg_dutyman'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')';

                    //责任人部门
                    $v['jg_dutydept'] = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
                } else {
                    $v['jg_dutyman'] = '-';
                    $v['jg_dutydept'] = '-';
                }
            }
            $header = ['设备编码', '部标编码', '设备类型','机柜',  '厂家', '型号', '使用状态','采购日期','启用日期', '地区', '楼宇', '房间号', '责任人', '责任部门', '使用人', '使用部门', '备注'];
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
                $userId = $v['jg_useman'];
                if (!empty($userId)) {
                    $userName = D('org')->getViewPerson($userId);
                    $v['jg_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['jg_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['jg_useman'] = '-';
                    $v['jg_usedept'] = '-';
                }

                //责任人
                $dutuserId = $v['jg_dutyman'];
                if (!empty($dutuserId)) {
                    $dutuserName = D('org')->getViewPerson($dutuserId);
                    $v['jg_dutyman'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')';

                    //责任人部门
                    $v['jg_dutydept'] = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
                } else {
                    $v['jg_dutyman'] = '-';
                    $v['jg_dutydept'] = '-';
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
        $ids = trim(I('post.jg_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('jigui');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['jg_modifytime'] = $time;
                $data['jg_modifyuser'] = $user;
                $data['jg_atpstatus'] = 'DEL';
                $res = $model->where("jg_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('jigui', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    D('relation')->delRelation($id, 'jigui');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('jigui', '对象删除日志', 'delete', "删除xxx 失败", '失败');
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
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='机柜'")
            ->find();
        $jgDicId = $res['dic_id'];

        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');

        $changJia = D('Dic')->getFactoryList('机柜');
        $changJiaArray = array_column($changJia, 'dic_name');

        $zhuangTai = $arrDic['使用状态(电源机柜)'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');

        //字段
        $fields = ['jg_devicecode', 'jg_anecode', 'jg_type','jg_name',  'jg_factory', 'jg_modelnumber', 'jg_status','jg_purchadsetime','jg_startusetime', 'jg_area', 'jg_belongfloor', 'jg_roomno', 'jg_dutyman', 'jg_useman', 'jg_remark'];
        //设备编码,部标编码,机柜,主要用途,厂家,型号,使用状态,地区,楼宇,房间号,责任人,责任部门,使用人,使用部门,备注
        $model = M('jigui');
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

                    case 'jg_status': //使用状态
                        $deptNameField = 'jg_status';
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
                    case 'jg_factory': //厂家
                        $deptNameField = 'jg_factory';
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
                            $xingHao = $this->getDicXingHaoByPid($dicId, $jgDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'jg_modelnumber': //型号
                        $deptNameField = 'jg_modelnumber';
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
                    case 'jg_area': //地区
                        $deptNameField = 'jg_area';
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

                    case 'jg_belongfloor': //楼宇
                        $deptNameField = 'jg_belongfloor';
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
                    case 'jg_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'jg_anecode': //部标编码
                        $arr[$field] = $v;
                        break;
                    case 'jg_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'jg_remark': //备注
                        $arr[$field] = $v;
                        break;
                    case 'jg_name': //机柜
                        $fieldName = '机柜';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 机柜名称不能为空<br>";
                        }
                        $arr[$field] = $v;
                        break;

                    case 'jg_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'jg_dutyman';
                        $userInfo = D('org')->getUserNames($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'jg_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'jg_useman';
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
                $arr['jg_createtime'] = $time;
                $arr['jg_createuser'] = $loginUserId;
                $arr['jg_type'] = '机柜';

                $arr['jg_atpid'] = makeGuid();
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
                    $value['jg_dutydept'] = D('org')->getDeptId($value['jg_dutyman']);
                    $value['jg_usedept'] = D('org')->getDeptId($value['jg_useman']);
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
