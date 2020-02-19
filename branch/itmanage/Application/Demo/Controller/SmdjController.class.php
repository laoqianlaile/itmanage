<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class SmdjController extends BaseController {

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
            $Model = M("smdj");
            $data = $Model->where("smdj_atpid='%s'", array($id))->find();
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
                $Model = M("smdj");
                foreach ($array as $id) {
                    $data    = $Model->where("smdj_atpid='%s'", $id)->find();
                    $data['smdj_atpstatus'] = 'DEL';
                    $data['smdj_atpmodifydatetime'] = date('Y-m-d H:i:s',time());
                    $data['smdj_atpmodifyuser'] = I('session.username','');
                    $Model->where("smdj_atpid='%s'", $id)->save($data);
                    //$this->recordLog('delete', 'account','删除','smdj',$data['smdj_atpid']);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M('smdj');
        $where=[];
        $where[0]['smdj_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['smdjname'])))
        {
            $where[0]['lower(smdj_name)']=['like',"%".strtolower(trim($queryparam['smdjname']))."%"];
        }
        if(!empty(trim($queryparam['factory'])))
        {
            $where[0]['smdj_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['xh'])))
        {
            $where[0]['smdj_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(smdj_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['smdj_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['didian'])))
        {
            $where[0]['smdj_didian']=['like',"%".trim($queryparam['didian'])."%"];
        }
        if(!empty(trim($queryparam['dept'])))
        {
            $where[0]['smdj_dept']=['like',"%".trim($queryparam['dept'])."%"];
        }
        $Result=$Model->where($where)
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->limit( $queryparam['limit'],$queryparam['offset'])
            ->select();
        //print_r($Result);die;
        $Count=$Model->where($where)->count();
//        foreach($Result as $key=> &$value){
//            $value['smdj_model']=$this->getdicname($value['smdj_model']);
//            $value['smdj_factory']=$this->getdicname($value['smdj_factory']);
//            $value['smdj_area']=$this->getdicname($value['smdj_area']);
//        }
        echo json_encode(array( 'total' => $Count,'rows' => $Result));
    }

    public function view(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("smdj");
            $data = $Model->where("smdj_atpid='%s'", array($id))->find();
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
        $column = Array("编号","名称","厂家","型号","密级","用途","所属部门","放置地点","责任人","使用情况","其他说明","区域","设备序列号","启用时间","操作系统安装日期","硬盘序列号");
        $thead  = array_diff($column,$excelsheet[1]);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }

        $cc = count($excelsheet);
        if (count($excelsheet) > 1) {
            $isRepeat = [];
            for($i=2;$i<=$cc;$i++){
                $smdjname = $excelsheet[$i]['C'];
                $smdjcode= $excelsheet[$i]['B'];
                if(empty($smdjname)||empty($smdjcode)) continue;
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
                $smdjcode = $excelsheet[$i]['B'];
                $smdjname= $excelsheet[$i]['C'];
                if(empty($smdjcode)||empty($smdjname)) continue;
                $data = array();
                //$data['smdj_atpid']        = $this->makeGuid();
                $data['smdj_name']   = $excelsheet[$i]['C']; //名称
                $data['smdj_code']         = $excelsheet[$i]['B']; //编号
                $data['smdj_factory']  = $excelsheet[$i]['D']; //厂家
                $data['smdj_model']        = $excelsheet[$i]['E']; //型号
                $data['smdj_secret']       = $excelsheet[$i]['F']; //密级
                $data['smdj_usage']      = $excelsheet[$i]['G']; //用途
                $data['smdj_dept']      = $excelsheet[$i]['H']; //所属部门
                $data['smdj_didian']      = $excelsheet[$i]['I']; //放置地点
                $data['smdj_dutyman'] = $excelsheet[$i]['J']; //责任人
                $data['smdj_status'] = $excelsheet[$i]['K'];//使用情况
                $data['smdj_ps'] = $excelsheet[$i]['L'];//其他说明
                $data['smdj_area']         = $excelsheet[$i]['M']; //区域名
                $data['smdj_sn']         = $excelsheet[$i]['N']; //设备序列号
                $data['smdj_qiyong'] = $excelsheet[$i]['O'];//启用时间
                $data['smdj_osdate']         =$excelsheet[$i]['P'];      //操作系统安装日期
                $data['smdj_disknum'] = $excelsheet[$i]['Q'];//硬盘序列号
                $data['smdj_atpcreateuser'] = I('session.username', '');//创建人
                $data['smdj_atpcreatedatetime'] = date('Y-m-d H:i:s', time());//创建时间

                $isHas = M('smdj')->where("smdj_code ='".$smdjcode."'  and smdj_atpstatus is null")->getField('smdj_atpid');
                if($isHas){
                    $hasIP[]    = $smdjcode;
                    $hasIPids[] = $isHas;
                    $data['smdj_atpmodifyuser'] = I('session.username', '');
                    $data['smdj_atpmodifydatetime'] = date('Y-m-d H:i:s', time());
                    $datas[] = " select '".makeGuid()."','".$data['smdj_name']."','".$data['smdj_code']."','".$data['smdj_factory']."','".$data['smdj_model']."','".$data['smdj_secret']."','".$data['smdj_usage']."','".$data['smdj_dept']."','".$data['smdj_didian']."','".$data['smdj_dutyman']."','".$data['smdj_status']."','".$data['smdj_ps']."','".$data['smdj_area']."','".$data['smdj_sn']."','".$data['smdj_qiyong']."','".$data['smdj_osdate']."','".$data['smdj_disknum']."','".$data['smdj_atpcreateuser']."','".$data['smdj_atpcreatedatetime']."','".$data['smdj_atpmodifyuser']."','".$data['smdj_atpmodifydatetime']."' from dual ";
                }else{
                    $datas[] = " select '".makeGuid()."','".$data['smdj_name']."','".$data['smdj_code']."','".$data['smdj_factory']."','".$data['smdj_model']."','".$data['smdj_secret']."','".$data['smdj_usage']."','".$data['smdj_dept']."','".$data['smdj_didian']."','".$data['smdj_dutyman']."','".$data['smdj_status']."','".$data['smdj_ps']."','".$data['smdj_area']."','".$data['smdj_sn']."','".$data['smdj_qiyong']."','".$data['smdj_osdate']."','".$data['smdj_disknum']."','".$data['smdj_atpcreateuser']."','".$data['smdj_atpcreatedatetime']."','','' from dual ";
                    $addcount++;
                }
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_smdj (smdj_atpid,smdj_name,smdj_code,smdj_factory,smdj_model,smdj_secret,smdj_usage,smdj_dept,smdj_didian,smdj_dutyman,smdj_status,smdj_ps,smdj_area,smdj_sn,smdj_qiyong,smdj_osdate,smdj_disknum,smdj_atpcreateuser,smdj_atpcreatedatetime,smdj_atpmodifyuser,smdj_atpmodifydatetime) ".$datas;
//            echo $sql;die;
            M('smdj')->execute($sql);
        }
        if($hasIPids){
            $hasIPids = implode("','",$hasIPids);
            M('smdj')->where("smdj_atpid in ('".$hasIPids."')")->setField("smdj_atpstatus",'DEL');
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
        $Model = M('smdj');
        $data = $Model->create();
        try{
            if(null==$data['smdj_atpid']) {
                $data['smdj_atpid'] = $this->makeGuid();
                $data['smdj_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
                $data['smdj_atpcreateuser'] = I('session.username', '');
                $content = '';
                foreach ($data as $key => $val) {
                    if (!empty($val)) $content .= $key . ":" . $val . ";";
                }
                $Model->add($data);
                //$this->recordLog('add', 'account',$content,'smdj',$data['smdj_atpid']);
                $this->ajaxReturn("success");
            }
            else
            {
                $oldmsgs = $Model->where("smdj_atpid='%s'",array($data['smdj_atpid']))->find();
                $data['smdj_atpmodifydatetime']=date('Y-m-d H:i:s', time());
                $data['smdj_atpmodifyuser']= I('session.username', '');
                $Model->where("smdj_atpid='%s'",array($data['smdj_atpid']))->save($data);
                $content = '';
                $diff  = array_diff($oldmsgs,$data);
                foreach($diff as $key=>$val){
                    if(!empty($val) || !empty($data[$key]) || ($key == 'smdj_atpcreatedatetime') || ($key == 'smdj_atpmodifydatetime')) $content .= $key."：".$val."-".$data[$key]."；";
                }
                //$this->recordLog('update', 'account',$content,'smdj',$data['smdj_atpid']);
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