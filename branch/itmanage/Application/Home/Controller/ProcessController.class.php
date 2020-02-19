<?php
namespace Home\Controller;

use Think\Controller;

class ProcessController extends Controller
{

    public function index()
    {

        unset($_SESSION['indexUser']);
        unset($_SESSION['guaranteeUser']);
        unset($_SESSION['fillUser']);
//        $username = session('realusername');
//        addLog("", "用户访问日志", "", "访问应用系统页面", "成功");
        $domainusername = 'hq\lihubin';
        $username = '虎斌';
        $deptname = '总体部';
        $officename = '科研室';
        $type = '临时人员';
        $yong = '全日制';
        $miji = '秘密';
        $card = '140322199407010213';
        $this->assign('miji',$miji);
        $this->assign('card',$card);
        $this->assign('yong',$yong);
        $this->assign('type',$type);
        $this->assign('domainusername',$domainusername);
        $this->assign('username',$username);
        $this->assign('deptname',$deptname);
        $this->assign('officename',$officename);
        $this->display();
    }


    //担保信息核实
    public function guarantee(){
        $data = I('get.');
        session('indexUser',$data);
        if($data['username'] == '虎斌'){
            $user = ['纪纬'];
        }else if($data['username'] == '云迪'){
            $user = ['马培'];
        }else if($data['username'] == '宝宝'){
            $user = ['东哥'];
        }
        $this->assign('user',$user);
        $this->display();
    }


    public function getUser(){
//        $dept = '五院信息中心';
//        $office = '科研室';
//        $job = '组长';
//        $result = [$dept,$office,$job];
//        echo json_encode($result);
    }


    public function fill(){
        $data = I('get.');
        if($data['order'] == 1){
            session('indexUser',$data);
        }else{
            session('guaranteeUser',$data);
        }

        $list = session('indexUser');
        $dept = $list['dept'];
        $first = ['总体部'];
        $second = ['总环','物资','动保','通信','卫星应用'];
        $three = ['综合管理层','钱学森实验室'];
        if(in_array($dept,$first)){
            $biao = 1;
        }else if(in_array($dept,$second)){
            $biao = 2;
        }else if(in_array($dept,$three)){
            $biao = 3;
        }
        $this->assign('biao',$biao);
        $this->display();
    }

    public function app(){
        $data = I('get.');
        session('fillUser',$data);
        $list = session('indexUser');
        if($list['dept'] == '总体部' && $list['type'] == '临时人员'){
            $biao = 1;
        }else{
            $biao = 2;
        }
        $this->assign('biao',$biao);
        $this->display();
    }

    public function read(){
        $data = I('get.');
//        print_r($data);die;
        $indexlist = session('indexUser');
        $guaranteelist = session('guaranteeUser');
        $filllist = session('fillUser');
        $this->assign('data',$data);
        $this->assign('indexList',$indexlist);
        $this->assign('guaranteeList',$guaranteelist);
        $this->assign('fillList',$filllist);
        $this->display();
    }


}