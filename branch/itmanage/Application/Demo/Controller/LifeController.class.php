<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class LifeController extends BaseController {

    public function index(){
        $this->assignsbtype();
        $this->display();
    }

    public function Cycle(){
        $relationInfo = D('Dictionary')->getRelations();
        $this->assign('relationInfo',$relationInfo);
        $this->display();
    }
    public function getLogdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
//        $id = $queryparam['id'];
        $Model = M();
        $sql_select="
                select * from  it_change  du";
        $sql_count="
                select
                    count(1) c
               from  it_change du";
        if ("" != $queryparam['sbbm']){
            $searchcontent = trim($queryparam['sbbm']);
            $sql_select = $this->buildSql($sql_select,"du.bg_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.bg_devicecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['ipaddess']){
            $searchcontent = trim($queryparam['ipaddess']);
            $sql_select = $this->buildSql($sql_select,"du.bg_ip like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.bg_ip like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['macaddess']){
            $searchcontent = trim($queryparam['macaddess']);
            $sql_select = $this->buildSql($sql_select,"du.bg_mac like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.bg_mac like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['sbtype']){
            $searchcontent = trim($queryparam['sbtype']);
            $sql_select = $this->buildSql($sql_select,"du.bg_maintype ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.bg_maintype ='".$searchcontent."'");
        }
        if ("" != $queryparam['userman']){
            $searchcontent = trim($queryparam['userman']);
            $sql_select = $this->buildSql($sql_select,"du.bg_atplastmodifyuser ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.bg_atplastmodifyuser ='".$searchcontent."'");
        }
        if ("" != $queryparam['dutyman']){
            $searchcontent = trim($queryparam['dutyman']);
            $sql_select = $this->buildSql($sql_select,"du.bg_belongpersonid ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.bg_belongpersonid ='".$searchcontent."'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.bg_atpid  asc  ";
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
    public function getdutydept(){
        $Model = M('person');
        $dutyman= $_POST['dutyman'];
        $orgid = $Model->where("id='%s'",$dutyman)->getField('orgid');
        $sbtypelist = M('depart')->where("id='%s'",$orgid)->field('fullname,id')->select();
        echo json_encode($sbtypelist);
    }
    public function exportExcel(){
        vendor("PHPExcel.PHPExcel");
        $excel = new \PHPExcel();
        $Model = M();
        $sql_select = "select d.d_dictname,zd_devicecode,zd_seqno,zd_ipaddress,zd_macaddress,zd_name,zd_status from it_terminal zd left join
it_dictionary d on zd.zd_type=d.d_atpid where zd.zd_atpstatus is null";

        $sql_count = "select count(1) from it_terminal zd left join
it_dictionary d on zd.zd_type=d.d_atpid where zd.zd_atpstatus is null";
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
        $tableheader = array('设备类型', '设备编码', '出厂编号', 'IP地址', 'MAC地址', '设备名称', '设备状态');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        $total =intval($Count[0][count])+1;
        //$total =30;
        for ($i = 2; $i <= $total; $i++) {
            $j = 0;
            foreach ($Result[$i-2] as $key => $value) {
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
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="expexcel.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }


    public function assignsbtype(){
        $Model = M('dictionary');
        $data =$Model->where("d_belongtype='%s'","资产类型")->field('d_dictname,d_atpid')->select();
        $this->assign('ds_sbtype',$data);

    }
    public function getbuildingname($id){
        $building =M('dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getareaname($id){
        $building =M('dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getusername($id){
        $building =M('person')->where("id='%s'",$id)->field('realusername')->find();
        return $building['realusername'];
    }

    public function getlogsdataforview(){
        $queryparam = json_decode(file_get_contents("php://input"), true); //对json字符串进行编码，返回array
        $Results    = $this->getlogsdata($queryparam);
        $limit      = $queryparam['limit'];
        $offset     = empty($queryparam['offset']) ? 1:$queryparam['offset'];

        if(!empty($Results)){
            $list   = array_slice($Results, $offset - 1, $limit);
            foreach($list as $key=>$val){
                $mainid = $val['l_mainid'];
                if(strpos($mainid,'WY/BM') !== false){
                    $sql_select = "select ta.t_biaodanurl,ta.t_status,ta.t_nameid,ta.t_biaodanname from it_task ta where ta.t_rwid = '".$mainid."'";
                    $taskinfo   = M()->query($sql_select);
                    if($taskinfo){
                        $list[$key] = array_merge($list[$key],$taskinfo[0]);
                    }
                }else{
                    continue;
                }
            }
            $result = array(
                'total' => count($Results),
                'rows' => $list
            );
        }else{
            $result = array(
                'total' => 0,
                'rows' => array()
            );
        }
        echo json_encode($result);
    }

    public function getlogsdata($queryparam)
    {
        ini_set('memory_limit','512M');
        $Model      = M();
        $module     = trim($queryparam['modulename']);
        $ipaddress  = trim($queryparam['ipaddress']);
        $macaddress = strtoupper(trim($queryparam['macaddress']));
        $applyid    = trim($queryparam['applyid']);
        $keycode    = strtolower(trim($queryparam['keycode']));
        $taskid     = strtoupper(trim($queryparam['taskid']));
        $relationid = strtoupper(trim($queryparam['relationid']));
        $acctype    = trim($queryparam['acctype']);
        $opuserid=strtolower(trim($queryparam['opuserid']));
        $detail=trim($queryparam['$detail']);
        $operationtype=trim($queryparam['$optype']);

        $mainid     = [];
        $result     = [];
        $Results    = [];
        $mark       = 0; // 查询数据标志位

        if(($module == 'IP') && !empty($ipaddress)){
            //ipaddress表主键id
            $sql_ip   = "SELECT ip_atpid FROM it_ipaddress ip";
            $sql_ip   = $this->buildSql($sql_ip, "(ip.ip_start like '%".$ipaddress."%' or ip.ip_end like '%".$ipaddress."%')");
            $res_ip   = $Model->query($sql_ip);
            foreach($res_ip as $key=>$val){
                $mainid['ipaddress'][] = $val['ip_atpid'];
            }
            //ipbase表主键id
            $sql_ipb  = "SELECT ipb_atpid FROM it_ipbase ipb";
            $sql_ipb  = $this->buildSql($sql_ipb, "ipb.ipb_address like '%".$ipaddress."%'");
            $res_ipb  = $Model->query($sql_ipb);
            foreach($res_ipb as $key=>$val){
                $mainid['ipbase'][] = $val['ipb_atpid'];
            }
            $mark     = 1;
        }else if(($module == 'netdevice') && (!empty($ipaddress) || !empty($applyid))){
            //netdevice表主键id
            $sql_n   = "SELECT netdevice_atpid FROM it_netdevice n";
            $sql_n   = $this->buildSql($sql_n, "n.netdevice_atpstatus is null");
            if(!empty($ipaddress)){
                $sql_n   = $this->buildSql($sql_n, "n.netdevice_ipaddress like '%".$ipaddress."%'");
            }
            if(!empty($applyid)){
                $sql_n  = $this->buildSql($sql_n, "n.netdevice_dutyman = '".$applyid."'");
            }
            $res_n   = $Model->query($sql_n);
            foreach($res_n as $key=>$val){
                $mainid['netdevice'][] = $val['netdevice_atpid'];
            }
        }else if(($module == 'terminal') && (!empty($ipaddress) || !empty($macaddress) || !empty($applyid))){
            //terminal表主键id
            $sql_t  = "SELECT zd_atpid,zd_mainid FROM it_terminal t";
            if(!empty($ipaddress)){
                $sql_t  = $this->buildSql($sql_t, "t.zd_ipaddress like '%".$ipaddress."%'");
            }
            if(!empty($macaddress)){
                $sql_t  = $this->buildSql($sql_t, "t.zd_macaddress like '%".$macaddress."%'");
            }
            if(!empty($applyid)){
                $sql_t  = $this->buildSql($sql_t, "t.zd_useman = '".$applyid."'");
            }
            $res_t     = $Model->query($sql_t);
            $res_atpid  = $this->getAtpidByMainid($res_t);
            $res_atpids = $this->getAtpidByMainids($res_t);
            $res_atpid  = $res_atpid + $res_atpids;
            foreach($res_atpid as $key=>$val){
                $mainid['terminal'][] = $val;
            }
            $mark     = 1;
        }else if(($module == 'usbkey') && (!empty($keycode) || !empty($applyid))){
            //usbkey表主键id
            $sql_u  = "SELECT u_atpid FROM it_usbkey u";
            $sql_u  = $this->buildSql($sql_u, "u.u_code like '%".$keycode."%'");
            if(!empty($applyid)){
                $sql_u  = $this->buildSql($sql_u, "u.u_account = '".$applyid."'");
            }
            $res_u  = $Model->query($sql_u);
            foreach($res_u as $key=>$val){
                $mainid['usbkey'][] = $val['u_atpid'];
            }
            $mark     = 1;
        }else if((!empty($taskid) || !empty($applyid)) && ($module == 'gdbd')){
            //task表主键id
            $sql_gd  = "SELECT t_rwid FROM it_task gd";
            $sql_gd  = $this->buildSql($sql_gd, "gd.t_rwid like '%".$taskid."%'");
            if(!empty($applyid)){
                $sql_gd  = $this->buildSql($sql_gd, "gd.t_nameid = '".$applyid."'");
            }
            $res_gd = $Model->query($sql_gd);
            foreach($res_gd as $key=>$val){
                $mainid['gdbd'][] = $val['t_rwid'];
            }

            //work表主键id
            $sql_bd  = "SELECT rw_workid FROM it_work bd";
            $sql_bd  = $this->buildSql($sql_bd, "bd.rw_workid like '%".$taskid."%'");
            if(!empty($applyid)){
                $sql_bd  = $this->buildSql($sql_bd, "bd.rw_atpcreateuser = '".$applyid."'");
            }
            $res_bd = $Model->query($sql_bd);
            foreach($res_bd as $key=>$val){
                $mainid['gdbd'][] = $val['rw_workid'];
            }

            //tongxinzhanjob表主键id
            $sql_txz  = "SELECT txz_atpid,txz_tongxinid FROM it_tongxinzhanjob txz";
            $sql_txz  = $this->buildSql($sql_txz, "(txz.txz_tongxinid like '%".$taskid."%' or txz.txz_taskid like '%".$taskid."%')");
            if(!empty($applyid)){
                $sql_txz  = $this->buildSql($sql_txz, "txz.txz_user = '".$applyid."'");
            }
//            echo $sql_txz;die;
            $res_txz  = $Model->query($sql_txz);
            foreach($res_txz as $key=>$val){
                $mainid['gdbd'][] = $val['txz_atpid'];
                $mainid['gdbd'][] = $val['txz_tongxinid'];
            }
            $mark     = 1;
        }else if(($module == 'relation') && (!empty($ipaddress) || !empty($macaddress) || !empty($relationid))){
            //relation表主键id
            if(!empty($ipaddress) || !empty($macaddress)){
                $sql_t  = "SELECT zd_atpid FROM it_terminal t";
                if(!empty($ipaddress)){
                    $sql_t  = $this->buildSql($sql_t, "t.zd_ipaddress like '%".$ipaddress."%'");
                }
                if(!empty($macaddress)){
                    $sql_t  = $this->buildSql($sql_t, "t.zd_macaddress like '%".$macaddress."%'");
                }
                $res_t     = $Model->query($sql_t);
                if(!empty($res_t)){
                    $res_ts = [];
                    $zd_ids = '';
                    foreach($res_t as $k=>$v){
                        $res_ts[] = $v['zd_atpid'];
                    }
                    $zd_ids = implode("','",$res_ts);
                    $zd_ids = "'".$zd_ids."'";
                }
            }
            $sql_r  = "SELECT rl_atpid FROM it_relation r";
            if(!empty($relationid)){
                $sql_r  = $this->buildSql($sql_r, "r.rl_relation = '".$relationid."'");
            }
            if(!empty($zd_ids)){
                $sql_r  = $this->buildSql($sql_r, "r.rl_cmainid in (".$zd_ids.")");
            }
            $res_atpid     = $Model->query($sql_r);
            foreach($res_atpid as $key=>$val){
                $mainid['relation'][] = $val['rl_atpid'];
            }
            $mark     = 1;
        }
        //maidid 唯一性
        foreach($mainid as $key=>$val){
            $tmp = array_unique($val);
            $mainid[$key] = $tmp;
        }
//        print_r($mainid);die;

        if(empty($mainid) && ($module != 'AD') && ($mark != 1)){
            $sql_select = "select * from it_log l";
            $sql_select = $this->ConcatSql($queryparam,$sql_select);
//          echo $sql_select;die;
            $result[]   = $Model->query($sql_select);
        }else if($module == 'AD'){
            $sql_select = "select * from it_log l";
            if(!empty($keycode)) {
                $sql_select = $this->buildSql($sql_select, "l.l_detail like '%".$keycode ."%'");
            }
            if(!empty($applyid)){
                $sql_select  = $this->buildSql($sql_select, "l.l_detail like '%".$applyid."%'");
            }
            $sql_select = $this->ConcatSql($queryparam,$sql_select);

            $result[]   = $Model->query($sql_select);
        }else{
            if(!empty($mainid['ipaddress'])){
                $ip_mainid  = implode("','",$mainid['ipaddress']);
                $sql_select = "select l.*,(ip.ip_start || '-' || ip.ip_end) ipaddress from it_log l,it_ipaddress ip where ip.ip_atpid = l.l_mainid and l.l_mainid in ('".$ip_mainid."') ";
                $sql_select = $this->ConcatSql($queryparam,$sql_select);
//                print_r($sql_select);die;
                $result[]   = $Model->query($sql_select);
            }
            if(!empty($mainid['netdevice'])){
                $netdevice_mainid  = implode("','",$mainid['netdevice']);
                $sql_select        = "select l.*,n.netdevice_ipaddress ipaddress from it_log l,it_netdevice n where n.netdevice_atpid = l.l_mainid and l.l_mainid in ('".$netdevice_mainid."') ";
                $sql_select = $this->ConcatSql($queryparam,$sql_select);
//                print_r($sql_select);die;
                $result[]   = $Model->query($sql_select);
            }
            if(!empty($mainid['ipbase'])){
                $ipb_mainid  = implode("','",$mainid['ipbase']);
                $sql_select  = "select l.*,ipb.ipb_address ipaddress from it_log l,it_ipbase ipb where ipb.ipb_atpid = l.l_mainid and l.l_mainid in ('".$ipb_mainid."') ";
                $sql_select  = $this->ConcatSql($queryparam,$sql_select);
                $result[]    = $Model->query($sql_select);
            }
            if(!empty($mainid['gdbd'])){
                $bd_mainid   = implode("','",$mainid['gdbd']);
                $sql_select  = "select * from it_log l where l.l_mainid in ('".$bd_mainid."') ";
                $sql_select  = $this->ConcatSql($queryparam,$sql_select);
//                echo $sql_select;die;
                $result[]    = $Model->query($sql_select);
            }

            if(!empty($mainid['usbkey'])){
                $u_mainid   = implode("','",$mainid['usbkey']);
                $sql_select = "select l.*,u.u_code keycode from it_log l,it_usbkey u where u.u_atpid = l.l_mainid and l.l_mainid in ('".$u_mainid."') ";
                $sql_select = $this->ConcatSql($queryparam,$sql_select);
//                echo $sql_select;die;
                $result[]   = $Model->query($sql_select);
            }
            if(!empty($mainid['terminal'])){
                $t_mainid   = implode("','",$mainid['terminal']);
                $sql_select = "select l.*,t.zd_ipaddress ipaddress,t.zd_macaddress macaddress from it_log l,it_terminal t where t.zd_atpid = l.l_mainid and l.l_mainid in ('".$t_mainid."') ";
                $sql_select = $this->ConcatSql($queryparam,$sql_select);
//                echo $sql_select;die;
                $result[]   = $Model->query($sql_select);
            }
            if(!empty($mainid['relation'])){
                $r_mainid   = implode("','",$mainid['relation']);
                $sql_select = "select l.*,r.rl_relation relation,t.zd_ipaddress ipaddress,t.zd_macaddress macaddress from it_log l,it_relation r,it_terminal t  where r.rl_atpid = l.l_mainid and r.rl_cmainid = t.zd_atpid and l.l_mainid in ('".$r_mainid."') ";
                $sql_select = $this->ConcatSql($queryparam,$sql_select);
//                echo $sql_select;die;
                $result[]   = $Model->query($sql_select);
            }
        }
        foreach($result as $key=>$val){
            if(!empty($val)){
                $Results = array_merge($Results,$val);
            }
        }
//                print_r($Results);die;
if($module=='account')
{
    $logmodel= M('log');
    $servermodel= M('server');
    $applicationmodel= M('application');
    $secproductsmodel= M('secproducts');
    $logwhere=[];
    $logwhere[0]['l_modulename']=['eq','account'];
    if(!empty($opuserid))
    {
        $logwhere[0]['l_opuserid']=['like',"%$opuserid%"];
    }
    if(!empty($detail))
    {
        $logwhere[0]['l_detail']=['like',"%$detail%"];
    }
    if(!empty($operationtype))
    {
        $logwhere[0]['l_optype']=['eq',$operationtype];
    }
    switch($acctype)
    {
        case 'server':
            $logwhere[0]['l_tablename']=['eq','server'];
            if(!empty($ipaddress))
            {

                $serverids= $servermodel->field('server_atpid')->where("server_ip like '%s'",$ipaddress)->select();
                $serverids=removeArrKey($serverids,'server_atpid');
                $serverids = "'".implode("','", $serverids)."'";
                $logwhere[0]['l_mainid']=['exp',"in $serverids"];
                $Results= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.server_ip ipaddress')->where($logwhere)->join('it_server t on t.server_atpid=it_log.l_mainid')->select();
            }
            else
            {
                $Results1= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.server_ip ipaddress')->where($logwhere)->join('it_server t on t.server_atpid=it_log.l_mainid')->select();
                $logwhere[0]['l_mainid']=['exp','is null'];
                $Results2= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename')->where($logwhere)->select();
                $Results=array_merge($Results1,$Results2);
            }
            break;
        case 'secproducts':
            $logwhere[0]['l_tablename']=['eq','secproducts'];
            if(!empty($ipaddress))
            {
                $secproductsids= $secproductsmodel->field('secproducts_atpid')->where("it_secproducts_ip like '%s'",$ipaddress)->select();
                $secproductsids=removeArrKey($secproductsids,'secproducts_atpid');
                $secproductsids = "'".implode("','", $secproductsids)."'";
                $logwhere[0]['l_mainid']=['exp',"in $secproductsids"];
                $Results= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.secproducts_ip ipaddress')->where($logwhere)->join('it_secproducts t on t.secproducts_atpid=it_log.l_mainid')->select();
            }
            else
            {
                $Results1= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.secproducts_ip ipaddress')->where($logwhere)->join('it_secproducts t on t.secproducts_atpid=it_log.l_mainid')->select();
                $logwhere[0]['l_mainid']=['exp','is null'];
                $Results2= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename')->where($logwhere)->select();
                $Results=array_merge($Results1,$Results2);
            }
            break;
        case 'application':
            $logwhere[0]['l_tablename']=['eq','application'];
            if(!empty($ipaddress))
            {
                $applicationids= $applicationmodel->field('application_atpid')->where("application_host like '%s'",$ipaddress)->select();
                $applicationids=removeArrKey($applicationids,'application_atpid');
                $applicationids = "'".implode("','", $applicationids)."'";
                $logwhere[0]['l_mainid']=['exp',"in $applicationids"];
                $Results= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.application_host ipaddress')->where($logwhere)->join('it_application t on t.application_atpid=it_log.l_mainid')->select();
            }
            else
            {
                $Results1= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.application_host ipaddress')->where($logwhere)->join('it_application t on t.application_atpid=it_log.l_mainid')->select();
                $logwhere[0]['l_mainid']=['exp','is null'];
                $Results2= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename')->where($logwhere)->select();
                $Results=array_merge($Results1,$Results2);
            }
            break;
        default:
            if(!empty($ipaddress))
            {
                $Result1= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.server_ip ipaddress')->where($logwhere)->join('it_server t on t.server_atpid=it_log.l_mainid')->select();
                $Result2= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.secproducts_ip ipaddress')->where($logwhere)->join('it_secproducts t on t.secproducts_atpid=it_log.l_mainid')->select();
                $Result3= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.application_host ipaddress')->where($logwhere)->join('it_application t on t.application_atpid=it_log.l_mainid')->select();
                $Results=array_merge($Result1,$Result2,$Result3);
            }
            else
            {
                $Result1= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.server_ip ipaddress')->where($logwhere)->join('it_server t on t.server_atpid=it_log.l_mainid')->select();
                $Result2= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.secproducts_ip ipaddress')->where($logwhere)->join('it_secproducts t on t.secproducts_atpid=it_log.l_mainid')->select();
                $Result3= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename,t.application_host ipaddress')->where($logwhere)->join('it_application t on t.application_atpid=it_log.l_mainid')->select();
                $logwhere[0]['l_mainid']=['exp','is null'];
                $logwhere[0]['l_tablename']=['eq','server'];
                $Result4= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename')->where($logwhere)->select();
                $logwhere[0]['l_tablename']=['eq','secproducts'];
                $Result5= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename')->where($logwhere)->select();
                $logwhere[0]['l_tablename']=['eq','application'];
                $Result6= $logmodel->field('l_atpid,l_optime,l_opusername,l_detail,l_modulename')->where($logwhere)->select();
                $Results=array_merge($Result1,$Result2,$Result3,$Result4,$Result5,$Result6);
            }
            //$logwhere['l_mainid']=['exp','is null'];
            break;
    }
}

        if(!empty($Results)){
            foreach($Results as $key=>$value){
                $optype = $value['l_optype'];
                $module = $value['l_modulename'];
                switch($optype){
                    case 'add':$Results[$key]['optypename']    = '新增';break;
                    case 'del':$Results[$key]['optypename']    = '删除';break;
                    case 'delete':$Results[$key]['optypename'] = '删除';break;
                    case 'update':$Results[$key]['optypename'] = '修改';break;
                    case 'import':$Results[$key]['optypename'] = '导入';break;
                    case 'export':$Results[$key]['optypename'] = '导出';break;
                    case 'print':$Results[$key]['optypename']  = '打印';break;
                    case 'config':$Results[$key]['optypename'] = '交换机配置';break;
                    default:$Results[$key]['optypename']       = '';break;
                }
                switch($module){
                    case 'usbkey':$Results[$key]['modulename']    = 'USBKey';break;
                    case 'txz':$Results[$key]['modulename']       = '通信站';break;
                    case 'terminal':$Results[$key]['modulename']  = '资产管理';break;
                    case 'netdevice':$Results[$key]['modulename'] = '交换机台账';break;
                    case 'model':$Results[$key]['modulename']     = '交换机模板编辑';break;
                    case 'gongdan':$Results[$key]['modulename']   = '工单';break;
                    case 'biaodan':$Results[$key]['modulename']   = '表单';break;
                    case 'dict':$Results[$key]['modulename']      = '数据字典';break;
                    case 'account':$Results[$key]['modulename']      = '台账维护';break;
                    default:$Results[$key]['modulename']          = $module;break;
                }
                if(!empty($value['relation'])){
                    $relationInfo = D('dictionary')->getRelations(1);
                    $relation = strtoupper(trim($value['relation']));
                    $Results[$key]['relation'] = $relationInfo[$relation];
                }
            }
        }
        return $Results;
    }

    public function getloghydataforview()
    {
        $queryparam = json_decode(file_get_contents("php://input"), true);

        $name        = trim($queryparam['name']);       // 设备名称
        $devicecode  = trim($queryparam['devicecode']); // 设备编码
        $ipaddress   = trim($queryparam['ipaddress']);
        $macaddress  = trim($queryparam['macaddress']);
        $dutyman     = trim($queryparam['dutyman']);
        $status      = trim($queryparam['status']);
        $factoryname = trim($queryparam['factoryname']);
        $modelname   = trim($queryparam['modelname']);
        $opuserid    = trim($queryparam['opuserid']);
        $detail      = trim($queryparam['detail']);
        $optype      = trim($queryparam['optype']);

        $Model = M();
        $sql_select="
                select * from it_log_hy l left join it_terminal_hy t on t.zd_atpid=l.l_mainid";
        $sql_count="
                select count(*) c from it_log_hy l left join it_terminal_hy t on t.zd_atpid=l.l_mainid";
        if ($devicecode){
            $sql_select = $this->buildSql($sql_select,"t.zd_devicecode like '%".$devicecode."%'");
            $sql_count = $this->buildSql($sql_count,"t.zd_devicecode like '%".$devicecode."%'");
        }
        if ($ipaddress){
            $sql_select = $this->buildSql($sql_select,"t.zd_ipaddress like '%".$ipaddress."%'");
            $sql_count = $this->buildSql($sql_count,"t.zd_ipaddress like '%".$ipaddress."%'");
        }
        if ($macaddress){
            $searchcontentupper = strtoupper($macaddress);
            $sql_select = $this->buildSql($sql_select,"(t.zd_macaddress like '%".$macaddress."%' or upper(t.zd_macaddress) like '%".$searchcontentupper."%')");
            $sql_count = $this->buildSql($sql_count,"(t.zd_macaddress like '%".$macaddress."%' or upper(t.zd_macaddress) like '%".$searchcontentupper."%')");
        }
        if ($factoryname){
            $sql_select = $this->buildSql($sql_select,"t.zd_factoryname ='".$factoryname."'");
            $sql_count = $this->buildSql($sql_count,"t.zd_factoryname ='".$factoryname."'");
        }
        if ($modelname){
            $sql_select = $this->buildSql($sql_select,"t.zd_modelnumber ='".$modelname."'");
            $sql_count = $this->buildSql($sql_count,"t.zd_modelnumber ='".$modelname."'");
        }
        if ($dutyman){
            $sql_select = $this->buildSql($sql_select,"t.zd_dutyman like '%".$dutyman."%'");
            $sql_count = $this->buildSql($sql_count,"t.zd_dutyman like '%".$dutyman."%'");
        }
        if ($status){
            $sql_select = $this->buildSql($sql_select,"t.zd_status ='".$status."'");
            $sql_count = $this->buildSql($sql_count,"t.zd_status ='".$status."'");
        }
        if ($name){
            $sql_select = $this->buildSql($sql_select,"t.zd_name like '%".$name."%'");
            $sql_count = $this->buildSql($sql_count,"t.zd_name like '%".$name."%'");
        }
        if ($opuserid){
            $sql_select = $this->buildSql($sql_select,"l.l_opuserid = '".$opuserid."'");
            $sql_count = $this->buildSql($sql_count,"l.l_opuserid = '".$opuserid."'");
        }
        if ($optype){
            $sql_select = $this->buildSql($sql_select,"l.l_optype = '".$optype."'");
            $sql_count = $this->buildSql($sql_count,"l.l_optype = '".$optype."'");
        }
        if ($detail){
            $sql_select = $this->buildSql($sql_select,"l.l_detail like '%".$detail."%'");
            $sql_count = $this->buildSql($sql_count,"l.l_detail like '%".$detail."%'");
        }

        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'l_optime'){
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ',t.zd_atplastmodifytime desc,t.zd_atpid desc ';
            }else{
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
            }
        } else {
            $sql_select = $sql_select . " order by t.zd_atpid  asc  ";
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
        if(!empty($Result)) {
            foreach ($Result as $key => &$value) {
                $l_detail             = $value['l_detail'];
                if(strlen($l_detail)>100){
                    $Result[$key]['l_detailbrief'] = substr($l_detail,0,100).'...';
                }else{
                    $Result[$key]['l_detailbrief'] = $l_detail;
                }
                $value['zd_factory'] = getDictname($value['zd_factoryname']);
                $value['zd_model'] = getDictname($value['zd_modelnumber']);
                if (!empty($value['zd_dutydeptname'])) {
                    $zd_dutydept = explode(',', $value['zd_dutydeptname']);
                    $zd_dutydepts = [];
                    foreach ($zd_dutydept as $dept) {
                        $zd_dutydepts[] = substr($dept, 0, strpos($dept, '-'));
                    }
                    $value['zd_dutydept'] = implode(',', $zd_dutydepts);
                }
                $optype = $value['l_optype'];
                switch($optype){
                    case 'add':$Result[$key]['optypename']    = '新增';break;
                    case 'del':$Result[$key]['optypename']    = '删除';break;
                    case 'delete':$Result[$key]['optypename'] = '删除';break;
                    case 'update':$Result[$key]['optypename'] = '修改';break;
                    case 'print':$Result[$key]['optypename']  = '打印';break;
                    case 'config':$Result[$key]['optypename'] = '交换机配置';break;
                    default:$Result[$key]['optypename']       = '';break;
                }
            }
        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    /**
     * 查找删除过的历史atpid记录信息(从后往前追)
     * @param $idInfo
     * @return array
     */
    public function getAtpidByMainid($idInfo){
//        print_r($idInfo);die;
        STATIC $result = [];
        $Model  = M();
        foreach($idInfo as $key=>$val){
            $result[] = $val['zd_atpid'];
            $sql = "SELECT zd_atpid,zd_mainid FROM it_terminal t WHERE zd_mainid = '".$val['zd_atpid']."'";
            $res = $Model->query($sql);
            if(!empty($res)){
                $this->getAtpidByMainid($res);
            }
        }
        return $result;
    }

    /**
     * 查找删除过的历史atpid记录信息(从前往后追)
     * @param $idInfo
     * @return array
     */
    public function getAtpidByMainids($idInfo){
        STATIC $result = [];
        $Model  = M();
        foreach($idInfo as $key=>$val){
            $result[] = $val['zd_atpid'];
            if(!empty($val['zd_mainid'])){
                $zd_mainid = $val['zd_mainid'];
                $sql = "SELECT zd_atpid,zd_mainid FROM it_terminal t WHERE zd_atpid = '".$zd_mainid."'";
                $res = $Model->query($sql);
                if(!empty($res)){
                    $mark = 0;
                    foreach($res as $k=>$v){
                        $result[] = $v['zd_atpid'];
                        if($v['zd_mainid'] != '') $mark = 1;
                    }
                    if($mark == 1){
                        $this->getAtpidByMainids($res);
                    }
                }
            }
        }
        return $result;
    }

    public function ConcatSql($queryparam,$sql_select)
    {
        if ('' != $queryparam['opuserid']) {
            $searchcontent = trim($queryparam['opuserid']);
            $sql_select = $this->buildSql($sql_select, " l.l_opuserid = 'hq\\" . $searchcontent . "' ");
        }
        if ('' != $queryparam['optype']) {
            $searchcontent = trim($queryparam['optype']);
            if($searchcontent == 'delete'){
                $sql_select = $this->buildSql($sql_select, "(l.l_optype = 'delete' or l.l_optype = 'del') ");
            }else{
                $sql_select = $this->buildSql($sql_select, "l.l_optype = '" . $searchcontent . "' ");
            }
        }
        if ('' != $queryparam['modulename']) {
            $searchcontent = trim($queryparam['modulename']);
            if($searchcontent == 'gdbd'){
                $sql_select = $this->buildSql($sql_select, " (l.L_MODULENAME in ('biaodan','gongdan','txz','bxd') or l.L_MODULENAME like '表单%' )");
            }else{
                $sql_select = $this->buildSql($sql_select, " l.L_MODULENAME = '" . $searchcontent . "' ");
            }
        }
        if ('' != $queryparam['detail']) {
            $searchcontent = trim($queryparam['detail']);
            $sql_select = $this->buildSql($sql_select, " l.L_DETAIL like '%" . $searchcontent . "%' ");
        }

        if ("l_optime" != $queryparam['sort']) {
            $searchcontent = trim($queryparam['sort']);
            if($queryparam['sort'] == 'optypename'){
                $searchcontent = 'l_optype';
            }else if($queryparam['sort'] == 'modulename'){
                $searchcontent = 'l_modulename';
            }
            $sql_select = $sql_select." order by ".$searchcontent.' '.$queryparam['order'].' ';
        }else {
            $sql_select = $sql_select . " order by l.l_optime desc nulls last";
        }
        return $sql_select;
    }

    function cycle_exp()
    {
        header("content-type:text/html;charset=utf-8");
        $queryparam = I('get.');
        $Result     = $this->getlogsdata($queryparam);
//        print_r($Result);die;

        $data = array();
        foreach($Result as $key=>$value){
            $data[$key][] = $key+1;
            $data[$key][] = $value['l_opusername'];
            $data[$key][] = $value['l_optime'];
            $data[$key][] = $value['optypename'];
            $data[$key][] = $value['ipaddress'];
            $data[$key][] = $value['macaddress'];
            $data[$key][] = $value['relation'];
            $data[$key][] = $value['keycode'];
            $data[$key][] = $value['taskid'];
            $data[$key][] = $value['modulename'];
            $data[$key][] = $value['l_detail'];
        }

        vendor("PHPExcel.PHPExcel");
        $excel = new \PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G','H','I','J','K');
        $tableheader = array('序号', '操作人', '操作时间', '操作类型','IP地址','MAC地址','关联关系','Key编号','任务单号','模块名称','操作内容');
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
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="expexcel.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
}