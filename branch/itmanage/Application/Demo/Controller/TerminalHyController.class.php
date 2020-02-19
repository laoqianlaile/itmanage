<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class TerminalHyController extends BaseController {

    public function index(){
        $Datas = D('Dictionary')->assignsbtype();
//        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }
    public function add(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }

    public function addrelation(){
        $id = $_GET['id'];
        $this->assign('relationid',$id);
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assignrelation();
        $this->display();
    }
    public function edit(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("terminal_hy");
            $data = $Model->where("zd_atpid='%s'", array($id))->find();
//            print_r($data);die;
            if ($data) {
                $this->assign('data', $data);

            }
            $depid = $data['zd_dutydeptid'];
            $userid = $data['zd_useman'];
            $dutyuserid = $data['zd_dutyman'];
            if($depid){
                $data2 = M('depart')->where("id='%s'", $depid)->select();
                $this->assign('dutydeptname',$data2);
            }
            $data3 = M('person')->where("username='%s'", $userid)->select();
            $this->assign('username',$data3);
            $data4 = M('person')->where("username='%s'", $dutyuserid )->select();
            $this->assign('dutymanname',$data4);

        }
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display('edit');
    }
    public function del()
    {
        try {
            $ids = I('post.ids');
            $array = explode(',', $ids);
            if ($array && count($array) > 0) {
                $Model = M("terminal_hy");
                foreach ($array as $id) {
                    $data    = $Model->where("zd_atpid='%s'", $id)->find();
                    $related = M('relation')->where("(rl_cmainid='".$id."' or rl_rmainid = '".$id."') and rl_atpstatus is null")->find();
                    if(!empty($related)){
                        echo 'error';
                        return false;
                    }
                    $data['zd_atpstatus'] = 'DEL';
                    $data['zd_atplastmodifytime'] = date('Y-m-d H:i:s',time());
                    $data['zd_atplastmodifyuser'] = I('session.username','');
                    $Model->where("zd_atpid='%s'", $id)->save($data);
                   //修改ip地址使用状态
//                    if(!empty($data['zd_ipaddress'])){
//                        $ipbid  = $data['zd_ipaddress'];
//                        $status = null;
//                        D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
//                    }
                    $this->recordLoghy('delete', 'terminal','删除资产信息;ip地址:'.$data['zd_ipaddress'].';mac地址:'.$data['zd_macaddress'].';责任人:'.$data['zd_dutyman'].';责任部门:'.$data['zd_dutydeptname'].';','terminal',$id);
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
                select * from it_terminal_hy du";
        $sql_count="
                select
                    count(1) c
                from it_terminal_hy du";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.zd_atpstatus is null");


        if ("" != $queryparam['ipaddess']){
            $searchcontent = trim($queryparam['ipaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_ipaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_ipaddress like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['area']){
            $searchcontent = trim($queryparam['area']);
            $sql_select = $this->buildSql($sql_select,"du.zd_area ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_area ='".$searchcontent."'");
        }
        if ("" != $queryparam['building']){
            $searchcontent = trim($queryparam['building']);
            $sql_select = $this->buildSql($sql_select,"du.zd_belongfloor like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_belongfloor like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['secretlevel']){
            $searchcontent = trim($queryparam['secretlevel']);
            $sql_select = $this->buildSql($sql_select,"du.zd_secretlevel ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_secretlevel ='".$searchcontent."'");
        }
        if ("" != $queryparam['terminalname']){
            $searchcontent = trim($queryparam['terminalname']);
            $sql_select = $this->buildSql($sql_select,"du.zd_name like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_name like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'zd_building') $queryparam['sort'] = 'zd_belongfloor';
            if($queryparam['sort'] == 'zd_atpsort'){
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ',du.zd_atplastmodifytime desc,du.zd_atpid desc ';
            }else{
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
            }
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
       // echo $sql_select;die;
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
//        foreach($Result as $key=> &$value){
//            $value['zd_area']     = getDictname($value['zd_area']);
//            $value['zd_building'] = getDictname($value['zd_belongfloor']);
//            $value['zd_factory']  = getDictname($value['zd_factoryname']);
//            $value['zd_model']    = getDictname($value['zd_modelnumber']);
//            if(!empty($value['zd_dutydeptname'])){
//                $zd_dutydept  = explode(',',$value['zd_dutydeptname']);
//                $zd_dutydepts = [];
//                foreach($zd_dutydept as $dept){
//                    $zd_dutydepts[] = substr($dept,0,strpos($dept,'-'));
//                }
//                $value['zd_dutydept'] = implode(',',$zd_dutydepts);
//            }
//        }

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    public function getterminaldata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype'
                 ";
        $sql_count="
                select
                    count(1) c
                from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype'";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.zd_atpstatus is null");


        if ("" != $queryparam['rlsbbm']){
            $searchcontent = trim($queryparam['rlsbbm']);
            $sql_select = $this->buildSql($sql_select,"du.zd_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_devicecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['rlipaddess']){
            $searchcontent = trim($queryparam['rlipaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_ipaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_ipaddress like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['rlmacaddess']){
            $searchcontent = trim($queryparam['rlmacaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_macaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_macaddress like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['rlsbtype']){
            $searchcontent = trim($queryparam['rlsbtype']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_type ='".$searchcontent."'");
        }
        if ('zd_atpsort' != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpsort asc,du.zd_macaddress asc nulls last ";
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
    public function getBinddata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $macupper = M('terminal')->where("zd_atpid='%s'",$id)->getField('zd_macaddress');
        $mac = strtolower($macupper);
        $Result = M('switchnewinfoT')->where("sw_macaddress='%s'",$mac)->select();
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function getBindolddata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $macupper = M('terminal')->where("zd_atpid='%s'",$id)->getField('zd_macaddress');
        $mac = strtolower($macupper);
        $Result = M('switcholdinfoT')->where("sw_macaddress='%s'",$mac)->order('sw_atplastmodifydatetime asc nulls last')->select();
        $newC   = M('switchnewinfoT')->where("sw_macaddress='%s'",$mac)->count();
        $Result = array_slice($Result,$newC);
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function getLogdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $Model = M();
        $sql_select="
                select l.*,t.*,d.d_dictname d_dictname from it_log_hy l,it_terminal_hy t,it_dictionary d where l.l_mainid = t.zd_atpid and t.zd_type=d.d_atpid and  d.d_belongtype='equipmenttype' and d.d_dictype='terminal' and l.l_tablename like '%terminal_hy' and l.l_mainid ='".$id."'";
        $Result = $Model->query($sql_select);
        foreach($Result as $key=>$value) {
            $optype = $value['l_optype'];
            switch ($optype) {
                case 'add':
                    $Result[$key]['optypename'] = '新增';
                    break;
                case 'del':
                    $Result[$key]['optypename'] = '删除';
                    break;
                case 'delete':
                    $Result[$key]['optypename'] = '删除';
                    break;
                case 'update':
                    $Result[$key]['optypename'] = '修改';
                    break;
                case 'print':
                    $Result[$key]['optypename'] = '打印';
                    break;
                default:
                    $Result[$key]['optypename'] = '';
                    break;
            }
        }
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }

    /**
     * Excel导入数据保存
     */
    public function submitimp()
    {
        ini_set('memory_limit','1024M');
        set_time_limit(0);
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('xls','xlsx','csv');
        $upload->rootPath = './Public/uploads/';
        $upload->savePath = '';
        $info = $upload->upload();
        if($upload->getError()){
            exit(makeStandResult(2,$upload->getError()));
        }
        $filename = './Public/uploads/' . $info["updataexcel2007"]["savepath"] . $info["updataexcel2007"]["savename"];

        if($info['updataexcel2007']['ext'] == 'csv'){
            $handle = fopen($filename,'r');
            $excelsheet = input_csv($handle);
        }else{
            vendor("PHPExcel.PHPExcel");

            $excelType = \PHPExcel_IOFactory::identify($filename);
            $excelReader = \PHPExcel_IOFactory::createReader($excelType);
            $phpexcel=$excelReader->load($filename);
            $excelsheet = $phpexcel->getActiveSheet()->toArray(null, true, true, true, true);
        }

//        dump($excelsheet);die;

        $column = Array("设备编码","设备名称","设备类型","厂家","型号","密级","出厂编号","部标编码","状态","IP地址","MAC地址","责任人","责任部门","地区","楼宇","房间号","启用日期","备注");
        $thead  = array_diff($column,$excelsheet[1]);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }

        $cc = count($excelsheet);
        if (count($excelsheet) > 1) {
            $hasIP    = [];
            $hasIPIds = [];
            $datas    = [];
            for($i=2;$i<=$cc;$i++)
            {
                $ipaddress = trim($excelsheet[$i]['J']);
                if(empty($ipaddress)) continue;
                $data = array();
                $data['zd_atpid']        = $this->makeGuid();
                $data['zd_devicecode']   = trim($excelsheet[$i]['A']); //设备编码
                $data['zd_name']         = trim($excelsheet[$i]['B']); //设备名称
                $data['zd_factoryname']  = trim($excelsheet[$i]['D']); //厂家
                $data['zd_modelnumber']  = trim($excelsheet[$i]['E']); //型号
                $data['zd_secretlevel']  = trim($excelsheet[$i]['F']); //密级
                $data['zd_seqno']        = trim($excelsheet[$i]['G']); //出厂编号
                $data['zd_anecode']      = trim($excelsheet[$i]['H']); //部标编码
                $data['zd_status']       = trim($excelsheet[$i]['I']); //状态
                $data['zd_ipaddress']    = trim($ipaddress);           //IP地址
                $data['zd_macaddress']   = trim($excelsheet[$i]['K']); //MAC地址
                $data['zd_dutymanname']  = trim($excelsheet[$i]['L']);
                $data['zd_dutydeptname'] = trim($excelsheet[$i]['M']); //责任部门
                $data['zd_area']         = trim($excelsheet[$i]['N']); //地区
                $data['zd_belongfloor']  = trim($excelsheet[$i]['O']); //楼宇
                $data['zd_roomno']       = trim($excelsheet[$i]['P']); //房间号
                $data['zd_startusetime'] = trim($excelsheet[$i]['Q']); //启用日期
                $data['zd_memo']         = trim($excelsheet[$i]['R']); //备注
                $data['zd_type']         = C('公用计算机');      //设备类型
                $data['zd_atpcreateuser'] = I('session.username', '');
                $data['zd_atpcreatetime'] = date('Y-m-d H:i:s', time());
                $isHas = M('terminal_hy')->field('zd_atpid,zd_ipaddress')->where("zd_ipaddress ='".$ipaddress."' and zd_atpstatus is null")->find();
                if($isHas){
                    $hasIP[]    = $ipaddress;
                    $hasIPIds[] = $isHas['zd_atpid'];
                    $data['zd_atplastmodifyuser'] = I('session.username', '');
                    $data['zd_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                    $datas[] = " select '".makeGuid()."','".$data['zd_devicecode']."','".$data['zd_name']."','".$data['zd_factoryname']."','".$data['zd_modelnumber']."','".$data['zd_secretlevel']."','".$data['zd_seqno']."','".$data['zd_anecode']."','".$data['zd_status']."','".$data['zd_ipaddress']."','".$data['zd_macaddress']."','".$data['zd_dutymanname']."','".$data['zd_dutydeptname']."','".$data['zd_area']."','".$data['zd_belongfloor']."','".$data['zd_roomno']."','".$data['zd_startusetime']."','".$data['zd_memo']."','".$data['zd_type']."','".$data['zd_atpcreateuser']."','".$data['zd_atpcreatetime']."','".$data['zd_atplastmodifyuser']."','".$data['zd_atplastmodifytime']."' from dual ";
                }else{
                    $datas[] = " select '".makeGuid()."','".$data['zd_devicecode']."','".$data['zd_name']."','".$data['zd_factoryname']."','".$data['zd_modelnumber']."','".$data['zd_secretlevel']."','".$data['zd_seqno']."','".$data['zd_anecode']."','".$data['zd_status']."','".$data['zd_ipaddress']."','".$data['zd_macaddress']."','".$data['zd_dutymanname']."','".$data['zd_dutydeptname']."','".$data['zd_area']."','".$data['zd_belongfloor']."','".$data['zd_roomno']."','".$data['zd_startusetime']."','".$data['zd_memo']."','".$data['zd_type']."','".$data['zd_atpcreateuser']."','".$data['zd_atpcreatetime']."','','' from dual ";
                }
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_terminal_hy (zd_atpid,zd_devicecode,zd_name,zd_factoryname,zd_modelnumber,zd_secretlevel,zd_seqno,zd_anecode,zd_status,zd_ipaddress,zd_macaddress,zd_dutymanname,zd_dutydeptname,zd_area,zd_belongfloor,zd_roomno,zd_startusetime,zd_memo,zd_type,zd_atpcreateuser,zd_atpcreatetime,zd_atplastmodifyuser,zd_atplastmodifytime) ".$datas;
            M('terminal_hy')->execute($sql);
        }
        $count = $cc-1;
        $content = "Excelp批量上传信息".$count."条";
        $this->recordLoghy('add', 'tmnhy',$content,'terminal_hy','');
        if(!empty($hasIP)){
            $hasIPIds = implode("','", $hasIPIds);
            $changeData = [];
            $changeData['zd_atpstatus'] = 'DEL';
            $changeData['zd_atplastmodifytime'] = I('session.username', '');
            $changeData['zd_atplastmodifyuser'] = date('Y-m-d H:i:s', time());
            M('terminal_hy')->where("zd_atpid in ('".$hasIPIds."')")->setField($changeData);
            $hasIP = implode(',',$hasIP);
            $this->recordLoghy('update', 'tmnhy',"导入Excel覆盖原信息，IP:".$hasIP,'terminal_hy','');
            exit($this->makeStandResult(0,'覆盖资产信息，IP地址：'.$hasIP));
        }else{
            exit($this->makeStandResult(0,'批量导入资产信息成功'));
        }
    }

    /**
     * it_terminal_hy表补充zd_dutydeptid和zd_dutydeptname
     */
    function insertTmnHYDutyDept($dutydeptid)
    {
        $fullnames = D('Depart')->getAllFullName();
        $dutydept = explode('-',$dutydeptid);
        foreach($fullnames as $key=>$item){
            if(!empty($item)){
                $sign = 1;
                $departs  = explode('-',$item);
                foreach($dutydept as $val){
                    if(!in_array($val,$departs)){
                        $sign = 0;
                        continue;
                    }
                }
                if($sign == 1) return $key;
            }
        }
    }

    public function getdutydept(){
        $Model = M('person');
        $dutyman= $_POST['dutyman'];
        $orgid = $Model->where("username='%s'",$dutyman)->getField('orgid');
        $sbtypelist = M('depart')->where("id='%s'",$orgid)->field('fullname,id')->select();
        echo json_encode($sbtypelist);
    }

    public function HYsubmit(){
        $Model = M('terminal_hy');
        $data = $Model->create();
        // if(!empty($data['zd_dutyman'])){
        //     $zd_dutymanname = [];
        //     $zd_dutyman     = $data['zd_dutyman'];
        //     foreach($zd_dutyman as $username){
        //         $zd_dutymanname[] = getRealusername($username);
        //     }
        //     $data['zd_dutyman']     = implode(',',$data['zd_dutyman']);
        //     $data['zd_dutymanname'] = implode(',',$zd_dutymanname);
        // }
        // if(!empty($data['zd_dutydeptid'])){
        //     $zd_dutydept    = [];
        //     $zd_dutydeptid  = $data['zd_dutydeptid'];
        //     foreach($zd_dutydeptid as $deptid){
        //         $zd_dutydept[] = getFullDeptname($deptid);
        //     }
        //     $data['zd_dutydeptid']   = implode(',',$data['zd_dutydeptid']);
        //     $data['zd_dutydeptname'] = implode(',',$zd_dutydept);
        // }
//        print_r($data);die;
        try{
            if(null==$data['zd_atpid']){
                $data['zd_atpid'] = $this->makeGuid();
                $data['zd_atpcreatetime'] = date('Y-m-d H:i:s', time());;
                $data['zd_atpcreateuser'] = I('session.username', '');
                $content                  = '';
                foreach($data as $key=>$val){
                    if(!empty($val)) $content .= $key.":".$val.";";
                }
                $Model->add($data);
                // print_r($data);die;
                // 修改IP地址使用状态
//                if($data['zd_ipaddress']){
//                    $ipbid  = $data['zd_ipaddress'];
//                    $status = '2';
//                    $res    = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
//                    if(!$res){
//                        $this->ajaxReturn("error");
//                        return true;
//                    }
//                }
                $this->recordLoghy('add', 'tmnhy',$content,'terminal_hy',$data['zd_atpid']);
                $this->ajaxReturn("success");
            }else{
                $oldmsgs = $Model->where("zd_atpid='%s'",array($data['zd_atpid']))->find();
                $data['zd_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                $data['zd_atplastmodifyuser'] = I('session.username', '');
                $Model->where("zd_atpid='%s'",array($data['zd_atpid']))->save($data);
                $diff  = array_diff($oldmsgs,$data);
                $content = '';
                foreach($diff as $key=>$val){
                    if(!empty($val) || !empty($data[$key]) || ($key == 'zd_atpcreatetime') || ($key == 'zd_atplastmodifytime')) $content .= $key."：".$val."-".$data[$key]."；";
                }
                //修改ip地址使用状态
//                if(!empty($diff['zd_ipaddress'])){
//                    $ipbid  = $diff['zd_ipaddress'];
//                    $status = null;
//                    $res    = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
//                    if(!$res){
//                        $this->ajaxReturn("error");
//                        return true;
//                    }else if($res) {
//                        if($data['zd_ipaddress']){
//                            $ipbid  = $data['zd_ipaddress'];
//                            $status = '2';
//                            $res    = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
//                            if(!$res){
//                                $this->ajaxReturn("error");
//                                return true;
//                            }
//                        }
//                    }
//                }
                $this->recordLoghy('update', 'tmnhy',$content,'terminal_hy',$data['zd_atpid']);
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
            echo $e;
        }
    }

    public function getbuilding(){
        $Model = M('dictionary');
        $area= $_POST['area'];
        $buildinglist = $Model->where("d_parentid='%s'",$area)->field('d_dictname,d_atpid')->select();
        echo json_encode($buildinglist);
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

    /**
     * 记录公用计算机操作日志
     * @param $type
     * @param $module
     * @param $content
     * @return mixed
     */
    public function recordLoghy($type, $module, $content,$table = '',$atpid = ''){
        $optime = date('Y-m-d H:i:s',time());
        $data = array(
            'l_atpid'      => $this->makeGuid(),
            'l_optime'     => $optime,
            'l_ipaddress'  => get_client_ip(),
            'l_optype'     => $type,
            'l_opuserid'   => session('user_name'),
            'l_opusername' => session('realusername'),
            'l_modulename' => $module,
            'l_detail'     => $content,
            'l_tablename'  => $table,
            'l_mainid'     => $atpid
        );
        $res = M('log_hy')->add($data);
        return $res;
    }
}