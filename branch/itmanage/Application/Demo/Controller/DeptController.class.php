<?php
namespace Demo\Controller;
use Think\Controller;
class DeptController extends BaseController
{
    public function index()
    {
        addLog('', '用户访问日志', '访问部门待办事项', '成功');
        $orgModel = M('v_org_kyb');
        $list = D('Admin/dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('state', $list);
        $this->assign('dept', $dept);
        $this->assign('zr', $zeren);
        $this->display();
    }

    /*
     * 访问提交页面
     * */
    public function tijiao()
    {
        addLog('', '用户访问日志', '访问部门待办事项提交页面', '成功');
        $userid = session('user_id');
        $userModel = M('v_sysuser');
        $orgModel = M('v_org_kyb');
        $list = D('Admin/dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('state', $list);
        $this->assign('dept', $dept);
        $this->assign('zr', $zeren);
        $this->assign('userid', $userid);
        $this->display();
    }

    /*
     * 访问确认页面
     * */
    public function confirm()
    {
        addLog('', '用户访问日志', '访问部门待办事项确认页面', '成功');
        $userid = session('user_id');
//        $userid = 'T4EB0A60A9A994EBFBD4588DB';
        $orgModel = M('v_org_kyb');
        $list = D('Admin/dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('state', $list);
        $this->assign('dept', $dept);
        $this->assign('zr', $zeren);
        $this->assign('userid', $userid);
        $this->display();
    }

    /*
     * 访问查询页面
     * */
    public function rogatory()
    {
        addLog('', '用户访问日志', '访问部门待办事项查询页面', '成功');
        $orgModel = M('v_org_kyb');
        $list = D('Admin/dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('state', $list);
        $this->assign('dept', $dept);
        $this->assign('zr', $zeren);
        $this->display();
    }

    /*
   * 取消
   * */
    public function nullify()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $ids = I('post.ids');
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));
        $model = M('unittodoinfo');
        $work = M('workflow');
        $userModel = M('sysuser');
        $idArr = explode(',', $ids);
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'", $userid)->find();
        $model->startTrans();
        try{
            foreach($idArr as $id){
                $data['utd_canceltime'] = $time;
                $data['utd_cancelmanid'] = $userid;
                $data['utd_cancelman'] = $username['user_realusername'];
                $data['utd_iscancel'] = '是';
                $data['utd_flowstatus'] = '已取消';
                $model->where("utd_id = '%s'",$id)->save($data);
                $works = D('Yuan')->GetWorkFlow('取消','部门待办事项',$id,'');
                $work->add($works);
            }
            $model->commit();
            addLog('unittodoinfo', '对象修改日志', '取消待办事项成功', '成功');
            exit(makeStandResult(1, '取消成功'));
        }catch(\Exception $e){
            $model->rollback();
            addLog('unittodoinfo', '对象修改日志', '取消待办事项失败', '失败');
            exit(makeStandResult(-1, '取消失败'));
        }
    }


    public function getData($isExport = false)
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        if ($isExport === true) {
            $queryParam = I('get.');
        } else {
            $queryParam = I('put.');
        }
        $fileModel = M('filerelation');
        $model = M('v_unittodoinfo');
        $workModel = M('workflow');
        $stateTj = trim($queryParam['stateTj']);
        $orgName = trim($queryParam['orgName']);
        $utdName = trim($queryParam['utd_name']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $kybChargeManId = trim($queryParam['kybchargemanid']);
        $dept = trim($queryParam['dept']);
        $utdStatus = trim($queryParam['utd_status']);
        $userid = trim($queryParam['user_id']);
        $where = array();
        if (!empty($stateTj)) {
            $stateTj = explode(',', $stateTj);
            $where['utd_flowstatus'] = array('in', $stateTj);
        }
        if (!empty($utdName)) $where['utd_name'] = array('like', "%$utdName%");
        if (!empty($start)) {
            $where[0]['_logic'] = 'and';
            $where[0][0]["to_char(utd_planfinishdate,'YYYY-mm-dd')"] = array('egt', $start);
        }
        if (!empty($end)) {
            $where[0]['_logic'] = 'and';
            $where[0][1]["to_char(utd_planfinishdate,'YYYY-mm-dd')"] = array('elt', $end);
        }
        if (!empty($kybChargeManName)) $where['utd_kybchargeman'] = array('eq', $kybChargeManName);
        if (!empty($kybChargeManId)) $where['utd_kybchargemanid'] = array('eq', $kybChargeManId);
        if (!empty($dept)) $where['utd_kybunit'] = array('eq', $dept);
        if (!empty($orgName)) $where['utd_kybunit'] = array('eq', $orgName);
        if (!empty($utdStatus)) $where['status'] = array('eq', $utdStatus);
        if (!empty($userid)) $where['utd_kybleaderid'] = array('eq', $userid);
        $count = $model->where($where)->count();
        $field = "utd_meetname,utd_name,utd_finishtype,to_char(utd_planfinishdate,'YYYY-mm-dd') as utd_planfinishdate,to_char(utd_modifyfinishdate,'YYYY-mm-dd') as utd_modifyfinishdate,utd_confirmtime,utd_kybchargeman,utd_kybunit,utd_kybleader,cast(status as varchar(100)) status";
        $obj = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");

        if ($isExport === false) {
            $field .= ",utd_id,utd_iscancel,utd_backcomment,utd_flowstatus";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach ($data as $key => $val) {
                $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '部门待办事项'", $val['utd_id'])->count();
                $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '部门待办事项'",$val['utd_id'])->order('wf_time')->select();
                foreach($work as $k =>$v){
                    if(!empty($v['wf_content'])){
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                    }else{
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                    }
                }
                $data[$key]['workflow'] = $work;

                if(mb_strlen($val['utd_meetname'], 'utf8') >15){
                    $val['utd_meetname'] = mb_substr($val['utd_meetname'], 0, 15, 'utf8') .'...';
                }else{
                    $val['utd_meetname'] = $val['utd_meetname'];
                }

                if(mb_strlen($val['utd_name'], 'utf8') >25){
                    $val['utd_name'] = mb_substr($val['utd_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['utd_name'] = $val['utd_name'];
                }
                $data[$key]['utd_meetname'] = "<div data-toggle='tooltip'title='{$data[$key]['utd_meetname']}'>{$val['utd_meetname']}</div>";
                $data[$key]['utd_name'] = "<div data-toggle='tooltip'title='{$data[$key]['utd_name']}'>{$val['utd_name']}</div>";
            }
            exit(json_encode(array('total' => $count, 'rows' => $data)));
        } else {
            $data = $obj->field($field)->select();
            $header = ['会议名称','待办事项', '完成形式', '计划完成时间', '预计完成时间','实际完成时间' , '责任人','责任处室','责任领导',  '完成确认'];
            if ($count == 0) {
                exit(json_encode(array('code' => -1, 'message' => '没有要导出的记录')));
            } else if ($count > 5000) {
                csvExport($header, $data, true);
            } else {
                excelExport($header, $data, true);
            }
        }
    }
        public function getDataConfirm($isExport = false)
            {
                M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
                if ($isExport === true) {
                    $queryParam = I('get.');
                } else {
                    $queryParam = I('put.');
                }
                $fileModel = M('filerelation');
                $model = M('v_unittodoinfo');
                $workModel = M('workflow');
                $state = trim($queryParam['state']);
                $utdName = trim($queryParam['utd_name']);
                $start = trim($queryParam['start']);
                $end = trim($queryParam['end']);
                $kybChargeManName = trim($queryParam['kybchargemanname']);
                $dept = trim($queryParam['dept']);
                $utdStatus = trim($queryParam['utd_status']);
                $userid = trim($queryParam['user_id']);
                $where = array();
                $rolesid = session('roleids');
                $roleModel = M('sysrole');
                $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
                $roles = removeArrKey($roles, 'role_name');
                $role1 = ['科研部部领导'];
                $role2 = ['科研部处室领导'];
                $role3 = ['科研部员工'];
                if(!empty(array_intersect($role1,$roles))){
                    if(!empty($userid))$where['utd_kybleaderid'] = array('eq' ,$userid);
                }else if(!empty(array_intersect($role2,$roles)) && !empty(array_intersect($role3,$roles))){
                    $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
                    $where[1]['_logic'] = 'or';
                    $where[1][0]['utd_kybunit'] = array('eq' ,$orgName['org_name']);
                    $where[1][1]['utd_kybchargemanid'] = array('eq' ,$userid);

                }else if(!empty(array_intersect($role2,$roles))){
                    $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
                    $where['utd_kybunit'] = array('eq' ,$orgName['org_name']);
                }else if(!empty(array_intersect($role3,$roles))){
                    if(!empty($userid))$where['utd_kybchargemanid'] = array('eq' ,$userid);
                }else{
                    $where['utd_kybunit'] = '无效组织';
                }
                if (!empty($state)) {
                    $states = explode(',', $state);
                    $where['utd_flowstatus'] = array('in', $states);
                }
                if (!empty($utdName)) $where['utd_name'] = array('like', "%$utdName%");
                if (!empty($start)) {
                    $where[0]['_logic'] = 'and';
                    $where[0][0]["to_char(utd_planfinishdate,'YYYY-mm-dd')"] = array('egt', $start);
                }
                if (!empty($end)) {
                    $where[0]['_logic'] = 'and';
                    $where[0][1]["to_char(utd_planfinishdate,'YYYY-mm-dd')"] = array('elt', $end);
                }
                if (!empty($kybChargeManName)) $where['utd_kybchargeman'] = array('eq', $kybChargeManName);
                if (!empty($dept)) $where['utd_kybunit'] = array('eq', $dept);
                if (!empty($utdStatus)) $where['status'] = array('eq', $utdStatus);
                $count = $model->where($where)->count();
                $field = "utd_meetname,utd_name,utd_finishtype,to_char(utd_planfinishdate,'YYYY-mm-dd') as utd_planfinishdate,to_char(utd_modifyfinishdate,'YYYY-mm-dd') as utd_modifyfinishdate,utd_confirmtime,utd_kybchargeman,utd_kybunit,utd_kybleader,cast(status as varchar(100)) status";
                $obj = $model
                    ->where($where)
                    ->order("$queryParam[sort] $queryParam[sortOrder] ");

                if ($isExport === false) {
                    $field .= ",utd_id";
                    $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
                    foreach ($data as $key => $val) {
                        $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '部门待办事项'", $val['utd_id'])->count();
                        $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '部门待办事项'",$val['utd_id'])->order('wf_time')->select();
                        foreach($work as $k =>$v){
                            if(!empty($v['wf_content'])){
                                $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                            }else{
                                $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                            }
                        }
                        $data[$key]['workflow'] = $work;
                        if(mb_strlen($val['utd_meetname'], 'utf8') >15){
                            $val['utd_meetname'] = mb_substr($val['utd_meetname'], 0, 15, 'utf8') .'...';
                        }else{
                            $val['utd_meetname'] = $val['utd_meetname'];
                        }

                        if(mb_strlen($val['utd_name'], 'utf8') >25){
                            $val['utd_name'] = mb_substr($val['utd_name'], 0, 25, 'utf8') .'...';
                        }else{
                            $val['utd_name'] = $val['utd_name'];
                        }
                        $data[$key]['utd_meetname'] = "<div data-toggle='tooltip'title='{$data[$key]['utd_meetname']}'>{$val['utd_meetname']}</div>";
                        $data[$key]['utd_name'] = "<div data-toggle='tooltip'title='{$data[$key]['utd_name']}'>{$val['utd_name']}</div>";
                    }
                    exit(json_encode(array('total' => $count, 'rows' => $data)));
                } else {
                    $data = $obj->field($field)->select();
                    $header = ['会议名称','待办事项', '完成形式', '计划完成时间', '预计完成时间', '主管领导', '责任处室', '责任人', '状态', '完成情况', '备注'];
                    if ($count == 0) {
                        exit(json_encode(array('code' => -1, 'message' => '没有要导出的记录')));
                    } else if ($count > 5000) {
                        csvExport($header, $data, true);
                    } else {
                        excelExport($header, $data, true);
                    }
                }
            }

    public function getDatas($isExport = false)
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        if ($isExport === true) {
            $queryParam = I('get.');
        } else {
            $queryParam = I('put.');
        }
        $where = array();
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $workModel = M('workflow');
        $fileModel = M('filerelation');
        $roles = $roleModel->field('role_name')->where(['role_id' => ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['院领导', '科研部部领导', '科研部综合处'];
        $role2 = ['科研部处室领导', '科研部处室管理员'];
        $role3 = ['各单位责任人', '各单位科技处管理人员'];
        $userid = session('user_id');
        if (!empty(array_intersect($role1, $roles))) {

        } else if (!empty(array_intersect($role2, $roles))) {
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ", $userid)->find();
            $where['utd_kybunit'] = array('eq', $orgName['org_name']);
        }else{
            $where['utd_kybunit'] = '无效组织';
        }
        $model = M('v_unittodoinfo');
        $state = trim($queryParam['state']);
        $states = trim($queryParam['states']);
        $utdName = trim($queryParam['utd_name']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $dept = trim($queryParam['dept']);
        $utdStatus = trim($queryParam['utd_status']);
        $userid = trim($queryParam['user_id']);

        if (!empty($state) || !empty($states)) {
            $states = [$state, $states];
            $where['status'] = array('in', $states);
        }
        if (!empty($utdName)) $where['utd_name'] = array('like', "%$utdName%");
        if (!empty($start)) {
            $where[0]['_logic'] = 'and';
            $where[0][0]["to_char(utd_planfinishdate,'YYYY-mm-dd')"] = array('egt', $start);
        }
        if (!empty($end)) {
            $where[0]['_logic'] = 'and';
            $where[0][1]["to_char(utd_planfinishdate,'YYYY-mm-dd')"] = array('elt', $end);
        }
        if (!empty($kybChargeManName)) $where['utd_kybchargeman'] = array('eq', $kybChargeManName);
        if (!empty($dept)) $where['utd_kybunit'] = array('eq', $dept);
        if (!empty($utdStatus)) $where['status'] = array('eq', $utdStatus);
        if (!empty($userid)) $where['utd_kybleaderid'] = array('eq', $userid);
        $count = $model->where($where)->count();
        $field = "utd_meetname,utd_name,utd_finishtype,to_char(utd_planfinishdate,'YYYY-mm-dd') as utd_planfinishdate,to_char(utd_modifyfinishdate,'YYYY-mm-dd') as utd_modifyfinishdate,utd_confirmtime,utd_kybchargeman,utd_kybunit,utd_kybleader,cast(status as varchar(100)) status";
        $obj = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");

        if ($isExport === false) {
            $field .= ",utd_id,utd_finishtime,utd_finishman,utd_confirmman,utd_modifyman";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach ($data as $key => $val) {
                $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '部门待办事项'", $val['utd_id'])->count();
                $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '部门待办事项'",$val['utd_id'])->order('wf_time')->select();
                foreach($work as $k =>$v){
                    if(!empty($v['wf_content'])){
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                    }else{
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                    }
                }
                $data[$key]['workflow'] = $work;
                if(mb_strlen($val['utd_meetname'], 'utf8') >15){
                    $val['utd_meetname'] = mb_substr($val['utd_meetname'], 0, 15, 'utf8') .'...';
                }else{
                    $val['utd_meetname'] = $val['utd_meetname'];
                }

                if(mb_strlen($val['utd_name'], 'utf8') >25){
                    $val['utd_name'] = mb_substr($val['utd_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['utd_name'] = $val['utd_name'];
                }
                $data[$key]['utd_meetname'] = "<div data-toggle='tooltip'title='{$data[$key]['utd_meetname']}'>{$val['utd_meetname']}</div>";
                $data[$key]['utd_name'] = "<div data-toggle='tooltip'title='{$data[$key]['utd_name']}'>{$val['utd_name']}</div>";
            }
            exit(json_encode(array('total' => $count, 'rows' => $data)));
        } else {
            $data = $obj->field($field)->select();
            $header = ['会议名称','待办事项', '完成形式', '计划完成时间', '预计完成时间','实际完成时间' , '责任人','责任处室','责任领导',  '完成确认'];
            if ($count == 0) {
                exit(json_encode(array('code' => -1, 'message' => '没有要导出的记录')));
            } else if ($count > 5000) {
                csvExport($header, $data, true);
            } else {
                excelExport($header, $data, true);
            }
        }
    }

    /*
     * 添加部门待办事项数据
    * */
    public function add()
    {
        addLog('', '用户访问日志', '部门待办事项添加\修改页面', '成功');
        $id = trim(I('get.id'));
        $orgModel = M('v_org_kyb');
        $meetModel = M('meetinfo');
        $model = M('unittodoinfo');
        if (!empty($id)) {
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model->where("utd_id='%s'", $id)->find();
            $this->assign('data', $data);
        }
        $list = D('Admin/dictionary')->getDicValueByName(['流程状态', '完成形式']);
        $dept = $orgModel->field('org_id,org_name')->select();
        $ling = D('Admin/User')->getUserRole(['科研部部领导', '科研部员工']);
        $meets = $meetModel->field('mt_id,mt_name')->select();
        $this->assign('zr', $ling['科研部员工']);
        $this->assign('ling', $ling['科研部部领导']);
        $this->assign('meets', $meets);
        $this->assign('dept', $dept);
        $this->assign('state', $list['流程状态']);
        $this->assign('finish', $list['完成形式']);
        $this->display();
    }

    /*
     * 数据提交
     * */
    public function submit()
    {
        $data = I('post.');
        $model = M('unittodoinfo');
        $work = M('workflow');
        $id = trim($data['utd_id']);
        $time = date('Y-m-d H:i:s');
        $userid = session('user_id');
        $orgModel = M('v_org_kyb');
        $userModel = M('sysuser');
        $kybunitName = $orgModel->field('org_name')->where("org_id = '%s'", $data['utd_kybunitid'])->find();
        $kybLeaderName = $userModel->field('user_realusername')->where("user_id = '%s'", $data['utd_kybleaderid'])->find();
        $kybChargemanName = $userModel->field('user_realusername')->where("user_id = '%s'", $data['utd_kybchargemanid'])->find();
        $data['utd_kybunit'] = $kybunitName['org_name'];
        $data['utd_kybleader'] = $kybLeaderName['user_realusername'];
        $data['utd_kybchargeman'] = $kybChargemanName['user_realusername'];
        $model->startTrans();
        try {
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            if (empty($id)) {
                $data['utd_createtime'] = $time;
                $data['utd_id'] = makeGuid();
                $data['utd_createuser'] = $userid;
                $model->add($data);
                $works = D('Yuan')->GetWorkFlow('创建','部门待办事项',$data['utd_id'],'');
                $work->add($works);
                addLog('unittodoinfo', '对象修改日志', '添加部门待办事项=>' . $data['utd_name'] . '成功', '成功');
            } else {
                $data['utd_lastmodifytime'] = $time;
                $data['utd_lastmodifyuser'] = $userid;
                $before = $model->where("utd_id = '%s'",$id)->find();
                $content = "";
                $content .=D('Yuan')->judge('DeptToDo',$before,$data);
                $works = D('Yuan')->GetWorkFlow('修改','部门待办事项',$data['utd_id'],$content);
                $work->add($works);
                $model->where("utd_id = '%s'", $id)->save($data);
                addLog('unittodoinfo', '对象修改日志', '修改部门待办事项=>' . $data['utd_name'] . '成功', '成功');
            }
            $model->commit();
            exit(makeStandResult(1, '添加成功'));
        } catch (\Exception $e) {
            addLog('unittodoinfo', '对象修改日志', '编辑部门待办事项=>' . $data['utd_name'] . '失败', '失败');
            $model->rollback();
            exit(makeStandResult(-1, '添加失败'));
        }
    }

    /*
    * 删除待办事项
    */
    public function del()
    {
        $ids = I('post.ids');
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));
        $id = explode(',', $ids);
        $model = M('unittodoinfo');
        $res = $model->where(['utd_id' => ['in', $id]])->delete();
        if (empty($res)) {
            addLog('unittodoinfo', '对象修改日志', '删除部门待办事项失败', '失败');
            exit(makeStandResult(-1, '删除失败'));
        } else {
            addLog('unittodoinfo', '对象修改日志', '删除部门待办事项成功', '成功');
            exit(makeStandResult(1, '删除成功'));
        }
    }

    /*
    * 提交页面提交
    * */
    public function tjSubmit()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:mi:ss'");
        $model = M('unittodoinfo');
        $userModel = M('sysuser');
        $work = M('workflow');
        $ids = I('post.ids');
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));
        $idArr = explode(',', $ids);
        $time = date('Y-m-d H:i:s');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'", $userid)->find();
        $model->startTrans();
        try {
            foreach ($idArr as $id) {
                $data['utd_finishtime'] = $time;
                $data['utd_finishmanid'] = $userid;
                $data['utd_finishman'] = $username['user_realusername'];
                $data['utd_flowstatus'] = '已提交';
                $data['utd_isback'] = '否';
                $res = $model->where("utd_id = '%s'", $id)->save($data);
                if($res){
                    $works = D('Yuan')->GetWorkFlow('提交','部门待办事项',$id,'');
                    $work->add($works);
                }
            }
            $model->commit();
            exit(makeStandResult(1, '提交成功'));
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '提交失败'));
        }
    }

    /*
     * 确认页面提交
     * */
    public function CfSubmit()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $model = M('unittodoinfo');
        $userModel = M('sysuser');
        $work = M('workflow');
        $ids = I('post.ids');
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));
        $idArr = explode(',', $ids);
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'", $userid)->find();
        $model->startTrans();
        try {
            foreach ($idArr as $id) {
                $data['utd_confirmtime'] = $time;
                $data['utd_confirmmanid'] = $userid;
                $data['utd_confirmman'] = $username['user_realusername'];
                $data['utd_flowstatus'] = '已确认';
                $model->where("utd_id = '%s'", $id)->save($data);
                $works = D('Yuan')->GetWorkFlow('确认','部门待办事项',$id,'');
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1, '确认成功'));
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '确认失败'));
        }

    }

    /*
    * 调整
    * */
    public function adjust()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $id = trim(I('get.id'));
        $model = M('unittodoinfo');
        $list = $model->field('utd_id,utd_modifyfinishdate,utd_modifyresult')->where("utd_id = '%s'", $id)->find();
        $this->assign('data', $list);
        $this->display();
    }

    public function adSubmit()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $data = I('post.');
        $model = M('unittodoinfo');
        $work = M('workflow');
        $userModel = M('sysuser');
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'", $userid)->find();
        $model->startTrans();
        try {
            $data['utd_modifytime'] = $time;
            $data['utd_modifymanid'] = $userid;
            $data['utd_modifyman'] = $username['user_realusername'];
            $model->where("utd_id = '%s'", $data['utd_id'])->save($data);
            $time =  $model->where("utd_id = '%s'",$data['utd_id'])->find();
            $content = '原定计划完成时间'.$time['utd_planfinishdate'].'，调整后预计完成时间'.$data['utd_modifyfinishdate'];
            $works = D('Yuan')->GetWorkFlow('调整','部门待办事项',$data['utd_id'],$content);
            $work->add($works);
            $model->commit();
            exit(makeStandResult(1, '调整成功'));
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '调整失败'));
        }
    }

    /**
     * 部门待办集成页面
     */
    public function frame()
    {
        $powers = D('RefinePower')->getViewPowers('DeptRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display();
    }

    /*
     * 批量添加
     * */
    public function saveCopyTables()
    {
        $receiveData = I('post.');
        $head = explode(',', $receiveData['head']);
        $data = $receiveData['data'];
        if (empty($data)) exit(makeStandResult(-1, '未接收到数据'));
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
        $fields = ['utd_meetname','utd_name', 'utd_finishtype', 'utd_planfinishdate', 'utd_kybchargemanid', 'utd_kybunitid', 'utd_kybleaderid'];

        $orgModel = D('Org'); //初始化org model 查询部门id
        $userModel = D('Admin/User');; //初始化user model 查询用户id
        $dictionaryModel = D('Admin/Dictionary'); //初始化Dictionary model 查询字典
//        $meetModel = D('Meet'); //初始化meet model 查询会议id
        $model = M('unittodoinfo');
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);
        foreach ($data as $key => $value) {
            $lineNum = $key + 1; //表格行号
            foreach ($value as $k => $v) {
                $field = $fields[$k - $reduce];
                $fieldName = '';
                $deptNameField = '';
                $userNameField = '';
                switch ($field) {
                    case 'utd_kybunitid':
                        $deptNameField = empty($deptNameField) ? 'utd_kybunit' : $deptNameField;
                        $fieldName = empty($fieldName) ? '科研部责任处室' : $fieldName;
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $unitId = $orgModel->getOrgId($v);
                        if (empty($unitId)) {
                            $error .= "第{$lineNum} 行  {$fieldName} 不存在<br>";
                            break;
                        }
                        $arr[$field] = $unitId;
                        $arr[$deptNameField] = $v;
                        break;
                    case 'utd_kybleaderid':
                        $fieldName = '科研部主管领导';
                        $userNameField = 'utd_kybleader';
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealNameRole($v, 'user_id,user_realusername');
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        } else if (count($userInfo) > 1) {
                            $userInfo['user_id'] = '';
                            $userInfo['user_realusername'];
                            break;
                        } else {
                            $userInfo = $userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'utd_kybchargemanid':
                        $fieldName = empty($fieldName) ? '科研部责任人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'utd_kybchargeman' : $userNameField;
                        if (empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealName($v, 'user_id,user_realusername');
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        } else if (count($userInfo) > 1) {
                            $userInfo['user_id'] = '';
                            $userInfo['user_realusername'];
                            break;
                        } else {
                            $userInfo = $userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'utd_planfinishdate':
                        $arr[$field] = date('Y-m-d', strtotime($v));
                        break;
                    case 'utd_name':
                        $todoNames[] = $v;
                    default:
                        $arr[$field] = $v;
                        break;
                }
            }
            $arr['utd_createtime'] = $time;
            $arr['utd_id'] = makeGuid();
            $arr['utd_createuser'] = $loginUserId;
            $initTables[] = $arr;
        }
        $model->startTrans();
        try{
            if (empty($error)) {
                $successNum = 0;
                $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
                foreach ($initTables as $value) {
                    $res = $model->add($value);
                    if (!empty($res)) $successNum += 1;
                }
                $failNum = $exportNum - $successNum;
                addLog('unittodoinfo', '对象导入日志', '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
                $model->commit();
                exit(makeStandResult(1, '添加成功'));
            } else {
                exit(makeStandResult(-1, $error));
            }
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'添加失败'));
        }

    }

    public function tjTime(){
        $id = trim(I('get.id'));
        $this->assign('utd_id', $id);
        $this->assign('time',date('Y-m-d'));
        $this->display();
    }

    public function back()
    {
        $id = trim(I('get.id'));
        $this->assign('utd_id', $id);
        $this->display();
    }

    public function BackSubmit()
    {
        $model = M('unittodoinfo');
        $data = I('post.');
        $work = M('workflow');
        $idArr = explode(',',$data['utd_id']);
        try{
            foreach($idArr as $id){
                $list['utd_isback'] = '是';
                $list['utd_backcomment'] = $data['utd_backcomment'];
                $list['utd_flowstatus'] = '被退回';
                $list['utd_finishtime'] = '';
                $list['utd_confirmtime'] = '';
                $model->where("utd_id = '%s'",$id)->save($list);
                $works = D('Yuan')->GetWorkFlow('退回','部门待办事项',$id,$data['utd_backcomment']);
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1,'成功退回'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'退会失败'));
        }
    }
}