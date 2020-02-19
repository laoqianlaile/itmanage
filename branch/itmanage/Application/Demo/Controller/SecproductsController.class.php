<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class SecproductsController extends BaseController {

    public function index(){
        $Datas = D('Dictionary')->assignsbtype();
        $Model    = M('dictionary');
        $data     = $Model->where("d_parentid='guidF5C36989-8993-4112-9E69-20DB4D4220BB'")->order('d_sortno')->field('d_dictname,d_atpid')->select();
        $this->assign('ds_sbtype',$data);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->assign('ds_cj',$Datas[6]);
        $this->display();
    }
    public function add(){
        $Datas = D('Dictionary')->assignsbtype();
        $Model    = M('dictionary');
        $data     = $Model->where("d_parentid='guidF5C36989-8993-4112-9E69-20DB4D4220BB'")->order('d_sortno')->field('d_dictname,d_atpid')->select();
        $this->assign('ds_sbtype',$data);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }
    public function edit(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $id = $_GET['id'];
        if ($id) {
            $Model = M("secproducts");
            $data = $Model->where("secproducts_atpid='%s'", array($id))->find();
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
                $Model = M("secproducts");
                foreach ($array as $id) {
                    $data    = $Model->where("secproducts_atpid='%s'", $id)->find();
                    $data['secproducts_atpstatus'] = 'DEL';
                    $data['secproducts_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
                    $data['secproducts_atplastmodifyuser'] = I('session.username','');
                    $Model->where("secproducts_atpid='%s'", $id)->save($data);
                    $this->recordLog('delete', 'account','删除','secproducts',$data['secproducts_atpid']);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M('secproducts');
        $where=[];
        $where[0]['secproducts_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['secproductsname'])))
        {
            $where[0]['lower(secproducts_name)']=['like',"%".strtolower(trim($queryparam['secproductsname']))."%"];
        }
        if(!empty(trim($queryparam['ipaddress'])))
        {
            $where[0]['secproducts_ip']=['like',"%".trim($queryparam['ipaddress'])."%"];
        }
        if(!empty(trim($queryparam['factory'])))
        {
            $where[0]['secproducts_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(secproducts_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['amount'])))
        {

            $where[0]['secproducts_num']=['like',"%".trim($queryparam['amount'])."%"];
        }
        if(!empty(trim($queryparam['zsnum'])))
        {
            $where[0]['lower(secproducts_certsn)']=['like',"%".strtolower(trim($queryparam['zsnum']))."%"];
        }
        if(!empty(trim($queryparam['area'])))
        {
            $where[0]['secproducts_area']=['like',"%".trim($queryparam['area'])."%"];
        }
        if(!empty(trim($queryparam['building'])))
        {
            $where[0]['secproducts_building']=['like',"%".trim($queryparam['building'])."%"];
        }
        if(!empty(trim($queryparam['room'])))
        {
            $where[0]['secproducts_room']=['like',"%".trim($queryparam['room'])."%"];
        }
        if(!empty(trim($queryparam['xh'])))
        {
            $where[0]['lower(secproducts_model)']=['like',"%".strtolower(trim($queryparam['xh']))."%"];
        }
        $Result=$Model->where($where)
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->limit( $queryparam['limit'],$queryparam['offset'])
            ->select();
        //print_r($Result);die;
        $Count=$Model->where($where)->count();
        foreach($Result as $key=> &$value){
            $value['secproducts_building']=$this->getdicname($value['secproducts_building']);
            $value['secproducts_area']=$this->getdicname($value['secproducts_area']);
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
            $Model = M("secproducts");
            $data = $Model->where("secproducts_atpid='%s'", array($id))->find();
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
        $column = Array('产品名称','IP地址','厂家','型号','楼宇','房间号','数量','用途','证书编号','区域名');
        $thead  = array_diff($column,$excelsheet[1]);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }

        $cc = count($excelsheet);
        if (count($excelsheet) > 1) {
            $datas    = [];
            $addcount=0;
            for($i=2;$i<=$cc;$i++)
            {
                $data = array();
                //$data['server_atpid']        = $this->makeGuid();
                $data['secproducts_name']   = $excelsheet[$i]['B']; //产品名称
                $data['secproducts_ip']         = $excelsheet[$i]['C']; //IP地址
                $data['secproducts_factory']  = $excelsheet[$i]['D']; //厂家
                $data['secproducts_model']        = $excelsheet[$i]['E']; //型号
                $data['secproducts_usage']      = $excelsheet[$i]['I']; //用途
                $data['secproducts_area']         = $excelsheet[$i]['K']; //区域名
                if(empty($data['secproducts_area']))
                {
                    exit(makeStandResult(4,"区域名存在空列，请修改！"));
                }
                $areaid=$this->getdicid($data['secproducts_area'],'region');
                if(empty($areaid))
                {
                    exit(makeStandResult(4,"区域名".$data['secproducts_area']."不存在，请在字典中添加后在导入"));
                }
                $data['secproducts_area']=$areaid;
                $data['secproducts_building']  = $excelsheet[$i]['F'];//楼宇
                if(empty($data['secproducts_building']))
                {
                    exit(makeStandResult(4,"楼宇存在空列，请修改！"));
                }
                $buildid=$this->getdicid($data['secproducts_building'],$areaid);
                if(empty($buildid))
                {
                    exit(makeStandResult(4,"楼宇".$data['secproducts_building']."不存在，请在字典中添加后在导入"));
                }
                $data['secproducts_building']=$buildid;
                $data['secproducts_room'] = $excelsheet[$i]['G']; //房间号
                $data['secproducts_num'] = $excelsheet[$i]['H']; //数量
                $data['secproducts_certsn'] = $excelsheet[$i]['J']; //证书编号

                $data['secproducts_atpcreateuser'] = I('session.username', '');//创建人
                $data['secproducts_atpcreatetime'] = date('Y-m-d H:i:s', time());//创建时间

                $datas[] = " select '".makeGuid()."','".$data['secproducts_name']."','".$data['secproducts_ip']."','".$data['secproducts_factory']."','".$data['secproducts_model']."','".$data['secproducts_usage']."','".$data['secproducts_building']."','".$data['secproducts_room']."','".$data['secproducts_area']."','".$data['secproducts_num']."','".$data['secproducts_certsn']."','".$data['secproducts_atpcreateuser']."','".$data['secproducts_atpcreatetime']."','','' from dual ";
                $addcount++;
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_secproducts (secproducts_atpid,secproducts_name,secproducts_ip,secproducts_factory,secproducts_model,secproducts_usage,secproducts_building,secproducts_room,secproducts_area,secproducts_num,secproducts_certsn,secproducts_atpcreateuser,secproducts_atpcreatedatetime,secproducts_atplastmodifyuser,secproducts_atplastmodifydatetime) ".$datas;
            //echo $sql;die;
            M('secproducts')->execute($sql);
        }
        $count = $cc-1;
        $content = "Excelp批量上传信息".$count."条";
        $this->recordLog('import', 'account',"新增$addcount"."条",'secproducts','');
        exit($this->makeStandResult(0,"新增$addcount"."条"));
    }

    public function submit(){
        $Model = M('secproducts');
        $data = $Model->create();
        try{
            if(null==$data['secproducts_atpid']) {
                $data['secproducts_atpid'] = $this->makeGuid();
                $data['secproducts_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
                $data['secproducts_atpcreateuser'] = I('session.username', '');
                $content = '';
                foreach ($data as $key => $val) {
                    if (!empty($val)) $content .= $key . ":" . $val . ";";
                }
                $Model->add($data);
                $this->recordLog('add', 'terminal',$content,'terminal',$data['zd_atpid']);
                $this->ajaxReturn("success");
            }
            else
            {
                $oldmsgs = $Model->where("secproducts_atpid='%s'",array($data['secproducts_atpid']))->find();
                $data['secproducts_atplastmodifydatetime']=date('Y-m-d H:i:s', time());
                $data['secproducts_atplastmodifyuser']= I('session.username', '');
                $Model->where("secproducts_atpid='%s'",array($data['secproducts_atpid']))->save($data);
                $content = '';
                $diff  = array_diff($oldmsgs,$data);
                foreach($diff as $key=>$val){
                    if(!empty($val) || !empty($data[$key]) || ($key == 'secproducts_atpcreatedatetime') || ($key == 'secproducts_atplastmodifydatetime')) $content .= $key."：".$val."-".$data[$key]."；";
                }
                $this->recordLog('update', 'account',$content,'secproducts',$data['secproducts_atpid']);
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