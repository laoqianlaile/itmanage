<?php
namespace Demo\Controller;
use Think\Controller;
class TasksolutionController extends BaseController {

    public function index(){
        $ywperson = M('ywperson')->where("yw_type='二线'")->select();
        $this->assign('ds_chuliperson',$ywperson);
        $this->assign('username',session('username'));
        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function detail(){
        $id = $_GET['id'];
        $this->assign('t_taskid',$id);
        $this->display();
    }
    public function bddetail(){
        $bdid =$_GET['id'];
        $this->assign("t_rwid",$bdid);
        $this->display();
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
//        print_r($queryparam);die;
        $taskradio= $queryparam['taskradio'];
        $username = session('username');
//        $username = "huxuhua";
        $Model = M();
        $sql_select="
                select t.* ,sovlnum from it_task t
                left join
                (select tl.tl_taskid,count(1) sovlnum  from it_taskdetail tl where tl.tl_process not like '审批表单流入建立工单：%' group by tl.tl_taskid) tl
                on t.t_taskid = tl.tl_taskid";
        $sql_count="
                select
                    count(1) c
                from it_task t";
        $sql_select = $sql_select." where (t.t_rwtype ='BD' or t.t_person ='".$username."')";
        $sql_count  = $sql_count." where (t.t_rwtype ='BD' or t.t_person ='".$username."')";
        if($taskradio !="")
        {
            $sql_select = $this->buildSql($sql_select, "t.t_status ='".$taskradio."'");
            $sql_count = $this->buildSql($sql_count,  "t.t_status ='".$taskradio."'");
        }
        if (null != $queryparam['t_nameid']) {
//            $t_name   = trim($queryparam['t_name']);
            $t_nameid = trim($queryparam['t_nameid']);
            $sql_select = $this->buildSql($sql_select, "t.t_nameid ='".$t_nameid."'");
//            $sql_select = $this->buildSql($sql_select, "(t.t_nameid ='".$t_nameid."' or t_name ='".$t_name."')");
            $sql_count = $this->buildSql($sql_count,  "t.t_nameid ='".$t_nameid."'");
//            $sql_count = $this->buildSql($sql_count,  "(t.t_nameid ='".$t_nameid."' or t_name ='".$t_name."')");
        }
        if (null != $queryparam['t_person']) {
            $t_person= $queryparam['t_person'];
            $sql_select = $this->buildSql($sql_select, "t.t_person ='".$t_person."'");
            $sql_count = $this->buildSql($sql_count,  "t.t_person ='".$t_person."'");
        }
        if (null != $queryparam['t_createuserid']) {
            $t_createuserid = $queryparam['t_createuserid'];
            $sql_select = $this->buildSql($sql_select, "t.t_createuserid ='".$t_createuserid."'");
            $sql_count = $this->buildSql($sql_count,  "t.t_createuserid ='".$t_createuserid."'");
        }
        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'statusprint') $queryparam['sort'] = 't_status';
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by t.t_arrivetime  asc  ";
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
            switch($value['t_status']){
                case "0":
                    $value['statusprint'] ="未处理";
                    break;
                case "1":
                    $value['statusprint'] ="处理中";
                    break;
                case "2":
                    $value['statusprint'] ="已完成";
                    break;

            }
            switch($value['t_biaodanname']){
                case "110":
                    $value['bdname'] ="涉密网计算机入网";
                    break;
                case "501":
                    $value['bdname'] ="涉密网测试用机入网";
                    break;
                case "119":
                    $value['bdname'] ="涉密网设备入网";
                    break;
                case "15":
                    $value['bdname'] ="涉密网计算机变更入网";
                    break;
                case "502":
                    $value['bdname'] ="涉密网测试用机变更入网";
                    break;
                case "308":
                    $value['bdname'] ="涉密网设备变更入网";
                    break;
                case "21":
                    $value['bdname'] ="涉密网计算机撤销入网";
                    break;
                case "503":
                    $value['bdname'] ="涉密网测试用机撤销入网";
                    break;
                case "122":
                    $value['bdname'] ="涉密网设备撤销入网";
                    break;
                case "18":
                    $value['bdname'] ="涉密网计算机新增入网申请";
                    break;
                case "105":
                    $value['bdname'] ="总体部涉密计算机特殊需求申请";
                    break;
                case "115":
                    $value['bdname'] ="涉密网计算机安全防护特殊需求申请";
                    break;
                case "118":
                    $value['bdname'] ="总体部涉密人员密级审定表";
                    break;
                case "134":
                    $value['bdname'] ="涉密网应用系统帐号申请";
                    break;
                case "135":
                    $value['bdname'] ="涉密网公共机登录权限申请";
                    break;
                case "136":
                    $value['bdname'] ="涉密网公共机登录权限撤销申请";
                    break;
                case "137":
                    $value['bdname'] ="涉密网测试用机域账号申请";
                    break;
                case "138":
                    $value['bdname'] ="涉密网测试用机域账号撤销申请";
                    break;
                case "506":
                    $value['bdname'] ="UsbKey管理表";
                    break;
                case "508":
                    $value['bdname'] ="临时域账号申请";
                    break;
                case "526":
                    $value['bdname'] ="综合管理层涉密计算机处理审批登记";
                    break;
                default:
                    $value['bdname'] ="-";

            }
            if(empty($value['sovlnum'])) $Result[$key]['sovlnum'] = 0;
            //申请人姓名根据t_nameid查
            if(!empty($value['t_nameid'])){
                $userid                 = trim($value['t_nameid']);
                $Result[$key]['t_name'] = getRealusername($userid);
            }

            $value['bdidurl']   ="<a onclick=\"opentaskdetail('".$value['t_taskid']."')\" style='color:DarkRed;'>".$value['t_rwid'];
            $value['t_taskid'] ="<a onclick=\"opentaskdetail('".$value['t_taskid']."')\" style='color:DarkBlue;'>".$value['t_taskid'];
//            $value['zd_area'] = M("dictionary")->where(array("d_atpid"=>$value['zd_area'],"d_belongtype"=>'地区'))->getField("d_dictname");
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function gethistorydata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $taskid =$queryparam['id'];
        $Model = M();
        $sql_select="
                select * from it_taskdetail t";
        $sql_count="
                select
                    count(1) c
                from it_taskdetail t";
        $sql_select = $this->buildSql($sql_select, "t.tl_taskid ='".$taskid."'");
        $sql_count = $this->buildSql($sql_count, "t.tl_taskid ='".$taskid."'");
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by t.tl_solvetime  asc  ";
        }
        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value){
            switch($value['tl_type']){
                case "1":
                    $value['tl_type'] ="正常处理";
                    break;
                case "2":
                    $value['tl_type'] ="跳过处理";
                    break;
                case "3":
                    $value['tl_type'] ="转派二线";
                    break;
                case "4":
                    $value['tl_type'] ="转派通信站";
                    break;

            }
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function getbdhistorydata(){
//       工单号，工单接收时间，处理时间，处理人，处理情况
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $rwid =$_GET['id'];
        $Model = M();
        $sql_select="
                select * from it_task t left join it_taskdetail tl on t.t_taskid=tl.tl_taskid";
        $sql_count="
                select
                    count(1) c
                from it_task t left join it_taskdetail tl on t.t_taskid=tl.tl_taskid";
        $sql_select = $this->buildSql($sql_select, "t.t_status  !='2'");
        $sql_count = $this->buildSql($sql_count, "t.t_status  !='2'");
        if ("" != $queryparam['id']) {
            $searchcontent = trim($queryparam['id']);
            $sql_select = $this->buildSql($sql_select, "t.t_rwid  ='" . $searchcontent . "'");
            $sql_count = $this->buildSql($sql_count, "t.t_rwid  ='" . $searchcontent . "'");
        }
        $sql_select = $sql_select . " order by t.t_arrivetime asc ,tl.tl_solvetime asc ";
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
            switch($value['tl_type']){
                case "1":
                    $value['tl_type'] ="正常处理";
                    break;
                case "2":
                    $value['tl_type'] ="跳过处理";
                    break;
                case "3":
                    $value['tl_type'] ="转派二线";
                    break;
                case "4":
                    $value['tl_type'] ="转派通信站";
                    break;

            }
//            $value['zd_area'] = M("dictionary")->where(array("d_atpid"=>$value['zd_area'],"d_belongtype"=>'地区'))->getField("d_dictname");
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function gettask(){
        $chuliperson      = I('session.username', '');
        $Model            = M('task');
        $data['t_status'] = '1'; //将该工单置为处理中
        $data['t_person'] = $chuliperson;
        $id               = $_POST['id'];
        try{
            $oldData = $Model->where("t_atpid='%s'",$id)->field('t_rwid')->getField('t_rwid');
            $Model->where("t_atpid='%s'",$id)->setField($data);
            $this->recordLog('update', 'gongdan', "工单状态：未处理-处理中；处理人：".$chuliperson."；", 'task',$oldData);
        }
        catch(\Exception $e)
        {
            echo $e;
        }
        $sbtypelist =$Model->where("t_atpid='%s'",$id)->find();
        $url = str_replace('{0}',session('username'),$sbtypelist['t_biaodanurl']);
        $sbtypelist['t_biaodanurl'] = $url;
        echo json_encode($sbtypelist);
    }
    public function sovltask()
    {
        $id              = I('post.t_atpid');
        $type            = I('post.sovltype');
        $process         = I('post.process');
        $person          = I('post.person');
        $chuliperson     = I('session.username', '');
        $taskdata        = M('task')->where("t_atpid='%s'",$id)->find();
        $Model           = M('taskdetail');
        $contentdata2    = "";
        $contentdatawork = "";
        if($taskdata['t_rwtype'] =='BD')
        {
            if($taskdata['t_bdstatus'] == ""){
                $this->ajaxReturn("-1");
            }
            else if($taskdata['t_bdstatus'] != "" && ($type=="3" ||$type=="4")){
                $this->ajaxReturn("-2");
            }
            else
            {
                $data['tl_atpid']                 = $this->makeGuid();
                $data['tl_taskid']                = $taskdata['t_taskid'];
                $data['tl_type']                  = $type;
                $data['tl_tasktype']              = $taskdata['t_tasktype'];
                $data['tl_person']                = $chuliperson;
                $data['tl_solvetime']             = date('Y-m-d H:i:s', time());
                $data2['t_atplastmodifyuser']     = $chuliperson;
                $data2['t_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                if($type == '1') //正常处理，将该工单置为完成
                {
                    $data2['t_status']  = '2';
                    $data2['t_person']  = $chuliperson;
                    $data['tl_process'] = $taskdata['t_tasktype'].":结束;".$process;
                }
                else if($type == '2'){
                    //跳过处理，将该工单置为待处理
                    $data2['t_status']  ='0';
                    $data['tl_process'] = $taskdata['t_tasktype'].":".$process;
                }
                M()->startTrans();
                try{
                    $Model->add($data);
                    M('task')->where("t_atpid='%s'",$id)->setField($data2);
                    $this->recordLog('update', 'gongdan', "工单状态：处理中-已完成；处理人：".$chuliperson."；", 'task',$data2['t_rwid']);
                    M()->commit();
                }

                catch(\Exception $e){
                    M()->rollback();
                    $this->ajaxReturn("error");
                }
            }

        }
        else if($taskdata['t_rwtype'] =='YW') //业务工单
        {
            $now = date('Y-m-d H:i:s', time());
            //taskdetail表新增数据
            $data['tl_atpid']     = $this->makeGuid();
            $data['tl_taskid']    = $taskdata['t_taskid'];
            $data['tl_type']      = $type;
            $data['tl_tasktype']  = $taskdata['t_tasktype'];
            $data['tl_person']    = $chuliperson;
            $data['tl_solvetime'] = $now;
            //IT_WORK表修改数据
            $datawork  = M('work')->where("rw_workid='%s'",$taskdata['t_taskid'])->find();
            $datawork['rw_count']         += 1;                        //work处理次数
            $datawork['rw_atpcreateuser'] = $chuliperson;              //work处理人
            $datawork['rw_dealtime']      = $now;                      //work处理时间
            $datawork['rw_process']       = $data['tl_process'];       //work处理结果
            //task表修改数据
            $data2                        = [];
            if($type == '1'){         //正常处理，将该工单置为完成
                $data['tl_process']     = $taskdata['t_tasktype'].":结束;".$process;
                $data2['t_status']      = '2';
                $data2['t_person']      = $chuliperson;  //写入处理人
                $datawork['rw_status']  = '2';   //work处理状态
                $contentdata2           = "工单状态：已完成；处理人：".$chuliperson."；";
                $contentdatawork        = "工单状态：已完成；处理人：".$chuliperson."；处理次数：".$datawork['rw_count']."；处理结果：".$datawork['rw_process']."；";
            }else if($type == '2'){  //跳过处理，将该工单置为待处理
                $data['tl_process']     = $taskdata['t_tasktype'].":".$process;
                $data2['t_status']      = '2';
                $datawork['rw_status']  = '0';   //work处理状态
                $contentdata2           = "跳过处理，工单状态：已完成；处理人：".$chuliperson."；";
                $contentdatawork        = "跳过处理，工单状态：未处理；处理人：".$chuliperson."；处理次数：".$datawork['rw_count']."；处理结果：".$datawork['rw_process']."；";
            }else if($type == '3'){  //转派二线，该工单置为待处理，同时工单变为二线工单
                $data2['t_status']      = '0';
                $data2['t_tasktype']    = '二线';
                $data2['t_person']      = $person; //$chuliperson
                $data['tl_process']     = "一线:转派二线";
                $datawork['rw_status']  = '2';                 //work处理状态
                $contentdata2           = "转派二线，工单状态：未处理；工单类型：二线；处理人：".$person."；";
                $contentdatawork        = "转派二线，工单状态：已完成；处理人：".$chuliperson."；处理次数：".$datawork['rw_count']."；处理结果：".$datawork['rw_process']."；";
            }else if($type == '4'){ //转派通信站，该工单完成，仅通信站人人员可看，流转到通信站派工单
                $data['tl_process']     = $taskdata['t_tasktype'].":结束;".$process;
                $data2['t_status']      = '2';
                $data2['t_person']      = $chuliperson;        //写入处理人
                $datawork['rw_status']  = '2';                 //work处理状态
                $contentdata2           = "转派通信站，工单状态：已完成；处理人：".$chuliperson."；";
                $contentdatawork        = "转派通信站，工单状态：已完成；处理人：".$chuliperson."；处理次数：".$datawork['rw_count']."；处理结果：".$datawork['rw_process']."；";
                //here need to change
                $this->tongxinzhancreate($taskdata['t_taskid'],$taskdata['t_nameid'],$taskdata['t_depart'],$taskdata['t_office'],$taskdata['t_phone']);
            }
            M()->startTrans();
            try{
                $Model->add($data);
                M('task')->where("t_atpid='%s'",$id)->setField($data2);
                M('work')->where("rw_workid='%s'",$taskdata['t_taskid'])->setField($datawork);
                //记录日志
                $this->recordLog('update', 'biaodan', $contentdatawork, 'work',$datawork['rw_workid']);
                $oldData = M('task')->where("t_atpid='%s'",$id)->field('t_rwid')->getField('t_rwid');
                $this->recordLog('update', 'gongdan', $contentdata2, 'task',$oldData);
                M()->commit();
            }
            catch(\Exception $e){
                M()->rollback();
                $this->ajaxReturn("error");
            }
        }
        else if($taskdata['t_rwtype'] =='BZ')
        {
            if($type=="3" ||$type=="4")
                $this->ajaxReturn("-2");
            else{
                $now = date('Y-m-d H:i:s', time());
                //taskdetail表新增数据
                $data['tl_atpid']     = $this->makeGuid();
                $data['tl_taskid']    = $taskdata['t_taskid'];
                $data['tl_type']      = $type;
                $data['tl_tasktype']  = $taskdata['t_tasktype'];
                $data['tl_person']    = $chuliperson;
                $data['tl_solvetime'] = $now;
                $data['tl_process']   = $taskdata['t_tasktype'].":".$process;
                //IT_WORK表修改数据
                $datawork  = M('work')->where("rw_workid='%s'",$taskdata['t_taskid'])->find();
                $datawork['rw_count']         += 1;                        //work处理次数
                $datawork['rw_atpcreateuser'] = $chuliperson; //work处理人
                $datawork['rw_dealtime']      = $now;                      //work处理时间
                $datawork['rw_process']       = $data['tl_process'];       //work处理结果
                //task表修改数据
                $data2 = [];
                if($type == '1'){        //正常处理，将该工单置为完成
                    $data2['t_status']      = '2';
                    $data2['t_person']      = $chuliperson; //写入处理人
                    $datawork['rw_status']  = '2';   //work处理状态
                    $contentdata2           = "工单状态：已完成；处理人：".$chuliperson."；";
                    $contentdatawork        = "工单状态：已完成；处理人：".$chuliperson."；处理次数：".$datawork['rw_count']."；处理结果：".$datawork['rw_process']."；";
                }else if($type == '2'){  //跳过处理，将该工单置为待处理
                    $data2['t_status']      = '2';
                    $datawork['rw_status']  = '0';   //work处理状态
                    $contentdata2           = "跳过处理，工单状态：已完成；处理人：".$chuliperson."；";
                    $contentdatawork        = "跳过处理，工单状态：未处理；处理人：".$chuliperson."；处理次数：".$datawork['rw_count']."；处理结果：".$datawork['rw_process']."；";
                }
                M()->startTrans();
                try{
                    $Model->add($data);
                    M('task')->where("t_atpid='%s'",$id)->setField($data2);
                    M('work')->where("rw_workid='%s'",$taskdata['t_taskid'])->setField($datawork);
                    //记录日志
                    $this->recordLog('update', 'biaodan', $contentdatawork, 'work',$datawork['rw_workid']);
                    $oldData = M('task')->where("t_atpid='%s'",$id)->field('t_rwid')->getField('t_rwid');
                    $this->recordLog('update', 'gongdan', $contentdata2, 'task',$oldData);
                    M()->commit();
                }
                catch(\Exception $e){
                    M()->rollback();
                    $this->ajaxReturn("error");
                }
            }
        }

    }
    public function resettask(){
        $id = $_POST['t_atpid'];
        $data2['t_status'] ='0';
        M('task')->where("t_atpid='%s'",$id)->save($data2);

    }

    public function submit(){
        $Model = M('terminal');
        $data = $Model->create();
//        $Model->startTrans();
        try{
            if(null==$data['zd_atpid']){
                $data['zd_atpid'] = $this->makeGuid();
                $Model->add($data);
                $this->logZichansys( $data,'add','it_terminal',"");
                $this->ajaxReturn("success");

            }else{
                $data['zd_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                $data['zd_atplastmodifyuser'] = I('session.username', '');
                $Model->where("zd_atpid='%s'",array($data['zd_atpid']))->save($data);
                $this->logZichansys( $data,'update','it_terminal',"");
                $this->ajaxReturn("success");
            }
//            $Model->commit();
        }catch (\Exception $e){
//            $Model->rollback();
            echo $e;
        }

    }


    public function tongxinzhancreate($taskid,$txz_user,$txz_depart,$txz_office,$txz_phone){

        $data =array();
        $data['txz_atpid'] = $this->makeGuid();
        $data['txz_tongxinid'] =$this->gettongxinid();
        $data['txz_taskid'] = $taskid;
        $data['txz_user'] = $txz_user;
        $data['txz_depart'] = $txz_depart;
        $data['txz_office'] = $txz_office;
        $data['txz_phone'] = $txz_phone;
        $data['txz_status'] = '未完成';
        $data['txz_submittime'] =date('Y-m-d H:i:s', time());
        try{
            M('tongxinzhanjob')->add($data);
            $this->recordLog('add', 'txz', "工单转通信站派工单，通信站派工单号：".$data['txz_tongxinid']."；任务单号：".$data['txz_taskid']."；申请人：".$data['txz_user']."；申请部门：".$data['txz_depart']."；申请处室：".$data['txz_office']."；电话：".$data['txz_phone']."；状态：".$data['txz_status']."；提交时间：".$data['txz_submittime']."；", 'tongxinzhanjob',$data['txz_atpid']);
        }catch(\Exception $e){
            echo $e;
        }

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

    //处理表单系统表单记录
    public function recorddealperson(){
        $taskid = I('post.taskid');
        if($taskid == ''){
            $this->makeStandResult(1,'工单号缺失！');
            return false;
        }
        $data['t_person'] = session('username');
        $res    = M('task')->where("t_taskid ='".$taskid."'")->setField($data);
        if($res){
            echo $this->makeStandResult(0,'');
        }else{
            echo $this->makeStandResult(2,'请求失败，请稍后再试！');
        }
    }

    public function txz_add(){
        $t_atpid = I('get.t_atpid');
        $taskData = M('task')->where("t_atpid ='".$t_atpid."'")->find();
        if(!empty($taskData['t_area']) || !empty($taskData['t_belongfloor']) || !empty($taskData['t_roomno'])){
            $address = [];
            if(!empty($taskData['t_area'])) $address[] = $taskData['t_area'];
            if(!empty($taskData['t_belongfloor'])) $address[] = $taskData['t_belongfloor'];
            if(!empty($taskData['t_roomno'])) $address[] = $taskData['t_roomno'];
            $taskData['address'] = implode('-',$address);
        }else if(!empty($taskData['t_rwid'])){
            $rwid = $taskData['t_rwid'];
            $addressInfo = M('task')->where("t_rwid ='".$rwid."' and (t_area is not null or t_belongfloor is not null or t_roomno is not null) ")->find();
            if(!empty($addressInfo)){
                $address = [];
                if(!empty($addressInfo['t_area'])) $address[] = $addressInfo['t_area'];
                if(!empty($addressInfo['t_belongfloor'])) $address[] = $addressInfo['t_belongfloor'];
                if(!empty($addressInfo['t_roomno'])) $address[] = $addressInfo['t_roomno'];
                $taskData['address'] = implode('-',$address);
            }
        }
        $this->assign('taskData',$taskData);
        $this->display();
    }

}