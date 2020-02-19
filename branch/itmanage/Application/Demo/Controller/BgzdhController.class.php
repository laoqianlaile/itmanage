<?php
namespace Demo\Controller;
use Think\Controller;

class BgzdhController extends BaseController {
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

    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M('bgzdh');
        $where=[];
        $where[0]['bgzdh_atpstatus']=['exp','is null'];
        if(trim($queryparam['bgzdhcode']))
        {

            $where[0]['bgzdh_code']=['like',"%".strtolower(trim($queryparam['bgzdhcode']))."%"];
        }
        if(trim($queryparam['bgzdhname']))
        {
            $where[0]['bgzdh_name']=['like',"%".trim($queryparam['bgzdhname'])."%"];
        }
        if(trim($queryparam['bgzdhdept']))
        {
            $where[0]['bgzdh_dept']=['like',"%".trim($queryparam['bgzdhdept'])."%"];
        }
        if(trim($queryparam['bgzdhdidian']))
        {
            $where[0]['bgzdh_didian']=['like',"%".trim($queryparam['bgzdhdidian'])."%"];
        }
        if(trim($queryparam['factory']))
        {
            $where[0]['bgzdh_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(trim($queryparam['xh']))
        {
            $where[0]['bgzdh_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(trim($queryparam['yt']))
        {
            $where[0]['lower(bgzdh_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(trim($queryparam['secretlevel']))
        {
            $where[0]['bgzdh_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(trim($queryparam['bgzdhdutyman']))
        {
            $where[0]['bgzdh_dutyman']=['like',"%".trim($queryparam['bgzdhdutyman'])."%"];
        }
        if(trim($queryparam['bgzdhip']))
        {
            $where[0]['bgzdh_ip']=['like',"%".trim($queryparam['bgzdhip'])."%"];
        }
        if(trim($queryparam['bgzdhmac']))
        {
            $where[0]['bgzdh_mac']=['like',"%".trim($queryparam['bgzdhmac'])."%"];
        }




        $Result=$Model->where()
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->limit( $queryparam['limit'],$queryparam['offset'])
            ->where($where)
//            ->buildSql();
            ->select();
        $Count=$Model->where($where)->count();
        echo json_encode(array( 'total' => $Count,'rows' => $Result));
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
            $Model = M("bgzdh");
            $data = $Model->where("bgzdh_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }
        }
        $this->display();
    }



    public  function submit(){
        $Model = M('bgzdh');
        $data = $Model->create();
        try{
            if(null==$data['bgzdh_atpid']) {
                $data['bgzdh_atpid'] = $this->makeGuid();
                $data['bgzdh_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
                $data['bgzdh_atpcreateuser'] = I('session.username', '');
                $content = '';
                foreach ($data as $key => $val) {
                    if (!empty($val)) $content .= $key . ":" . $val . ";";
                }
                //dump($data);die;
                $Model->add($data);
                //$this->recordLog('add', 'account',$content,'bgzdh',$data['bgzdh_atpid']);
                $this->ajaxReturn("success");
            }
            else
            {
                $oldmsgs = $Model->where("bgzdh_atpid='%s'",array($data['bgzdh_atpid']))->find();
                $Model->where("bgzdh_atpid='%s'",array($data['bgzdh_atpid']))->save($data);
                $content = '';
                $diff  = array_diff($oldmsgs,$data);
                foreach($diff as $key=>$val){
                    if(!empty($val)) $content .= $key."：".$val."-".$data[$key]."；";
                }
                //$this->recordLog('update', 'account',$content,'bgzdh',$data['bgzdh_atpid']);
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
            echo $e;
        }

    }



    public function del(){
        try {
            $ids = I('post.ids');
            $array = explode(',', $ids);
            if ($array && count($array) > 0) {
                $Model = M("bgzdh");
                foreach ($array as $id) {
                    $data    = $Model->where("bgzdh_atpid='%s'", $id)->find();
                    $data['bgzdh_atpstatus'] = 'DEL';
                    $Model->where("bgzdh_atpid='%s'", $id)->save($data);

//                    $this->recordLog('delete', 'account','删除','bgzdh',$data['bgzdh_atpid']);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
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
        $column = Array("编号","名称","型号","厂家","密级","用途","所属部门","放置地点","责任人","IP地址（联网设备）","MAC地址（联网设备）","启用时间","使用情况");
        $thead  = array_diff($column,$excelsheet[1]);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }
        $cc = count($excelsheet);
        if (count($excelsheet) > 1) {
            $isRepeat = [];
            for($i=2;$i<=$cc;$i++){
                $ipaddress = $excelsheet[$i]['B'];
                $servername= $excelsheet[$i]['C'];
                if(empty($ipaddress)||empty($servername)) continue;
                $isRepeat[] =  $excelsheet[$i]['B'];
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
                $ipaddress = $excelsheet[$i]['B'];
                $servername= $excelsheet[$i]['C'];
                if(empty($ipaddress)||empty($servername)) continue;
                $data = array();
                $data['bgzdh_atpid']        = $this->makeGuid();
                $data['bgzdh_code']   = $excelsheet[$i]['B']; //编号
                $data['bgzdh_name']         = $excelsheet[$i]['C']; //名称
                $data['bgzdh_model']  = $excelsheet[$i]['D']; //型号
                $data['bgzdh_factory']  = $excelsheet[$i]['E']; //厂家
                $data['bgzdh_secret']  = $excelsheet[$i]['F']; //密级
                $data['bgzdh_usage']        = $excelsheet[$i]['G']; //用途
                $data['bgzdh_dept']       = $excelsheet[$i]['H']; //部门
                $data['bgzdh_didian']    = $excelsheet[$i]['I']; //放置地点
                $data['bgzdh_dutyman']   = $excelsheet[$i]['J']; //责任人
                $data['bgzdh_ip']         = $excelsheet[$i]['K']; //ip地址
                $data['bgzdh_mac']  = $excelsheet[$i]['L'];//mac地址
                $data['bgzdh_qiyong'] = $excelsheet[$i]['M']; //启用时间
                $data['bgzdh_status']  = $excelsheet[$i]['N']; //使用情况
                $data['bgzdh_atpcreateuser'] = I('session.username', '');//创建人
                $data['bgzdh_atpcreatedatetime'] = date('Y-m-d H:i:s', time());//创建时间
                $isHas = M('bgzdh')->where("bgzdh_code ='".$ipaddress."'  and bgzdh_atpstatus is null")->getField('bgzdh_atpid');
                if($isHas){
                    $hasIP[]    = $ipaddress;
                    $hasIPids[] = $isHas;
                    $data['bgzdh_atplastmodifyuser'] = I('session.username', '');
                    $data['bgzdh_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                    $datas[] = " select '".makeGuid()."','".$data['bgzdh_code']."','".$data['bgzdh_name']."','".$data['bgzdh_model']."','".$data['bgzdh_factory']."','".$data['bgzdh_secret']."','".$data['bgzdh_usage']."','".$data['bgzdh_dept']."','".$data['bgzdh_didian']."','".$data['bgzdh_dutyman']."','".$data['bgzdh_ip']."','".$data['bgzdh_mac']."','".$data['bgzdh_qiyong']."','".$data['bgzdh_status']."','".$data['bgzdh_atpcreateuser']."','".$data['bgzdh_atpcreatedatetime']."','".$data['bgzdh_atplastmodifyuser']."','".$data['bgzdh_atplastmodifydatetime']."' from dual ";
                }else{
                    $datas[] = " select '".makeGuid()."','".$data['bgzdh_code']."','".$data['bgzdh_name']."','".$data['bgzdh_model']."','".$data['bgzdh_factory']."','".$data['bgzdh_secret']."','".$data['bgzdh_usage']."','".$data['bgzdh_dept']."','".$data['bgzdh_didian']."','".$data['bgzdh_dutyman']."','".$data['bgzdh_ip']."','".$data['bgzdh_mac']."','".$data['bgzdh_qiyong']."','".$data['bgzdh_status']."','".$data['bgzdh_atpcreateuser']."','".$data['bgzdh_atpcreatedatetime']."','','' from dual ";
                    $addcount++;
                }
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_bgzdh (bgzdh_atpid,bgzdh_code,bgzdh_name,bgzdh_model,bgzdh_factory,bgzdh_secret,bgzdh_usage,bgzdh_dept,bgzdh_didian,bgzdh_dutyman,bgzdh_ip,bgzdh_mac,bgzdh_qiyong,bgzdh_status,bgzdh_atpcreateuser,bgzdh_atpcreatedatetime,bgzdh_atplastmodifyuser,bgzdh_atplastmodifydatetime) ".$datas;
//            echo $sql;die;
            M('bgzdh')->execute($sql);
        }
        if($hasIPids){
            $hasIPids = implode("','",$hasIPids);
            M('bgzdh')->where("bgzdh_atpid in ('".$hasIPids."')")->setField("bgzdh_atpstatus",'DEL');
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
            //$this->recordLog('import', 'account',"新增$addcount"."条",'bgzdh','');
            exit($this->makeStandResult(0,"新增$addcount"."条"));
        }
    }



}





?>

