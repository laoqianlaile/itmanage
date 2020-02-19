<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class SxsbController extends BaseController {

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
            $Model = M("sxsb");
            $data = $Model->where("sxsb_atpid='%s'", array($id))->find();
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
                $Model = M("sxsb");
                foreach ($array as $id) {
                    $data    = $Model->where("sxsb_atpid='%s'", $id)->find();
                    $data['sxsb_atpstatus'] = 'DEL';
                    $data['sxsb_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
                    $data['sxsb_atplastmodifyuser'] = I('session.username','');
                    $Model->where("sxsb_atpid='%s'", $id)->save($data);
                    //$this->recordLog('delete', 'account','删除','sxsb',$data['sxsb_atpid']);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M('sxsb');
        $where=[];
        $where[0]['sxsb_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['sxsbname'])))
        {
            $where[0]['lower(sxsb_name)']=['like',"%".strtolower(trim($queryparam['sxsbname']))."%"];
        }
        if(!empty(trim($queryparam['factory'])))
        {
            $where[0]['sxsb_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['xh'])))
        {
            $where[0]['sxsb_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(sxsb_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['sxsb_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['didian'])))
        {
            $where[0]['sxsb_didian']=['like',"%".trim($queryparam['didian'])."%"];
        }
        if(!empty(trim($queryparam['dept'])))
        {
            $where[0]['sxsb_dept']=['like',"%".trim($queryparam['dept'])."%"];
        }
        $Result=$Model->where($where)
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->limit( $queryparam['limit'],$queryparam['offset'])
            ->select();
        //print_r($Result);die;
        $Count=$Model->where($where)->count();
//        foreach($Result as $key=> &$value){
//            $value['sxsb_model']=$this->getdicname($value['sxsb_model']);
//            $value['sxsb_factory']=$this->getdicname($value['sxsb_factory']);
//            $value['sxsb_area']=$this->getdicname($value['sxsb_area']);
//        }
        echo json_encode(array( 'total' => $Count,'rows' => $Result));
    }

    public function view(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("sxsb");
            $data = $Model->where("sxsb_atpid='%s'", array($id))->find();
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
        //编号	名称	厂家	型号	密级	用途（外携使用/内部使用）	所属部门	放置地点	责任人	使用情况	其他说明	区域	设备序列号	启用时间	操作系统安装日期	硬盘序列号
        $column = Array("编号","名称","厂家","型号","密级","用途","所属部门","放置地点","责任人","使用情况","设备序列号","启用时间");
        $thead  = array_diff($column,$excelsheet[1]);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }

        $cc = count($excelsheet);
        if (count($excelsheet) > 1) {
            $isRepeat = [];
            for($i=2;$i<=$cc;$i++){
                $sxsbname = $excelsheet[$i]['C'];
                $sxsbcode= $excelsheet[$i]['B'];
                if(empty($sxsbname)||empty($sxsbcode)) continue;
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
                $sxsbcode = $excelsheet[$i]['B'];
                $sxsbname= $excelsheet[$i]['C'];
                if(empty($sxsbcode)||empty($sxsbname)) continue;
                $data = array();
                //$data['sxsb_atpid']        = $this->makeGuid();
                $data['sxsb_name']   = $excelsheet[$i]['C']; //名称
                $data['sxsb_code']         = $excelsheet[$i]['B']; //编号
                $data['sxsb_factory']  = $excelsheet[$i]['D']; //厂家
                $data['sxsb_model']        = $excelsheet[$i]['E']; //型号
                $data['sxsb_secret']       = $excelsheet[$i]['F']; //密级
                $data['sxsb_usage']      = $excelsheet[$i]['G']; //用途
                $data['sxsb_dept']      = $excelsheet[$i]['H']; //所属部门
                $data['sxsb_didian']      = $excelsheet[$i]['I']; //放置地点
                $data['sxsb_dutyman'] = $excelsheet[$i]['J']; //责任人
                $data['sxsb_status'] = $excelsheet[$i]['K'];//使用情况
                $data['sxsb_sn']         = $excelsheet[$i]['L']; //设备序列号
                $data['sxsb_qiyong'] = $excelsheet[$i]['M'];//启用时间
                $data['sxsb_atpcreateuser'] = I('session.username', '');//创建人
                $data['sxsb_atpcreatedatetime'] = date('Y-m-d H:i:s', time());//创建时间

                $isHas = M('sxsb')->where("sxsb_code ='".$sxsbcode."'  and sxsb_atpstatus is null")->getField('sxsb_atpid');
                if($isHas){
                    $hasIP[]    = $sxsbcode;
                    $hasIPids[] = $isHas;
                    $data['sxsb_atplastmodifyuser'] = I('session.username', '');
                    $data['sxsb_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                    $datas[] = " select '".makeGuid()."','".$data['sxsb_name']."','".$data['sxsb_code']."','".$data['sxsb_factory']."','".$data['sxsb_model']."','".$data['sxsb_secret']."','".$data['sxsb_usage']."','".$data['sxsb_dept']."','".$data['sxsb_didian']."','".$data['sxsb_dutyman']."','".$data['sxsb_status']."','".$data['sxsb_sn']."','".$data['sxsb_qiyong']."','".$data['sxsb_atpcreateuser']."','".$data['sxsb_atpcreatedatetime']."','".$data['sxsb_atplastmodifyuser']."','".$data['sxsb_atplastmodifydatetime']."' from dual ";
                }else{
                    $datas[] = " select '".makeGuid()."','".$data['sxsb_name']."','".$data['sxsb_code']."','".$data['sxsb_factory']."','".$data['sxsb_model']."','".$data['sxsb_secret']."','".$data['sxsb_usage']."','".$data['sxsb_dept']."','".$data['sxsb_didian']."','".$data['sxsb_dutyman']."','".$data['sxsb_status']."','".$data['sxsb_sn']."','".$data['sxsb_qiyong']."','".$data['sxsb_atpcreateuser']."','".$data['sxsb_atpcreatedatetime']."','','' from dual ";
                    $addcount++;
                }
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_sxsb (sxsb_atpid,sxsb_name,sxsb_code,sxsb_factory,sxsb_model,sxsb_secret,sxsb_usage,sxsb_dept,sxsb_didian,sxsb_dutyman,sxsb_status,sxsb_sn,sxsb_qiyong,sxsb_atpcreateuser,sxsb_atpcreatedatetime,sxsb_atplastmodifyuser,sxsb_atplastmodifydatetime) ".$datas;
            //echo $sql;die;
            M('sxsb')->execute($sql);
        }
        if($hasIPids){
            $hasIPids = implode("','",$hasIPids);
            M('sxsb')->where("sxsb_atpid in ('".$hasIPids."')")->setField("sxsb_atpstatus",'DEL');
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
            //$this->recordLog('import', 'account',"新增$addcount"."条，更新数据"."$tempcount"."条",'server','');
            exit($this->makeStandResult(0,"新增$addcount"."条，更新数据"."$tempcount"."条"));
        }else{
           // $this->recordLog('import', 'account',"新增$addcount"."条",'server','');
            exit($this->makeStandResult(0,"新增$addcount"."条"));
        }
    }

    public function submit(){
        $Model = M('sxsb');
        $data = $Model->create();
        try{
            if(null==$data['sxsb_atpid']) {
                $data['sxsb_atpid'] = $this->makeGuid();
                $data['sxsb_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
                $data['sxsb_atpcreateuser'] = I('session.username', '');
                $content = '';
                foreach ($data as $key => $val) {
                    if (!empty($val)) $content .= $key . ":" . $val . ";";
                }
                $Model->add($data);
                //$this->recordLog('add', 'account',$content,'sxsb',$data['sxsb_atpid']);
                $this->ajaxReturn("success");
            }
            else
            {
                $oldmsgs = $Model->where("sxsb_atpid='%s'",array($data['sxsb_atpid']))->find();
                $data['sxsb_atplastmodifydatetime']=date('Y-m-d H:i:s', time());
                $data['sxsb_atplastmodifyuser']= I('session.username', '');
                $Model->where("sxsb_atpid='%s'",array($data['sxsb_atpid']))->save($data);
                $content = '';
                $diff  = array_diff($oldmsgs,$data);
                foreach($diff as $key=>$val){
                    if(!empty($val) || !empty($data[$key]) || ($key == 'sxsb_atpcreatedatetime') || ($key == 'sxsb_atplastmodifydatetime')) $content .= $key."：".$val."-".$data[$key]."；";
                }
                //$this->recordLog('update', 'account',$content,'sxsb',$data['sxsb_atpid']);
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