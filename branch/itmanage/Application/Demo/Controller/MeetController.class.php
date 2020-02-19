<?php
namespace Demo\Controller;
use Think\Controller;
class MeetController extends BaseController {
    public function index(){
        $user = M('v_sysuser');
        $userId = session('user_id');
        $dept = $user->where("user_id = '%s'",$userId)->find();
        $year = D('Admin/Dictionary')->getDicValueByName('年度');
        $this->assign('dept',$dept['org_name']);
        $this->assign('year',$year);
        $this->assign('yearNow',date('Y'));
        $this->display();
    }

    public function indexNopower(){
        $model = M('meetinfo');
        $nowYear = date('Y');
        $yearCount = $model->where("to_char(mt_date,'YYYY') = '%s'",$nowYear)->count();
        $yzkCount = $model->where("to_char(mt_date,'YYYY') = '%s' and mt_isdone = '是'",$nowYear)->count();
        $wzkCount = $model->where("to_char(mt_date,'YYYY') = '%s' and mt_isdone = '否'",$nowYear)->count();
        $year = D('Admin/Dictionary')->getDicValueByName('年度');
        $this->assign('yearCount',$yearCount);
        $this->assign('yzkCount',$yzkCount);
        $this->assign('wzkCount',$wzkCount);
        $this->assign('year',$year);
        $this->assign('yearNow',date('Y'));
        $this->display();
    }

    public function indexAll(){
//        $year = D('meetinfo')->field("to_char(mt_date,'YYYY') mt_date")->group("to_char(mt_date,'YYYY')")->select();
        $year = D('Admin/Dictionary')->getDicValueByName('年度');
        $this->assign('year',$year);
        $this->assign('yearNow',date('Y'));
        $this->display();
    }
    /*
     * 获取会议数据
     */
    public function getData($isExport = false){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        if($isExport === true){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $model = M('meetinfo');
        $mtId = trim($queryParam['mt_id']);
        $file = M('filerelation');
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $search = trim($queryParam['search']);
        $dept = trim($queryParam['dept']);
        $date = trim($queryParam['mt_date']);
        $list = M('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type','left')->join('dic_type t on d.dic_type = t.dic_type_id','left')->where("to_char(mt_date,'YYYY') = '%s'",$date)->group("mt_type,dic_order")->order('dic_order')->select();
        $meet = removeArrKey($list, 'mt_type');
        $where = array();
        if($mtId == '全部管理活动'){

        }else if(in_array($mtId,$meet)){
            $where['mt_type'] = array('eq' ,$mtId);
        }else{
            $where['mt_id'] = array('eq' ,$mtId);
        }
        if(!empty($start)){
            $where[0]['_logic'] = 'and';
            $where[0][0]['mt_date'] = array('egt' ,$start);
        }
        if(!empty($end)){
            $where[0]['_logic'] = 'and';
            $where[0][1]['mt_date'] = array('elt' ,$end);
        }
        if(!empty($search)){
            $where[1]['_logic'] = 'or';
            $where[1][1]['mt_name'] = array('like' ,"%$search%");
            $where[1][2]['mt_joiners'] = array('like' ,"%$search%");
            $where[1][3]['mt_leadername'] = array('like' ,"%$search%");
            $where[1][4]['mt_deptname'] = array('like' ,"%$search%");
        }
        if(!empty($dept))$where['mt_deptname'] = array('eq' ,$dept);
        if(!empty($date))$where["to_char(mt_date,'YYYY')"] = array('eq' ,$date);
        $field = "mt_type,mt_ztype,mt_name,to_char(mt_date,'YYYY-mm-dd') mt_date,mt_chargemanname,mt_resman,mt_joiners,mt_isjy,mt_deptname,mt_level,mt_isdone";
        $count = $model->where($where)->count();
        $obj=$model->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");

        if($isExport === false){
            $field .= ",mt_id";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach($data as $key => $val){
                $data[$key]['material'] = $file->where("fr_objid = '%s' and fr_objtype = '会议材料'",$val['mt_id'])->count();
                $data[$key]['jy'] = $file->where("fr_objid = '%s' and fr_objtype = '会议文件'",$val['mt_id'])->count();

                if(mb_strlen($val['mt_name'], 'utf8') >25){
                    $val['mt_name'] = mb_substr($val['mt_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['mt_name'] = $val['mt_name'];
                }
                $data[$key]['mt_name'] = "<div data-toggle='tooltip'title='{$data[$key]['mt_name']}'>{$val['mt_name']}</div>";
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }else{
            $data = $obj->field($field)->select();
            $header = ['管理活动类别','管理活动子类别','管理活动名称', '召开时间','主持人', '联系人','主要参加人员', '是否产生纪要', '责任处室','会议级别','是否已召开'];
            if($count == 0){
                exit(json_encode(array('code' => -1, 'message' => '没有要导出的记录')));
            } else if( $count > 5000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }
    }

    /*
    * 会议添加。修改
    */
    public function add(){
        $id = trim(I('get.id'));
        $mtDept = trim(I('get.dept'));
        $model = M('meetinfo');
        if(!empty($id)){
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data=$model->where("mt_id='%s'",$id)->find();
            $users=explode(',',$data['mt_joiners']);
            $this->assign('data',$data);
            $this->assign('users',$users);
        }
        $orgModel = M('v_org_kyb');
        $ling = D('Admin/User')->getUserRole('科研部部领导');
        $dept = $orgModel->field('org_id,org_name')->select();
        $list = D('Admin/Dictionary')->getDicValueByName(['会议类别','是否','会议级别']);
        $this->assign('mtDept',$mtDept);
        $this->assign('ling',$ling);
        $this->assign('dept',$dept);
        $this->assign('type',$list['会议类别']);
        $this->assign('is',$list['是否']);
        $this->assign('jb',$list['会议级别']);
        $this->display();
    }

    /*
     * 会议数据提交
     */
    public function submit(){

        $data = I('post.');
        $userid = $data['mt_leaderid'];
        $orgModel = M('v_org_kyb');
        $user = M('sysuser');
        $username=$user->field('user_realusername')->where("user_id='%s'",$userid)->find();
        $dept = $orgModel->where("org_id = '%s'",$data['mt_deptid'])->find();
        $data['mt_deptname'] = $dept['org_name'];
        $data['mt_leadername'] = $username['user_realusername'];
        $charge = $user->field('user_id')->where("user_realusername='%s'",$data['mt_chargemanname'])->find();
        $response = $user->field('user_id')->where("user_realusername='%s'",$data['mt_resman'])->find();
        $data['mt_chargemanid'] = $charge['user_id'];
        $data['mt_resmanid'] = $response['user_id'];
        $time=date('Y-m-d H:i:s');
        $userid=session('user_id');
        $model = M('meetinfo');
        $id = trim($data['mt_id']);
        $model->startTrans();
        try{
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            if(empty($id)){
                $data['mt_createtime'] = $time;
                $data['mt_id'] = makeGuid();
                $data['mt_createuser'] = $userid;
                $model->add($data);
            }else{
                $data['mt_lastmodifytime'] = $time;
                $data['mt_lastmodifyuser'] = $userid;
                $model->where("mt_id = '%s'",$id)->save($data);
            }
            $model->commit();
            exit(makeStandResult(1,$data['mt_id']));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'添加失败'));
        }
    }
    /*
     * 删除会议
     */
    public function del(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('meetinfo');
        M('wytodoinfo')->where(['td_meetid'=> ['in', $id]])->delete();
        $res = $model-> where(['mt_id'=> ['in', $id]])->delete();
        if(empty($res)){
            exit(makeStandResult(-1,'删除失败'));
        }else{
            exit(makeStandResult(1,'删除成功'));
        }
    }

    /**
     * 会议管理集成页面
     */
    public function frame(){
        $powers = D('RefinePower')->getViewPowers('MeetRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display();
    }

    /**
     * 获取会议树
     */
    public function getOrgTree(){
        $mt_name = trim(I('post.mt_name'));
        $mt_date = trim(I('post.mt_date'));
        $dept = trim(I('post.dept'));
//        $orgName = trim(I('post.orgName'));
//        $userid = trim(I('post.user_id'));
//        $state = trim(I('post.state'));
//        $states = trim(I('post.states'));
//        $where = [];
        if(!empty($mt_name)) $where['mt_type'] = ['like', "%$mt_name%"];
        if(!empty($mt_date)) $where["to_char(mt_date,'YYYY')"] = ['eq', $mt_date];
        if(!empty($dept)) $where["mt_deptname"] = ['eq', $dept];
//        if(!empty($orgName))$where['td_unit'] = array('eq' ,$orgName);
//        if(!empty($userid))$where['td_kybleaderid'] = array('eq' ,$userid);
//        if(!empty($state) || !empty($states)){
//            $states = [$state,$states];
//            $where['status'] = array('in' ,$states);
//        }
        $model = D('Yuan');
        $data = $model->getMeetLists(false, $where);
        $list = M('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type')->join('dic_type t on d.dic_type = t.dic_type_id')->where($where)->group("mt_type,dic_order")->order('dic_order')->select();
        $arr = array();
        foreach($list as $key => $val){
            $arr[] = [
                'mt_name' => $val['mt_type'],
                'mt_id' => $val['mt_type'],
                'mt_type' => '全部管理活动',
            ];
        }
        $arr[] = [
            'mt_name' => '全部管理活动',
            'mt_id' => '全部管理活动',
            'mt_type' => '0',
        ];
        $data = array_merge($arr,$data);
        $initData = [];
        if(empty($initData)) $initData = [];
        foreach($data as &$value){
            $value['name'] = $value['mt_name'];
            $value['open'] = 'true';
            $value['icon'] = __ROOT__.'/Public/vendor/zTree_v3/css/zTreeStyle/img/diy/10.png';
            $value['color'] = isset($value['color']) ? $value['color'] : '';
            $initData[] = $value;
        }
        echo json_encode($data);
    }

    /**
     * 批量增加数据
     */
    public function saveCopyTables(){
        //td_kybchargemanid 科研部责任人
        //td_kybleaderid 科研部主管领导
        $receiveData = I('post.');
        $head = explode(',', $receiveData['head']);
        $data = $receiveData['data'];
        if(empty($data)) exit(makeStandResult(-1, '未接收到数据'));
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
        $fields = [ 'mt_type','mt_ztype', 'mt_name', 'mt_date', 'mt_chargemanid','mt_resmanid', 'mt_joiners', 'mt_isjy',  'mt_deptid','mt_level','mt_isdone'];
        $orgModel = D('Org'); //初始化org model 查询部门id
        $userModel = D('Admin/User');; //初始化user model 查询用户id
        $dictionaryModel = D('Admin/Dictionary'); //初始化Dictionary model 查询字典
//        $meetModel = D('Meet'); //初始化meet model 查询会议id
        $model = M('meetinfo');
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);
        foreach($data as $key=>$value){
            $lineNum = $key + 1; //表格行号
            foreach($value as $k=>$v){
                $field = $fields[$k - $reduce];
                $fieldName = '';
                $deptNameField = '';
                $userNameField = '';
                switch($field){
                    case 'mt_deptid':
                        $deptNameField = empty($deptNameField) ? 'mt_deptname' : $deptNameField;
                        $fieldName = empty($fieldName) ? '责任处室' : $fieldName;
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $unitId = $orgModel->getOrgId($v);
                        if(empty($unitId)){
                            $error .= "第{$lineNum} 行  {$fieldName} 不存在<br>";
                            break;
                        }
                        $arr[$field] = $unitId;
                        $arr[$deptNameField] = $v;
                        break;
                    case 'mt_chargemanid':
                        $fieldName = empty($fieldName) ? '主持人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'mt_chargemanname' : $userNameField;
                        $userInfo = $userModel->getUserInfoByRealNames($v, 'user_id,user_realusername');
                        $userInfo=$userInfo[0];
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'mt_resmanid':
                        $fieldName = empty($fieldName) ? '联系人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'mt_resman' : $userNameField;
                        $userInfo = $userModel->getUserInfoByRealNames($v, 'user_id,user_realusername');
                        $userInfo=$userInfo[0];
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'mt_date':
                        $arr[$field] = date('Y-m-d', strtotime($v));
                        break;
                    case 'mt_name':
                        $todoNames[] = $v;
                    default: $arr[$field] = $v;
                        break;
                }
            }
            $arr['mt_createtime'] = $time;
            $arr['mt_id'] = makeGuid();
            $arr['mt_createuser'] = $loginUserId;
            $initTables[] = $arr;
        }
        $model->startTrans();
        try{
            if(empty($error)){
                $successNum = 0;
                $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
                foreach($initTables as $value){
                    $res = $model->add($value);
                    if(!empty($res)) $successNum +=1;
                }
                $failNum = $exportNum - $successNum;
                $model->commit();
                exit(makeStandResult(1, '添加成功'));
            }else{
                exit(makeStandResult(-1, $error));
            }
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'添加失败'));
        }
    }

    /**
     * 批量增加数据
     */
    public function saveCopyTablesBen(){
        //td_kybchargemanid 科研部责任人
        //td_kybleaderid 科研部主管领导
        $receiveData = $_POST;
        $head = explode(',', trim($receiveData['head']));
        $data = json_decode($receiveData['data'], true);
        if(empty($data)) exit(makeStandResult(-1, '未接收到数据'));
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
        $dept = trim($receiveData['extraParam']);
        $fields = [ 'mt_type', 'mt_name', 'mt_date', 'mt_chargemanid','mt_resmanid', 'mt_joiners', 'mt_isjy','mt_level','mt_isdone'];
        $orgModel = D('Admin/Org'); //初始化org model 查询部门id
        $userModel = D('Admin/User'); //初始化user model 查询用户id
        $dictionaryModel = D('Admin/Dictionary'); //初始化Dictionary model 查询字典
//        $meetModel = D('Meet'); //初始化meet model 查询会议id
        $model = M('meetinfo');
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);

        foreach($data as $key=>$value){
            $lineNum = $key + 1; //表格行号
            foreach($value as $k=>$v){
                $field = $fields[$k - $reduce];
                $fieldName = '';
                $deptNameField = '';
                $userNameField = '';
                switch($field){
                    case 'mt_chargemanid':
                        $fieldName = empty($fieldName) ? '主持人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'mt_chargemanname' : $userNameField;
                        $userInfo = $userModel->getUserInfoByRealNames($v, 'user_id,user_realusername');
                        $userInfo=$userInfo[0];
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'mt_resmanid':
                        $fieldName = empty($fieldName) ? '联系人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'mt_resman' : $userNameField;
                        $userInfo = $userModel->getUserInfoByRealNames($v, 'user_id,user_realusername');
                        $userInfo=$userInfo[0];
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'mt_date':
                        $arr[$field] = date('Y-m-d', strtotime($v));
                        break;
                    case 'mt_name':
                        $todoNames[] = $v;
                    default: $arr[$field] = $v;
                        break;
                }
            }
            $arr['mt_createtime'] = $time;
            $arr['mt_id'] = makeGuid();
            $arr['mt_createuser'] = $loginUserId;
            $initTables[] = $arr;
        }
        $model->startTrans();
//        try{
            if(empty($error)){
                $successNum = 0;
                $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
                foreach($initTables as $value){
                    if(!empty($dept)){
                        $unitId = $orgModel->getOrgId($dept);
                        $value['mt_deptid']=$unitId;
                        $value['mt_deptname']=$dept;
                    }
                    $res = $model->add($value);
                    if(!empty($res)) $successNum +=1;
                }
                $failNum = $exportNum - $successNum;
//                addLog('meetinfo', '对象导入日志', 'add', '批量导入如下待办事项('.implode(',', $todoNames). '),成功'.$successNum.'条数据,失败'.$failNum . '条数据' , '成功');
                $model->commit();
                exit(makeStandResult(1, '添加成功'));
            }else{
                exit(makeStandResult(-1, $error));
            }
//        }catch(\Exception $e){
//            $model->rollback();
//            exit(makeStandResult(-1,'添加失败'));
//        }
    }
}