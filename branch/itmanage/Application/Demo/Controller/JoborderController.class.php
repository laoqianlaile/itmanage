<?php
namespace Demo\Controller;
use Think\Controller;
use Think\Model;

//use Think\Upload;
class JoborderController extends BaseController
{

    public function index()
    {
        $this->display();
    }

    public function assigntask()
    {
//        $ywperson = M('ywperson')->where("yw_type='一线'")->select();
        $ywperson = M('ywperson')->select();
        $this->assign('ds_chuliperson',$ywperson);
        $this->display();
    }

    public function txzjob()
    {
        $this->display();
    }

    public function txzgongdan(){
        $id = I('get.id');
        if ($id) {
            $Model = M('tongxinzhanjob');
            $data = $Model->where("txz_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }}
        $this->display();
    }

    public function add(){
        $this->display('txzgongdan_add');
    }

    public function txzgongdan_edit(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M('tongxinzhanjob');
            $data = $Model->where("txz_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }}
        $this->display('txzgongdan_edit');
    }

    public function txzprint()
    {
        $id = I('get.id');
        if ($id) {
            $Model = M('tongxinzhanjob');
            $data = $Model->where("txz_atpid='%s'", array($id))->find();
            $data['txz_print'] = '1';
            $Model->where("txz_atpid='%s'", array($id))->save($data);
            //记录日志
            $this->recordLog('print', 'txz', '打印通信站派工单:'.$data['txz_tongxinid'],'tongxinzhanjob',$data['txz_tongxinid']);
            if ($data) {
                $this->assign('data', $data);
            }}
        $this->display();
    }

    public function getuserdept() //派工单中用户查询
    {
        $Model = M('person');
        $username = $_POST['username'];
        $orgid = $Model->where("username='%s'", $username)->getField('orgid');
        $depart = M('depart')->where("id='%s'", $orgid)->getField('fullname');
        if ($depart==null){$depart = '未查找到此用户，请确认申请人名称是否正确';}
        $this->ajaxReturn($depart);
    }

    public function getuserdept_z() //通信站派工单中用户查询
    {
        $Model = M('person');
        $username = $_POST['username'];
        $orgid = $Model->where("username='%s'", $username)->getField('orgid');
        $pid=  M('depart')->where("id='%s'", $orgid)->getField('pid');
        $office = M('depart')->where("id='%s'", $orgid)->getField('name');
        $depart = M('depart')->where("id='%s'", $pid)->getField('name');
        if ($depart==null){$depart = '未查找到此用户，请确认申请人名称是否正确';$office = '未查找到此用户，请确认申请人名称是否正确';}
        $arr = array();
        array_push($arr, $depart);
        array_push($arr, $office);
        $this->ajaxReturn($arr);
    }

    public function getworkid()
    {
        $Model = M('work');
        $role = $_POST['role'];
        $idlist = array();
        $tmpdate = date("Ymd", time());
        $arrayid = $Model->where("rw_workid like '%".$role."-".$tmpdate."%'")->field('rw_workid')->select();
        if ($arrayid == null ||count($arrayid)==0) {
            $workid = 'CC-' . $role . '-' . $tmpdate . '-001';
        } else {
            foreach ($arrayid as $key => $value) {
                array_push($idlist,$value['rw_workid']);
            }
            rsort($idlist);
            $tmp = explode("-", $idlist[0]);
            $num = sprintf("%03d",($tmp[3] +1));
            $workid = 'CC-' . $role . '-' . $tmpdate . '-' . $num;
        }
        $this->ajaxReturn($workid);
    }

    public function submit()
    {
        $data = I('post.');
        $problemtype ='';
        $type = I('post.search_u_role');
        foreach($data['checkbox'] as $i)
        {$problemtype .=$i.',';}
        $Model = M('work');
        $data       = [];
        $detailData = [];
        try{
                $workid                   = I('post.workid');
                $data['rw_atpid']         = $this->makeGuid();
                $data['rw_type']          = I('post.search_u_role');
                $data['rw_name']          = $this->getrealname(I('post.username'));
                $data['rw_depart']        = I('post.depart');
                $data['rw_account']       = I('post.username');
                $data['rw_phone']         = I('post.phone');
                $data['rw_problemtype']   = $problemtype;
                $data['rw_problemdes']    = I('post.problems');
                $data['rw_process']       = I('post.process');
                $data['rw_atpcreateuser'] = I('session.username'); //待添加session失效时的措施
                $data['rw_atpcreatetime'] = date('Y-m-d H:i:s', time());
                $data['rw_workid']        = $workid;

                $detailData['tl_atpid']     = $this->makeGuid();
                $detailData['tl_taskid']    = $data['rw_workid'];
                $detailData['tl_type']      = '';
                $detailData['tl_tasktype']  = '';
                $detailData['tl_person']    = $data['rw_atpcreateuser'];
                $detailData['tl_solvetime'] = date('Y-m-d H:i:s', time());
                switch($type){
                    case 'BZ':
                        $process = '现场保障';break;
                    case 'YW':
                        $process = '业务';break;
                    case 'ZX':
                        $process = '单步';break;
                }

                if($type =="YW" || $type =="BZ"){
                    $detailData['tl_process']   = '建立'.$process.'任务单';
                    $data['rw_status']          = '0';//0表示待分配
                }else if($type =="ZX"){
                    $detailData['tl_process']   = '完成'.$process.'任务单';
                    $data['rw_status']          = '2';//2表示已完成
                    $data['rw_receipttime']     = date('Y-m-d H:i:s', time());
                    $data['rw_dealtime']        = date('Y-m-d H:i:s', time());
                    $data['rw_count']           = '1';
                    $data['rw_gdtype']          = '单步';
                }
                $Model->add($data);
                M('taskdetail')->add($detailData); //处理过程信息
                $contents = "";
                foreach($data as $key=>$val){
                    if(!empty($val)) $contents .= $key."：".$val."；";
                }
                $this->recordLog("add",'biaodan',$contents,'work',$workid);
                $this->ajaxReturn("success");
        }
        catch(\Exception $e)
        {
            echo $e;
            $this->ajaxReturn("error");
        }
    }

    public function getdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select *  from it_work du where (du.rw_type ='YW' or du.rw_type ='BZ')";
        $sql_count="
                select
                    count(1) c
                from it_work du where (du.rw_type ='YW' or du.rw_type ='BZ')";
        if ("" != $queryparam['workid']){
            $searchcontent = trim($queryparam['workid']);
            $sql_select = $this->buildSql($sql_select,"du.rw_workid like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.rw_workid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['taskradio']){
            $searchcontent = trim($queryparam['taskradio']);
            $sql_select = $this->buildSql($sql_select,"du.rw_status ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.rw_status ='".$searchcontent."'");
        }

        if ("" != $queryparam['person']){
            $searchcontent = trim($queryparam['person']);
            $sql_select = $this->buildSql($sql_select,"du.rw_name ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.rw_name ='".$searchcontent."'");
        }
        if ("" != $queryparam['depart']){
            $searchcontent = trim($queryparam['depart']);
            $sql_select = $this->buildSql($sql_select,"du.rw_depart ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.rw_depart ='".$searchcontent."'");
        }
        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'statusprint') $queryparam['sort'] = 't_status';
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        }
        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value){
            switch($value['rw_status']){
                case "0":
                    $value['statusprint'] ="待分派";
                    break;
                case "1":
                    $value['statusprint'] ="已分派";
                    break;

            }
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    } //assigntask调用

    public function assignwork(){
        $id     = I('post.id');
        $person = I('post.person');
        $data   = M('work')->where("rw_atpid='%s'",$id)->find();
        if($data <> null){
            $data['rw_status']        = '1'; //工单状态置为已分派
            $data['rw_receipttime']   = date('Y-m-d H:i:s', time()); //任务单接收时间
            $data['rw_count']         += 1; //处理次数
            $data['rw_atpcreateuser'] = $person; //处理人
            $type = '';
            switch($data['rw_type']){
                case 'BZ':
                    $type = '现场保障';break;
                case 'YW':
                    $type = '业务';break;
                case 'ZX':
                    $type = '单步';break;
            }
            $personfrom           = I('session.username');
            $personfrom         = $this->getrealname($personfrom);
            $personto           = $this->getrealname($person);
            $data['rw_process'] = '建立'.$type.'工单,'.$personfrom."分派给".$personto; //处理情况
            $data2 =$this->createtask($data,$person,$data['rw_process']);
            $taskData   = $data2[0];
            $detailData = $data2[1];

            M()->startTrans();
            try{
                M('work')->where("rw_atpid='%s'",$id)->save($data);
                $this->recordLog("update",'biaodan',"工单状态：未处理-处理中；处理人：".$person."；处理次数：".$data['rw_count']."；",'work',$data['rw_workid']);
                M('task')->add($taskData);
                $content = '';
                foreach($taskData as $key=>$val){
                    if(!empty($val)) $content .= $key."：".$val."；";
                }
                $this->recordLog("add",'gongdan',$content,'task',$taskData['t_rwid']);
                M('taskdetail')->add($detailData);
                M()->commit();
                $this->ajaxReturn("success");
            }
            catch(\Exception $e){
                M()->rollback();
                $this->ajaxReturn("error");
            }
        }else{
            $this->ajaxReturn("error");
        }
    }

    public function getwork(){
        $Model = M('work');
        $id= $_POST['id'];
        try{
            $sbtypelist =$Model->where("rw_atpid='%s'",$id)->find();
        }
        catch(\Exception $e)
        {
            echo $e;
        }
        echo json_encode($sbtypelist);
    }

    public function createtask($data,$person,$process){
        $taskData   = [];
        $detailData = [];

        $detailData['tl_atpid']              = $this->makeGuid();
        $detailData['tl_taskid']             = $data['rw_workid'];
        $detailData['tl_type']               = '1';
        $detailData['tl_tasktype']           = '一线';
        $detailData['tl_person']             = $person;
        $detailData['tl_solvetime']          = date('Y-m-d H:i:s', time());
        $detailData['tl_process']            = $process;

        $taskData['t_atpid']                 = $this->makeGuid();
        $taskData['t_tasktype']              ='一线';
        $taskData['t_name']                  = $data['rw_name'];
        $taskData['t_nameid']                = $data['rw_account'];
        $taskData['t_atplastmodifyuser']     = $person;
        $taskData['t_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
        if(!empty($data['rw_name'])){
            $dept = $this->getuserdept_new($data['rw_atpcreateuser']);
            if(!empty($dept)){
                $taskData['t_depart'] = $dept[0];
                $taskData['t_office'] = $dept[1];
            }
        }
        if(empty($taskData['t_office'])){
            $taskData['t_depart'] = '';
            $taskData['t_office'] = '';
        }
        $taskData['t_phone']          = $data['rw_phone'];
        $taskData['t_rwid']           = $data['rw_workid'];
        $taskData['t_person']         = $person;
        $taskData['t_description']    = $data['rw_problemdes'];
        $taskData['t_arrivetime']     = date('Y-m-d H:i:s', time());
        $taskData['t_status']         = "0";
        $taskData['t_taskid']         = $data['rw_workid'];
        $taskData['t_problemtype']    = $data['rw_problemtype'];
        $taskData['t_rwtype']         = $data['rw_type'];
        $taskData['t_createuserid']   = I('session.username');
        $taskData['t_createusername'] = I('session.realusername');
        return [$taskData,$detailData];

    }

    public function getrealname($username){
        $realusername =M('person')->where("username='%s'",$username)->getField('realusername');
        return $realusername;
    }

    public function gettxzdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="select *  from it_tongxinzhanjob du";
        $sql_count="select  count(1) c from it_tongxinzhanjob du";
        if ("" != $queryparam['tongxinid']){
            $searchcontent = trim($queryparam['tongxinid']);
            $sql_select = $this->buildSql($sql_select,"du.txz_tongxinid like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.txz_tongxinid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['taskid']){
            $searchcontent = trim($queryparam['taskid']);
            $sql_select = $this->buildSql($sql_select,"du.txz_taskid like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.txz_taskid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['username']){
            $searchcontent = trim($queryparam['username']);
            $sql_select = $this->buildSql($sql_select,"du.txz_user = '".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.txz_user = '".$searchcontent."'");
        }
        if ("" != $queryparam['workid']){
            $searchcontent = trim($queryparam['workid']);
            $sql_select = $this->buildSql($sql_select,"du.txz_workid like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.txz_workid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['status']){
            $searchcontent = trim($queryparam['status']);
            $sql_select = $this->buildSql($sql_select,"du.txz_status ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.txz_status ='".$searchcontent."'");
        }
        if ("" != $queryparam['account']){
            $searchcontent = trim($queryparam['account']);
            $sql_select = $this->buildSql($sql_select,"du.txz_account like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.txz_account like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['submittime']){
            $searchcontent = trim($queryparam['submittime']);
            $sql_select = $this->buildSql($sql_select,"du.txz_submittime >= '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.txz_submittime >= '%".$searchcontent."%'");
        }
        if ("" != $queryparam['completetime']){
            $searchcontent = trim($queryparam['completetime']);
            $sql_select = $this->buildSql($sql_select,"du.txz_completetime <= '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.txz_completetime <= '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.txz_tongxinid  desc  ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function gettongxinid()
    {
        $Model = M('tongxinzhanjob');
        $tmpdate = date("Y", time());
        $arrayid = $Model->field("txz_tongxinid")->order("txz_tongxinid desc")->where("txz_tongxinid like '".$tmpdate."%'")->select();
        if ($arrayid == null ||count($arrayid)==0) {
            $tongxinid = $tmpdate . '0001';
        } else {
            $i=$arrayid[0]['txz_tongxinid'];
            $tongxinid=intval($i)+1;
        }
        Return($tongxinid);
    }

    public function txzsubmit(){
        $data  = I('post.');
        $atpid = I('post.txz_atpid');
        if($data['txz_sign'] != 'on') {//on表示为从"编辑页面"调用txzsubmit（）
            $txz_tongxinid = $this->gettongxinid();
        }else{
            $txz_tongxinid = I('post.txz_tongxinid');
        }
        $txz_type = '';
        foreach($data['checkbox'] as $i){
            $txz_type .= $i.',';
        }
        unset($data['checkbox']);
        $Model = M('tongxinzhanjob');
        try{
            if($atpid == null) {
                $data['txz_atpid']        = $this->makeGuid();
                $data['txz_account']      = session('username');
                $data['txz_status']       = '未完成';
                $data['txz_accountname']  = session('realusername');
                $data['txz_submittime']   = date('Y-m-d H:i:s', time());
                $data['txz_type']         = $txz_type;
                $data['txz_tongxinid']    = $txz_tongxinid;
                $data['txz_user']         = I('post.txz_user');
                $data['txz_username']     = $this->getrealname(I('post.txz_user'));
                $data['txz_depart']       = I('post.txz_depart');
                $data['txz_office']       = I('post.txz_office');
                $data['txz_address']      = I('post.txz_address');
                $data['txz_phone']        = I('post.txz_phone');
                $data['txz_xswitch']      = I('post.txz_xswitch');
                $data['txz_xport']        = I('post.txz_xport');
                $data['txz_detail']       = I('post.txz_detail');
                $data['txz_tswitch']      = I('post.txz_tswitch');
                $data['txz_tport']        = I('post.txz_tport');
                $data['txz_tresult']      = I('post.txz_tresult');
                $data['txz_taccountname'] = I('post.txz_taccountname');
                $data['txz_ps']           = I('post.txz_ps');
                $data['txz_meterial']     = I('post.txz_meterial');
                $Model->add($data);
                $content = '';
                foreach($data as $key=>$val){
                    if(!empty($val)) $content .= $key."：".$val."；";
                }
                $this->recordLog('add', 'txz', "新增通信站派工单，".$content, 'tongxinzhanjob',$txz_tongxinid);
                $this->ajaxReturn("success");}
            else{
                if($data['txz_sign'] != 'on'){ //on表示为编辑页面调用此方法，否则为提交页面调用
                    $data['txz_status']       = '已完成';
                    $data['txz_submitname']   = session('realusername'); //提交人
                    $data['txz_submitid']     = session('username');
                    $txz_completetime         = I('post.txz_completetime');
                    $data['txz_completetime'] = empty($txz_completetime)?date('Y-m-d H:i:s', time()):$txz_completetime;  //完成时间
                }
                else{
                    $data['txz_status']       = '未完成';
                }
                $data['txz_type']         = $txz_type;
                $data['txz_user']         = I('post.txz_user');
                $data['txz_username']     = $this->getrealname(I('post.txz_user'));
                $data['txz_depart']       = I('post.txz_depart');
                $data['txz_office']       = I('post.txz_office');
                $data['txz_address']      = I('post.txz_address');
                $data['txz_phone']        = I('post.txz_phone');
                $data['txz_xswitch']      = I('post.txz_xswitch');
                $data['txz_xport']        = I('post.txz_xport');
                $data['txz_detail']       = I('post.txz_detail');
                $data['txz_tswitch']      = I('post.txz_tswitch');
                $data['txz_tport']        = I('post.txz_tport');
                $data['txz_tresult']      = I('post.txz_tresult');
                $data['txz_taccountname'] = I('post.txz_taccountname');
                $data['txz_ps']           = I('post.txz_ps');
                $data['txz_meterial']     = I('post.txz_meterial');
                $Model->where("txz_atpid='%s'",array($atpid))->save($data);
                $data = $Model->where("txz_atpid='%s'", array($atpid))->find();
                if($data['txz_sign'] != 'on'){
                    $this->recordLog('update', 'txz', "提交通信站派工单，用户名：".$data['txz_user']."；部门：".$data['txz_depart']."；科室：".$data['txz_office']."；地址：".$data['txz_address']."；电话：".$data['txz_phone']."；交换机地址（信息中心）：".$data['txz_xswitch']."；交换机端口（信息中心）：".$data['txz_xport']."；任务内容（信息中心）：".$data['txz_detail']."；交换机地址（通信站）：".$data['txz_tswitch']."；交换机端口（通信站）：".$data['txz_tport']."；通信站意见：".$data['txz_tresult']."；实施人：".$data['txz_taccountname']."；用料情况：".$data['txz_meterial']."；完成时间：".$data['txz_completetime']."；备注：".$data['txz_ps'],$data['txz_tongxinid']);
                }
                else{
                    $this->recordLog('update', 'txz', "修改通信站派工单", 'tongxinzhanjob',$txz_tongxinid);
                }
                $this->ajaxReturn("success");
            }
        }
        catch(\Exception $e)
        {
            echo $e;
        }}

    //生成打印单
    public function piliangdayin(){
        try {
            $ids = I('get.ids');
            $array = explode(',', $ids);
            $dataarray=array();
            $Model = M('tongxinzhanjob');
            foreach($array as $a){
                $da = $Model->where("txz_atpid='%s'",$a)->find();
                $da['txz_print'] = '1';
                $Model->where("txz_atpid='%s'", array($a))->save($da);
                //记录日志
                $this->recordLog('print', 'txz', '批量打印通信站派工单:'.$da['txz_tongxinid'],'tongxinzhanjob',$da['txz_tongxinid']);
                array_push($dataarray,$da);
            }
            $this->assign("formlist",$dataarray);
            $this->display("tprint");
        }
        catch (Exception $e){
            echo $e;
        }
    }

    public function tprint()
    {
        $this->display();
    }
}

