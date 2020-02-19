<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class ApplicationController extends BaseController {

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
            $Model = M("application");
            $data = $Model->where("application_atpid='%s'", array($id))->find();
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
                $Model = M("application");
                foreach ($array as $id) {
                    $data    = $Model->where("application_atpid='%s'", $id)->find();
                    $data['application_atpstatus'] = 'DEL';
                    $data['application_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
                    $data['application_atplastmodifyuser'] = I('session.username','');
                    $Model->where("application_atpid='%s'", $id)->save($data);
                    $this->recordLog('delete', 'account','删除','application',$data['application_atpid']);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M('application');
        $where=[];
        $where[0]['application_atpstatus']=['exp','is null'];
        //系统名称 密级 安装位置 用途 访问方式 开发单位
        if(!empty(trim($queryparam['applicationname'])))
        {
            $where[0]['lower(application_name)']=['like',"%".strtolower(trim($queryparam['applicationname']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['application_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['applicationhost'])))
        {
            $where[0]['lower(application_host)']=['like',"%".strtolower(trim($queryparam['applicationhost']))."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(application_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['applicationusemode'])))
        {
            $where[0]['lower(application_usemode)']=['like',"%".strtolower(trim($queryparam['applicationusemode']))."%"];
        }
        if(!empty(trim($queryparam['applicationdeveloper'])))
        {
            $where[0]['lower(application_developer)']=['like',"%".strtolower(trim($queryparam['applicationdeveloper']))."%"];
        }

//        if(!empty(trim($queryparam['applicationinfo'])))
//        {
//            $where[0]['lower(application_info)']=['like',"%".strtolower(trim($queryparam['applicationinfo']))."%"];
//        }
//
//        if(!empty(trim($queryparam['applicationmode'])))
//        {
//            $where[0]['lower(application_mode)']=['like',"%".strtolower(trim($queryparam['applicationmode']))."%"];
//        }
//
//        if(!empty(trim($queryparam['applicationuserscope'])))
//        {
//            $where[0]['lower(application_userscope)']=['like',"%".strtolower(trim($queryparam['applicationuserscope']))."%"];
//        }
//
//        if(!empty(trim($queryparam['applicationaloginmode'])))
//        {
//            $where[0]['lower(application_aloginmode)']=['like',"%".strtolower(trim($queryparam['applicationaloginmode']))."%"];
//        }
//        if(!empty(trim($queryparam['applicationloginmode'])))
//        {
//            $where[0]['lower(application_loginmode)']=['like',"%".strtolower(trim($queryparam['applicationloginmode']))."%"];
//        }
//        if(!empty(trim($queryparam['applicationqx'])))
//        {
//            $where[0]['lower(application_accessauthority)']=['like',"%".strtolower(trim($queryparam['applicationqx']))."%"];
//        }

        $Result=$Model->where($where)
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->limit( $queryparam['limit'],$queryparam['offset'])
            ->select();
        $Count=$Model->where($where)->count();
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
        $column = array('系统名称','密级','信息资源','安装位置','存放方式','用途','用户范围','访问方式','管理员鉴别方式','普通用户鉴别方式','访问权限','开发单位');
        $thead  = array_diff($column,$excelsheet[1]);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }

        $cc = count($excelsheet);
        if (count($excelsheet) > 1) {
            $isRepeat = [];
            for($i=2;$i<=$cc;$i++){
                $applicationname= $excelsheet[$i]['B'];
                if(empty($applicationname)) continue;
                $isRepeat[] =  $excelsheet[$i]['B'];
            }
            $repeatarr= array_count_values($isRepeat);
           // $isRepeats = array_unique($isRepeat);

//            if(count($isRepeats) != count($isRepeat)){
//                exit(makeStandResult(3,"导入的Excel模板中有重复数据，请先修改！"));
//            }
            $hasIP    = [];
            $hasIPids = [];
            $datas    = [];
            $addcount=0;
            for($i=2;$i<=$cc;$i++)
            {
                $applicationname= $excelsheet[$i]['B'];
                if(empty($applicationname)) continue;
                $data = array();
                //系统名称	密级	信息资源	安装位置	存放形式	用途	用户范围	访问方式	管理员鉴别方式	普通用户鉴别方式	访问权限	开发单位
                //$data['application_atpid']        = $this->makeGuid();
                $data['application_name']   = $excelsheet[$i]['B']; //系统名称
                if($repeatarr[$data['application_name']]>1)
                {
                    exit(makeStandResult(3,"导入的Excel模板中系统名称".$data['application_name']."重复，请先修改！"));
                }
                $data['application_secret']         = $excelsheet[$i]['C']; //密级
                $data['application_info']  = $excelsheet[$i]['D']; //信息资源
                $data['application_host']  = $excelsheet[$i]['E']; //安装位置
                $data['application_mode']  = $excelsheet[$i]['F']; //存放形式
                $data['application_usage']        = $excelsheet[$i]['G']; //用途
                $data['application_userscope']      = $excelsheet[$i]['H']; //用户范围
                $data['application_usemode']       = $excelsheet[$i]['I']; //访问方式
                $data['application_aloginmode']    = $excelsheet[$i]['J'];           //管理员鉴别方式
                $data['application_loginmode']   = $excelsheet[$i]['K']; //普通用户鉴别方式
                $data['application_accessauthority']  = $excelsheet[$i]['L'];//访问权限
                $data['application_developer'] = $excelsheet[$i]['M']; //开发单位
                $data['application_atpcreateuser'] = I('session.username', '');//创建人
                $data['application_atpcreatetime'] = date('Y-m-d H:i:s', time());//创建时间

                $isHas = M('application')->where("application_name ='".$applicationname."'  and application_atpstatus is null")->getField('application_atpid');
                if($isHas){
                    $hasIP[]    = $applicationname;
                    $hasIPids[] = $isHas;
                    $data['application_atplastmodifyuser'] = I('session.username', '');
                    $data['application_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                    $datas[] = " select '".makeGuid()."','".$data['application_name']."','".$data['application_secret']."','".$data['application_info']."','".$data['application_host']."','".$data['application_mode']."','".$data['application_usage']."','".$data['application_userscope']."','".$data['application_usemode']."','".$data['application_aloginmode']."','".$data['application_loginmode']."','".$data['application_accessauthority']."','".$data['application_developer']."','".$data['application_atpcreateuser']."','".$data['application_atpcreatetime']."','".$data['application_atplastmodifyuser']."','".$data['application_atplastmodifydatetime']."' from dual ";
                }else{
                    $datas[] = " select '".makeGuid()."','".$data['application_name']."','".$data['application_secret']."','".$data['application_info']."','".$data['application_host']."','".$data['application_mode']."','".$data['application_usage']."','".$data['application_userscope']."','".$data['application_usemode']."','".$data['application_aloginmode']."','".$data['application_loginmode']."','".$data['application_accessauthority']."','".$data['application_developer']."','".$data['application_atpcreateuser']."','".$data['application_atpcreatetime']."','','' from dual ";
                    $addcount++;
                }
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_application (application_atpid,application_name,application_secret,application_info,application_host,application_mode,application_usage,application_userscope,application_usemode,application_aloginmode,application_loginmode,application_accessauthority,application_developer,application_atpcreateuser,application_atpcreatedatetime,application_atplastmodifyuser,application_atplastmodifydatetime) ".$datas;
//            echo $sql;die;
            M('application')->execute($sql);
        }
        if($hasIPids){
            $hasIPids = implode("','",$hasIPids);
            M('application')->where("application_atpid in ('".$hasIPids."')")->setField("application_atpstatus",'DEL');
//            echo M('application')->_sql();die;
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
            $this->recordLog('import', 'account',"新增$addcount"."条，更新数据"."$tempcount"."条",'application','');
            exit($this->makeStandResult(0,"新增$addcount"."条，更新数据"."$tempcount"."条"));
        }else{
            $this->recordLog('import', 'account',"新增$addcount"."条",'application','');
            exit($this->makeStandResult(0,"新增$addcount"."条"));
        }
    }

    public function submit(){
        $Model = M('application');
        $data = $Model->create();
        try{
            if(null==$data['application_atpid']) {
                $data['application_atpid'] = $this->makeGuid();
                $data['application_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
                $data['application_atpcreateuser'] = I('session.username', '');
                $content = '';
                foreach ($data as $key => $val) {
                    if (!empty($val)) $content .= $key . ":" . $val . ";";
                }
                $Model->add($data);
                $this->recordLog('add', 'account',$content,'application',$data['application_atpid']);
                $this->ajaxReturn("success");
            }
            else
            {
                $oldmsgs = $Model->where("application_atpid='%s'",array($data['application_atpid']))->find();
                $data['application_atplastmodifydatetime']=date('Y-m-d H:i:s', time());
                $data['application_atplastmodifyuser']= I('session.username', '');
                $Model->where("application_atpid='%s'",array($data['application_atpid']))->save($data);
                $content = '';
                $diff  = array_diff($oldmsgs,$data);
                foreach($diff as $key=>$val){
                    if(!empty($val) || !empty($data[$key]) || ($key == 'application_atpcreatedatetime') || ($key == 'application_atplastmodifydatetime')) $content .= $key."：".$val."-".$data[$key]."；";
                }
                $this->recordLog('update', 'account',$content,'application',$data['application_atpid']);
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