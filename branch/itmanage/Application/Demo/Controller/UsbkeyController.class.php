<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class UsbkeyController extends BaseController {

    public function index(){
        $this->assigndepart();
        $this->display();
    }
    public function import(){
        $this->display();
    }
    public function usbtask(){
        $this->assigndepart();
        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function pushdb(){
        $this->display();
    }
    public function printusb(){
        $id =$_GET['id'];
        $data = M('usbkey')->where("u_atpid='%s'",$id)->find();
        if($data['u_gender'] =='0')
            $data['u_gender']='男';
        else if($data['u_gender'] =='1')
            $data['u_gender']='女';
        $orgid = M('person')->where("username='%s'",$data['u_account'])->getField('orgid');
        $data['u_depart'] = M('depart')->where("id='%s'",$orgid)->getField('fullname');
        $this->assign('data',$data);
        $this->display();
    }
    public function assigncard(){
        $id = I('get.id');
        if(strpos($id,',') === false){
            $data = M('usbkey')->where("u_atpid='%s'",$id)->find();
            $idsstr = $data['u_code'];
            $this->assign('data',$data);
            $this->assign('id',$id);
            $this->assign('ids',$idsstr);
        }else{
            $id1  = explode(',',$id);
            $id1  = implode("','",$id1);
            $id1  = "'".$id1."'";
            $ids = M('usbkey')->where("u_atpid in (".$id1.")")->field('u_code')->select();
            $idsstr = [];
            foreach($ids as $val){
                $idsstr[] = $val['u_code'];
            }
            $idsstr = implode(',',$idsstr);
//            print_r($idsstr);die;
            $this->assign('data',[]);
            $this->assign('id',$id);
            $this->assign('ids',$idsstr);
        }
        $this->display();
    }
    public function edit(){
        header("Content-type:text/html;charset=utf-8");
        $id = $_GET['id'];
        if ($id) {
            $Model = M("usbkey");
            $data = $Model->where("u_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);

            }
        }
        $this->display();
    }
    public function taskedit(){
        header("Content-type:text/html;charset=utf-8");
        $id = $_GET['id'];
        if ($id) {
            $Model = M("usbkey");
            $data = $Model->where("u_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);

            }
        }
        $this->display();
    }
    public function del()
    {
        try {
            $ids = $_POST['ids'];
            $array = explode(',', $ids);
            if ($array && count($array) > 0) {
                $Model = M("usbkey");
                foreach ($array as $id) {
                    $data = $Model->where("u_atpid='%s'", $id)->find();
                    $data['u_atpstatus'] = 'DEL';
                    $data['u_atplastmodifytime'] = date('Y-m-d H:i:s',time());
                    $data['u_atplastmodifyuser'] = I('session.username','');
                    $Model->where("u_atpid='%s'", $id)->save($data);
                    $this->logUsbkey($data['u_code'],"delete",'0',"删除USBkey数据成功，Key编号：".$data['u_code']."；");
                    $this->recordLog('delete', 'usbkey', "删除USBkey数据，Key编号：".$data['u_code']."；",'usbkey',$id);
                    }
                }

        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_usbkey du left join
                (select d1.id,d1.pid,d1.name officename ,d2.name departname from it_depart d1
                left join it_depart d2 on d1.pid =d2.id) d
                on du.u_office=d.id
                 ";
        $sql_count="
                select
                    count(1) c
                from it_usbkey du left join
                (select d1.id,d1.pid,d1.name officename ,d2.name departname from it_depart d1 left join it_depart d2 on d1.pid =d2.id) d
                on du.u_office=d.id";
        $sql_select = $this->buildSql($sql_select,"du.u_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.u_atpstatus is null");


        if ("" != $queryparam['usbcode']){
            $searchcontent      = trim($queryparam['usbcode']);
            $searchcontentlower = strtolower($queryparam['usbcode']);
            $sql_select = $this->buildSql($sql_select,"(du.u_code like '%".$searchcontent."%' or lower(du.u_code) like '%".$searchcontentlower."%')");
            $sql_count = $this->buildSql($sql_count,"(du.u_code like '%".$searchcontent."%' or lower(du.u_code) like '%".$searchcontentlower."%')");
        }
        if ("" != $queryparam['account']){
            $searchcontent = trim($queryparam['account']);
            $sql_select = $this->buildSql($sql_select,"du.u_account like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_account like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['depart']){
            $searchcontent = trim($queryparam['depart']);
            $sql_select = $this->buildSql($sql_select,"(du.u_depart like '%".$searchcontent."%' or d.departname like '%".$searchcontent."%')");
            $sql_count = $this->buildSql($sql_count,"(du.u_depart like '%".$searchcontent."%' or d.departname like '%".$searchcontent."%')");
        }
        if ("" != $queryparam['office']){
            $searchcontent = trim($queryparam['office']);
            $sql_select = $this->buildSql($sql_select,"(du.u_office like '%".$searchcontent."%' or d.officename like '%".$searchcontent."%')");
            $sql_count = $this->buildSql($sql_count,"(du.u_office like '%".$searchcontent."%' or d.officename like '%".$searchcontent."%')");
        }
        if ("" != $queryparam['status']){
            $searchcontent = trim($queryparam['status']);
            $sql_select = $this->buildSql($sql_select,"du.u_status ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_status ='".$searchcontent."'");
        }

        if ("" != $queryparam['finishtime']){
            $searchcontent = trim($queryparam['finishtime']);
            $sql_select = $this->buildSql($sql_select,"du.u_finishtime ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_finishtime ='".$searchcontent."'");
        }
        if ("" != $queryparam['isforce']){
            $searchcontent = trim($queryparam['isforce']);
            $sql_select = $this->buildSql($sql_select,"du.u_isforce ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_isforce ='".$searchcontent."'");
        }
        if ("" != $queryparam['u_stockman']){
            $searchcontent = trim($queryparam['u_stockman']);
            $sql_select = $this->buildSql($sql_select,"du.u_stockman like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_stockman like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['u_dbtime']){
            $searchcontent = trim($queryparam['u_dbtime']);
            $sql_select = $this->buildSql($sql_select,"du.u_dbtime like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_dbtime like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'tl_type') $queryparam['sort'] = 'u_status';
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.u_atpid  asc  ";
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
        foreach($Result as $key=> &$value) {
            switch ($value['u_status']) {
                case "0":
                    $value['tl_type'] = "库存";
                    break;
                case "1":
                    $value['tl_type'] = "发放在用";
                    break;
                case "2":
                    $value['tl_type'] = "丢失";
                    break;
                case "3":
                    $value['tl_type'] = "损坏";
                    break;
                case "4":
                    $value['tl_type'] = "未发";
                    break;
                case "5":
                    $value['tl_type'] = "发放未用";
                    break;
                case "6":
                    $value['tl_type'] = "冻结";
                    break;
            }
            switch ($value['u_isforce']) {
                case "0":
                    $value['u_isforce'] = "是";
                    break;
                case "1":
                    $value['u_isforce'] = "否";
                    break;
            }
            if(empty($value['departname'])) $Result[$key]['departname'] = $value['u_depart'];
            if(empty($value['officename'])) $Result[$key]['officename'] = $value['u_office'];
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function getData2(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_usbkey du
                 ";
        $sql_count="
                select
                    count(1) c
                from it_usbkey du ";
        $sql_select = $this->buildSql($sql_select,"du.u_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.u_atpstatus is null");
        $sql_select = $this->buildSql($sql_select,"du.u_status  = '0'");
        $sql_count = $this->buildSql($sql_count,"du.u_status  = '0'");

        if ("" != $queryparam['usbcode']){
            $searchcontent      = trim($queryparam['usbcode']);
            $searchcontentlower = strtolower($searchcontent);
            $sql_select = $this->buildSql($sql_select,"(du.u_code like '%".$searchcontent."%' or lower(du.u_code) like '%".$searchcontentlower."%')");
            $sql_count = $this->buildSql($sql_count,"(du.u_code like '%".$searchcontent."%' or lower(du.u_code) like '%".$searchcontentlower."%')");
        }
        if ("" != $queryparam['account']){
            $searchcontent = trim($queryparam['account']);
            $sql_select = $this->buildSql($sql_select,"du.u_account like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_account like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['depart']){
            $searchcontent = trim($queryparam['depart']);
            $sql_select = $this->buildSql($sql_select,"du.u_depart like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_depart like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['office']){
            $searchcontent = trim($queryparam['office']);
            $sql_select = $this->buildSql($sql_select,"du.u_office like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_office like '%".$searchcontent."%'");
        }
//        if ("" != $queryparam['status']){
//            $searchcontent = trim($queryparam['status']);
//            $sql_select = $this->buildSql($sql_select,"du.u_status ='".$searchcontent."'");
//            $sql_count = $this->buildSql($sql_count,"du.u_status ='".$searchcontent."'");
//        }
        if ("" != $queryparam['finishtime']){
            $searchcontent = trim($queryparam['finishtime']);
            $sql_select = $this->buildSql($sql_select,"du.u_finishtime ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_finishtime ='".$searchcontent."'");
        }
        if ("" != $queryparam['isforce']){
            $searchcontent = trim($queryparam['isforce']);
            $sql_select = $this->buildSql($sql_select,"du.u_isforce ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_isforce ='".$searchcontent."'");
        }
        if ("" != $queryparam['u_stockman']){
            $searchcontent = trim($queryparam['u_stockman']);
            $sql_select = $this->buildSql($sql_select,"du.u_stockman = '".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_stockman = '".$searchcontent."'");
        }
        if ("" != $queryparam['u_dbtime']){
            $searchcontent = trim($queryparam['u_dbtime']);
            $sql_select = $this->buildSql($sql_select,"du.u_dbtime like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_dbtime like '%".$searchcontent."%'");
        }
        if ('u_dbtime' != $queryparam['sort']) {
            if($queryparam['sort'] == 'tl_type') $queryparam['sort'] = 'u_status';
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.u_dbtime desc nulls last ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
       // echo $sql_select;die;
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value) {
            switch ($value['u_status']) {
                case "0":
                    $value['tl_type'] = "库存";
                    break;
                case "1":
                    $value['tl_type'] = "发放在用";
                    break;
                case "2":
                    $value['tl_type'] = "丢失";
                    break;
                case "3":
                    $value['tl_type'] = "损坏";
                    break;
                case "4":
                    $value['tl_type'] = "未发";
                    break;
                case "5":
                    $value['tl_type'] = "发放未用";
                    break;
                case "6":
                    $value['tl_type'] = "冻结";
                    break;

            }
            switch ($value['u_isforce']) {
                case "0":
                    $value['u_isforce'] = "是";
                    break;
                case "1":
                    $value['u_isforce'] = "否";
                    break;

            }
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function getData3(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_usbkey du left join
                (select d1.id,d1.pid,d1.name officename ,d2.name departname from it_depart d1
                left join it_depart d2 on d1.pid =d2.id) d
                on du.u_office=d.id
                 ";
        $sql_count="
                select
                    count(1) c
                from it_usbkey du left join
                (select d1.id,d1.pid,d1.name officename ,d2.name departname from it_depart d1 left join it_depart d2 on d1.pid =d2.id) d
                on du.u_office=d.id";
        $sql_select = $this->buildSql($sql_select,"du.u_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.u_atpstatus is null");
        $sql_select = $this->buildSql($sql_select,"du.u_status  = '4'");
        $sql_count = $this->buildSql($sql_count,"du.u_status  = '4'");

        if ("" != $queryparam['usbcode']){
            $searchcontent      = trim($queryparam['usbcode']);
            $searchcontentlower = strtolower($queryparam['usbcode']);
            $sql_select = $this->buildSql($sql_select,"(du.u_code like '%".$searchcontent."%' or lower(du.u_code) like '%".$searchcontentlower."%')");
            $sql_count = $this->buildSql($sql_count,"(du.u_code like '%".$searchcontent."%' or lower(du.u_code) like '%".$searchcontentlower."%')");
        }
        if ("" != $queryparam['account']){
            $searchcontent = trim($queryparam['account']);
            $sql_select = $this->buildSql($sql_select,"du.u_account like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_account like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['depart']){
            $searchcontent = trim($queryparam['depart']);
            $sql_select = $this->buildSql($sql_select,"du.u_depart like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_depart like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['office']){
            $searchcontent = trim($queryparam['office']);
            $sql_select = $this->buildSql($sql_select,"du.u_office like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_office like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['status']){
            $searchcontent = trim($queryparam['status']);
            $sql_select = $this->buildSql($sql_select,"du.u_status ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_status ='".$searchcontent."'");
        }

        if ("" != $queryparam['finishtime']){
            $searchcontent = trim($queryparam['finishtime']);
            $sql_select = $this->buildSql($sql_select,"du.u_finishtime ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_finishtime ='".$searchcontent."'");
        }
        if ("" != $queryparam['isforce']){
            $searchcontent = trim($queryparam['isforce']);
            $sql_select = $this->buildSql($sql_select,"du.u_isforce ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.u_isforce ='".$searchcontent."'");
        }
        if ("" != $queryparam['u_stockman']){
            $searchcontent = trim($queryparam['u_stockman']);
            $sql_select = $this->buildSql($sql_select,"du.u_stockman like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_stockman like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['u_dbtime']){
            $searchcontent = trim($queryparam['u_dbtime']);
            $sql_select = $this->buildSql($sql_select,"du.u_dbtime like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.u_dbtime like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'tl_type') $queryparam['sort'] = 'u_status';
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.u_atpid  asc  ";
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
        foreach($Result as $key=> &$value) {
            switch ($value['u_status']) {
                case "0":
                    $value['tl_type'] = "库存";
                    break;
                case "1":
                    $value['tl_type'] = "发放在用";
                    break;
                case "2":
                    $value['tl_type'] = "丢失";
                    break;
                case "3":
                    $value['tl_type'] = "损坏";
                    break;
                case "4":
                    $value['tl_type'] = "未发";
                    break;
                case "5":
                    $value['tl_type'] = "发放未用";
                    break;
                case "6":
                    $value['tl_type'] = "冻结";
                    break;

            }
            switch ($value['u_isforce']) {
                case "0":
                    $value['u_isforce'] = "是";
                    break;
                case "1":
                    $value['u_isforce'] = "否";
                    break;

            }
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function getLogdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from  it_usbkeyhistory du";
        $sql_count="
                select
                    count(1) c
               from  it_usbkeyhistory du";
        if ("" != $queryparam['usbcode']){
            $searchcontent      = trim($queryparam['usbcode']);
            $searchcontentlower = strtolower($queryparam['usbcode']);
            $sql_select = $this->buildSql($sql_select,"(du.uh_code like '%".$searchcontent."%' or lower(du.uh_code) like '%".$searchcontentlower."%')");
            $sql_count = $this->buildSql($sql_count,"(du.uh_code like '%".$searchcontent."%' or lower(du.uh_code) like '%".$searchcontentlower."%')");
        }
        if ("" != $queryparam['account']){
            $searchcontent = trim($queryparam['account']);
            $sql_select = $this->buildSql($sql_select,"du.uh_opuserid = '".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.uh_opuserid = '".$searchcontent."'");
        }

        if ("" != $queryparam['object']){
            $searchcontent = trim($queryparam['object']);
            $sql_select = $this->buildSql($sql_select,"du.uh_object = '".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.uh_object = '".$searchcontent."'");
        }
        if ("" != $queryparam['optype']){
            $searchcontent = trim($queryparam['optype']);
            $sql_select = $this->buildSql($sql_select,"du.uh_type = '".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.uh_type = '".$searchcontent."'");
        }
        if ("" != $queryparam['begintime']){
            $searchcontent = trim($queryparam['begintime']);
            $sql_select = $this->buildSql($sql_select,"du.uh_optime > '".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.uh_optime > '".$searchcontent."'");
        }
        if ("" != $queryparam['endtime']){
            $searchcontent = trim($queryparam['endtime']);
            $sql_select = $this->buildSql($sql_select,"du.uh_optime < '".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.uh_optime <'".$searchcontent."'");
        }
        if ("" != $queryparam['content']){
            $searchcontent = trim($queryparam['content']);
            $sql_select = $this->buildSql($sql_select,"du.uh_content like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.uh_content like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.uh_optime desc  ";
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
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function exportExcel(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Model = M();
        $sql_select="
            select * from it_usbkey du left join
            (select d1.id,d1.pid,d1.name officename ,d2.name departname from it_depart d1
            left join it_depart d2 on d1.pid =d2.id) d
            on du.u_office=d.id
             ";
        $sql_select = $this->buildSql($sql_select,"du.u_atpstatus is null");

        if(isset($queryparam['ids']) && ($queryparam['ids'] != '')){
            $searchcontent = trim($queryparam['ids']);
            $searchcontent = explode(',',$searchcontent);
            $searchcontent = implode("','",$searchcontent);
            $searchcontent = "'".$searchcontent."'";
            $sql_select = $this->buildSql($sql_select,"du.u_atpid in (".$searchcontent.")");
        }else{
            if ("" != $queryparam['usbcode']){
                $searchcontent = trim($queryparam['usbcode']);
                $sql_select = $this->buildSql($sql_select,"du.u_code like '%".$searchcontent."%'");
            }
            if ("" != $queryparam['account']){
                $searchcontent = trim($queryparam['account']);
                $sql_select = $this->buildSql($sql_select,"du.u_account like '%".$searchcontent."%'");
            }

            if ("" != $queryparam['depart']){
                $searchcontent = trim($queryparam['depart']);
                 $sql_select = $this->buildSql($sql_select,"(du.u_depart like '%".$searchcontent."%' or d.departname like '%".$searchcontent."%')");
            }
            if ("" != $queryparam['office']){
                $searchcontent = trim($queryparam['office']);
               $sql_select = $this->buildSql($sql_select,"(du.u_office like '%".$searchcontent."%' or d.officename like '%".$searchcontent."%')");
            }
            if ("" != $queryparam['status']){
                $searchcontent = trim($queryparam['status']);
                $sql_select = $this->buildSql($sql_select,"du.u_status ='".$searchcontent."'");
            }

            if ("" != $queryparam['finishtime']){
                $searchcontent = trim($queryparam['finishtime']);
                $sql_select = $this->buildSql($sql_select,"du.u_finishtime ='".$searchcontent."'");
            }
            if ("" != $queryparam['isforce']){
                $searchcontent = trim($queryparam['isforce']);
                $sql_select = $this->buildSql($sql_select,"du.u_isforce ='".$searchcontent."'");
            }
            if (null != $queryparam['sort']) {
                $queryparam['sortorder'] = isset($queryparam['sortorder'])?$queryparam['sortorder']:'asc';
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortorder'] . ' ';
            } else {
                $sql_select = $sql_select . " order by du.u_atpid  asc  ";
            }
        }
       // echo $sql_select;die;
        $Result = $Model->query($sql_select);


        $data = array();
        foreach($Result as $key=>$value){
            $status = '';
            switch ($value['u_status']) {
                case "0":
                    $status = "库存";
                    break;
                case "1":
                    $status = "发放在用";
                    break;
                case "2":
                    $status = "丢失";
                    break;
                case "3":
                    $status = "损坏";
                    break;
                case "4":
                    $status = "未发";
                    break;
                case "5":
                    $status = "发放未用";
                    break;
                case "6":
                    $status = "冻结";
                    break;

            }
            // $data[$key][] = $key+1;
            $data[$key][] = $value['u_code'];
            $data[$key][] = $value['u_name'];
            $data[$key][] = $value['u_idcard'];
            if($value['u_gender'] === '0'){
                $data[$key][] = '男';
            }else if($value['u_gender'] === '1'){
                $data[$key][] = '女';
            }else{
                $data[$key][] = '';
            }
            $data[$key][] = $value['u_firstname'];
            $data[$key][] = $value['u_secondname'];
            $data[$key][] = $value['u_dutywork'];//职务
            $data[$key][] = empty($value['u_account'])?'':'cast.hq.'.$value['u_account'];
            $data[$key][] = empty($value['id'])?$value['u_depart']:$value['departname'];
            $data[$key][] = empty($value['id'])?$value['u_office']:$value['officename'];
            $data[$key][] = '职员'; //用户组
            $data[$key][] = empty($value['u_account'])?'':$value['u_account'].'@hq.cast.casc';
            $data[$key][] = '普通';//访问权限
            $data[$key][] = '普通';//证书类型
            $data[$key][] = '有';//签名证书
            $data[$key][] = '有';//加密证书
            $data[$key][] = '20301230';//SIGN有效期
            $data[$key][] = '20301230';//ENC有效期
            $data[$key][] = '北京市海淀区友谊路104号'; //单位地址
            $data[$key][] = '100094'; //邮政编码
            $data[$key][] = $value['u_phone'];
            $data[$key][] = $value['u_stockman'];
            $data[$key][] = $value['u_solveman'];
            $data[$key][] = $status;//USB状态
            if($value['u_isforce'] === '0'){
                $data[$key][] = '强制USBkey登录';
            }else if($value['u_isforce'] === '1'){
                $data[$key][] = '不强制USBkey登录';
            }else{
                $data[$key][] = '';
            };//是否强制
            $data[$key][] = $value['u_expiredate'];//结束时间U_EXPIREDATE
            $data[$key][] = $value['u_reason'];//原因说明
        }
        // dump($data);die;
        $tableheader = array('序号','USBKEY编号', '申请人', '身份证号', '性别', '姓','名', '职务','用户登录','部门名称','处室名称','用户组','EMAIL地址','访问权限','证书类型','签名证书','加密证书','SIGN有效期','ENC有效期','单位地址','邮政编码','联系电话','录入人','审批人','USB状态','是否强制','结束时间','原因说明');
        excelExport($tableheader,$data,false);
    }
    public function exportExceltask(){
        set_time_limit(0);
        $queryparam = I('get.');
        $Model = M();
        $sql_select="
            select * from it_usbkey du left join
            (select d1.id,d1.pid,d1.name officename ,d2.name departname from it_depart d1
            left join it_depart d2 on d1.pid =d2.id) d
            on du.u_office=d.id
             ";
        $sql_select = $this->buildSql($sql_select,"du.u_atpstatus is null");
//        $sql_select = $this->buildSql($sql_select,"du.u_status  = '4'");


        if ("" != $queryparam['ids']){
            $searchcontent = trim($queryparam['ids']);
            $searchcontent = explode(',',$searchcontent);
            $searchcontent = implode("','",$searchcontent);
            $searchcontent = "'".$searchcontent."'";
            $sql_select = $this->buildSql($sql_select,"du.u_atpid in (".$searchcontent.")");
        }
//        echo $sql_select;die;
        $Result = $Model->query($sql_select);
        $data = array();
        foreach($Result as $key=>$value){
            $data[$key][] = $key+1;
            $data[$key][] = $value['u_code'];
            $data[$key][] = $value['u_atpcreateuser'];
            $data[$key][] = $value['u_idcard'];
            if($value['u_gender'] === '0'){
                $data[$key][] = '男';
            }else if($value['u_gender'] === '1'){
                $data[$key][] = '女';
            }else{
                $data[$key][] = '';
            }
            $data[$key][] = $value['u_name'];
            $data[$key][] = '';//职务
            $data[$key][] = $value['u_account'];
            $data[$key][] = empty($value['id'])?$value['u_depart'].'-'.$value['u_office']:$value['departname'].'-'.$value['officename'];
            $data[$key][] = ''; //用户组
            $data[$key][] = empty($value['u_account'])?'':$value['u_account'].'@cast.casc';
            if($value['u_isforce'] === '1'){
                $data[$key][] = '强制USBkey登录';
            }else if($value['u_isforce'] === '0'){
                $data[$key][] = '不强制USBkey登录';
            }else{
                $data[$key][] = '';
            };
            $data[$key][] = $value['u_certfctype'];
            $data[$key][] = $value['u_certfcsign'];
            $data[$key][] = $value['u_certfcencode'];
            $data[$key][] = $value['u_expiredate'];
            $data[$key][] = $value['u_encexpiredate'];
            $data[$key][] = ''; //单位地址
            $data[$key][] = ''; //邮政编码
            $data[$key][] = $value['u_phone'];
            $data[$key][] = $value['u_stockman'];
            $data[$key][] = $value['u_solveman'];
        }
        vendor("PHPExcel.PHPExcel");
        $excel = new \PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V');
        $tableheader = array('序号','USBKEY编号', '申请人', '身份证号', '性别', '姓 名', '职务','用户登录','部门名称','用户组','EMAIL地址','访问权限','证书类型','签名证书','加密证书','SIGN有效期','ENC有效期','单位地址','邮政编码','联系电话','录入人','审批人');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }
        $write = new \PHPExcel_Writer_Excel2007($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="expexcel.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
    public function assigndepart(){
        $Model = M('depart');
        $data =$Model->where("( pid='3911431181BD2D2CE054D4C9EF06663E' or pid='3911431181E72D2CE054D4C9EF06663E' ) and id!='3911431181E72D2CE054D4C9EF06663E'")->field('name,id')->select();
//        echo $Model->_sql();die;
        $this->assign('ds_depart',$data);
    }
    public function getoffice(){
        $Model = M('depart');
        $depart = $_POST['depart'];
        $officelist = $Model->where("pid='%s'",$depart)->field('name,id')->select();
        echo json_encode($officelist);
    }
    public function getusername($id){
        $realusername =M('person')->where("username='%s'",$id)->getField('realusername');
        return $realusername;
    }
    public function getofficeid($username){
        $orgid = M('person')->where("username='%s'",$username)->getField('orgid');
        return $orgid;
    }
    public function getdepartid($username){
        $orgid = M('person')->where("username='%s'",$username)->getField('orgid');
        $pid = M('depart')->where("id='%s'",$orgid)->getField('pid');
        return $pid;
    }
    public function getdepartname($username){
        $orgid    = M('person')->where("username='%s'",$username)->getField('orgid');
        $fullname = M('depart')->where("id='%s'",$orgid)->getField('fullname');
        $depts    = explode('-',$fullname);
        if(in_array('综合管理层',$depts)){
            foreach($depts as $key=>$val){
                if($val == '综合管理层'){
                    $pname   = $depts[$key-1];
                    $orgname = $depts[$key-2];
                }
            }
        }else{
            foreach($depts as $key=>$val){
                if($val == '中国航天科技集团公司第五研究院'){
                    $pname   = $depts[$key-1];
                    $orgname = $depts[$key-2];
                }
            }
        }
        return [$pname,$orgname];
    }
    public function submitusbassign(){
        $Model = M('usbkey');
        $data = $Model->create();
        try{
            if(null==$data['u_atpid']){
                $data['u_atpid']    = $this->makeGuid();
                $data['u_status']   ='0';
                $data['u_stockman'] = I('session.username', '');
                $data['u_dbtime']   = date('Y-m-d H:i:s', time());
                $Model->add($data);
                $content = '';
                foreach($data as $key=>$val){
                    if(!empty($val)) $content .= $key."：".$val."；";
                }
                $this->logUsbkey($data['u_code'],"add",'0',$content);
                $this->recordLog('add', 'usbkey', $content,'usbkey',$data['u_atpid']);
                $this->ajaxReturn("success");
            }else{
                $ids   = explode(',',$data['u_atpid']);
                $codes = explode(',',$data['u_code']);
                $data['u_status']    ='4';
                $data['u_isforce']   ='1';
                $data['u_name']      = $this->getusername($data['u_account']);
                $deptInfo            = $this->getdepartname($data['u_account']);
                $data['u_depart']    = $deptInfo[0];
                $data['u_office']    = $deptInfo[1];
                $data['u_assignman'] = I('session.username', '');
                $data['u_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                $data['u_atplastmodifyuser'] = I('session.username', '');
//                print_r($data);die;
                foreach($ids as $key=>$val){
                    $data['u_atpid'] = $val;
                    $data['u_code']  = $codes[$key];
                    $Model->where("u_atpid = '".$val."'")->save($data);
                    $this->logUsbkey($data['u_code'],"update",'0',"数据：域账户，".$data['u_account']."；身份证号，".$data['u_idcard']."；性别，".$data['u_gender']."；电话，".$data['u_phone']."；姓，".$data['u_firstname']."；名，".$data['u_secondname']."；状态，未发；是否强制USBKey：否；");
                    $this->recordLog('update', 'usbkey', "域账户：".$data['u_account']."；身份证号：".$data['u_idcard']."；性别：".$data['u_gender']."；电话：".$data['u_phone']."；姓：".$data['u_firstname']."；名：".$data['u_secondname']."；状态：未发；是否强制USBKey：否；",'usbkey',$val);
                }
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
//            $Model->rollback();
            echo $e;
        }

    }
    public function submit(){
        $Model = M('usbkey');
        $data = $Model->create();
        try{
            if(null==$data['u_atpid']){
                $data['u_atpid'] = $this->makeGuid();
                $Model->add($data);
                $content = '';
                foreach($data as $key=>$val){
                    if(!empty($val)) $content .= $key."：".$val."；";
                }
                $this->logUsbkey($data['u_code'],"add",'0',$content);
                $this->recordLog('add', 'usbkey', $content,'usbkey',$data['u_atpid']);
                $this->ajaxReturn("success");
            }else{
                $data['u_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                $data['u_atplastmodifyuser'] = I('session.username', '');
                $Model->where("u_atpid='%s'",array($data['u_atpid']))->save($data);
                $this->logUsbkey($data['u_code'],"update",'0',"状态：".$data['u_status']."；是否强制USBKey：".$data['u_isforce']."；到期日期：".$data['u_finishtime']."；原因：".$data['u_reason']."；临时原因：".$data['u_linshireason']."；");
                $this->recordLog('update', 'usbkey', "域账户：".$data['u_account']."；身份证号：".$data['u_idcard']."；性别：".$data['u_gender']."；电话：".$data['u_phone']."；姓：".$data['u_firstname']."；名：".$data['u_secondname']."；状态：未发；是否强制USBKey：否；",'usbkey',$data['u_atpid']);
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
//            $Model->rollback();
            echo $e;
        }

    }
    public function submittask(){
        $Model = M('usbkey');
        $data = $Model->create();
        try{
            if(null==$data['u_atpid']){
                $data['u_atpid'] = $this->makeGuid();
                $Model->add($data);
                $content = '';
                foreach($data as $key=>$val){
                    if(!empty($val)) $content .= $key."：".$val."；";
                }
                $this->logUsbkey($data['u_code'],"add",'0',$content);
                $this->recordLog('add', 'usbkey', $content,'usbkey',$data['u_atpid']);
                $this->ajaxReturn("success");
            }else{
                $data['u_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                $data['u_atplastmodifyuser'] = I('session.username', '');
                $Model->where("u_atpid='%s'",array($data['u_atpid']))->save($data);
                $this->logUsbkey($data['u_code'],"update",'0', "域账户：".$data['u_account']."；身份证号：".$data['u_idcard']."；性别：".$data['u_gender']."；电话：".$data['u_phone']."；姓：".$data['u_firstname']."；名：".$data['u_secondname']."；状态：未发；是否强制USBKey：1；");
                $this->recordLog('update', 'usbkey', "域账户：".$data['u_account']."；身份证号：".$data['u_idcard']."；性别：".$data['u_gender']."；电话：".$data['u_phone']."；姓：".$data['u_firstname']."；名：".$data['u_secondname']."；状态：未发；是否强制USBKey：1；",'usbkey',$data['u_atpid']);

                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
//            $Model->rollback();
            echo $e;
        }

    }
    public function submitimp()
    {
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('xls');
        $upload->rootPath = './Public/uploads/';
        $upload->savePath = '';
        $info = $upload->upload();
        $filename = './Public/uploads/' . $info["updataexcel2007"]["savepath"] . $info["updataexcel2007"]["savename"];
////        dump($info);
//        $filename = $_FILES['updataexcel2007']['tmp_name'];
        vendor("PHPExcel.PHPExcel");
        $objPhpExcel = \PHPExcel_IOFactory::load($filename);
        $excelsheet = $objPhpExcel->getActiveSheet()->toArray(null, true, true, true, true);
//        dump($excelsheet);
        $cc = count($excelsheet);
        if (count($excelsheet) > 2) {
            for($i=3;$i<=$cc;$i++)
            {
                $data = array();
                $data['u_atpid']      = $this->makeGuid();
                $data['u_code']       = $excelsheet[$i]['B'];
                $data['u_status']     = '0';
                $data['u_isforce']    = '1';
                $data['u_stockman']   = I('session.username', '');
                $data['u_dbtime']     = date('Y-m-d H:i:s', time());
                M('usbkey')->add($data);
                $this->recordLog('add', 'usbkey', "Excel上传,域账户：".$data['u_account']."；姓名：".$data['u_name']."；编号：".$data['u_code']."；电话：".$data['u_phone']."；姓：".$data['u_firstname']."；名：".$data['u_secondname']."；状态：库存；是否强制USBKey：否；",'usbkey',$data['u_atpid']);
            }
        }
        echo $this->makeStandResult(0,'');
    }
    public function submitdistribute()
    {
        $filename = $_FILES['updataexcel2007']['tmp_name'];
        vendor("PHPExcel.PHPExcel");
        $objPhpExcel = \PHPExcel_IOFactory::load($filename);
        $excelsheet = $objPhpExcel->getActiveSheet()->toArray(null, true, true, true, true);
//        dump($excelsheet);
        $cc = count($excelsheet);
        if (count($excelsheet) > 2) {
            for($i=3;$i<=$cc;$i++)
            {
                $data                   = array();
                $ucode                  = trim(strtolower($excelsheet[$i]['B']));
                if(empty($ucode)) continue;

                $account                = trim(strtolower($excelsheet[$i]['C']));
                $data['u_idcard']       = trim($excelsheet[$i]['D']);
                $data['u_phone']        = trim($excelsheet[$i]['E']);
                $data['u_firstname']    = trim(strtoupper($excelsheet[$i]['F']));
                $data['u_secondname']   = trim(strtolower($excelsheet[$i]['G']));
                $data['u_status']       = '4'; // 状态为 未发
                $data['u_isforce']      = '1'; // 强制为 未强制
                $data['u_atpstatus']    = null; // 状态为空
                $data['u_atplastmodifyuser'] = I('session.username', '');
                $data['u_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                $data['u_assignman']    = I('session.username', '');
                $data['u_code']         = $ucode;
                $data['u_account']      = $account;
                $userInfo               = D('Person')->getPersonDept($account);
                if(!empty($userInfo)){
                    $data['u_name']   = $userInfo['realusername'];
                    $data['u_depart'] = $userInfo['departname'];
                    $data['u_office'] = $userInfo['officename'];
                }
                $data['u_result']       = null;
                $data['u_reason']       = null;
                $data['u_linshireason'] = null;
                $data['u_gender']       = null;
                $data['u_solveman']     = null;
                $data['u_solvetime']    = null;

                $hasUsb = D('Usbkey')->getInfoByUsbCode($ucode);
                if(empty($hasUsb)){ //新增分配
                    $data['u_atpid']      = $this->makeGuid();
                    $data['u_stockman']   = I('session.username', '');
                    $data['u_dbtime']     = date('Y-m-d H:i:s', time());
                    M('usbkey')->add($data);
                    $this->recordLog('add', 'usbkey', "Excel批量分配-新增,域账户：".$data['u_account']."；姓名：".$data['u_name']."；编号：".$data['u_code']."；电话：".$data['u_phone']."；姓：".$data['u_firstname']."；名：".$data['u_secondname']."；状态：未发；是否强制USBKey：否；",'usbkey',$data['u_atpid']);
                }else{ //修改分配
                    M('usbkey')->where("u_atpid = '".$hasUsb['u_atpid']."'")->save($data);
                    $this->recordLog('update', 'usbkey', "Excel批量分配-修改,域账户：".$data['u_account']."；姓名：".$data['u_name']."；编号：".$data['u_code']."；电话：".$data['u_phone']."；姓：".$data['u_firstname']."；名：".$data['u_secondname']."；状态：未发；是否强制USBKey：否；",'usbkey',$hasUsb['u_atpid']);
                }
            }
        }
        echo $this->makeStandResult(0,'');
    }
    public function exp()
    {
        try {
            $ids = $_POST['ids'];
            $array = explode(',', $ids);
            if ($array && count($array) > 0) {
                $Model = M("usbkey");
                foreach ($array as $id) {
                    $data = $Model->where("u_atpid='%s'", $id)->find();
                    $data['u_status'] = '1';
                    $Model->where("u_atpid='%s'", $id)->save($data);
                }
            }

        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
}