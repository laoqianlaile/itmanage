<?php
namespace Demo\Controller;
use Think\Controller;
class MonthController extends BaseController {
    public function index(){
        $orgModel = M('v_org_kyb');
        $month = date('m')-1;
        $first = date('Y').'-'.'0'.$month.'-'.'26';
        $end = date('Y').'-'.date('m').'-'.'25';
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('first',$first);
        $this->assign('end',$end);
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('zr',$zeren);
        $this->display();
    }

    public function indexBc(){
        $userModel = M('v_sysuser');
        $userId = session('user_id');
        $orgName = $userModel->where("user_id = '%s' and org_type = '内部部门'",$userId)->find();
        $orgModel = M('v_org_kyb');
        $month = date('m')-1;
        $first = date('Y').'-'.'0'.$month.'-'.'26';
        $end = date('Y').'-'.date('m').'-'.'25';
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('first',$first);
        $this->assign('end',$end);
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('zr',$zeren);
        $this->assign('orgName',$orgName['org_name']);
        $this->display();
    }

    public function xiaFa(){
        $userid = session('user_id');
        $month = date('m')-1;
        $first = date('Y').'-'.'0'.$month.'-'.'26';
        $end = date('Y').'-'.date('m').'-'.'25';
        $userModel = M('v_sysuser');
        $orgName = $userModel->where("user_id = '%s' and org_type = '内部部门'",$userid)->find();
        $orgModel = M('v_org_kyb');
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('first',$first);
        $this->assign('end',$end);
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('zr',$zeren);
        $this->assign('userid',$userid);
        $this->display();
    }

    /*
     * 访问提交页面
     * */
    public function tijiao(){
        $userid = session('user_id');
        $month = date('m')-1;
        $first = date('Y').'-'.'0'.$month.'-'.'26';
        $end = date('Y').'-'.date('m').'-'.'25';
        $userModel = M('v_sysuser');
        $orgName = $userModel->where("user_id = '%s' and org_type = '内部部门'",$userid)->find();
        $orgModel = M('v_org_kyb');
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('first',$first);
        $this->assign('end',$end);
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('zr',$zeren);
        $this->assign('userid',$userid);
        $this->display();
    }

    /*
     * 访问确认页面
     * */
    public function confirm(){
        $userid = session('user_id');
//        $userid = 'T4EB0A60A9A994EBFBD4588DB';
        $month = date('m')-1;
        $first = date('Y').'-'.'0'.$month.'-'.'26';
        $end = date('Y').'-'.date('m').'-'.'25';
        $orgModel = M('v_org_kyb');
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('first',$first);
        $this->assign('end',$end);
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('zr',$zeren);
        $this->assign('userid',$userid);
        $this->display();
    }

    /*
     * 访问查询页面
     * */
    public function rogatory(){
        $orgModel = M('v_org_kyb');
        $month = date('m')-1;
        $first = date('Y').'-'.'0'.$month.'-'.'26';
        $end = date('Y').'-'.date('m').'-'.'25';
        $list = D('Admin/Dictionary')->getDicValueByName('流程状态');
        $dept = $orgModel->field('org_id,org_name')->select();
        $zeren = D('Admin/User')->getUserRole('科研部员工');
        $this->assign('first',$first);
        $this->assign('end',$end);
        $this->assign('state',$list);
        $this->assign('dept',$dept);
        $this->assign('zr',$zeren);
        $this->display();
    }

    /*
     * 取消
     * */
    public function nullify(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $model = M('monthtodoinfo');
        $work = M('workflow');
        $userModel = M('sysuser');
        $idArr = explode(',', $ids);
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'", $userid)->find();
        $model->startTrans();
        try{
            foreach($idArr as $id){
                $data['ytd_canceltime'] = $time;
                $data['ytd_cancelmanid'] = $userid;
                $data['ytd_cancelman'] = $username['user_realusername'];
                $data['ytd_iscancel'] = '是';
                $data['ytd_flowstatus'] = '已取消';
                $list = $model->where("ytd_id = '%s'",$id)->find();
                if($list['ytd_ispublish']){
                    exit(makeStandResult(-1, '只能取消已下发的数据！'));
                }
                $model->where("ytd_id = '%s'",$id)->save($data);
                $works = D('Yuan')->GetWorkFlow('取消','月计划',$id,'');
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1, '取消成功'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1, '取消失败'));
        }
    }

    /*
     * 获取月计划数据
     * */
    public function getData($isExport = false){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        if($isExport === true){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $model=M('v_monthtodoinfo');
        $fileModel = M('filerelation');
        $workModel = M('workflow');
        $ispu = trim($queryParam['ispu']);
        $stateTj = trim($queryParam['stateTj']);
        $orgName = trim($queryParam['orgName']);
        $userid = trim($queryParam['user_id']);
        $ytdName = trim($queryParam['ytd_name']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $kybChargeManId = trim($queryParam['kybchargemanid']);
        $buid = trim($queryParam['buid']);
        $dept = trim($queryParam['dept']);
        $ytdStatus = trim($queryParam['ytd_status']);
        $istd = trim($queryParam['istd']);
        $isip = trim($queryParam['isip']);
        $where=array();
        if(!empty($stateTj)){
            $stateTj = explode(',',$stateTj);
            $where['ytd_flowstatus'] = array('in' ,$stateTj);
        }
        if(!empty($ytdName))$where['ytd_name'] = array('like' ,"%$ytdName%");
        if(!empty($start)){
            $where[0]['_logic'] = 'and';
            $where[0][0]['ytd_planfinishdate'] = array('egt' ,$start);
        }
        if(!empty($end)){
            $where[0]['_logic'] = 'and';
            $where[0][1]['ytd_planfinishdate'] = array('elt' ,$end);
        }
        if(!empty($kybChargeManName))$where['ytd_kybchargeman'] = array('eq' ,$kybChargeManName);
        if(!empty($kybChargeManId))$where['ytd_kybchargemanid'] = array('eq' ,$kybChargeManId);
        if(!empty($dept))$where['ytd_kybunit'] = array('eq' ,$dept);
        if(!empty($orgName))$where['ytd_kybunit'] = array('eq' ,$orgName);
        if(!empty($ytdStatus))$where['status'] = array('eq' ,$ytdStatus);
        if(!empty($userid))$where['ytd_kybleaderid'] = array('eq' ,$userid);
        if(!empty($ispu))$where['ytd_ispublish'] = array('eq' ,$ispu);
        if(!empty($buid))$where['ytd_kybleaderid'] = array('eq' ,$buid);
        if(!empty($istd)){
            if($istd == '是'){
                $where["ytd_iswytodo"] = array('eq' ,$istd);
            }else{
                $where["ytd_iswytodo"] = array('exp' ,'is null');
            }
        }
        if(!empty($isip)){
            if($isip == '是'){
                $where["ytd_isip"] = array('eq' ,$isip);
            }else{
                $where["ytd_isip"] = array('exp' ,'is null');
            }
        }
        $count = $model->where($where)->count();
        $field = "ytd_worktype,ytd_name,ytd_content,to_char(ytd_planfinishdate,'YYYY-mm-dd') ytd_planfinishdate,to_char(ytd_modifyfinishdate,'YYYY-mm-dd') ytd_modifyfinishdate,ytd_finishtype,ytd_kybchargeman,ytd_kybunitleader,ytd_kybunit,ytd_kybleader,ytd_confirmtime,cast(status as varchar(100)) status,ytd_dealmethod,ytd_iswytodo,ytd_isip,ytd_ispublish";
        $obj = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");
        if($isExport === false){
            $field .= ",ytd_id,ytd_iscancel,ytd_backcomment,ytd_flowstatus";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach ($data as $key => $val) {
                $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '月计划'", $val['ytd_id'])->count();
                $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '月计划'",$val['ytd_id'])->order('wf_time')->select();
                foreach($work as $k =>$v){
                    if(!empty($v['wf_content'])){
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                    }else{
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                    }
                }
                $data[$key]['workflow'] = $work;
                if(mb_strlen($val['ytd_name'], 'utf8') >25){
                    $val['ytd_name'] = mb_substr($val['ytd_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['ytd_name'] = $val['ytd_name'];
                }
                $data[$key]['ytd_name'] = "<div data-toggle='tooltip'title='{$data[$key]['ytd_name']}'>{$val['ytd_name']}</div>";
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }else{
            $data = $obj->field($field)->select();
            $header = ['工作类别', '工作事项','行动项目', '完成时间','预计完成时间', '完成形式',  '责任人', '主管处领导','责任处室', '主管部领导','实际完成时间', '完成情况','处置措施', '是否属于院级待办事项','是否属于目标管理事项','是否下发'];
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
     * 获取月计划数据
     * */
    public function getDataComfirm(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $queryParam = I('put.');
        $model=M('v_monthtodoinfo');
        $fileModel = M('filerelation');
        $ispu = trim($queryParam['ispu']);
        $workModel = M('workflow');
        $state = trim($queryParam['state']);
        $userid = trim($queryParam['user_id']);
        $ytdName = trim($queryParam['ytd_name']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $dept = trim($queryParam['dept']);
        $ytdStatus = trim($queryParam['ytd_status']);
        $istd = trim($queryParam['istd']);
        $isip = trim($queryParam['isip']);
        $where=array();
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['科研部部领导'];
        $role2 = ['科研部处室领导'];
        $role3 = ['科研部员工'];
        if(!empty(array_intersect($role1,$roles))){
            if(!empty($userid))$where['ytd_kybleaderid'] = array('eq' ,$userid);
        }else if(!empty(array_intersect($role2,$roles)) && !empty(array_intersect($role3,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where[1]['_logic'] = 'or';
            $where[1][0]['ytd_kybunit'] = array('eq' ,$orgName['org_name']);
            $where[1][1]['ytd_kybchargemanid'] = array('eq' ,$userid);

        }else if(!empty(array_intersect($role2,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where['ytd_kybunit'] = array('eq' ,$orgName['org_name']);
        }else if(!empty(array_intersect($role3,$roles))){
            if(!empty($userid))$where['ytd_kybchargemanid'] = array('eq' ,$userid);
        }else{
            $where['ytd_kybunit'] = '无效组织';
        }
        if (!empty($state)) {
            $states = explode(',', $state);
            $where['ytd_flowstatus'] = array('in', $states);
        }
        if(!empty($ytdName))$where['ytd_name'] = array('like' ,"%$ytdName%");
        if(!empty($start)){
            $where[0]['_logic'] = 'and';
            $where[0][0]['ytd_planfinishdate'] = array('egt' ,$start);
        }
        if(!empty($end)){
            $where[0]['_logic'] = 'and';
            $where[0][1]['ytd_planfinishdate'] = array('elt' ,$end);
        }
        if(!empty($kybChargeManName))$where['ytd_kybchargeman'] = array('eq' ,$kybChargeManName);
        if(!empty($ispu))$where['ytd_ispublish'] = array('eq' ,$ispu);
        if(!empty($dept))$where['ytd_kybunit'] = array('eq' ,$dept);
        if(!empty($ytdStatus))$where['status'] = array('eq' ,$ytdStatus);
        if(!empty($istd)){
            if($istd == '是'){
                $where["ytd_iswytodo"] = array('eq' ,$istd);
            }else{
                $where["ytd_iswytodo"] = array('exp' ,'is null');
            }
        }
        if(!empty($isip)){
            if($isip == '是'){
                $where["ytd_isip"] = array('eq' ,$isip);
            }else{
                $where["ytd_isip"] = array('exp' ,'is null');
            }
        }
        $count = $model->where($where)->count();
        $data = $model->field("ytd_id,ytd_worktype,ytd_name,ytd_content,to_char(ytd_planfinishdate,'YYYY-mm-dd') ytd_planfinishdate,to_char(ytd_modifyfinishdate,'YYYY-mm-dd') ytd_modifyfinishdate,ytd_finishtype,ytd_kybchargeman,ytd_kybunitleader,ytd_kybunit,ytd_kybleader,ytd_confirmtime,cast(status as varchar(100)) status,ytd_dealmethod,ytd_iswytodo,ytd_isip,ytd_ispublish")
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        foreach ($data as $key => $val) {
            $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '月计划'", $val['ytd_id'])->count();
            $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '月计划'",$val['ytd_id'])->order('wf_time')->select();
            foreach($work as $k =>$v){
                if(!empty($v['wf_content'])){
                    $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                }else{
                    $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                }
            }
            $data[$key]['workflow'] = $work;

            if(mb_strlen($val['ytd_name'], 'utf8') >25){
                $val['ytd_name'] = mb_substr($val['ytd_name'], 0, 25, 'utf8') .'...';
            }else{
                $val['ytd_name'] = $val['ytd_name'];
            }
            $data[$key]['ytd_name'] = "<div data-toggle='tooltip'title='{$data[$key]['ytd_name']}'>{$val['ytd_name']}</div>";
        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }
    /*
     * 获取月计划数据
     * */
    public function getDatas($isExport = false){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        if($isExport === true){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $where=array();
        $fileModel = M('filerelation');
        $rolesid = session('roleids');
        $roleModel = M('sysrole');
        $workModel = M('workflow');
        $roles = $roleModel ->field('role_name')-> where(['role_id'=> ['in', $rolesid]])->select();
        $roles = removeArrKey($roles, 'role_name');
        $role1 = ['院领导','科研部部领导','科研部综合处'];
        $role2 = ['科研部处室领导','科研部处室管理员'];
        $userid = session('user_id');
        if(!empty(array_intersect($role1,$roles))){

        }else if(!empty(array_intersect($role2,$roles))){
            $orgName = M('v_sysuser')->where("user_id = '%s'and org_type = '内部部门' ",$userid)->find();
            $where['ytd_kybunit'] = array('eq' ,$orgName['org_name']);
        }else{
            $where['ytd_kybunit'] = '无效组织';
        }
        $model=M('v_monthtodoinfo');
        $ispu = trim($queryParam['ispu']);
        $userid = trim($queryParam['user_id']);
        $ytdName = trim($queryParam['ytd_name']);
        $start = trim($queryParam['start']);
        $end = trim($queryParam['end']);
        $istd = trim($queryParam['istd']);
        $isip = trim($queryParam['isip']);
        $kybChargeManName = trim($queryParam['kybchargemanname']);
        $dept = trim($queryParam['dept']);
        $ytdStatus = trim($queryParam['ytd_status']);
        if(!empty($ytdName))$where['ytd_name'] = array('like' ,"%$ytdName%");
        if(!empty($start)){
            $where[0]['_logic'] = 'and';
            $where[0][0]['ytd_planfinishdate'] = array('egt' ,$start);
        }
        if(!empty($end)){
            $where[0]['_logic'] = 'and';
            $where[0][1]['ytd_planfinishdate'] = array('elt' ,$end);
        }
        if(!empty($kybChargeManName))$where['ytd_kybchargeman'] = array('eq' ,$kybChargeManName);
        if(!empty($ispu))$where['ytd_ispublish'] = array('eq' ,$ispu);
        if(!empty($dept))$where['ytd_kybunit'] = array('eq' ,$dept);
        if(!empty($ytdStatus))$where['status'] = array('eq' ,$ytdStatus);
        if(!empty($userid))$where['ytd_kybleaderid'] = array('eq' ,$userid);
        if(!empty($istd)){
            if($istd == '是'){
                $where["ytd_iswytodo"] = array('eq' ,$istd);
            }else{
                $where["ytd_iswytodo"] = array('exp' ,'is null');
            }
        }
        if(!empty($isip)){
            if($isip == '是'){
                $where["ytd_isip"] = array('eq' ,$isip);
            }else{
                $where["ytd_isip"] = array('exp' ,'is null');
            }
        }
        $count = $model->where($where)->count();
        $field = "ytd_worktype,ytd_name,ytd_content,to_char(ytd_planfinishdate,'YYYY-mm-dd') ytd_planfinishdate,to_char(ytd_modifyfinishdate,'YYYY-mm-dd') ytd_modifyfinishdate,ytd_finishtype,ytd_kybchargeman,ytd_kybunitleader,ytd_kybunit,ytd_kybleader,ytd_confirmtime,cast(status as varchar(100)) status,ytd_dealmethod,ytd_iswytodo,ytd_isip";
        $obj = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ");
        if($isExport === false){
            $field .= ",ytd_id,ytd_finishtime,ytd_finishman,ytd_confirmman,ytd_modifyman";
            $data = $obj->field($field)->limit($queryParam['offset'], $queryParam['limit'])->select();
            foreach ($data as $key => $val) {
                $data[$key]['material'] = $fileModel->where("fr_objid = '%s' and fr_objtype = '月计划'", $val['ytd_id'])->count();
                $work = $workModel->field("to_char(wf_time,'YYYY-mm-dd HH24:mi:ss') wf_time,wf_user,wf_action,wf_content")->where("wf_objid = '%s' and wf_objtype = '月计划'",$val['ytd_id'])->order('wf_time')->select();
                foreach($work as $k =>$v){
                    if(!empty($v['wf_content'])){
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'].','.$v['wf_content'];
                    }else{
                        $work[$k]['work'] = $v['wf_time'].'【'.$v['wf_user'].'】'.'已'.$v['wf_action'];
                    }
                }
                $data[$key]['workflow'] = $work;
                if(mb_strlen($val['ytd_name'], 'utf8') >25){
                    $val['ytd_name'] = mb_substr($val['ytd_name'], 0, 25, 'utf8') .'...';
                }else{
                    $val['ytd_name'] = $val['ytd_name'];
                }
                $data[$key]['ytd_name'] = "<div data-toggle='tooltip'title='{$data[$key]['ytd_name']}'>{$val['ytd_name']}</div>";
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }else{
            $data = $obj->field($field)->select();
            $header = ['工作类别', '工作事项','行动项目', '完成时间','预计完成时间', '完成形式',  '责任人', '主管处领导','责任处室', '主管部领导','实际完成时间', '完成情况','处置措施', '是否属于院级待办事项','是否属于目标管理事项'];
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
         * 月计划添加。修改
         * */
    public function add(){
        $id = trim(I('get.id'));
        $orgName = trim(I('get.orgName'));
        $this->assign('orgName',$orgName);
        $orgModel = M('v_org_kyb');
        $meetModel = M('meetinfo');
        $model = M('monthtodoinfo');
        if(!empty($id)){
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data=$model->where("ytd_id='%s'",$id)->find();
        }
        $list=D('Admin/Dictionary')->getDicValueByName(['流程状态','完成形式','是否']);
        $dept=$orgModel->field('org_id,org_name')->select();
        $ling=D('Admin/User')->getUserRole(['科研部部领导','科研部员工','科研部处室领导']);
        $meets=$meetModel->field('mt_id,mt_name')->select();
        $this->assign('data',$data);
        $this->assign('zr',$ling['科研部员工']);
        $this->assign('chu',$ling['科研部处室领导']);
        $this->assign('ling',$ling['科研部部领导']);
        $this->assign('meets',$meets);
        $this->assign('dept',$dept);
        $this->assign('is',$list['是否']);
        $this->assign('state',$list['流程状态']);
        $this->assign('finish',$list['完成形式']);
        $this->display();
    }

    /*
     * 月计划添加。修改提交
     * */
    public function submit(){
        $data=I('post.');
        $model = M('monthtodoinfo');
        $id = $data['ytd_id'];
        $work = M('workflow');
        $time = date('Y-m-d H:i:s');
        $userid = session('user_id');
        $userModel = M('sysuser');
        $orgModel = M('v_org_kyb');
        $kybunitName = $orgModel->field('org_name')->where("org_id = '%s'",$data['ytd_kybunitid'])->find();
        $kybLeaderName = $userModel->field('user_realusername')->where("user_id = '%s'",$data['ytd_kybleaderid'])->find();
        $kybChargemanName = $userModel->field('user_realusername')->where("user_id = '%s'",$data['ytd_kybchargemanid'])->find();
        $kybunitleaderName = $userModel->field('user_realusername')->where("user_id = '%s'",$data['ytd_kybunitleaderid'])->find();
        $data['ytd_kybunit'] = $kybunitName['org_name'];
        $data['ytd_kybleader'] = $kybLeaderName['user_realusername'];
        $data['ytd_kybchargeman'] = $kybChargemanName['user_realusername'];
        $data['ytd_kybunitleader'] = $kybunitleaderName['user_realusername'];
        $model->startTrans();
        try{
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            if(empty($id)){
                $data['ytd_createtime'] = $time;
                $data['ytd_id'] = makeGuid();
                $data['ytd_createuser'] = $userid;
                $model->add($data);
                $works = D('Yuan')->GetWorkFlow('创建','月计划',$data['ytd_id'],'');
                $work->add($works);
            }else{
                $data['ytd_lastmodifytime'] = $time;
                $data['ytd_lastmodifyuser'] = $userid;
                $before = $model->where("ytd_id = '%s'",$id)->find();
                $content = "";
                $content .=D('Yuan')->judge('MonthToDo',$before,$data);
                $works = D('Yuan')->GetWorkFlow('修改','月计划',$data['ytd_id'],$content);
                $work->add($works);
                $model->where("ytd_id = '%s'",$id)->save($data);
            }
            $model->commit();
            exit(makeStandResult(1,'编辑成功'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'编辑失败'));
        }
    }

    /*
     * 查询是否存在已下发的数据
     * */
    public function delSelect(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('monthtodoinfo');
        $res = $model-> where(['ytd_id'=> ['in', $id]])->select();
        $xiaFa =removeArrKey($res, 'ytd_ispublish');
        if(in_array('是',$xiaFa)){
            exit(makeStandResult(-1,'只能删除未下发的数据！'));
        }else{
            exit(makeStandResult(1,'可以删除！'));
        }
    }

    /*
   * 删除月计划
   */
    public function del(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('monthtodoinfo');
        $res = $model-> where(['ytd_id'=> ['in', $id]])->delete();
        if(empty($res)){
            exit(makeStandResult(-1,'删除失败'));
        }else{
            exit(makeStandResult(1,'删除成功'));
        }
    }

    /*
     * 提交页面提交
     * */
    public function tjSubmit()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:mi:ss'");
        $model = M('monthtodoinfo');
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
                $data['ytd_finishtime'] = $time;
                $data['ytd_finishmanid'] = $userid;
                $data['ytd_finishman'] = $username['user_realusername'];
                $data['ytd_flowstatus'] = '已提交';
                $data['ytd_isback'] = '否';
                $model->where("ytd_id = '%s'", $id)->save($data);
                $works = D('Yuan')->GetWorkFlow('提交','月计划',$id,'');
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1, '提交成功'));
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '提交失败'));
        }
    }

    /*
     * 提交页面提交
     * */
    public function xfSubmit()
    {
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:mi:ss'");
        $model = M('monthtodoinfo');
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
                $data['ytd_ispublish'] = '是';
                $model->where("ytd_id = '%s'", $id)->save($data);
                $works = D('Yuan')->GetWorkFlow('下发','月计划',$id,'');
                $work->add($works);
            }
            $model->commit();
            exit(makeStandResult(1, '下发成功'));
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '下发失败'));
        }
    }

    /*
    * 确认页面提交
    * */
    public function ConfirmSubmit(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $model = M('monthtodoinfo');
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
                $data['ytd_confirmtime'] = $time;
                $data['ytd_confirmmanid'] = $userid;
                $data['ytd_confirmman'] = $username['user_realusername'];
                $data['ytd_flowstatus'] = '已确认';
                $model->where("ytd_id = '%s'",$id)->save($data);
                $works = D('Yuan')->GetWorkFlow('确认','月计划',$id,'');
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
        $model = M('monthtodoinfo');
        $list = $model->field('ytd_id,ytd_modifyfinishdate,ytd_modifyresult')->where("ytd_id = '%s'",$id)->find();
        $this->assign('data',$list);
        $this->display();
    }

    public function adSubmit(){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $data = I('post.');
        $model = M('monthtodoinfo');
        $work = M('workflow');
        $userModel = M('sysuser');
        $time = date('Y-m-d');
        $userid = session('user_id');
        $username = $userModel->field('user_realusername')->where("user_id = '%s'",$userid)->find();
        $model->startTrans();
        try{
            $data['ytd_modifytime'] = $time;
            $data['ytd_modifymanid'] = $userid;
            $data['ytd_modifyman'] = $username['user_realusername'];
            $model->where("ytd_id = '%s'",$data['ytd_id'])->save($data);
            $time =  $model->where("ytd_id = '%s'",$data['ytd_id'])->find();
            $content = '原定计划完成时间'.$time['ytd_planfinishdate'].'，调整后预计完成时间'.$data['ytd_modifyfinishdate'];
            $works = D('Yuan')->GetWorkFlow('调整','月计划',$data['ytd_id'],$content);
            $work->add($works);
            $model->commit();
            exit(makeStandResult(1,'调整成功'));
        }catch(\Exception $e){
            $model->rollback();
            exit(makeStandResult(-1,'调整失败'));
        }
    }

    /**
     * 月计划待办集成页面
     */
    public function frame(){
        $powers = D('RefinePower')->getViewPowers('MonthRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display();
    }

    /*
    * 批量添加
    * */
    public function saveCopyTables(){
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
        $fields = [ 'ytd_worktype','ytd_name','ytd_content', 'ytd_planfinishdate','ytd_finishtype',  'ytd_kybchargemanid','ytd_kybunitleaderid', 'ytd_kybunitid', 'ytd_kybleaderid','ytd_dealmethod'];

        $orgModel = D('Org'); //初始化org model 查询部门id
        $userModel = D('Admin/User');; //初始化user model 查询用户id
        $dictionaryModel = D('Admin/Dictionary'); //初始化Dictionary model 查询字典
//        $meetModel = D('Meet'); //初始化meet model 查询会议id
        $model = M('monthtodoinfo');
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);
        foreach($data as $key=>$value) {
            $lineNum = $key + 1; //表格行号
            foreach($value as $k=>$v){
                $field = $fields[$k - $reduce];
                $fieldName = '';
                $deptNameField = '';
                $userNameField = '';
                switch($field){
                    case 'ytd_kybunitid':
                        $deptNameField = empty($deptNameField) ? 'ytd_kybunit' : $deptNameField;
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
                    case 'ytd_kybleaderid':
                        $fieldName = '主管部领导';
                        $userNameField = 'ytd_kybleader';
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealNameRole($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        }else if(count($userInfo) > 1){
                            $userInfo['user_id']='';
                            $userInfo['user_realusername'];
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'ytd_kybunitleaderid':
                        $fieldName = empty($fieldName) ? '主管处领导' : $fieldName;
                        $userNameField = empty($userNameField) ? 'ytd_kybunitleader' : $userNameField;
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealNameChu($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        }else if(count($userInfo) > 1){
                            $userInfo['user_id']='';
                            $userInfo['user_realusername'];
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'ytd_kybchargemanid':
                        $fieldName = empty($fieldName) ? '责任人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'ytd_kybchargeman' : $userNameField;
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealName($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        }else if(count($userInfo) > 1){
                            $userInfo['user_id']='';
                            $userInfo['user_realusername'];
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'ytd_planfinishdate':
                        $arr[$field] = date('Y-m-d', strtotime($v));
                        break;
                    case 'ytd_name':
                        $todoNames[] = $v;
                    default: $arr[$field] = $v;
                        break;
                }
            }
            $arr['ytd_createtime'] = $time;
            $arr['ytd_id'] = makeGuid();
            $arr['ytd_createuser'] = $loginUserId;
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


    public function saveCopyTablesBc(){
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
        $orgName = trim($receiveData['extraParam']);
        $fields = [ 'ytd_worktype','ytd_name','ytd_content', 'ytd_planfinishdate','ytd_finishtype',  'ytd_kybchargemanid','ytd_kybunitleaderid', 'ytd_kybleaderid','ytd_dealmethod'];
        $orgModel = D('Org'); //初始化org model 查询部门id
        $orgModel = D('Org'); //初始化org model 查询部门id
        $userModel = D('Admin/User');; //初始化user model 查询用户id
        $dictionaryModel = D('Admin/Dictionary'); //初始化Dictionary model 查询字典
//        $meetModel = D('Meet'); //初始化meet model 查询会议id
        $model = M('monthtodoinfo');
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);
        foreach($data as $key=>$value) {
            $lineNum = $key + 1; //表格行号
            foreach($value as $k=>$v){
                $field = $fields[$k - $reduce];
                $fieldName = '';
                $deptNameField = '';
                $userNameField = '';
                switch($field){
                    case 'ytd_kybleaderid':
                        $fieldName = '主管部领导';
                        $userNameField = 'ytd_kybleader';
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealNameRole($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        }else if(count($userInfo) > 1){
                            $userInfo['user_id']='';
                            $userInfo['user_realusername'];
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'ytd_kybunitleaderid':
                        $fieldName = empty($fieldName) ? '主管处领导' : $fieldName;
                        $userNameField = empty($userNameField) ? 'ytd_kybunitleader' : $userNameField;
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealNameChu($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        }else if(count($userInfo) > 1){
                            $userInfo['user_id']='';
                            $userInfo['user_realusername'];
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'ytd_kybchargemanid':
                        $fieldName = empty($fieldName) ? '责任人' : $fieldName;
                        $userNameField = empty($userNameField) ? 'ytd_kybchargeman' : $userNameField;
                        if(empty($v)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 为空<br>";
                            break;
                        }
                        $userInfo = $userModel->getUserInfoByRealName($v, 'user_id,user_realusername');
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到<br>";
                            break;
                        }else if(count($userInfo) > 1){
                            $userInfo['user_id']='';
                            $userInfo['user_realusername'];
                            break;
                        }else{
                            $userInfo=$userInfo[0];
                        }
                        $arr[$field] = $userInfo['user_id'];
                        $arr[$userNameField] = $userInfo['user_realusername'];
                        break;
                    case 'ytd_planfinishdate':
                        $arr[$field] = date('Y-m-d', strtotime($v));
                        break;
                    case 'ytd_name':
                        $todoNames[] = $v;
                    default: $arr[$field] = $v;
                        break;
                }
            }
            $arr['ytd_createtime'] = $time;
            $arr['ytd_id'] = makeGuid();
            $arr['ytd_createuser'] = $loginUserId;
            $initTables[] = $arr;
        }
        $model->startTrans();
        try{
            if(empty($error)){
                $successNum = 0;
                $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
                foreach($initTables as $value){
                    if(!empty($orgName)){
                        $unitId = $orgModel->getOrgId($orgName);
                        $value['ytd_kybunitid']=$unitId;
                        $value['ytd_kybunit']=$orgName;
                    }
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

    public function back()
    {
        $id = trim(I('get.id'));
        $this->assign('ytd_id', $id);
        $this->display();
    }

    public function BackSubmit()
    {
        $model = M('monthtodoinfo');
        $data = I('post.');
        $work = M('workflow');
        $idArr = explode(',',$data['ytd_id']);
        try{
            foreach($idArr as $id){
                $list['ytd_isback'] = '是';
                $list['ytd_backcomment'] = $data['ytd_backcomment'];
                $list['ytd_flowstatus'] = '被退回';
                $list['ytd_finishtime'] = '';
                $list['ytd_confirmtime'] = '';
                $model->where("ytd_id = '%s'",$id)->save($list);
                $works = D('Yuan')->GetWorkFlow('退回','月计划',$id,$data['ytd_backcomment']);
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