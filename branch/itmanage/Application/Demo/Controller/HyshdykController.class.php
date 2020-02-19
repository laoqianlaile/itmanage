<?php
namespace Demo\Controller;
use Think\Controller;
//use Think\Upload;
class HyshdykController extends BaseController {

    public function index(){
        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function edit(){
        $id = I('get.id');
        if ($id) {
            $Model = D("Hyshdyk");
            $data = $Model->where("hyshdyk_atpid='%s'", array($id))->find();
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
                $Model = D('Hyshdyk');
                $ids   = implode("','",$ids);
                $data  = [];
                $data['hyshdyk_atpstatus'] = 'DEL';
                $data  = $Model->create($data,2);
                $Model->where("hyshdyk_atpid in ('$ids')")->save($data);
            }
            exit(makeStandResult(0,'删除成功'));
        } catch (\Exception $e) {
            exit(makeStandResult(1,"delete muli row fail" . $e));
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $res = D('Hyshdyk')->getData($queryparam);
        $Result = $res[0];
        $Count = $res[1];
        echo json_encode(array( 'total' => $Count,'rows' => $Result));
    }

    public function view(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("server");
            $data = $Model->where("hyshdyk_atpid='%s'", array($id))->find();
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
        // 序号   编号  名称  密级（涉密专用）    所属部门    使用情况    责任人
        $column = Array("序号","编号","名称","密级（涉密专用）","所属部门","使用情况","责任人");
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
                $hyshdyk_name = $excelsheet[$i]['C'];
                if(empty($hyshdyk_name)) continue;
                $data = array();
                $data['hyshdyk_code']    = $excelsheet[$i]['B'];
                $data['hyshdyk_name']    = $hyshdyk_name;
                $data['hyshdyk_secret']  = $excelsheet[$i]['D'];
                $data['hyshdyk_dept']    = $excelsheet[$i]['E'];
                $data['hyshdyk_status']  = $excelsheet[$i]['F'];
                $data['hyshdyk_dutyman'] = $excelsheet[$i]['G'];

                $datas[] = " select '".makeGuid()."','".$data['hyshdyk_code']."','".$data['hyshdyk_name']."','".$data['hyshdyk_secret']."','".$data['hyshdyk_dept']."','".$data['hyshdyk_status']."','".$data['hyshdyk_dutyman']."','".getUserId()."','".getDatetime()."' from dual ";
            }
        }
        if(!empty($datas)){
            $datas = implode('union',$datas);
            $sql = "insert into it_hyshdyk (hyshdyk_atpid,hyshdyk_code,hyshdyk_name,hyshdyk_secret,hyshdyk_dept,hyshdyk_status,hyshdyk_dutyman,hyshdyk_atpcreateuser,hyshdyk_atpcreatedatetime) ".$datas;
            $res = M('hyshdyk')->execute($sql);
        }
        if(!empty($res)){
            exit($this->makeStandResult(0,"导入数据".$res."条"));
        }else{
            exit($this->makeStandResult(1,"导入数据失败"));
        }
    }

    public function submit(){
        $Model = D('Hyshdyk');
        $data  = I('post.');
        try{
            if(null==$data['hyshdyk_atpid']) {
                $data  = $Model->create($data,1);
                $Model->add($data);
                $this->ajaxReturn("success");
            }
            else
            {
                $data  = $Model->create($data,2);
                $Model->where("hyshdyk_atpid='%s'",array($data['hyshdyk_atpid']))->save($data);
                $this->ajaxReturn("success");
            }
        }catch (\Exception $e){
            echo $e;
        }
    }
}