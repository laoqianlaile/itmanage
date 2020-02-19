<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class HlwjsjController extends BaseController {

    public function index(){
        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function edit(){
        $id = I('get.id');
        if ($id) {
            $Model = D("Hlwjsj");
            $data = $Model->where("hlwjsj_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('hData', $data);
            }
        }
        $this->display('add');
    }
    public function del()
    {
        try {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            if (count($ids) > 0) {
                $Model = D('Hlwjsj');
                $ids   = implode("','",$ids);
                $data  = [];
                $data['hlwjsj_atpstatus'] = 'DEL';
                $data  = $Model->create($data,2);
                $Model->where("hlwjsj_atpid in ('$ids')")->save($data);
            }
            exit(makeStandResult(0,'删除成功'));
        } catch (\Exception $e) {
            exit(makeStandResult(1,"delete muli row fail" . $e));
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Hlwjsj')->getData($queryparam);
        $Result = $res[0];
        $Count = $res[1];
        echo json_encode(array( 'total' => $Count,'rows' => $Result));
    }

    public function view(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("server");
            $data = $Model->where("hlwjsj_atpid='%s'", array($id))->find();
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
        // 序号   编号  名称  IP地址    MAC地址   用途  所属部门    放置地点    集团许可证编号 使用情况    厂家  型号  操作系统及版本 启用时间    操作系统安装日期    硬盘序列号   责任人
        $column = Array("序号","编号","名称","IP地址","MAC地址","用途","所属部门","放置地点","集团许可证编号","使用情况","厂家","型号","操作系统及版本","启用时间","操作系统安装日期","硬盘序列号","责任人");
        $thead  = array_filter($excelsheet[1],'notEmpty');
        $thead  = array_diff($column,$thead);
        if($thead){
            exit(makeStandResult(2,"请按照模板填写导入数据，保持列头一致"));
        }

        $cc = count($excelsheet);
        if (count($excelsheet) > 2) {
            $datas = [];
            for($i=2;$i<=$cc;$i++)
            {
                $hlwjsj_name = $excelsheet[$i]['C'];
                if(empty($hlwjsj_name)) continue;
                $data = array();
                $data['hlwjsj_code']    = $excelsheet[$i]['B'];
                $data['hlwjsj_name']    = $hlwjsj_name;
                $data['hlwjsj_ip']      = $excelsheet[$i]['D'];
                $data['hlwjsj_mac']     = $excelsheet[$i]['E'];
                $data['hlwjsj_usage']   = $excelsheet[$i]['F'];
                $data['hlwjsj_dept']    = $excelsheet[$i]['G'];
                $data['hlwjsj_didian']  = $excelsheet[$i]['H'];
                $data['hlwjsj_jtxkz']   = $excelsheet[$i]['I'];
                $data['hlwjsj_status']  = $excelsheet[$i]['J'];
                $data['hlwjsj_factory'] = $excelsheet[$i]['K'];
                $data['hlwjsj_model']   = $excelsheet[$i]['L'];
                $data['hlwjsj_os']      = $excelsheet[$i]['M'];
                $data['hlwjsj_qiyong']  = $excelsheet[$i]['N'];
                $data['hlwjsj_osdate']  = $excelsheet[$i]['O'];
                $data['hlwjsj_disknum'] = $excelsheet[$i]['P'];
                $data['hlwjsj_dutyman'] = $excelsheet[$i]['Q'];

                $datas[] = " select '".makeGuid()."','".$data['hlwjsj_code']."','".$data['hlwjsj_name']."','".$data['hlwjsj_ip']."','".$data['hlwjsj_mac']."','".$data['hlwjsj_usage']."','".$data['hlwjsj_dept']."','".$data['hlwjsj_didian']."','".$data['hlwjsj_jtxkz']."','".$data['hlwjsj_status']."','".$data['hlwjsj_factory']."','".$data['hlwjsj_model']."','".$data['hlwjsj_os']."','".$data['hlwjsj_qiyong']."','".$data['hlwjsj_osdate']."','".$data['hlwjsj_disknum']."','".$data['hlwjsj_dutyman']."','".getUserId()."','".getDatetime()."' from dual ";
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_hlwjsj (hlwjsj_atpid,hlwjsj_code,hlwjsj_name,hlwjsj_ip,hlwjsj_mac,hlwjsj_usage,hlwjsj_dept,hlwjsj_didian,hlwjsj_jtxkz,hlwjsj_status,hlwjsj_factory,hlwjsj_model,hlwjsj_os,hlwjsj_qiyong,hlwjsj_osdate,hlwjsj_disknum,hlwjsj_dutyman,hlwjsj_atpcreateuser,hlwjsj_atpcreatedatetime) ".$datas;
            $res = M('hlwjsj')->execute($sql);
        }
        if(!empty($res)){
            exit($this->makeStandResult(0,"导入数据".$res."条"));
        }else{
            exit($this->makeStandResult(1,"导入数据失败"));
        }
    }

    public function submit(){
        $Model = D('Hlwjsj');
        $data  = I('post.');
        try{
            if(null==$data['hlwjsj_atpid']) {
                $data  = $Model->create($data,1);
                $Model->add($data);
                $this->ajaxReturn("success");
            }
            else
            {
                $data  = $Model->create($data,2);
                $Model->where("hlwjsj_atpid='%s'",array($data['hlwjsj_atpid']))->save($data);
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
            echo $e;
        }
    }
}