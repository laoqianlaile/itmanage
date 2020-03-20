<?php
namespace Home\Controller;

use Think\Controller;

class ProcessController extends BaseController
{

    public function first(){
        unset($_SESSION['guaranteeUser']);
        unset($_SESSION['indexUser']);
        unset($_SESSION['AraeData']);
        unset($_SESSION['fillUser']);
        unset($_SESSION['read']);
        unset($_SESSION['Rtype']);
//        $id = I('get.uuid');
        $id = 'TC841EABB102B4123A5D992C5';
        $data = M('xingwei')->where("xw_atpid = '%s'",$id)->getField('xw_content');
        $data = json_decode($data,true);
        if(!empty($data)){
            session('indexUser',$data[0]);
            session('guaranteeUser',$data[1]);
            session('fillUser',$data[2]);
            session('read',$data[3]);
            $this->assign('Rtype',$data[4]);
        }
        $this->assign('uuid',$id);
        $this->display();
    }

    public function inAll(){
        $ids = I('post.bian');
        $id = explode(',',$ids);
        if(count($id) > 1){
            exit(makeStandResult(-1, '只能选择一项！'));
        }else{
            exit(makeStandResult(1, 'success'));
        }
    }

    public function index()
    {
        $id = I('get.uuid');
        $Rtype = I('get.Rtype');
//         $id = 'TA74CEB1C038646F4A0C58618';
        session('Rtype',$Rtype);
//        $data = M('xingwei')->where("xw_atpid = '%s'",$id)->getField('xw_content');
//        $data = json_decode($data,true);
        $User = session('indexUser');
        if(empty($User)){
//            unset($_SESSION['guaranteeUser']);
//            unset($_SESSION['indexUser']);
//            unset($_SESSION['AraeData']);
//            unset($_SESSION['fillUser']);
//            unset($_SESSION['read']);
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
        }else{
//            session('indexUser',$data[0]);
//            session('guaranteeUser',$data[1]);
//            session('fillUser',$data[2]);
//            session('read',$data[3]);
//            $User = $data[0];
            $username = M('it_person')->where("domainusername = '%s'",$User['username'])->getField('realusername');
            $domainusername = $User['username'];
            $deptname = $User['dept'];
            $officename = $User['office'];
            $type = $User['type'];
            $yong = $User['yong'];
            $miji = $User['miji'];
            $card = $User['card'];
            $this->assign('id',$id);
        }
        $this->assign('Rtype',$Rtype);
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
        $guaranteelist = session('guaranteeUser');
        if(!empty($guaranteelist)){
            $username = $guaranteelist['username'];
            $realusername = M('it_person')->where("domainusername = '%s'",$username)->getField('realusername');
            $dept = $guaranteelist['dept'];
            $office = $guaranteelist['office'];
            $job = $guaranteelist['job'];
            $this->assign('username',$username);
            $this->assign('realusername',$realusername);
            $this->assign('dept',$dept);
            $this->assign('office',$office);
            $this->assign('job',$job);
        }
        $this->assign('domainusername',$data['domainusername']);
        $this->display();
    }


    public function getOrg(){
        // $list= session('indexUser');
        // $username = $list['username'];
        // $orgId = M('it_person')->where("domainusername = '%s'",$username)->getField('orgid');
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
        $fillUser = session('fillUser');
//        print_r($fillUser);die;
        if(!empty($fillUser)){
            $this->assign('area',$fillUser['area']);
            $this->assign('louyu',$fillUser['louyu']);
            $this->assign('roomno',$fillUser['roomno']);
            $this->assign('card',$fillUser['card']);
            $this->assign('mac',$fillUser['mac']);
            $this->assign('zhong',$fillUser['zhong']);
            $this->assign('zichan',$fillUser['zichan']);
            $this->assign('sk',explode(',',$fillUser['sk']));
            $this->assign('hui',$fillUser['hui']);
            $this->assign('ji',$fillUser['ji']);
            $this->assign('isSk',$fillUser['isSk']);
            $this->assign('Smac',$fillUser['Smac']);
        }
        $AraeData = session('AraeData');
        if(!empty($AraeData)){
            $this->assign('area',$AraeData['0']);
            $this->assign('louyu',$AraeData['1']);
            $this->assign('roomno',$AraeData['2']);
            $this->assign('miji',$AraeData['3']);
        }
        $arr = ['地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('biao',$biao);
        $this->assign('Rtype',$Rtype);
        $this->display();
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
//        $arr = url($user);//对接接口，获取设备
        $arr = [
            ['type'=>'111','area'=>'白石桥','louyu'=>'白石桥南站','roomno'=>'111','miji'=>'秘密'],
            ['type'=>'222','area'=>'白石桥','louyu'=>'白石桥北站','roomno'=>'222','miji'=>'机密'],
            ['type'=>'333','area'=>'航天城','louyu'=>'航天城1号','roomno'=>'333','miji'=>'非密']
        ];
        $this->assign('arr',$arr);
        $this->display();
    }

    public function getSel(){
        $data = I('post.type');
        $arr = explode('-',$data);
        if(!empty($data)){
            session('AraeData',$arr);
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

    public function app(){
        $data = I('get.');
        $data['area'] = $this->getDicById($data['area'], 'dic_name'); //地区
        $data['louyu'] = $this->getDicLouYuById($data['louyu'], 'dic_name'); //楼宇
        session('fillUser',$data);
        $list = session('indexUser');
        if($list['dept'] == '总体部' && $list['type'] == '临时人员'){
            $biao = 1;
        }else{
            $biao = 2;
        }
        $this->assign('biao',$biao);
        $read = session('read');
        if(!empty($read)){
            $reads = explode(',',$read['many']);
            $this->assign('read',$reads);
            $this->assign('qita',$read['qita']);
        }
        $this->display();
    }

    public function read(){
        $data = I('get.');
        if($data['biao'] == 1){
            $data['area'] = $this->getDicById($data['area'], 'dic_name'); //地区
            $data['louyu'] = $this->getDicLouYuById($data['louyu'], 'dic_name'); //楼宇
            session('fillUser',$data);
        }else if($data['biao'] == 2){
            session('read',$data);
        }
        $Rtype = session('Rtype');
        $indexlist = session('indexUser');
        $guaranteelist = session('guaranteeUser');
        $filllist = session('fillUser');
        $read = session('read');
        $this->assign('Rtype',$Rtype);
        $this->assign('data',$read);
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
        $Rtype = session('Rtype');
        $list = [$indexlist,$guaranteelist,$filllist,$readlist,$Rtype];
        $list= json_encode($list);
        $data = [];
        if(empty($indexlist['uuid'])){
            $data['xw_atpid'] = makeGuid();
        }else{
            $data['xw_atpid'] = $indexlist['uuid'];
        }
        $arr = [];
        $arr['uuid'] = $data['xw_atpid'];
        $arr['Sdept'] = $indexlist['dept'];
        $arr['Susername'] = $indexlist['username'];
        $arr['Soffice'] = $indexlist['office'];
        $arr['Stype'] = $indexlist['type'];
        $arr['Syong'] = $indexlist['yong'];
        $arr['Smiji'] = $indexlist['miji'];
        $arr['Scard'] = $indexlist['card'];
        if(!empty($guaranteelist)){
            $arr['Dusername'] = $guaranteelist['username'];
            $arr['Ddept'] = $guaranteelist['dept'];
            $arr['Doffice'] = $guaranteelist['office'];
            $arr['Djob'] = $guaranteelist['job'];
        }
        $arr['Jarea'] = $filllist['area'];
        $arr['Jlouyu'] = $filllist['louyu'];
        $arr['Jroomno'] = $filllist['roomno'];
        $arr['Jcard'] = $filllist['card'];
        $arr['Jmac'] = $filllist['mac'];
        $arr['Jzhong'] = $filllist['zhong'];
        $arr['Ymany'] = $readlist['many'];
        $arr['Ymany'] = $readlist['qita'];
        $arr = json_encode($arr,true);
//        $arr = url($arr);//对接接口
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