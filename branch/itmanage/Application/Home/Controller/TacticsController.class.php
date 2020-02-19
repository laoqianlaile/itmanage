<?php
namespace Home\Controller;

use Think\Controller;

class TacticsController extends BaseController
{
//应用系统管理
    public function index()
    {
        $arr = ['防火墙特殊配置','策略状态'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('tspz', $arrDic['防火墙特殊配置']);
        $this->assign('clzt', $arrDic['策略状态']);

        addLog("", "用户访问日志",  "访问防火墙策略页面", "成功");
        $this->display();
    }

    public function fwdata(){
        $q = strtolower($_POST['data']['q']);
        $Model = M();
        $sql_select="select  p.fw_name id,p.fw_name text from  firewall p where
                (lower(p.fw_name) like '%".$q."%') and p.fw_atpstatus is null";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    /**
     * 应用系统添加或修改
     */
    public function add()
    {
        $id = trim(I('get.cl_atpid'));
        $Objtype = trim(I('get.type'));
        if (!empty($id)) {
            $model = M('fwcl');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");

            $data = $model
                ->field('cl_atpid,cl_sqrname,cl_sqr,cl_sqrbm,cl_sqrtel,cl_starttime,cl_endtime,cl_xtmc,cl_sourceip,cl_objectip,cl_port,cl_tspz,cl_rwid,cl_clid,cl_clr,cl_fw,cl_bz,cl_status')
                ->where("cl_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //责任人
            $userId = $data['cl_sqr'];
//            dump($userid);die;
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $userMan['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $userMan['username'] = $userName['username'];

//                $userDept = $this -> removeStr($userName['orgfullname']);//去掉字符串
            }else{
                $userMan = [];
//                $userDept = '';
            }
            //防火墙
            $relation = D("relation")->getViewRelationInfo($id, 'firewall');
            //隐藏的id值
            $cl_fw_id = implode(',', array_column($relation, 'r_id'));
            // dump($sevv_app_id);die;
            $this->assign('cl_fw_id', $cl_fw_id);

            $this->assign('userMan', $userMan['name']);
            $userDept = D('org')->getDeptName($data['cl_sqrbm']);
            $this->assign('userDept', $userDept);


            $this->assign('data', $data);
        }
        $arr = ['防火墙特殊配置','策略状态'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('Objtype', $Objtype);
        $this->assign('tspz', $arrDic['防火墙特殊配置']);
        $this->assign('clzt', $arrDic['策略状态']);

        addLog('', '用户访问日志', "访问防火墙策略添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['cl_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $model = M('fwcl');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        $ids = explode(',',$data['cl_fw_id']);
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if (empty($id)) {
            $data['cl_atpid'] = makeGuid();
            $data['cl_atpcreatedatetime'] = $time;
            $data['cl_atpcreateuser'] = $user;
            $data['cl_clr'] = $user;
            $data = $model->create($data);
            $res = $model->add($data);
            if (empty($res)) {
                // 修改日志
                addLog('fwcl', '对象添加日志', '添加主键为'.$data['cl_atpid'] . '失败', '失败',$data['cl_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                $this->changeRelationFire($data['cl_atpid'], $data['cl_name'], '', '', 'fwcl', $ids);
                addLog('fwcl', '对象添加日志', '添加主键为'.$data['cl_atpid'] . '成功', '成功',$data['cl_atpid']);
                //添加服务器关联关系
//                $this -> changeRelationSev($data['cl_atpid'],$data['cl_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);

            $list = session('list');
            $content = LogContent($data,$list);
            $data['cl_atplastmodifydatetime'] = $time;
            $data['cl_atplastmodifyuser'] = $user;
            $data['cl_clr'] = $user;
            $data = $model->create($data);
            $res = $model->where("cl_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('fwcl', '对象修改日志', '修改主键为'.$id . '失败', '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
                $this->changeRelationFire($data['cl_atpid'], $data['cl_name'], '', '', 'fwcl', $ids);
                if(!empty($content)) {
                    addLog('fwcl', '对象修改日志', $content, '成功', $id);
                }
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }

    /**
     * 获取应用系统数据
     */
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');

            $filedStr = 'cl_sqr,cl_sqr zh,cl_sqrbm,cl_sqrtel,cl_starttime,cl_endtime,cl_xtmc,cl_sourceip,cl_objectip,cl_port,cl_tspz,cl_rwid,cl_clid,cl_status,cl_clr,cl_fw,cl_bz';
        } else {
            $filedStr = 'cl_atpid,cl_sqr,cl_sqrname,cl_sqrbm,cl_sqrtel,cl_starttime,cl_endtime,cl_xtmc,cl_sourceip,cl_objectip,cl_port,cl_tspz,cl_rwid,cl_clid,cl_status,cl_clr,cl_fw,cl_bz';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['cl_atpstatus'] = ['exp', 'IS NULL'];
        $cl_sqrname = trim($queryParam['cl_sqrname']);
        if (!empty($cl_sqrname)) $where['cl_sqr'] = ['eq', "$cl_sqrname"];

        $cl_sqrbm = trim($queryParam['cl_sqrbm']);
        if (!empty($cl_sqrbm)) {
            $sql = "select id from it_depart start with id= '$cl_sqrbm' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['cl_sqrbm'] = ['in', $ids];
//            $where['cl_sqrbm'] = ['eq', "$cl_sqrbm"];
        }

        $cl_starttime = trim($queryParam['cl_starttime']);
        if (!empty($cl_starttime)) $where['cl_starttime'] = ['eq', "$cl_starttime"];

        $cl_endtime = trim($queryParam['cl_endtime']);
        if (!empty($cl_endtime)) $where['cl_endtime'] = ['eq', "$cl_endtime"];

        $cl_xtmc = trim($queryParam['cl_xtmc']);
        if (!empty($cl_xtmc)) $where['cl_xtmc'] = ['like', "%$cl_xtmc%"];

        $cl_sourceip = trim($queryParam['cl_sourceip']);
        if (!empty($cl_sourceip)) $where['cl_sourceip'] = ['like', "%$cl_sourceip%"];

        $cl_objectip = trim($queryParam['cl_objectip']);
        if (!empty($cl_objectip)) $where['cl_objectip'] = ['like', "%$cl_objectip%"];

        $cl_tspz = trim($queryParam['cl_tspz']);
        if (!empty($cl_tspz)) $where['cl_tspz'] = ['like', "%$cl_tspz%"];

        $cl_rwid = trim($queryParam['cl_rwid']);
        if (!empty($cl_rwid)) $where['cl_rwid'] = ['like', "%$cl_rwid%"];

        $cl_clid = trim($queryParam['cl_clid']);
        if (!empty($cl_clid)) $where['cl_clid'] = ['like', "%$cl_clid%"];

        $cl_status = trim($queryParam['cl_status']);
        if (!empty($cl_status)) $where['cl_status'] = ['like', "%$cl_status%"];

        $cl_bz = strtolower(trim($queryParam['cl_bz']));
        if (!empty($cl_bz)) $where['lower(cl_bz)'] = ['like', "%$cl_bz%"];

        $cl_fw = trim($queryParam['cl_fw']);
        if (!empty($cl_fw)) $where['cl_fw'] = ['like', "%$cl_fw%"];

        $relation = trim($queryParam['relation']);
        if (!empty($relation)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '防火墙策略'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');
            $arr = array_chunk($rlx_zyids,1000);
            if($relation == '1'){
                $where[0][0]['cl_atpid'] = ['in', $arr[0]];
                $where[0][1]['cl_atpid'] = ['in', $arr[1]];
                $where[0]['_logic'] = 'or';
            }else{
                $where[0][0]['cl_atpid'] = ['not in', $arr[0]];
                $where[0][1]['cl_atpid'] = ['not in', $arr[1]];
                $where[0]['_logic'] = 'and';
            }
        }

        $model = M('fwcl');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");


        if ($isExport) {
            $data = $obj->select();
            $header = ['申请人','申请人账号', '申请人部门', '申请人联系电话', '策略生效日期', '策略截止日期', '应用系统','源IP', '目的IP', '端口号', '特殊配置', '表单编号', '策略编号', '策略状态', '处理人', '防火墙', '备注'];
            foreach ($data as $k => &$v) {
                //责任人
                if(!empty($v['cl_sqr'])){
                    $userName = D('org')->getViewPerson($v['cl_sqr']);
//                    $v['cl_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['cl_sqr'] = $userName['realusername'];

                }else{
                    $v['cl_sqr'] = '-';
                }
                $userDept = D('org')->getDeptName($v['cl_sqrbm']);
                $data[$k]['cl_sqrbm'] = $userDept;

                $data[$k]['cl_clr'] = M('sysuser')->where("user_id = '%s'",$v['cl_clr'])->getField('user_realusername');
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
                //翻译字典
//                $v['cl_status'] = !empty($v['cl_status'])?$this ->getDicById($v['cl_status'],'dic_name'):'-';//使用状态
//                $v['cl_secret'] = !empty($v['cl_secret'])?$this ->getDicById($v['cl_secret'],'dic_name'):'-';//密级
                //责任人
                if(!empty($v['cl_sqr'])){
                    $userName = D('org')->getViewPerson($v['cl_sqr']);
//                    $v['cl_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['cl_sqr'] = $userName['realusername'];

                }else{
                    $v['cl_sqr'] = '-';
                }
                $userDept = D('org')->getDeptName($v['cl_sqrbm']);
                $data[$k]['cl_sqrbm'] = $userDept;

                $data[$k]['cl_clr'] = M('sysuser')->where("user_id = '%s'",$v['cl_clr'])->getField('user_realusername');

                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['cl_atpid'])->count();
                $data[$k]['clCount'] = $rlxCount;
            }
            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }

    public function assignApp(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  app_name id,app_name text from  it_application where
                    app_name like '%".$q."%' and app_atpstatus is null";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.cl_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('fwcl');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['cl_atplastmodifydatetime'] = $time;
                $data['cl_atplastmodifyuser'] = $user;
                $data['cl_atpstatus'] = 'DEL';
                $res = $model->where("cl_atpid='%s'", $id)->save($data);
                $list['rlx_atpstatus'] = 'DEL';
                M('it_relationx')->where("rlx_zyid = '%s'",$id)->save($list);
                if ($res) {
                    // 修改日志
                    addLog('fwcl', '对象删除日志',  "删除主键为".$id."成功", '成功',$id);
                    //删除关联关系
                    D('relation')->delRelation($id, 'it_application');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('fwcl', '对象删除日志', "删除xxx 失败", '失败');
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
        if(empty($data)) exit(makeStandResult(-1, '未接收到数据'));
        $data = json_decode($data,true);

        $reduce = 0;
        if($head[0] == '序号'){
            foreach($data as &$value){
                unset($value[0]);
            }
            unset($value);
            $reduce = 1;
        }
        $time = date('Y-m-d H:i:s');
        $loginUserId = session('user_id');

        //字典
        $arr = ['防火墙特殊配置','策略状态'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $miJi = $arrDic['防火墙特殊配置'];
        $tspz = array_column($miJi,'dic_name');
        $zhuangTai = $arrDic['策略状态'];
        $clzt = array_column($zhuangTai,'dic_name');

        //字段
        $fields = ['cl_sqr', 'cl_sqrtel', 'cl_starttime', 'cl_endtime', 'cl_xtmc', 'cl_sourceip',  'cl_objectip', 'cl_port', 'cl_tspz', 'cl_rwid', 'cl_clid', 'cl_status', 'cl_fw', 'cl_bz'];
        //申请人,申请人电话,策略生效时间,策略截止时间,系统名称,源IP,目的IP,端口号,特殊配置,表单编号,策略编号,策略状态,防火墙,备注

        $model = M('fwcl');
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
                    case 'cl_tspz': //特殊配置
                        $deptNameField = 'cl_tspz';
                        $fieldName = '特殊配置';
                        if(!empty($v)){
                            if(!in_array($v,$tspz)){
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            }else{
                                $k = array_search($v,$tspz);
                            }
                            $dicId = $miJi[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        }else{
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'cl_status': //策略状态
                        $deptNameField ='cl_status';
                        $fieldName = '策略状态';
                        if(!empty($v)){
                            if(!in_array($v,$clzt)){
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            }else{
                                $k = array_search($v,$clzt);
                            }
                            $dicId = $zhuangTai[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        }else{
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'cl_cqr': //申请人
                        $fieldName = '申请人';
                        $userNameField = 'cl_cqr';
                        $userInfo = D('org')->getUserNames($v);
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;

                    case 'cl_starttime': //策略生效日期
                        $fieldName = '策略生效日期';
                        if(!empty($v)){
                            $strTime = strtotime($v);
                            if($strTime === false){
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        }else{
                            $arr[$field] = '';
                        }
                        break;

                    case 'cl_endtime': //策略截止日期
                        $fieldName = '策略截止日期';
                        if(!empty($v)){
                            $strTime = strtotime($v);
                            if($strTime === false){
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        }else{
                            $arr[$field] = '';
                        }
                        break;
                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['cl_atpcreatedatetime'] = $time;
                $arr['cl_atpcreateuser'] = $loginUserId;
                $arr['cl_clr'] = $loginUserId;
                $arr['cl_atpid'] = makeGuid();
            }
            $initTables[] = $arr;
        }
        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    $value['cl_sqrbm'] = M('it_person')->where("domainusername = '%s'",$value['cl_sqr'])->getfield('orgid');
                    $res = $model->add($value);
                    if (!empty($res)) $successNum += 1;
                }
                $failNum = $exportNum - $successNum;
                addLog('todo', '对象导入日志',  '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
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