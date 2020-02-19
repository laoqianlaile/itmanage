<?php
namespace Demo\Controller;
use Think\Controller;
class BiaodanController extends BaseController {

    public function index(){
        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function bddetail(){
        $atpid      = I('get.id');
        $datatask  = M('task')->where("t_atpid='%s'",$atpid)->find();
        if(empty($datatask['t_name']) && !empty($datatask['t_nameid'])){
            $userid = trim($datatask['t_nameid']);
            $datatask['t_name'] = getRealusername($userid);
        }
        if($datatask['t_biaodanname'] == '110'){
            $datatask['type'] = '涉密计算机入网单';
        }
        $this->assign("t_rwid",$datatask['t_rwid']);
        $this->assign("datatask",$datatask);
        $this->display();
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res        = D('task')->getBDData($queryparam);
//        print_r($res);die;
        $Result     = $res[0];
        $Count      = $res[1];

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function getbdhistorydata(){
        // 工单号，工单接收时间，处理时间，处理人，处理情况
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_task t inner join it_taskdetail tl on t.t_taskid=tl.tl_taskid";
        $sql_count="
                select
                    count(1) c
                from it_task t inner join it_taskdetail tl on t.t_taskid=tl.tl_taskid";
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
        $Result = $Model->query($sql_select);
//        echo $Model->_sql();die;
        $Count = $Model->query($sql_count);

//        foreach($Result as $key=> &$value){
//            switch($value['tl_type']){
//                case "1":
//                    $value['tl_type'] ="正常处理";
//                    break;
//                case "2":
//                    $value['tl_type'] ="跳过处理";
//                    break;
//                case "3":
//                    $value['tl_type'] ="转派二线";
//                    break;
//                case "4":
//                    $value['tl_type'] ="转派通信站";
//                    break;
//
//            }
//        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
//    public function assignuser(){
//        $q = $_POST['data']['q'];
//        $Model = M();
//        $sql_select="select  id,realusername||'('||username||')' text from  it_person  where
//(username like '%".$q."%' or realusername like '%".$q."%')";
//        $result=$Model->query($sql_select);
//        echo json_encode(array('q' =>$q, 'results' => $result));
//    }
//    public function assigndept(){
//
//        $q = $_POST['data']['q'];
//        $Model = M();
//        $sql_select="select  id,fullname text from
//it_depart  where
//fullname like '%".$q."%'";
//        $result=$Model->query($sql_select);
//        echo json_encode(array('q' =>$q, 'results' => $result));
//    }
}