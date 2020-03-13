<?php
namespace Home\Controller;

use Think\Controller;

class ProcessController extends BaseController
{

    public function index()
    {
        $id = I('get.uuid');
//        $id = 'T826A39C662E54A5F93DB6865';
        $data = M('xingwei')->where("xw_atpid = '%s'",$id)->getField('xw_content');
        if(empty($id)){
            unset($_SESSION['guaranteeUser']);
            unset($_SESSION['indexUser']);
            unset($_SESSION['AraeData']);
            unset($_SESSION['fillUser']);
            unset($_SESSION['read']);
            $model = M('sysuser s');
            $userid = session('user_id');
            $list = $model->field('user_name,user_realusername,org_fullname,user_type,user_jobtype,user_secretlevel,user_card')
                    ->join('org o on o.org_id = s.user_orgid')
                    ->where("user_id = '%s'",$userid)
                    ->find();
            $org = explode('-',$list['org_fullname']);
            $domainusername = $list['user_name'];
            $username = $list['user_realusername'];
            $deptname = $org[0];
            $officename = $org[1];
            $type = $list['user_type'];
            $yong = $list['user_jobtype'];
            $miji = $list['user_secretlevel'];
            $card = $list['user_card'];
        }else{
            $data = explode('||',$data);
            if(count($data) == '4'){
                session('indexUser',explode(';',$data[0]));
                session('guaranteeUser',explode(';',$data[1]));
                session('fillUser',explode(';',$data[2]));
                session('read',explode(';',$data[3]));
            }else{
                session('indexUser',explode(';',$data[0]));
                session('fillUser',explode(';',$data[1]));
                session('read',explode(';',$data[2]));

            }
            $User = $data[0];
            $User = explode(';',$User);
            $domainusername = M('sysuser')->where("user_name = '%s'",$User[1])->getField('user_realusername');
            $username = $User[1];
            $deptname = $User[0];
            $officename = $User[2];
            $type = $User[3];
            $yong = $User[4];
            $miji = $User[5];
            $card = $User[6];
            $this->assign('id',$id);
        }
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
            $username = $guaranteelist[0];
            $realusername = M('sysuser')->where("user_name = '%s'",$username)->getField('user_realusername');
            $dept = $guaranteelist[1];
            $office = $guaranteelist[2];
            $job = $guaranteelist[3];
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
        $list= session('indexUser');
        $username = $list['username'];
        $orgId = M('sysuser')->where("user_name = '%s'",$username)->getField('user_orgid');
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.user_name id,p.user_realusername||'('||p.user_name||')' text from  sysuser p inner join org o on o.org_id = p.user_orgid where
                    (p.user_name like '%".$q."%' or p.user_realusername like '%".$q."%') and p.user_issystem = '否' and user_enable = '启用' and user_isdelete = 0 and user_orgid = '".$orgId."'";
        $result=$Model->query($sql_select);
        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    public function getUserData(){
        $username = I('post.username');
        $model = M('sysuser s');
        $list = $model->field('user_name,user_realusername,org_fullname,user_type,user_jobtype,user_secretlevel,user_card,user_job')
            ->join('org o on o.org_id = s.user_orgid')
            ->where("user_name = '%s'",$username)
            ->find();
        $org = explode('-',$list['org_fullname']);
        $domainusername = $list['user_name'];
        $username = $list['user_realusername'];
        $deptname = $org[0];
        $officename = $org[1];
        $type = $list['user_type'];
        $yong = $list['user_jobtype'];
        $miji = $list['user_secretlevel'];
        $card = $list['user_card'];
        $arr = [$domainusername,$username,$deptname,$officename,$type,$yong,$miji,$card];
        if(!empty($list)){
            exit(makeStandResult(1, $arr));
        }else{
            exit(makeStandResult(-1, 'error!!!'));
        }
    }

    public function getUser(){
        $username = I('post.username');
        $model = M('sysuser s');
        $list = $model->field('user_name,user_realusername,org_fullname,user_type,user_jobtype,user_secretlevel,user_card,user_job')
            ->join('org o on o.org_id = s.user_orgid')
            ->where("user_name = '%s'",$username)
            ->find();
        $org = explode('-',$list['org_fullname']);
        $job = $list['user_job'];
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
        if(!empty($fillUser)){
            $this->assign('area',$fillUser[0]);
            $this->assign('louyu',$fillUser[1]);
            $this->assign('roomno',$fillUser[2]);
            $this->assign('card',$fillUser[3]);
            $this->assign('mac',$fillUser[4]);
            $this->assign('zhong',$fillUser[5]);
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
        $this->assign('user',$user);
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
            $reads = explode(',',$read[0]);
            $this->assign('read',$reads);
            $this->assign('qita',$read[1]);
        }
        $this->display();
    }

    public function read(){
        $data = I('get.');
        session('read',$data);
        $indexlist = session('indexUser');
        $guaranteelist = session('guaranteeUser');
        $filllist = session('fillUser');
        $this->assign('data',$data);
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
        $indexlists = implode(';',$indexlist);
        $guaranteelist = implode(';',$guaranteelist);
        $filllist = implode(';',$filllist);
        $readlist = implode(';',$readlist);

        if(!empty($guaranteelist)){
            $list = $indexlists.'||'.$guaranteelist.'||'.$filllist.'||'.$readlist;
        }else{
            $list = $indexlists.'||'.$filllist.'||'.$readlist;
        }
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