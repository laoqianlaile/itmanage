<?php
namespace Demo\Controller;
use Think\Controller;
class YuanController extends BaseController {
    public function index(){
        $orgCs = M('v_org_kyb');
        $orgDw = M('v_org_unit');
        $dept = $orgCs->field('org_id,org_name')->select();
        $deptDw = $orgDw->field('org_id,org_name')->select();
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $year = D('Admin/Dictionary')->getDicValueByName('年度');
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('year',$year);
        $this->assign('yearNow',date('Y'));
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('deptDw',$deptDw);
        $this->assign('zr',$zeren);
        $this->display();
    }

    /*
     * 访问提交页面
     * */
    public function tijiao(){
        $userid = session('user_id');
        $userModel = M('v_sysuser');
        $org = $userModel->where("user_id = '%s' and org_type = '外部组织'",$userid)->find();
        if(empty($org))
            $orgName = "无效组织";
        else
            $orgName = $org['org_name'];
        $this->assign('orgName',$orgName);
        $orgCs = M('v_org_kyb');
        $orgDw = M('v_org_unit');
        $dept = $orgCs->field('org_id,org_name')->select();
        $deptDw = $orgDw->field('org_id,org_name')->select();
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $year = D('Admin/Dictionary')->getDicValueByName('年度');
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('year',$year);
        $this->assign('yearNow',date('Y'));
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('deptDw',$deptDw);
        $this->assign('zr',$zeren);
        $this->display();
    }

    /*
     * 访问确认页面
     * */
    public function confirm(){
        $userid = session('user_id');
//        $userid = 'TF376B2AB5393435EB7A2B8E1';
        $this->assign('userid',$userid);
        $orgCs = M('v_org_kyb');
        $orgDw = M('v_org_unit');
        $dept = $orgCs->field('org_id,org_name')->select();
        $deptDw = $orgDw->field('org_id,org_name')->select();
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $year = D('Admin/Dictionary')->getDicValueByName('年度');
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('year',$year);
        $this->assign('yearNow',date('Y'));
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('deptDw',$deptDw);
        $this->assign('zr',$zeren);
        $this->display();
    }

    /*
     * 访问查询页面
     * */
    public function rogatory(){
        $orgCs = M('v_org_kyb');
        $orgDw = M('v_org_unit');
        $model = M('v_wytodoinfo v');
        $dept = $orgCs->field('org_id,org_name')->select();
        $deptDw = $orgDw->field('org_id,org_name')->select();
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $year = D('Admin/Dictionary')->getDicValueByName('年度');
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $nowYear = date('Y');
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['院领导','科研部部领导','科研部综合处'];
        $role2 = ['科研部处室领导','科研部处室管理员'];
        $role3 = ['各单位责任人','各单位科技处管理人员'];
        $userid = session('user_id');
        if(!empty(array_intersect($role1,$roles))){

        }else if(!empty(array_intersect($role2,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where['td_kybunit'] = array('eq' ,$orgName['org_name']);
        }else if(!empty(array_intersect($role3,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '外部组织' ",$userid)->find();
            $where['td_unit'] = array('eq' ,$orgName['org_name']);
        }else{
            $where['td_kybunit'] = '无效组织';
        }
        $dbCount = $model->where($where)->where("to_char(td_planfinishdate,'YYYY') = '%s'",$nowYear)->join('meetinfo m on v.td_meetid=m.mt_id')->count();
        $hdCount =  M('meetinfo m')->field("mt_type,to_char(mt_date,'YYYY') mt_date")
            ->join("v_wytodoinfo w on m.mt_id = w.td_meetid")
            ->where($where)
            ->where("to_char(mt_date,'YYYY') = '%s'",$nowYear)
            ->group('mt_id,mt_name,mt_type,mt_date')
            ->select();
        $finishCount = $model->where($where)->where("to_char(td_planfinishdate,'YYYY') = '%s' and status = '已完成'",$nowYear)->join('meetinfo m on v.td_meetid=m.mt_id')->count();
        $wCount = $model->where($where)->where("to_char(td_planfinishdate,'YYYY') = '%s' and status = '未完成'",$nowYear)->join('meetinfo m on v.td_meetid=m.mt_id')->count();
        $TzCount = $model->where($where)->where("to_char(td_planfinishdate,'YYYY') = '%s' and td_modifytime is not null",$nowYear)->join('meetinfo m on v.td_meetid=m.mt_id')->count();
        $FinishLv = ($finishCount/$dbCount)*100;
        $FinishLv =  mb_substr($FinishLv, 0, 5, 'utf8');
        $this->assign('TzCount',$TzCount);
        $this->assign('dbCount',$dbCount);
        $this->assign('hdCount',count($hdCount));
        $this->assign('finishCount',$finishCount);
        $this->assign('wCount',$wCount);
        $this->assign('FinishLv',$FinishLv);
        $this->assign('year',$year);
        $this->assign('yearNow',date('Y'));
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('deptDw',$deptDw);
        $this->assign('zr',$zeren);
        $this->display();
    }



    /*
     * 获取待办事项数据
     * */
    public function getData($isExport = false){
        if($isExport === true){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $model = M('v_wytodoinfo v');
        $fileModel = M('filerelation');
        $workModel = M('workflow');
        $tdName = trim($queryParam['td_name']);
        $unitName = trim($queryParam['unitname']);
        $stateTj = trim($queryParam['stateTj']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $dept = trim($queryParam['dept']);
        $tdStatus = trim($queryParam['td_status']);
        $mtId = trim($queryParam['mt_id']);
        $userid = trim($queryParam['user_id']);
        $orgName = trim($queryParam['orgName']);
        $date = trim($queryParam['mt_date']);
        $userIds = session('user_id');
        $where=array();
        if(!empty($stateTj)){
            $stateTj = explode(',',$stateTj);
            $where['td_flowstatus'] = array('in' ,$stateTj);
        }
        if(!empty($tdName))$where['td_name'] = array('like' ,"%$tdName%");
        if(!empty($unitName))$where['td_unit'] = array('eq' ,$unitName);
        if(!empty($orgName)){
            $where[1]['_logic'] = 'or';
            $where[1][0]['td_unit'] = array('eq' ,$orgName);
            $where[1][1]['td_kybchargemanid'] = array('eq' ,$userIds);
        }
        if(!empty($start)){
            $where[0]['_logic'] = 'and';
            $where[0][0]['td_planfinishdate'] = array('egt' ,$start);
        }
        if(!empty($end)){
            $where[0]['_logic'] = 'and';
            $where[0][1]['td_planfinishdate'] = array('elt' ,$end);
        }
        if(!empty($kybChargeManName))$where['td_kybchargeman'] = array('eq' ,$kybChargeManName);
        if(!empty($dept))$where['td_kybunit'] = array('eq' ,$dept);
        if(!empty($tdStatus))$where['status'] = array('eq' ,$tdStatus);
        if(!empty($date))$where["to_char(td_planfinishdate,'YYYY')"] = array('eq' ,$date);
        $list = D('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type','left')->join('dic_type t on d.dic_type = t.dic_type_id','left')->where("to_char(mt_date,'YYYY') = '%s'",$date)->group("mt_type,dic_order")->order('dic_order')->select();
        $meet = removeArrKey($list, 'mt_type');
        if($mtId == '全部待办事项'){

        }else if(in_array($mtId,$meet)){
            $where['mt_type'] = array('eq' ,$mtId);
        }else{
            $where['mt_id'] = array('eq' ,$mtId);
        }
        if(!empty($userid) && $mtId!='undefined')$where['td_kybleaderid'] = array('eq' ,$userid);
        $field="meettype,meetname,meetdate,td_name,td_planfinishdate,td_modifyfinishdate,td_unit,td_confirmtime,td_kybchargeman,td_kybunit,td_kybleader,cast(status as varchar(100)) status";
        $count = $model
            ->join('meetinfo m on v.td_meetid=m.mt_id','left')
            ->where($where)
            ->count();
        $obj = $model
            ->join('meetinfo m on v.td_meetid=m.mt_id','left')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");
        if($isExport === false){
            $field .= ",td_id,td_backcomment,td_iscancel,td_flowstatus";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach($data as $key => $val){
                $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '院级待办事项'",$val['td_id'])->count();
                $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '院待办事项'",$val['td_id'])->order('wf_time')->select();
                foreach($work as $k =>$v){
                    if(!empty($v['wf_content'])){
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                    }else{
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                    }
                }
                $data[$key]['workflow'] = $work;

                if(mb_strlen($val['meetname'], 'utf8') >25){
                    $val['meetname'] = mb_substr($val['meetname'], 0, 25, 'utf8') .'...';
                }else{
                    $val['meetname'] = $val['meetname'];
                }
                if(mb_strlen($val['td_name'], 'utf8') >25){
                    $val['td_name'] = mb_substr($val['td_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['td_name'] = $val['td_name'];
                }
                $data[$key]['meetname'] = "<div data-toggle='tooltip'title='{$data[$key]['meetname']}'>{$val['meetname']}</div>";
                $data[$key]['td_name'] = "<div data-toggle='tooltip'title='{$data[$key]['td_name']}'>{$val['td_name']}</div>";
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }else{
            $data = $obj->field($field)->select();
            $header = ['管理活动类别','管理活动名称','召开时间','待办事项', '计划完成时间','预计完成时间','责任单位', '实际完成时间', '督办人', '责任处室', '责任领导', '完成确认'];
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
     * 获取待办事项数据
     * */
    public function getDataConfirm($isExport = false){
        if($isExport === true){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $model = M('v_wytodoinfo v');
        $fileModel = M('filerelation');
        $workModel = M('workflow');
        $state = trim($queryParam['state']);
        $tdName = trim($queryParam['td_name']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $dept = trim($queryParam['dept']);
        $tdStatus = trim($queryParam['td_status']);
        $mtId = trim($queryParam['mt_id']);
        $userid = trim($queryParam['user_id']);
        $date = trim($queryParam['mt_date']);
        $where=array();
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['科研部部领导'];
        $role2 = ['科研部处室领导'];
        $role3 = ['科研部员工'];
        if(!empty(array_intersect($role1,$roles))){
            if(!empty($userid))$where['td_kybleaderid'] = array('eq' ,$userid);
        }else if(!empty(array_intersect($role2,$roles)) && !empty(array_intersect($role3,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where[1]['_logic'] = 'or';
            $where[1][0]['td_kybunit'] = array('eq' ,$orgName['org_name']);
            $where[1][1]['td_kybchargemanid'] = array('eq' ,$userid);

        }else if(!empty(array_intersect($role2,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where['td_kybunit'] = array('eq' ,$orgName['org_name']);
        }else if(!empty(array_intersect($role3,$roles))){
            if(!empty($userid))$where['td_kybchargemanid'] = array('eq' ,$userid);
        }
        if (!empty($state)) {
            $states = explode(',', $state);
            $where['td_flowstatus'] = array('in', $states);
        }
        if(!empty($tdName))$where['td_name'] = array('like' ,"%$tdName%");
        if(!empty($start)){
            $where[0]['_logic'] = 'and';
            $where[0][0]['td_planfinishdate'] = array('egt' ,$start);
        }
        if(!empty($end)){
            $where[0]['_logic'] = 'and';
            $where[0][1]['td_planfinishdate'] = array('elt' ,$end);
        }
        if(!empty($kybChargeManName))$where['td_kybchargeman'] = array('eq' ,$kybChargeManName);
        if(!empty($dept))$where['td_kybunit'] = array('eq' ,$dept);
        if(!empty($tdStatus))$where['status'] = array('eq' ,$tdStatus);
        if(!empty($date))$where["to_char(td_planfinishdate,'YYYY')"] = array('eq' ,$date);
        $list = D('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type','left')->join('dic_type t on d.dic_type = t.dic_type_id','left')->where("to_char(mt_date,'YYYY') = '%s'",$date)->group("mt_type,dic_order")->order('dic_order')->select();
        $meet = removeArrKey($list, 'mt_type');
        if($mtId == '全部待办事项'){

        }else if(in_array($mtId,$meet)){
            $where['mt_type'] = array('eq' ,$mtId);
        }else{
            $where['mt_id'] = array('eq' ,$mtId);
        }
        $field="meettype,meetname,meetdate,td_name,td_planfinishdate,td_modifyfinishdate,td_unit,td_confirmtime,td_kybchargeman,td_kybunit,td_kybleader,cast(status as varchar(100)) status";
        $count = $model
            ->join('meetinfo m on v.td_meetid=m.mt_id','left')
            ->where($where)
            ->count();
        $obj = $model
            ->join('meetinfo m on v.td_meetid=m.mt_id','left')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");
        if($isExport === false){
            $field .= ",td_id";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach($data as $key => $val){
                $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '院级待办事项'",$val['td_id'])->count();
                $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '院待办事项'",$val['td_id'])->order('wf_time')->select();
                foreach($work as $k =>$v){
                    if(!empty($v['wf_content'])){
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                    }else{
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                    }
                }
                $data[$key]['workflow'] = $work;
                if(mb_strlen($val['meetname'], 'utf8') >25){
                    $val['meetname'] = mb_substr($val['meetname'], 0, 25, 'utf8') .'...';
                }else{
                    $val['meetname'] = $val['meetname'];
                }
                if(mb_strlen($val['td_name'], 'utf8') >25){
                    $val['td_name'] = mb_substr($val['td_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['td_name'] = $val['td_name'];
                }
                $data[$key]['meetname'] = "<div data-toggle='tooltip'title='{$data[$key]['meetname']}'>{$val['meetname']}</div>";
                $data[$key]['td_name'] = "<div data-toggle='tooltip'title='{$data[$key]['td_name']}'>{$val['td_name']}</div>";
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }else{
            $data = $obj->field($field)->select();
            $header = ['会议名称','待办事项', '完成形式', '责任部门/单位', '计划完成时间','预计完成时间', '科研部主管领导', '科研部责任处室', '科研部责任人', '状态','备注'];
            if($count == 0){
                exit(json_encode(array('code' => -1, 'message' => '没有要导出的记录')));
            } else if( $count > 5000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }

    }

    public function getDatas($isExport = false){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        if($isExport === true){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $where=array();
        $fileModel = M('filerelation');
        $workModel = M('workflow');
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['院领导','科研部部领导','科研部综合处'];
        $role2 = ['科研部处室领导','科研部处室管理员'];
        $role3 = ['各单位责任人','各单位科技处管理人员'];
        $userid = session('user_id');
        if(!empty(array_intersect($role1,$roles))){

        }else if(!empty(array_intersect($role2,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where['td_kybunit'] = array('eq' ,$orgName['org_name']);
        }else if(!empty(array_intersect($role3,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '外部组织' ",$userid)->find();
            $where['td_unit'] = array('eq' ,$orgName['org_name']);
        }else{
            $where['td_kybunit'] = '无效组织';
        }
        $model = M('v_wytodoinfo v');
        $tdName = trim($queryParam['td_name']);
        $unitName = trim($queryParam['unitname']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $dept = trim($queryParam['dept']);
        $tdStatus = trim($queryParam['td_status']);
        $mtId = trim($queryParam['mt_id']);
        $userid = trim($queryParam['user_id']);
        $date = trim($queryParam['mt_date']);
        if(!empty($tdName))$where['td_name'] = array('like' ,"%$tdName%");
        if(!empty($unitName))$where['td_unit'] = array('eq' ,$unitName);
        if(!empty($start)){
            $where[0]['_logic'] = 'and';
            $where[0][0]['td_planfinishdate'] = array('egt' ,$start);
        }
        if(!empty($end)){
            $where[0]['_logic'] = 'and';
            $where[0][1]['td_planfinishdate'] = array('elt' ,$end);
        }
        if(!empty($kybChargeManName))$where['td_kybchargeman'] = array('eq' ,$kybChargeManName);
        if(!empty($dept))$where['td_kybunit'] = array('eq' ,$dept);
        if(!empty($tdStatus))$where['status'] = array('eq' ,$tdStatus);
        if(!empty($date))$where["to_char(td_planfinishdate,'YYYY')"] = array('eq' ,$date);
        $list = D('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type','left')->join('dic_type t on d.dic_type = t.dic_type_id','left')->where("to_char(mt_date,'YYYY') = '%s'",$date)->group("mt_type,dic_order")->order('dic_order')->select();
        $meet = removeArrKey($list, 'mt_type');
        if($mtId == '全部待办事项'){

        }else if(in_array($mtId,$meet)){
            $where['mt_type'] = array('eq' ,$mtId);
        }else{
            $where['mt_id'] = array('eq' ,$mtId);
        }
        if(!empty($userid) && $mtId!='undefined')$where['td_kybleaderid'] = array('eq' ,$userid);
        $field="meettype,meetname,meetdate,td_name,td_planfinishdate,td_modifyfinishdate,td_unit,td_confirmtime,td_kybchargeman,td_kybunit,td_kybleader,cast(status as varchar(100)) status";
        $count = $model
            ->join('meetinfo m on v.td_meetid=m.mt_id','left')
            ->where($where)
            ->count();
        $obj = $model
            ->join('meetinfo m on v.td_meetid=m.mt_id','left')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");
        if($isExport === false){
            $field .= ",td_id,td_finishtime,td_finishman,td_confirmman,td_modifyman,td_flowstatus";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach($data as $key => $val){
                $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '院级待办事项'",$val['td_id'])->count();
                $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '院待办事项'",$val['td_id'])->order('wf_time')->select();
                foreach($work as $k =>$v){
                    if(!empty($v['wf_content'])){
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                    }else{
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                    }
                }
                $data[$key]['workflow'] = $work;
                if(mb_strlen($val['meetname'], 'utf8') >25){
                    $val['meetname'] = mb_substr($val['meetname'], 0, 25, 'utf8') .'...';
                }else{
                    $val['meetname'] = $val['meetname'];
                }
                if(mb_strlen($val['td_name'], 'utf8') >25){
                    $val['td_name'] = mb_substr($val['td_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['td_name'] = $val['td_name'];
                }
                $data[$key]['meetname'] = "<div data-toggle='tooltip'title='{$data[$key]['meetname']}'>{$val['meetname']}</div>";
                $data[$key]['td_name'] = "<div data-toggle='tooltip'title='{$data[$key]['td_name']}'>{$val['td_name']}</div>";
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }else{
            $data = $obj->field($field)->select();
            $header = ['管理活动类别','管理活动名称','召开时间','待办事项', '计划完成时间','预计完成时间','责任单位', '实际完成时间', '督办人', '责任处室', '责任领导', '完成确认'];
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
     * 添加待办事项数据
     * */
    public function add(){
        $id = trim(I('get.id'));
        $mtId = trim(I('get.mt_id'));
        $meet = M('meetinfo');
        $model = M('wytodoinfo');
        if(!empty($id)){
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data=$model->where("td_id='%s'",$id)->find();
        }
        $orgCs = M('v_org_kyb');
        $orgDw = M('v_org_unit');
        $dept = $orgCs->field('org_id,org_name')->select();
        $deptDw = $orgDw->field('org_id,org_name')->select();
        $list = D('Admin/Dictionary')->getDicValueByName(['流程状态','完成形式']);
        $ling = D('Admin/User')->getUserRole(['科研部部领导','科研部员工']);
        $meets = $meet->field('mt_id,mt_name')->select();
        $addMeet = $meet->field('mt_id,mt_name')->where("mt_id = '%s'",$mtId)->find();
        $this->assign('mtname',$addMeet);
        $this->assign('data',$data);
        $this->assign('zr',$ling['科研部员工']);
        $this->assign('ling',$ling['科研部部领导']);
        $this->assign('meets',$meets);
        $this->assign('dept',$dept);
        $this->assign('deptDw',$deptDw);
        $this->assign('state',$list['流程状态']);
        $this->assign('finish',$list['完成形式']);
        $this->display();
    }

    /*
     * 数据提交
     * */
    public function submit(){
        $data=I('post.');
        $model = M('wytodoinfo');
        $monthModel = M('monthtodoinfo');
        $meetModel = M('meetinfo');
        $work = M('workflow');
        $id = $data['td_id'];
        $time = date('Y-m-d H:i:s');
        $userid = session('user_id');
        $userModel = M('sysuser');
        $orgCs = M('v_org_kyb');
        $orgDw = M('v_org_unit');
        $unitName = $orgDw->field('org_name')->where("org_id = '%s'",$data['td_unitid'])->find();
        $kybunitName = $orgCs->field('org_name')->where("org_id = '%s'",$data['td_kybunitid'])->find();
        $kybLeaderName = $userModel->field('user_realusername')->where("user_id = '%s'",$data['td_kybleaderid'])->find();
        $kybChargemanName = $userModel->field('user_realusername')->where("user_id = '%s'",$data['td_kybchargemanid'])->find();
        $data['td_unit'] = $unitName['org_name'];
        $data['td_kybunit'] = $kybunitName['org_name'];
        $data['td_kybleader'] = $kybLeaderName['user_realusername'];
        $data['td_kybchargeman'] = $kybChargemanName['user_realusername'];
        $model->startTrans();
        try{
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            if(empty($id)){
                $data['td_createtime'] = $time;
                $data['td_id'] = makeGuid();
                $data['td_createuser'] = $userid;
                $model->add($data);
                $list['ytd_id'] = makeGuid();
                $list['ytd_name'] = $data['td_name'];
                $list['ytd_kybunitid'] = $data['td_kybunitid'];
                $list['ytd_kybleaderid'] = $data['td_kybleaderid'];
                $list['ytd_kybchargemanid'] = $data['td_kybchargemanid'];
                $list['ytd_kybunit'] = $kybunitName['org_name'];
                $list['ytd_kybleader'] = $kybLeaderName['user_realusername'];
                $list['ytd_kybchargeman'] = $kybChargemanName['user_realusername'];
                $list['ytd_planfinishdate'] = $data['td_planfinishdate'];
                $list['ytd_iswytodo'] = '是';
                $list['ytd_tdid'] = $data['td_id'];
                $data['ytd_createtime'] = $time;
                $data['ytd_createuser'] = $userid;
                $monthModel->add($list);
                $works = D('Yuan')->GetWorkFlow('创建','院待办事项',$data['td_id'],'');
                $work->add($works);
            }else{
                $data['td_lastmodifytime'] = $time;
                $data['td_lastmodifyuser'] = $userid;
                $before = $model->where("td_id = '%s'",$id)->find();
                $content = "";
                $content .=D('Yuan')->judge('WyToDo',$before,$data);
                $works = D('Yuan')->GetWorkFlow('修改','院待办事项',$data['td_id'],$content);
                $work->add($works);
                $model->where("td_id = '%s'",$id)->save($data);
                $Mid =  $monthModel->field('ytd_id')->where("ytd_tdid = '%s'",$data['td_id'])->find();
                $list['ytd_name'] = $data['td_name'];
                $list['ytd_kybunitid'] = $data['td_kybunitid'];
                $list['ytd_kybleaderid'] = $data['td_kybleaderid'];
                $list['ytd_kybchargemanid'] = $data['td_kybchargemanid'];
                $list['ytd_kybunit'] = $kybunitName['org_name'];
                $list['ytd_kybleader'] = $kybLeaderName['user_realusername'];
                $list['ytd_kybchargeman'] = $kybChargemanName['user_realusername'];
                $list['ytd_planfinishdate'] = $data['td_planfinishdate'];
                $monthModel->where("ytd_id = '%s'",$Mid['ytd_id'])->save($list);
            }
            $model->commit();
            exit(makeStandResult(1,'编辑成功'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'编辑失败'));
        }
    }

    /*
     * 删除待办事项
     */
    public function del(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('wytodoinfo');
        $monthModel = M('monthtodoinfo');
        $Mid = $monthModel->where(['ytd_tdid'=> ['in', $id]])->delete();
        $res = $model-> where(['td_id'=> ['in', $id]])->delete();
        if(empty($res)){
            exit(makeStandResult(-1,'删除失败'));
        }else{
            exit(makeStandResult(1,'删除成功'));
        }
    }

    /*
     * 取消
     * */
    public function nullify(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $model = M('wytodoinfo');
        $work = M('workflow');
        $userModel = M('sysuser');
        $idArr = explode(',', $ids);
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'", $userid)->find();
        $model->startTrans();
        try{
            foreach($idArr as $id){
                $data['td_canceltime'] = $time;
                $data['td_cancelmanid'] = $userid;
                $data['td_cancelman'] = $username['user_realusername'];
                $data['td_iscancel'] = '是';
                $data['td_flowstatus'] = '已取消';
                $model->where("td_id = '%s'",$id)->save($data);
                $works = D('Yuan')->GetWorkFlow('取消','院待办事项',$id,'');
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1,'取消成功'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'取消失败'));
        }
    }

    /**
     * 获取会议树
     */
    public function getOrgTree(){
        $mt_name = trim(I('post.mt_name'));
        $mt_date = trim(I('post.mt_date'));
        $dept = trim(I('post.dept'));
        $orgName = trim(I('post.orgName'));
        $userid = trim(I('post.user_id'));
        $state =trim(I('post.state'));
        $where = [];
        if(!empty($mt_name)) $where['mt_name'] = ['like', "%$mt_name%"];
        if(!empty($mt_date)) $where["to_char(mt_date,'YYYY')"] = ['eq', $mt_date];
        if(!empty($dept)) $where["mt_deptname"] = ['eq', $dept];
        if(!empty($orgName))$where['td_unit'] = array('eq' ,$orgName);
        if(!empty($userid))$where['td_kybleaderid'] = array('eq' ,$userid);
        if (!empty($state)) {
            $states = explode(',', $state);
            $where['td_flowstatus'] = array('in', $states);
        }
        $model = D('Yuan');
        $data = $model->getMeetList(false, $where);
        $list = M('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type')->join('dic_type t on d.dic_type = t.dic_type_id')->where("to_char(mt_date,'YYYY') = '%s'",$mt_date)->group("mt_type,dic_order")->order('dic_order')->select();
        $arr = array();
        foreach($list as $key => $val){
            $arr[] = [
                'mt_name' => $val['mt_type'],
                'mt_id' => $val['mt_type'],
                'mt_type' => '全部待办事项',
            ];
        }
        $arr[] = [
            'mt_name' => '全部待办事项',
            'mt_id' => '全部待办事项',
            'mt_type' => '0',
        ];
        $data = array_merge($data,$arr);
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
     * 获取会议树
     */
    public function getOrgTreeConfirm(){
        $mt_name = trim(I('post.mt_name'));
        $mt_date = trim(I('post.mt_date'));
        $userid = trim(I('post.user_id'));
        $state =trim(I('post.state'));
        $where = [];
        if(!empty($mt_name)) $where['mt_name'] = ['like', "%$mt_name%"];
        if(!empty($mt_date)) $where["to_char(mt_date,'YYYY')"] = ['eq', $mt_date];
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['科研部部领导'];
        $role2 = ['科研部处室领导'];
        $role3 = ['科研部员工'];
        if(!empty(array_intersect($role1,$roles))){
            if(!empty($userid))$where['td_kybleaderid'] = array('eq' ,$userid);
        }else if(!empty(array_intersect($role2,$roles)) && !empty(array_intersect($role3,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where[1]['_logic'] = 'or';
            $where[1][0]['td_kybunit'] = array('eq' ,$orgName['org_name']);
            $where[1][1]['td_kybchargemanid'] = array('eq' ,$userid);

        }else if(!empty(array_intersect($role2,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where['td_kybunit'] = array('eq' ,$orgName['org_name']);
        }else if(!empty(array_intersect($role3,$roles))){
            if(!empty($userid))$where['td_kybchargemanid'] = array('eq' ,$userid);
        }else{
            $where['td_kybunit'] = '无效组织';
        }
        if (!empty($state)) {
            $states = explode(',', $state);
            $where['td_flowstatus'] = array('in', $states);
        }
        $model = D('Yuan');
        $data = $model->getMeetList(false, $where);
        $list = M('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type')->join('dic_type t on d.dic_type = t.dic_type_id')->where("to_char(mt_date,'YYYY') = '%s'",$mt_date)->group("mt_type,dic_order")->order('dic_order')->select();
        $arr = array();
        foreach($list as $key => $val){
            $arr[] = [
                'mt_name' => $val['mt_type'],
                'mt_id' => $val['mt_type'],
                'mt_type' => '全部待办事项',
            ];
        }
        $arr[] = [
            'mt_name' => '全部待办事项',
            'mt_id' => '全部待办事项',
            'mt_type' => '0',
        ];
        $data = array_merge($data,$arr);
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
     * 获取查询会议树
     */
    public function getOrgTreeFind(){
        $mt_name = trim(I('post.mt_name'));
        $mt_date = trim(I('post.mt_date'));
        $where = [];
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['院领导','科研部部领导','科研部综合处'];
        $role2 = ['科研部处室领导','科研部处室管理员'];
        $role3 = ['各单位责任人','各单位科技处管理人员'];
        $userid = session('user_id');
        if(!empty(array_intersect($role1,$roles))){

        }else if(!empty(array_intersect($role2,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where['td_kybunit'] = array('eq' ,$orgName['org_name']);
        }else if(!empty(array_intersect($role3,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '外部组织' ",$userid)->find();
            $where['td_unit'] = array('eq' ,$orgName['org_name']);
        }else{
            $where['td_kybunit'] = '无效组织';
        }
        if(!empty($mt_name)) $where['mt_name'] = ['like', "%$mt_name%"];
        if(!empty($mt_date)) $where["to_char(mt_date,'YYYY')"] = ['eq', $mt_date];
        $model = D('Yuan');
        $data = $model->getMeetList(false, $where);
        $list = M('meetinfo m')->field("mt_type,dic_order")->join('dic d on d.dic_name = m.mt_type')->join('dic_type t on d.dic_type = t.dic_type_id')->where("to_char(mt_date,'YYYY') = '%s'",$mt_date)->group("mt_type,dic_order")->order('dic_order')->select();
        $arr = array();
        foreach($list as $key => $val){
            $arr[] = [
                'mt_name' => $val['mt_type'],
                'mt_id' => $val['mt_type'],
                'mt_type' => '全部待办事项',
            ];
        }
        $arr[] = [
            'mt_name' => '全部待办事项',
            'mt_id' => '全部待办事项',
            'mt_type' => '0',
        ];
        $data = array_merge($data,$arr);
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
        $meetId = trim($receiveData['extraParam']);
        if(empty($meetId)) exit(makeStandResult(-1, '缺失参数'));
        $time = date('Y-m-d H:i:s');
        $loginUserId = session('user_id');
        $fields = [ 'td_name', 'td_planfinishdate', 'td_unitid', 'td_kybchargemanid', 'td_kybunitid', 'td_kybleaderid'];

        $orgModel = D('Org'); //初始化org model 查询部门id
        $userModel = D('Admin/User');; //初始化user model 查询用户id
        $dictionaryModel = D('Admin/Dictionary'); //初始化Dictionary model 查询字典
//        $meetModel = D('Meet'); //初始化meet model 查询会议id
        $user = M('sysuser');
        $org = M('v_org_kyb');
        $model = M('wytodoinfo');
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);
        foreach($data as $key=>$value){
            $lineNum = $key + 1; //表格行号
            $arr = [
                'td_meetid' => $meetId
            ];
            foreach($value as $k=>$v){
                $field = $fields[$k - $reduce];
                $fieldName = '';
                $deptNameField = '';
                $userNameField = '';
                switch($field){
                    case 'td_unitid':
                        $deptNameField = 'td_unit';
                        $fieldName = '责任单位';
                    case 'td_kybunitid':
                        $deptNameField = empty($deptNameField) ? 'td_kybunit' : $deptNameField;
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
                    case 'td_kybleaderid':
                        $fieldName = '责任领导';
                        $userNameField = 'td_kybleader';
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealNameRole($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        } else if(count($userInfo) > 1){
                            $userInfo['user_id'] = '';
                            $userInfo['user_realusername'] = '';
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }

                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'td_kybchargemanid':
                        $fieldName = empty($fieldName) ? '督办人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'td_kybchargeman' : $userNameField;
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealName($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        } else if(count($userInfo) > 1){
                            $userInfo['user_id'] = '';
                            $userInfo['user_realusername'] = '';
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }

                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'td_planfinishdate':
                        $arr[$field] = date('Y-m-d', strtotime($v));
                        break;
                    case 'td_name':
                        $todoNames[] = $v;
                    default: $arr[$field] = $v;
                        break;
                }
            }
            $arr['td_createtime'] = $time;
            $arr['td_id'] = makeGuid();
            $arr['td_createuser'] = $loginUserId;
            $initTables[] = $arr;
        }
        $model->startTrans();
        try{
            if(empty($error)){
                $successNum = 0;
                $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
                foreach($initTables as $value){
                    $res = $model->add($value);
                    $kybunitName = $org->field('org_name')->where("org_id = '%s'",$value['td_kybunitid'])->find();
                    $kybLeaderName = $user->field('user_realusername')->where("user_id = '%s'",$value['td_kybleaderid'])->find();
                    $kybChargemanName = $user->field('user_realusername')->where("user_id = '%s'",$value['td_kybchargemanid'])->find();
                    $list['ytd_id'] = makeGuid();
                    $list['ytd_name'] = $value['td_name'];
                    $list['ytd_kybunitid'] = $value['td_kybunitid'];
                    $list['ytd_kybleaderid'] = $value['td_kybleaderid'];
                    $list['ytd_kybchargemanid'] = $value['td_kybchargemanid'];
                    $list['ytd_kybunit'] = $kybunitName['org_name'];
                    $list['ytd_kybleader'] = $kybLeaderName['user_realusername'];
                    $list['ytd_kybchargeman'] = $kybChargemanName['user_realusername'];
                    $list['ytd_planfinishdate'] = $value['td_planfinishdate'];
                    $list['ytd_tdid'] = $value['td_id'];
                    $list['ytd_iswytodo'] = '是';
                    $data['ytd_createtime'] = $time;
                    $data['ytd_createuser'] = $loginUserId;
                    M('monthtodoinfo')->add($list);
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

    /*
     * 提交页面提交
     * */
    public function tjSubmit()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $model = M('wytodoinfo');
        $work = M('workflow');
        $userModel = M('sysuser');
        $ids = I('post.ids');
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));
        $idArr = explode(',', $ids);
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'", $userid)->find();
        $model->startTrans();
        try {
            foreach ($idArr as $id) {
                $data['td_finishtime'] = $time;
                $data['td_finishmanid'] = $userid;
                $data['td_finishman'] = $username['user_realusername'];
                $data['td_flowstatus'] = '已提交';
                $data['td_isback'] = '否';
                $res = $model->where("td_id = '%s'", $id)->save($data);
                if($res){
                    $works = D('Yuan')->GetWorkFlow('提交','院待办事项',$id,'');
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
    public function ConfirmSubmit(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $model = M('wytodoinfo');
        $userModel = M('sysuser');
        $work = M('workflow');
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $idArr = explode(',', $ids);
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'",$userid)->find();
        $model->startTrans();
        try{
            foreach($idArr as $id){
                $data['td_confirmtime'] = $time;
                $data['td_confirmmanid'] = $userid;
                $data['td_confirmman'] = $username['user_realusername'];
                $data['td_flowstatus'] = '已确认';
                $model->where("td_id = '%s'",$id)->save($data);
                $works = D('Yuan')->GetWorkFlow('确认','院待办事项',$id,'');
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1,'确认成功'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'确认失败'));
        }

    }

    /*
     * 调整
     * */
    public function adjust(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $id = trim(I('get.id'));
        $model = M('wytodoinfo');
        $list = $model->field('td_id,td_modifyfinishdate,td_modifyresult')->where("td_id = '%s'",$id)->find();
        $this->assign('data',$list);
        $this->display();
    }

    public function adSubmit(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $data = I('post.');
        $model = M('wytodoinfo');
        $work = M('workflow');
        $userModel = M('sysuser');
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'",$userid)->find();
        $model->startTrans();
        try{
            $data['td_modifytime'] = $time;
            $data['td_modifymanid'] = $userid;
            $data['td_modifyman'] = $username['user_realusername'];
            $model->where("td_id = '%s'",$data['td_id'])->save($data);
            $time =  $model->where("td_id = '%s'",$data['td_id'])->find();
            $content = '原定计划完成时间'.$time['td_planfinishdate'].'，调整后预计完成时间'.$data['td_modifyfinishdate'];
            $works = D('Yuan')->GetWorkFlow('调整','院待办事项',$data['td_id'],$content);
            $work->add($works);
            $model->commit();
            exit(makeStandResult(1,'调整成功'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'调整失败'));
        }
    }

    /**
     * 院级待办集成页面
     */
    public function frame(){
        $powers = D('RefinePower')->getViewPowers('YuanRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display();
    }

    public function indexNopower(){
        $id = trim(I('GET.id'));
        $this->assign('id',$id);
        $this->display();
    }

    public function addNoPower(){
        $id = trim(I('get.id'));
        $mtId = trim(I('get.mt_id'));
        $meet = M('meetinfo');
        $model = M('wytodoinfo');
        if(!empty($id)){
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data=$model->where("td_id='%s'",$id)->find();
        }
        $orgCs = M('v_org_kyb');
        $orgDw = M('v_org_unit');
        $dept = $orgCs->field('org_id,org_name')->select();
        $deptDw = $orgDw->field('org_id,org_name')->select();
        $list = D('Admin/Dictionary')->getDicValueByName(['流程状态','完成形式']);
        $ling = D('Admin/User')->getUserRole(['科研部部领导','科研部员工']);
        $meets = $meet->field('mt_id,mt_name')->select();
        $addMeet = $meet->field('mt_id,mt_name')->where("mt_id = '%s'",$mtId)->find();
        $this->assign('mtname',$addMeet);
        $this->assign('data',$data);
        $this->assign('zr',$ling['科研部员工']);
        $this->assign('ling',$ling['科研部部领导']);
        $this->assign('meets',$meets);
        $this->assign('dept',$dept);
        $this->assign('deptDw',$deptDw);
        $this->assign('state',$list['流程状态']);
        $this->assign('finish',$list['完成形式']);
        $this->display();
    }


    public function back(){
        $id = trim(I('get.id'));
        $this->assign('td_id',$id);
        $this->display();
    }

    public function BackSubmit(){
        $model = M('wytodoinfo');
        $work = M('workflow');
        $data = I('post.');
        $idArr = explode(',',$data['td_id']);
        $model->startTrans();
        try{
            foreach($idArr as $id){
                $list['td_isback'] = '是';
                $list['td_backcomment'] = $data['td_backcomment'];
                $list['td_finishtime'] = '';
                $list['td_confirmtime'] = '';
                $list['td_flowstatus'] = '被退回';
                $model->where("td_id = '%s'",$id)->save($list);
                $works = D('Yuan')->GetWorkFlow('退回','院待办事项',$id,$data['td_backcomment']);
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1,'成功退回'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'退会失败'));
        }
    }

    public function statistics(){
        $this->display();
    }


}