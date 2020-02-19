<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/1/2
 * Time: 10:33
 */

namespace Home\Controller;

use Think\Controller;

//巡检项页面
class CheckupController extends BaseController
{
    public function index(){
        addLog("","用户访问日志","","访问巡查项管理页面","成功");
        //查v_atpidall_checkup视图找到所有资产类型
        $type = $this->getType();
        $arr = ['巡检周期','所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('zhouQi', $arrDic['巡检周期']);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('type',$type);
        $this->assign('zhouQi',$arrDic);
        $this->display();
    }

    //查询数据
    public function getData()
    {
        //$filedStr = 'atpid,table,type,agroup,name,admina,adminb';
        $queryParam = I('put.');

        //模糊查询
        $where['atpstatus'] = ['exp', 'IS NULL'];
        $type = trim($queryParam['type']);
        if (!empty($type)) $where['type'] = ['like', "%$type%"];

        $type_name = trim($queryParam['name']);
        if (!empty($type_name)) $where['name'] = ['like', "%$type_name%"];

        $agroup = trim($queryParam['agroup']);
        if (!empty($agroup)) $where['agroup'] = ['like', "%$agroup%"];

        $admina= trim($queryParam['admina']);
        if (!empty($admina)) $where['admina'] = ['like', "%$admina%"];

        $adminb = trim($queryParam['adminb']);
        if (!empty($adminb)) $where['tl_adminb'] = ['like', "%$adminb%"];

        $model = M('v_atpidall_checkup');

        //查询数据库
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");//转换时间
        $count = $model->where($where)->count();
        $obj = $model->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");
        $data = $obj ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        //var_dump($data);die;
        foreach ($data as $k => &$v) {
            //管理员A岗
            if(!empty($v['admina'])){
                $userName = D('org')->getViewPerson($v['admina']);
                // $v['tl_admina'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                $v['admina'] = $userName['realusername'];
            }else{
                $v['admina'] = '-';
            }
            //管理员B岗
            if(!empty($v['adminb'])){
                $userName = D('org')->getViewPerson($v['adminb']);
                //$v['tl_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                $v['adminb'] = $userName['realusername'];
            }else {
                $v['adminb'] = '-';

            }

            $where['appid'] = ['eq', $v['atpid']];
            $rlxCount = M('checkup')->where($where)->count();
            $data[$k]['spCount'] = $rlxCount;
        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }

    //添加
  /*  public function add()
    {
        $id = trim(I('get.appid'));
        $Objtype = trim(I('get.type'));
        $typeName = trim(I('get.type_name'));

        if (!empty($id)) {
            $model = M('checkup_zy');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $data = $model
                ->field('appid,zyid,type,ip,name,admina,adminb')
                ->where("checkupid='%s'", $id)
                ->find();

            //管理员A
            $userId = $data['admina'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminA['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminA['username'] = $userName['username'];

            }else{
                $adminA = [];
            }
            $this->assign('adminA', $adminA);
            //管理员B
            $userId = $data['adminb'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminB['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminB['username'] = $userName['username'];

            }else{
                $adminB = [];
            }
            $this->assign('adminB', $adminB);
            $this->assign('typeName', $typeName);

        }
        $type = $this->getType();
        $this->assign('type',$type);
        $arr = ['周期'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('Objtype', $Objtype);
        $this->assign('zhouQi', $arrDic['周期']);
        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问工具软件管理添加、编辑页面", '成功');
        $this->display();
    }*/
    //编辑
 /*   public function adds()
    {
        $id = trim(I('get.zyid'));
        $type = trim(I('get.type'));
        $typeName = trim(I('get.type_name'));
        $ip = trim(I('get.ip'));

        if (!empty($id)) {
            $model = M('checkup_zy');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $data = $model
                ->field('appid,zyid,type,ip,name,admina,adminb')
                ->where("appid='%s'", $id)
                ->find();

            //管理员A
            $userId = $data['admina'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminA['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminA['username'] = $userName['username'];

            }else{
                $adminA = [];
            }
            $this->assign('adminA', $adminA);
            //管理员B
            $userId = $data['adminb'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminB['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminB['username'] = $userName['username'];

            }else{
                $adminB = [];
            }
            $this->assign('adminB', $adminB);
            $this->assign('typeName', $typeName);

        }
        $arr = ['周期'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('type', $type);
        $this->assign('ip', $ip);
        $this->assign('zhouQi', $arrDic['周期']);
        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问工具软件管理添加、编辑页面", '成功');
        $this->display();
    }*/

    //资产添加修改数据
  /*  public  function addData()
    {
        $data=I('post.');

       // print_r($data);die;
        $model = M('checkup_zy');

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');


        if(empty($data['appid'])){
             //资产id
             if(!empty($data['type_name'])){
                 $data['zyid']   = D('dic')->getTypeIId($data['type_name']);

             }
             //验证资产名称是否存在
             if(!empty($data['zyid']) ) {
                 $id = $model->where("zyid='%s'", $data['zyid'])->find();
                 if ($id['zyid']) {
                     exit(makeStandResult(-1, '资产名称已存在'));
                 }
             }
                 $data['appid'] = makeGuid();
                 $data['createtime'] = $time;
                 $data['createuser'] = $user;
                 $data = $model->create($data);
                    $res = $model->add($data);
                 if(empty($res)){
                     exit(makeStandResult(-1,'添加失败'));
                 }else{
                     exit(makeStandResult(1,'添加成功'));
                 }
         } else {
             $res = $model->where("appid='%s'",$data['appid'])->save($data);
            if(empty($res)){
                exit(makeStandResult(-1,'修改失败'));
            }else{
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }*/

    //巡检项展示页面
    public function getcheckupData()
    {
        $queryParam = json_decode(file_get_contents( "php://input"), true);


        $where['appid']=['eq',$queryParam['atpid']];
        $data = M('checkup')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->limit($queryParam['limit'],$queryParam['offset'])
            ->select();
        $count =  M('checkup')->where($where)->count();


        foreach($data as  $k =>  $v){
            $data[$k]['name'] = $queryParam['name'];
            //$require = $v['require']
          //  $data[$k]['require'] = str_replace('1','<br/>',$v['require']);

           // $data[$k]['content'] = $v['content'];
        }


        //print_r($data[$k]['require']);die;

        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }
    //打开巡检项页面
    public function addCheck(){
        $atpid = trim(I('get.atpid'));
        $name = trim(I('get.name'));

        if($atpid){
            $where['atpid'] = ['eq', $atpid];
            $data = M('v_atpidall_checkup')->where($where)->find();
            $admina = $data['admina'];
            $adminb = $data['adminb'];
            $type   = $data['type'];
           // $name   =$data['name'];
        }

        $this->assign('atpid',$atpid);
        $this->assign('name',$name);
        $this->assign('admina',$admina);
        $this->assign('adminb',$adminb);
        $this->assign('type',$type);
        $this->display();
    }
    //巡检项页面添加编辑页面
    public function editxjnr()
    {
        $atpid = trim(I('get.atpid'));
        $name = trim(I('get.name'));
        $checkupid = trim(I('get.checkupid'));

      // print_r($checkupid);die;
        if(!empty($checkupid))
        {
            $data = M('checkup')->where("checkupid='%s'", $checkupid)->find();
            $this->assign('data', $data);
        }
        //点击巡检项带过去对应资产的管理员岗位和类型
        if($atpid){
            $where['atpid'] = ['eq', $atpid];
            $data = M('v_atpidall_checkup')->where($where)->find();
            $admina = $data['admina'];
            $adminb = $data['adminb'];
            $type   = $data['type'];
            //$name   =$data['name'];
        }

        $arr = ['巡检周期','是否'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('zhouQi', $arrDic['巡检周期']);
        $this->assign('shiFou',$arrDic['是否']);
        $this->assign('atpid',$atpid);
        $this->assign('admina',$admina);
        $this->assign('adminb',$adminb);
        $this->assign('type', $type);
        $this->assign('name',$name);
        $this->display();
    }
    //巡检项页面添加编辑数据
    public function xjnrsubmit()
    {
        $data=I('post.');

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        if(empty($data['checkupid']))
        {
            $data['checkupid']=makeGuid();
            $data['appid'] = $data['atpid'];
            $data['createtime'] = $time;
            $data['createuser'] = $user;

            $data  = M('checkup')->create($data);
            $res=M('checkup')->add($data);
            if(empty($res)){
                exit(makeStandResult(-1,'添加失败'));
            }else{
                exit(makeStandResult(1,'添加成功'));
            }
        }
        else {
            $data['lastmodifytime'] = $time;
            $data['lastmodifyuser'] = $user;
            $res=M('checkup')->where("checkupid='%s'",$data['checkupid'])->save($data);
            if(empty($res)){
                exit(makeStandResult(-1,'修改失败'));
            }else{
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }
    //巡检项页面删除
    public function delxjnr(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $idStr = "'".implode("','", $id)."'";
        $model = M('checkup');
        $res = $model -> where("checkupid in ($idStr)")->delete();
        if(empty($res)){
            exit(makeStandResult(-1,'删除失败'));
        }else{
            exit(makeStandResult(1,'删除成功'));
        }
    }

    /**
     * 删除数据
     */
    public function delData(){
        $ids = trim(I('post.appid'));
        // print_r($ids);die;
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));

      /*  $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');*/

        $arr = explode(',', $ids);
        $model = M('checkup');

        $where['appid'] = ['in',$arr];


        /*  $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");*/
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
              /*  $data['sp_modifytime'] = $time;
                $data['sp_modifyuser'] = $user;
                $data['sp_atpstatus']  = 'DEL';*/
                $res =  $model->where("appid='%s'", $id)->delete();
                //$list['rlx_atpstatus'] = 'DEL';
               // M('it_relationx')->where("rlx_zyid = '%s'",$id)->save($list);
                if($res){
                    addLog('security', '对象删除日志',  "删除主键为".$id."成功", '成功');
                    D('relation')->delRelation($id, 'security_products');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('security', '对象删除日志',  "删除主键为".$id."失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }
    //删除资产
    public function delapp(){
        $ids = I('post.ids');

        //print_r($ids);die;
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $idStr = "'".implode("','", $id)."'";
        $checkupmodel = M('checkup');

        M()->startTrans();
        try
        {

            $checkupmodel -> where("appid in ($idStr)")->delete();
            M()->commit();
            exit(makeStandResult(1,'删除成功'));
        }
        catch(Exception $e)
        {
            M()->rollback();
            exit(makeStandResult(-1,'删除失败'));
        }
    }

    //查找资产类型

    public function getType(){

        /*$sql = "select distinct t.type_top from V_ATPIDALL t";
        $data = D('v_atpidall')->query($sql);*/

        //$data = M('v_atpidall_checkup')->getField('to_nchar(type)',true);
        $data = M('v_atpidall_checkup')->getField('type',true);

       // print_r($data);die;
        $data = array_unique($data);

      return $data;

    }
}