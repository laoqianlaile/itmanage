<?php
namespace Demo\Controller;
use Think\Controller;
class GongdanController extends BaseController {

    public function index(){

        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function taskdetail(){
        $taskid = I('get.id');
        $datawork  = M('work')->where("rw_workid='%s'",$taskid)->find();
        if(!empty($datawork['rw_account'])){
            $datawork['dealperson'] = $this->getrealname($datawork['rw_atpcreateuser']);
        }
        switch($datawork['rw_status']){
            case "0":
                $datawork['rw_status'] ="未处理";
                break;
            case "1":
                $datawork['rw_status'] ="处理中";
                break;
            case "2":
                $datawork['rw_status'] ="已完成";
                break;

        }
        switch($datawork['rw_type']){
            case "ZX":
                $datawork['rwtype'] ="单步";
                $datawork['rw_resource'] = '45888';
                break;
            case "YW":
                $datawork['rwtype'] ="业务";
                $datawork['rw_resource'] = '45888';
                break;
            case "BZ":
                $datawork['rwtype'] ="现场保障";
                $datawork['rw_resource'] = '45888';
                break;
            default:
                $datawork['rwtype'] ="";
                $datawork['rw_resource'] = '表单系统';
                break;
        }
        $datawork['rw_depart'] = '';
        $datawork['rw_office'] = '';
        if(!empty($datawork['rw_atpcreateuser'])){
            $dept = $this->getuserdept_new($datawork['rw_atpcreateuser']);
            if(!empty($dept)){
                $datawork['rw_depart'] = $dept[0];
                $datawork['rw_office'] = $dept[1];
            }
        }
        $this->assign("t_taskid",$taskid);
        $this->assign("datawork",$datawork);
        $this->display('detail');
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res        = D('work')->getGDData($queryparam);
        $Result     = $res[0];
        $Count      = $res[1];

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function gethistorydata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_taskdetail t";
        $sql_count="
                select
                    count(1) c
                from it_taskdetail t";
        if ("" != $queryparam['id']) {
            $searchcontent = trim($queryparam['id']);
            $sql_select = $this->buildSql($sql_select, "t.tl_taskid ='" . $searchcontent . "'");
            $sql_count = $this->buildSql($sql_count, "t.tl_taskid ='" . $searchcontent . "'");
        }
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

    public function getrealname($username){
        $realusername =M('person')->where("username='%s'",$username)->getField('realusername');
        if(empty($realusername)){
            $realusername =M('ywperson')->where("yw_account='%s'",$username)->getField('yw_name');
        }
        return $realusername;
    }

}