<?php
namespace Demo\Controller;
use Think\Controller;

class CcsbController extends BaseController {
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
        $Model = M('ccsb');
        $where=[];
        $where[0]['ccsb_atpstatus']=['exp','is null'];

        if(trim($queryparam['ccsbname']))
        {
            $where[0]['ccsb_name']=['like',"%".trim($queryparam['ccsbname'])."%"];
        }
        if(trim($queryparam['ccsbdept']))
        {
            $where[0]['ccsb_dept']=['like',"%".trim($queryparam['ccsbdept'])."%"];
        }
        if(trim($queryparam['ccsbdidian']))
        {
            $where[0]['ccsb_didian']=['like',"%".trim($queryparam['ccsbdidian'])."%"];
        }
        if(trim($queryparam['factory']))
        {
            $where[0]['ccsb_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(trim($queryparam['xh']))
        {
            $where[0]['ccsb_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(trim($queryparam['yt']))
        {
            $where[0]['lower(ccsb_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(trim($queryparam['secretlevel']))
        {
            $where[0]['ccsb_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
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
            $Model = M("ccsb");
            $data = $Model->where("ccsb_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }
        }
        $this->display();
    }



    public  function submit(){
        $Model = M('ccsb');
        $data = $Model->create();
        try{
            if(null==$data['ccsb_atpid']) {
                $data['ccsb_atpid'] = $this->makeGuid();
                $data['ccsb_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
                $data['ccsb_atpcreateuser'] = I('session.username', '');
                $content = '';
                foreach ($data as $key => $val) {
                    if (!empty($val)) $content .= $key . ":" . $val . ";";
                }
                //dump($data);die;
                $Model->add($data);
                //$this->recordLog('add', 'account',$content,'ccsb',$data['ccsb_atpid']);
                $this->ajaxReturn("success");
            }
            else
            {
                $oldmsgs = $Model->where("ccsb_atpid='%s'",array($data['ccsb_atpid']))->find();
                $Model->where("ccsb_atpid='%s'",array($data['ccsb_atpid']))->save($data);
                $content = '';
                $diff  = array_diff($oldmsgs,$data);
                foreach($diff as $key=>$val){
                    if(!empty($val)) $content .= $key."：".$val."-".$data[$key]."；";
                }
                //$this->recordLog('update', 'account',$content,'ccsb',$data['ccsb_atpid']);
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
                $Model = M("ccsb");
                foreach ($array as $id) {
                    $data    = $Model->where("ccsb_atpid='%s'", $id)->find();
                    $data['ccsb_atpstatus'] = 'DEL';
                    $Model->where("ccsb_atpid='%s'", $id)->save($data);

//                    $this->recordLog('delete', 'account','删除','ccsb',$data['ccsb_atpid']);
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
        $column = Array("编号","名称","厂家","型号","密级","用途","所属部门","放置地点","使用情况","序列号","启用时间","责任人");
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
                $data['ccsb_atpid']        = $this->makeGuid();
                $data['ccsb_code']   = $excelsheet[$i]['B']; //编号
                $data['ccsb_name']         = $excelsheet[$i]['C']; //名称
                $data['ccsb_factory']  = $excelsheet[$i]['D']; //厂家
                $data['ccsb_model']  = $excelsheet[$i]['E']; //型号
                $data['ccsb_secret']  = $excelsheet[$i]['F']; //密级
                $data['ccsb_usage']        = $excelsheet[$i]['G']; //用途
                $data['ccsb_dept']       = $excelsheet[$i]['H']; //部门
                $data['ccsb_didian']    = $excelsheet[$i]['I']; //放置地点
                $data['ccsb_status']  = $excelsheet[$i]['J']; //使用情况
                $data['ccsb_sn']         = $excelsheet[$i]['K']; //ip地址
                $data['ccsb_qiyong'] = $excelsheet[$i]['L']; //启用时间
                $data['ccsb_dutyman']   = $excelsheet[$i]['M']; //责任人
                $data['ccsb_atpcreateuser'] = I('session.username', '');//创建人
                $data['ccsb_atpcreatedatetime'] = date('Y-m-d H:i:s', time());//创建时间
                $isHas = M('ccsb')->where("ccsb_code ='".$ipaddress."'  and ccsb_atpstatus is null")->getField('ccsb_atpid');
                if($isHas){
                    $hasIP[]    = $ipaddress;
                    $hasIPids[] = $isHas;
                    $data['ccsb_atplastmodifyuser'] = I('session.username', '');
                    $data['ccsb_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
                    $datas[] = " select '".makeGuid()."','".$data['ccsb_code']."','".$data['ccsb_name']."','".$data['ccsb_factory']."','".$data['ccsb_model']."','".$data['ccsb_secret']."','".$data['ccsb_usage']."','".$data['ccsb_dept']."','".$data['ccsb_didian']."','".$data['ccsb_status']."','".$data['ccsb_sn']."','".$data['ccsb_qiyong']."','".$data['ccsb_dutyman']."','".$data['ccsb_atpcreateuser']."','".$data['ccsb_atpcreatedatetime']."','".$data['ccsb_atplastmodifyuser']."','".$data['ccsb_atplastmodifydatetime']."' from dual ";
                }else{
                    $datas[] = " select '".makeGuid()."','".$data['ccsb_code']."','".$data['ccsb_name']."','".$data['ccsb_factory']."','".$data['ccsb_model']."','".$data['ccsb_secret']."','".$data['ccsb_usage']."','".$data['ccsb_dept']."','".$data['ccsb_didian']."','".$data['ccsb_status']."','".$data['ccsb_sn']."','".$data['ccsb_qiyong']."','".$data['ccsb_dutyman']."','".$data['ccsb_atpcreateuser']."','".$data['ccsb_atpcreatedatetime']."','','' from dual ";
                    $addcount++;
                }
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_ccsb (ccsb_atpid,ccsb_code,ccsb_name,ccsb_factory,ccsb_model,ccsb_secret,ccsb_usage,ccsb_dept,ccsb_didian,ccsb_status,ccsb_sn,ccsb_qiyong,ccsb_dutyman,ccsb_atpcreateuser,ccsb_atpcreatedatetime,ccsb_atplastmodifyuser,ccsb_atplastmodifydatetime) ".$datas;
            M('ccsb')->execute($sql);
        }
        if($hasIPids){
            $hasIPids = implode("','",$hasIPids);
            M('ccsb')->where("ccsb_atpid in ('".$hasIPids."')")->setField("ccsb_atpstatus",'DEL');
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
            //$this->recordLog('import', 'account',"新增$addcount"."条",'ccsb','');
            exit($this->makeStandResult(0,"新增$addcount"."条"));
        }
    }



}





?>

