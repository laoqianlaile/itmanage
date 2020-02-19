<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class ServerController extends BaseController {

    public function index(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assign('ds_cj',$Datas[6]);
        $this->display();
    }
    public function add(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assign('ds_cj',$Datas[6]);
        $this->display();
    }
    public function edit(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assign('ds_cj',$Datas[6]);
        $id = $_GET['id'];
        if ($id) {
            $Model = M("server");
            $data = $Model->where("server_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }
        }
        $this->display();
    }
    public function del()
    {
        try {
            $ids = I('post.ids');
            $array = explode(',', $ids);
            if ($array && count($array) > 0) {
                $Model = M("server");
                foreach ($array as $id) {
                    $data    = $Model->where("server_atpid='%s'", $id)->find();
                    $data['server_atpstatus'] = 'DEL';
                    $data['server_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
                    $data['server_atplastmodifyuser'] = I('session.username','');
                    $Model->where("server_atpid='%s'", $id)->save($data);
                    $this->recordLog('delete', 'account','删除','server',$data['server_atpid']);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M('server');
        $where=[];
        $where[0]['server_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['servername'])))
        {
            $where[0]['lower(server_name)']=['like',"%".strtolower(trim($queryparam['servername']))."%"];
        }
        if(!empty(trim($queryparam['ipaddress'])))
        {
            $where[0]['server_ip']=['like',"%".trim($queryparam['ipaddress'])."%"];
        }
        if(!empty(trim($queryparam['zwym'])))
        {
            $where[0]['server_mask']=['like',"%".trim($queryparam['zwym'])."%"];
        }
        if(!empty(trim($queryparam['mrwg'])))
        {
            $where[0]['server_gateway']=['like',"%".trim($queryparam['mrwg'])."%"];
        }
        if(!empty(trim($queryparam['factory'])))
        {
            $where[0]['server_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['xh'])))
        {
            $where[0]['server_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(server_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['server_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['serveros'])))
        {

            $where[0]['server_os']=['like',"%".trim($queryparam['serveros'])."%"];
        }
        if(!empty(trim($queryparam['serverdatabase'])))
        {
            $where[0]['lower(server_database)']=['like',"%".strtolower(trim($queryparam['serverdatabase']))."%"];
        }
        if(!empty(trim($queryparam['area'])))
        {
            $where[0]['server_area']=['like',"%".trim($queryparam['area'])."%"];
        }
        if(!empty(trim($queryparam['building'])))
        {
            $where[0]['server_building']=['like',"%".trim($queryparam['building'])."%"];
        }
        if(!empty(trim($queryparam['room'])))
        {
            $where[0]['server_room']=['like',"%".trim($queryparam['room'])."%"];
        }

        $Result=$Model->where($where)
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->limit( $queryparam['limit'],$queryparam['offset'])
            ->select();
        //print_r($Result);die;
        $Count=$Model->where($where)->count();
        foreach($Result as $key=> &$value){
            $value['server_model']=$this->getdicname($value['server_model']);
            $value['server_factory']=$this->getdicname($value['server_factory']);
            $value['server_building']=$this->getdicname($value['server_building']);
            $value['server_area']=$this->getdicname($value['server_area']);
        }
        echo json_encode(array( 'total' => $Count,'rows' => $Result));
    }

    public function getLogdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $Model = M();
        $sql_select="
                select l.*,t.*,d.d_dictname d_dictname from it_log l,it_terminal t,it_dictionary d where l.l_mainid = t.zd_atpid and t.zd_type=d.d_atpid and  d.d_belongtype='equipmenttype' and d.d_dictype='terminal' and l.l_tablename like '%terminal' and l.l_mainid ='".$id."'";
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
    public function view(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("server");
            $data = $Model->where("server_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }
        }
        $this->display();
    }
    /**
     * Excel导入数据保存
     */
    public function submitimp()
    {
        set_time_limit(0);
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('xls','xlsx');
        $upload->rootPath = './Public/uploads/';
        $upload->savePath = '';
        $info = $upload->upload();
        $filename = './Public/uploads/' . $info["updataexcel2007"]["savepath"] . $info["updataexcel2007"]["savename"];
        vendor("PHPExcel.PHPExcel");
        $objPhpExcel = \PHPExcel_IOFactory::load($filename);
        $excelsheet = $objPhpExcel->getActiveSheet()->toArray(null, true, true, true, true);
        //服务器名称	IP地址	子网掩码	默认网关	厂家	型号	用途	密级	操作系统及版本	数据库及版本	楼宇	房间号	区域名	编号	所属部门	责任人	责任人账号	操作系统安装日期	硬盘序列号	MAC地址	机房区域	启用时间	使用情况
        $column = Array("服务器名称","IP地址","子网掩码","默认网关","厂家","型号","用途","密级","操作系统及版本","数据库及版本","楼宇","房间号","区域名","编号","所属部门","责任人","责任人账号","操作系统安装日期","硬盘序列号","MAC地址","机房区域","启用时间","使用情况");
        $thead  = array_diff($column,$excelsheet[1]);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }

        $cc = count($excelsheet);
        if (count($excelsheet) > 1) {
            $isRepeat = [];
            for($i=2;$i<=$cc;$i++){
                $ipaddress = $excelsheet[$i]['C'];
                $servername= $excelsheet[$i]['B'];
                if(empty($ipaddress)||empty($servername)) continue;
                $isRepeat[] =  $excelsheet[$i]['C'];
            }
            $isRepeats = array_unique($isRepeat);
            if(count($isRepeats) != count($isRepeat)){
                exit(makeStandResult(3,"导入的Excel模板中有重复数据，请先修改！"));
            }
            $hasIP    = [];
            $hasIPids = [];
            $datas    = [];
            $addcount=0;
            for($i=2;$i<=$cc;$i++)
            {
                $ipaddress = $excelsheet[$i]['C'];
                $servername= $excelsheet[$i]['B'];
                if(empty($ipaddress)||empty($servername)) continue;
                $data = array();
                //$data['server_atpid']        = $this->makeGuid();
                $data['server_name']   = $excelsheet[$i]['B']; //服务器名称
                $data['server_ip']         = $excelsheet[$i]['C']; //IP地址
                $data['server_mask']  = $excelsheet[$i]['D']; //子网掩码
                $data['server_gateway']  = $excelsheet[$i]['E']; //默认网关
                $data['server_factory']  = $excelsheet[$i]['F']; //厂家
                if(empty($data['server_factory']))
                {
                    exit(makeStandResult(4,"厂家存在空列，请修改！"));
                }
                $facid=$this->getdicid($data['server_factory'],'factoryinfo');
                if(empty($facid))
                {
                    exit(makeStandResult(4,"厂家".$data['server_factory']."不存在，请在字典中添加后在导入"));
                }
                $data['server_factory']=$facid;
                $data['server_model']        = $excelsheet[$i]['G']; //型号
                if(empty($data['server_model']))
                {
                    exit(makeStandResult(4,"型号存在空列，请修改！"));
                }
                $xhid=$this->getdicid($data['server_model'],$facid);
                if(empty($xhid))
                {
                    exit(makeStandResult(4,"型号".$data['server_model']."不存在，请在字典中添加后在导入"));
                }
                $data['server_model']=$xhid;
                $data['server_usage']      = $excelsheet[$i]['H']; //用途
                $data['server_secret']       = $excelsheet[$i]['I']; //密级
                $data['server_os']    = $excelsheet[$i]['J'];           //操作系统及版本
                $data['server_database']   = $excelsheet[$i]['K']; //数据库及版本
                $data['server_area']         = $excelsheet[$i]['N']; //区域名
                if(empty($data['server_area']))
                {
                    exit(makeStandResult(4,"区域名存在空列，请修改！"));
                }
                $areaid=$this->getdicid($data['server_area'],'region');
                if(empty($areaid))
                {
                    exit(makeStandResult(4,"区域名".$data['server_area']."不存在，请在字典中添加后在导入"));
                }
                $data['server_area']=$areaid;
                $data['server_building']  = $excelsheet[$i]['L'];//楼宇
                if(empty($data['server_building']))
                {
                    exit(makeStandResult(4,"楼宇存在空列，请修改！"));
                }
                $buildid=$this->getdicid($data['server_building'],$areaid);
                if(empty($buildid))
                {
                    exit(makeStandResult(4,"楼宇".$data['server_building']."不存在，请在字典中添加后在导入"));
                }
                $data['server_building']=$buildid;
                $data['server_room'] = $excelsheet[$i]['M']; //房间号
                $data['server_num']  = $excelsheet[$i]['O']; //编号
                $data['server_dept']       = $excelsheet[$i]['P']; //所属部门
                $data['server_dutyman'] = $excelsheet[$i]['Q']; //责任人
                $data['server_dutymanid']         = $excelsheet[$i]['R']; //责任人账号
                $data['server_osdate']         =$excelsheet[$i]['S'];      //操作系统安装日期
                $data['server_disknum'] = $excelsheet[$i]['T'];//硬盘序列号
                $data['server_macaddress'] = $excelsheet[$i]['U'];//MAC地址
                $data['server_jifang'] = $excelsheet[$i]['V'];//机房区域
                $data['server_qiyong'] = $excelsheet[$i]['W'];//启用时间
                $data['server_status'] = $excelsheet[$i]['X'];//使用情况
                $data['server_atpcreateuser'] = I('session.username', '');//创建人
                $data['server_atpcreatetime'] = date('Y-m-d H:i:s', time());//创建时间

                $isHas = M('server')->where("server_ip ='".$ipaddress."'  and server_atpstatus is null")->getField('server_atpid');
                if($isHas){
                    $hasIP[]    = $ipaddress;
                    $hasIPids[] = $isHas;
                    $data['server_atplastmodifyuser'] = I('session.username', '');
                    $data['server_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                    $datas[] = " select '".makeGuid()."','".$data['server_name']."','".$data['server_ip']."','".$data['server_mask']."','".$data['server_gateway']."','".$data['server_factory']."','".$data['server_model']."','".$data['server_usage']."','".$data['server_secret']."','".$data['server_os']."','".$data['server_database']."','".$data['server_building']."','".$data['server_room']."','".$data['server_area']."','".$data['server_num']."','".$data['server_dept']."','".$data['server_dutyman']."','".$data['server_dutymanid']."','".$data['server_osdate']."','".$data['server_disknum']."','".$data['server_macaddress']."','".$data['server_jifang']."','".$data['server_qiyong']."','".$data['server_status']."','".$data['server_atpcreateuser']."','".$data['server_atpcreatetime']."','".$data['server_atplastmodifyuser']."','".$data['server_atplastmodifydatetime']."' from dual ";
                }else{
                    $datas[] = " select '".makeGuid()."','".$data['server_name']."','".$data['server_ip']."','".$data['server_mask']."','".$data['server_gateway']."','".$data['server_factory']."','".$data['server_model']."','".$data['server_usage']."','".$data['server_secret']."','".$data['server_os']."','".$data['server_database']."','".$data['server_building']."','".$data['server_room']."','".$data['server_area']."','".$data['server_num']."','".$data['server_dept']."','".$data['server_dutyman']."','".$data['server_dutymanid']."','".$data['server_osdate']."','".$data['server_disknum']."','".$data['server_macaddress']."','".$data['server_jifang']."','".$data['server_qiyong']."','".$data['server_status']."','".$data['server_atpcreateuser']."','".$data['server_atpcreatetime']."','','' from dual ";
                    $addcount++;
                }
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_server (server_atpid,server_name,server_ip,server_mask,server_gateway,server_factory,server_model,server_usage,server_secret,server_os,server_database,server_building,server_room,server_area,server_num,server_dept,server_dutyman,server_dutymanid,server_osdate,server_disknum,server_macaddress,server_jifang,server_qiyong,server_status,server_atpcreateuser,server_atpcreatedatetime,server_atplastmodifyuser,server_atplastmodifydatetime) ".$datas;
//            echo $sql;die;
            M('server')->execute($sql);
        }
        if($hasIPids){
            $hasIPids = implode("','",$hasIPids);
            M('server')->where("server_atpid in ('".$hasIPids."')")->setField("server_atpstatus",'DEL');
//            echo M('server')->_sql();die;
            $hasIPs = implode(',',$hasIP);

            //$this->recordLog('update', 'tmnhy',"导入Excel覆盖原信息，IP:$hasIPs",'terminal_hy','');
        }
        $count = $cc-1;
        $content = "Excelp批量上传信息".$count."条";
        //$this->recordLog('add', 'tmnhy',$content,'terminal_hy','');
        if(!empty($hasIP)){
//            print_r($hasIP);die;
//            $hasIP = implode(',',$hasIP);
            $tempcount= count($hasIP);
            $this->recordLog('import', 'account',"新增$addcount"."条，更新数据"."$tempcount"."条",'server','');
            exit($this->makeStandResult(0,"新增$addcount"."条，更新数据"."$tempcount"."条"));
        }else{
            $this->recordLog('import', 'account',"新增$addcount"."条",'server','');
            exit($this->makeStandResult(0,"新增$addcount"."条"));
        }
    }

    public function submit(){
        $Model = M('server');
        $data = $Model->create();
        try{
            if(null==$data['server_atpid']) {
                $data['server_atpid'] = $this->makeGuid();
                $data['server_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
                $data['server_atpcreateuser'] = I('session.username', '');
                $content = '';
                foreach ($data as $key => $val) {
                    if (!empty($val)) $content .= $key . ":" . $val . ";";
                }
                $Model->add($data);
                $this->recordLog('add', 'account',$content,'server',$data['server_atpid']);
                $this->ajaxReturn("success");
            }
            else
            {
                $oldmsgs = $Model->where("server_atpid='%s'",array($data['server_atpid']))->find();
                $data['server_atplastmodifydatetime']=date('Y-m-d H:i:s', time());
                $data['server_atplastmodifyuser']= I('session.username', '');
                $Model->where("server_atpid='%s'",array($data['server_atpid']))->save($data);
                $content = '';
                $diff  = array_diff($oldmsgs,$data);
                foreach($diff as $key=>$val){
                    if(!empty($val) || !empty($data[$key]) || ($key == 'server_atpcreatedatetime') || ($key == 'server_atplastmodifydatetime')) $content .= $key."：".$val."-".$data[$key]."；";
                }
                $this->recordLog('update', 'account',$content,'server',$data['server_atpid']);
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
            echo $e;
        }
    }
    public function getdicname($id){
        $building =M('dictionary')->where("d_atpid='%s' and d_atpstatus is null",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getdicid($name,$type){
        $name=strtolower($name);
        if($type=='factoryinfo')
        {
            $building=M('dictionary')->where("lower(d_dictname)='%s' and d_belongtype='%s' and d_parentid='154117' and d_atpstatus is null",$name,$type)->field('d_atpid')->find();
        }else if($type=='region'){
            $building = M('dictionary')->where("lower(d_dictname)='%s' and d_belongtype='%s' and d_atpstatus is null", $name, $type)->field('d_atpid')->find();
        }
        else
        {
            $building = M('dictionary')->where("lower(d_dictname)='%s' and d_parentid='%s' and d_atpstatus is null", $name, $type)->field('d_atpid')->find();
        }
        return $building['d_atpid'];
    }
    public function getbuilding(){
        $Model = M('dictionary');
        $area= $_POST['area'];
        $buildinglist = $Model->where("d_parentid='%s' and d_atpstatus is null",$area)->field('d_dictname,d_atpid')->select();
        echo json_encode($buildinglist);
    }
    public function getbuildingname($id){
        $building =M('dictionary')->where("d_atpid='%s' and d_atpstatus is null",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getareaname($id){
        $building =M('dictionary')->where("d_atpid='%s' and d_atpstatus is null",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }

}