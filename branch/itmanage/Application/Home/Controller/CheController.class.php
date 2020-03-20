<?php
namespace Home\Controller;

use Think\Controller;

class CheController extends BaseController
{


    public function index()
    {
        $model = M('it_person s');
        $userid = session('domainusername');
        $list = $model->field('domainusername,realusername,fullname,name,type,jobtype,secretlevel,card')
                ->join('it_depart o on o.id = s.orgid')
                ->where("domainusername = '%s'",$userid)
                ->find();
        $org = explode('-',$list['fullname']);
        $domainusername = $list['domainusername'];
        $username = $list['realusername'];
        $deptname = $org[0];
        $officename = $list['name'];
        $type = $list['type'];
        $yong = $list['jobtype'];
        $miji = $list['secretlevel'];
        $card = $list['card'];
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
        $this->assign('domainusername',$data['domainusername']);
        $this->display();
    }


    public function getOrg(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.domainusername id,p.realusername||'('||p.domainusername||')' text from  it_person p inner join it_depart o on o.id = p.orgid where
                    (p.domainusername like '%".$q."%' or p.realusername like '%".$q."%')";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    public function isOrg(){
        $user = I('post.username');
        $data = session('indexUser');
        $userIndex = $data['username'];
        $id = M('it_person')->where("domainusername = '%s'",$user)->getField('orgid');
        $ids = M('it_person')->where("domainusername = '%s'",$userIndex)->getField('orgid');
        if($id == $ids){
            echo 'success';    
        }else{
            echo 'error';    
        }
    }

    public function getUserData(){
        $username = I('post.username');
        $model = M('it_person s');
        $list = $model->field('domainusername,realusername,name,fullname,type,jobtype,secretlevel,card,job')
            ->join('it_depart o on o.id = s.orgid')
            ->where("domainusername = '%s'",$username)
            ->find();
        $org = explode('-',$list['fullname']);
        $domainusername = $list['domainusername'];
        $username = $list['realusername'];
        $deptname = $org[0];
        $officename = $list['name'];
        $type = $list['type'];
        $yong = $list['jobtype'];
        $miji = $list['secretlevel'];
        $card = $list['card'];
        $arr = [$domainusername,$username,$deptname,$officename,$type,$yong,$miji,$card];
        if(!empty($list)){
            exit(makeStandResult(1, $arr));
        }else{
            exit(makeStandResult(-1, 'error!!!'));
        }
    }

    public function getUser(){
        $username = I('post.username');
        $model = M('it_person s');
        $list = $model->field('domainusername,realusername,fullname,type,jobtype,secretlevel,card,job')
            ->join('it_depart o on o.id = s.orgid')
            ->where("domainusername = '%s'",$username)
            ->find();
        $org = explode('-',$list['fullname']);
        $job = $list['job'];
        $deptname = $org[0];
        $officename = $org[1];
        $arr = [$deptname,$officename,$job];
        if(!empty($list)){
            exit(makeStandResult(1, $arr));
        }else{
            exit(makeStandResult(-1, 'error!!!'));
        }
    }


    public function fill(){
        $data = I('get.');
        if($data['order'] == 1){
            session('indexUser',$data);
        }else{
            session('guaranteeUser',$data);
        }
        $Rtype = session('Rtype');

        $AraeData = session('AraeData');
//        print_r($AraeData);die;
        if(!empty($AraeData)){
            $list = M('it_terminal')->where("zd_atpid = '%s'",$AraeData)->find();
            $this->assign('zd_type',$list['zd_type']);
            $this->assign('zd_devicecode',$list['zd_devicecode']);
            $this->assign('zd_secretlevel',$list['zd_secretlevel']);
            $this->assign('zd_area',$list['zd_area']);
            $this->assign('zd_belongfloor',$list['zd_belongfloor']);
            $this->assign('zd_roomno',$list['zd_roomno']);
            $this->assign('zd_macaddress',$list['zd_macaddress']);
            $this->assign('zd_ipaddress',$list['zd_ipaddress']);
            $this->assign('biao',1);
        }
        $User=session('indexUser');
        $user=$User['username'];

        $type = M('it_terminal')->field('zd_type')->where("zd_dutyman = '%s'",$user)->group('zd_type')->select();
        $code = M('it_terminal')->field('zd_devicecode')->where("zd_dutyman = '%s'",$user)->group('zd_devicecode')->select();
        $this->assign('type',$type);
        $this->assign('code',$code);
        $this->assign('Rtype',$Rtype);
        $this->display();
    }

    public function getData(){
        $type = I('post.type');
        $code = I('post.code');
        $User=session('indexUser');
        $user=$User['username'];
        $where = [];
        $where['zd_dutyman'] = ['eq',$user];
        $where['zd_type'] = ['eq',$type];
        $where['zd_devicecode'] = ['eq',$code];
        $list = M('it_terminal')->field('zd_secretlevel,zd_area,zd_belongfloor,zd_roomno,zd_macaddress,zd_ipaddress')->where($where)->find();
        if(!empty($list)){
            exit(makeStandResult(1, $list));
        }else{
            exit(makeStandResult(-1, "没找到数据!"));
        }

    }

    public function user(){
        $data = I('get.');
        $data['area'] = $this->getDicById($data['area'], 'dic_name'); //地区
        $data['louyu'] = $this->getDicLouYuById($data['louyu'], 'dic_name'); //楼宇
        session('fillUser',$data);
        $list = session('indexUser');
        $user = $list['username'];
        $realuser = M('it_person')->where("domainusername = '%s'",$user)->getField('realusername');
        $this->assign('$username',$realuser);
        $this->assign('domainusername',$user);
        $this->display();
    }

    public function select(){
        $data=session('indexUser');
        $user=$data['username'];

        $data = M('it_terminal')->field('zd_atpid,zd_name')->where("zd_dutyman = '%s'",$user)->select();
        $this->assign('data',$data);

        $this->display();
    }

    public function getSel(){
        $data = I('post.type');
        if(!empty($data)){
            session('AraeData',$data);
            exit(makeStandResult(1, 'success'));
        }else{
            exit(makeStandResult(-1, 'error'));
        }
    }

    public function isMac(){
        $mac = I('post.mac');
        if ($this->checkAddress($mac, 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        $res = M('it_terminal')->where("zd_macaddress = '%s'",$mac)->getField('zd_atpid');
        if($res){
            exit(makeStandResult(-1, '改终端已入网，请撤网后在填写入网申请'));
        }else{
            exit(makeStandResult(1, 'mac地址正确'));
        }

    }

    public function isMJ(){
        $miji = I('post.miji');
        $mj = C('SecretOrder');
        $user = session('indexUser');
        $UserMj = $user['miji'];
        $Zmj = $mj[$miji];
        $Umj = $mj[$UserMj];
        if($Zmj < $Umj){
            exit(makeStandResult(-1, '密集不一致，确认使用低密级终端'));
        }else if($Zmj > $Umj){
            exit(makeStandResult(-2, '禁止低密级使用'));
        }else{
            exit(makeStandResult(1, 'success'));

        }
    }

    public function first(){
        $data = I('get.');
        session('fillUser',$data);
        $this->display();
    }

    public function miji(){
        $username = I('post.username');
        $miji = M('it_person')->where("domainusername = '%s'",$username)->getField('secretlevel');
        if(!empty($miji)){
            exit(makeStandResult(1, $miji));
        }else{
            exit(makeStandResult(-1, '密级不存在！'));
        }
    }

    public function read(){
        $data = I('get.');
        session('first',$data);
        $indexlist = session('indexUser');
        $guaranteelist = session('guaranteeUser');
        $filllist = session('fillUser');
        $first = session('first');
        $this->assign('data',$first);
        $this->assign('indexList',$indexlist);
        $this->assign('guaranteeList',$guaranteelist);
        $this->assign('fillList',$filllist);
        $this->display();
    }

    public function addData(){
        $indexlist = session('indexUser');
        $guaranteelist = session('guaranteeUser');
        $filllist = session('fillUser');
        $readlist = session('read');
        $list = [$indexlist,$guaranteelist,$filllist,$readlist];
        $list= json_encode($list);
//
//        $data = [];
//        if(empty($indexlist['uuid'])){
            $data['xw_atpid'] = makeGuid();
//        }else{
//            $data['xw_atpid'] = $indexlist['uuid'];
//        }
//        $arr = [];
//        $arr['uuid'] = $data['xw_atpid'];
//        $arr['Sdept'] = $indexlist['dept'];
//        $arr['Susername'] = $indexlist['username'];
//        $arr['Soffice'] = $indexlist['office'];
//        $arr['Stype'] = $indexlist['type'];
//        $arr['Syong'] = $indexlist['yong'];
//        $arr['Smiji'] = $indexlist['miji'];
//        $arr['Scard'] = $indexlist['card'];
//        if(!empty($guaranteelist)){
//            $arr['Dusername'] = $guaranteelist['username'];
//            $arr['Ddept'] = $guaranteelist['dept'];
//            $arr['Doffice'] = $guaranteelist['office'];
//            $arr['Djob'] = $guaranteelist['job'];
//        }
//        $arr['Jarea'] = $filllist['area'];
//        $arr['Jlouyu'] = $filllist['louyu'];
//        $arr['Jroomno'] = $filllist['roomno'];
//        $arr['Jcard'] = $filllist['card'];
//        $arr['Jmac'] = $filllist['mac'];
//        $arr['Jzhong'] = $filllist['zhong'];
//        $arr['Ymany'] = $readlist['many'];
//        $arr['Ymany'] = $readlist['qita'];
//        $arr = json_encode($arr,true);
////        $arr = url($arr);//对接接口
//        $indexlists = implode(';',$indexlist);
//        $guaranteelist = implode(';',$guaranteelist);
//        $filllist = implode(';',$filllist);
//        $readlist = implode(';',$readlist);
//
//        if(!empty($guaranteelist)){
//            $list = $indexlists.'||'.$guaranteelist.'||'.$filllist.'||'.$readlist;
//        }else{
//            $list = $indexlists.'||'.$filllist.'||'.$readlist;
//        }
        $model = M('xingwei');
        $data['xw_content'] = $list;
        if(empty($indexlist['uuid'])){
            $res = $model->add($data);
        }else{
            $res = $model->where("xw_atpid = '%s'",$indexlist['uuid'])->save($data);
        }
        if($res){
            echo 'success';die;
        }else{
            echo 'error';die;
        }

    }


}