<?php
namespace Admin\Controller;
use Think\Controller;
class UserController extends BaseController {

    /**
     * 获取后台用户列表
     */
    public function getUserLists(){
        $model = M('sysuser');
        $search = I('post.');
        $search = trim($search['data']['q']);
        $data = $model->field("user_id id,user_realusername || '(' ||user_name||','||org_name||')' text")
            ->where("(user_realusername like '%s' or user_name like '%s') and user_issystem != '是'",[ "%$search%","%$search%"])
            ->join("left join view_org o on o.org_id=sysuser.user_orgid")
            ->order("user_sort asc")
            ->select();
        echo json_encode(array('q' => $search, 'results' => $data));
    }

    public function getUsers(){
        $model = M('sysuser');
        $search = I('post.');
        $search = trim($search['data']['q']);
        $data = $model->field("user_realusername id,user_realusername || '(' ||user_name ||')' text")
            ->where("(user_realusername like '%s' or user_name like '%s') and user_issystem != '是'",[ "%$search%","%$search%"])
            ->order("user_sort asc")
            ->select();
        echo json_encode(array('q' => $search, 'results' => $data));
    }

    /**
     * 用户管理
     */
    public function index(){
//        $dicType = D('Dictionary')->getDicType();
//        $this->assign('dictionaryType', $dicType);
        addLog('','用户访问日志','访问用户管理','成功');
        $this->display();
    }

    /**
     * 获取用户列表
     */
    public function getData($isThinkPhp = false){
        if($isThinkPhp){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $realName = trim($queryParam['real_name']);
        $userName=trim($queryParam['user_name']);
        $where['user_issystem'][]=[['neq','是'],['exp','is null'],'or'];
        $where['user_isdelete']=['neq',1];

        if(!empty($realName)) $where['user_realusername']=['like',"%$realName%"];
        if(!empty($userName)) $where['user_name']=['like',"%$userName%"];

        $model = M('sysuser');
        $count = $model->where($where)->count();
        $obj = $model->field('user_id,user_realusername,user_name,org_name,user_secretlevel,user_createtime,user_lastmodifytime,user_sort')
            ->where($where)
            ->join("view_org on sysuser.user_orgid=view_org.org_id")
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isThinkPhp){
            $data = $obj->select();
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])->select();
        }
        $staffSecret = D('Dictionary')->getDicValueByName('人员密级');
        $staffSecret = array_column($staffSecret, 'dic_name', 'val');
        foreach($data as &$value){
            if(!empty($value['user_createtime'])) $value['user_createtime'] = date('Y-m-d H:i:s',$value['user_createtime']);
            if(!empty($value['user_lastmodifytime'])) $value['user_lastmodifytime'] = date('Y-m-d H:i:s',$value['user_lastmodifytime']);
            $value['user_secretlevel'] = $staffSecret[$value['user_secretlevel']];
            if($isThinkPhp) unset($value['user_id']);
        }

        if($isThinkPhp){
            $header = array('姓名','账号','部门','密级','创建时间','上次修改时间', '排序号');
            addLog('','对象修改日志','导出用户列表','成功');
            if($count <= 0){
                exit(makeStandResult(-1, '没有需要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }

    /**
     * 用户添加或修改
     */
    public function add(){
        $id = trim(I('get.id'));
        if(!empty($id)){
            $model = M('sysuser');
            $data = $model->field('user_id,user_realusername,user_name,user_orgid,user_secretlevel,org_name,user_sort')
                ->join("left join view_org on sysuser.user_orgid = view_org.org_id")
                ->where("user_id='%s'", $id)
                ->find();
            $this->assign('data', $data);
        }

        $mijilist = D('dictionary')->getDicValueByName('人员密级');
        $this->assign('mijilist', $mijilist);
        $this->display();
    }

    /**
     * 用户添加修改
     */
    public function addSysUser(){
        $id = trim(I('post.id'));
        $data['user_realusername'] = trim(I('post.real_name'));
        $data['user_orgid'] = trim(I('post.org_id'));
        $data['user_secretlevel'] = trim(I('post.user_secretlevel'));
        $data['user_name'] = trim(I('post.user_name'));
        $data['user_sort'] = intval(I('post.user_sort'));
        //if(empty($data['role_name']))  exit(makeStandResult(-1,'请输入姓名'));
        $model = M('sysuser');
        //为空则添加
        if(empty($id)){
            $isexist= $model->where("user_name='%s'",$data['user_name'])->find();
            if(!empty($isexist))
            {
                addLog('sysuser','三员操作日志','新增用户'.$data['user_realusername'],'失败');
                exit(makeStandResult(-1,'账号已存在'));
            }
            $data['user_password'] = md5(C('PWD_SALT').md5('Guanli'.date('Y')));
            $data['user_createtime'] = time();
            $data['user_id'] = makeGuid();
            $data['user_createuser'] = session('user_id');
            $data['user_enable']='启用';
            $data['user_secretlevelcode']=md5($data['user_id'].trim(I('post.user_secretlevel')));
            $res = $model->add($data);
            if(empty($res)){
                addLog('sysuser','三员操作日志','新增用户'.$data['user_realusername'],'失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('sysuser','三员操作日志','新增用户'.$data['user_realusername'],'成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['user_lastmodifytime'] = time();
            $data['user_lastmodifyuser'] = session('user_id');
            $data['user_secretlevelcode']=md5($id.trim(I('post.user_secretlevel')));
            $data['user_enable']='启用';
            $tem= $model->where("user_name='%s'",$data['user_name'])->find();
            if($tem['user_id']!=$id&&!empty($tem))
            {
                addLog('sysuser','三员操作日志','修改用户'.$data['user_realusername'],'失败');
                exit(makeStandResult(-1,'账号已存在'));
            }
            $res = $model->where("user_id='%s'", $id)->save($data);
            if(empty($res)){
                addLog('sysuser','三员操作日志','修改用户'.$data['user_realusername'],'失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('sysuser','三员操作日志','修改用户'.$data['user_realusername'],'成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 删除模块
     */
    public function delSysUser(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $idStr = "'".implode("','", $id)."'";
        $model = M('sysuser');
        $userauth=M('userauth');
        foreach($id as $key=>$val)
        {
            $tem= $model->where("user_id='%s'",$val)->find();
            if(!empty($tem))
            {
                addLog('sysuser','三员操作日志','删除用户'.$tem['user_realusername'],'成功');
            }
        }
        $userauth->where("ua_userid in ($idStr)")->delete();
        $temp['user_isdelete']=1;
        $res = $model -> where("user_id in ($idStr)")->setField($temp);
        if(empty($res)){
            exit(makeStandResult(-1,'删除失败'));
        }else{
            exit(makeStandResult(1,'删除成功'));
        }
    }

    public function updateSecretCode(){
        echo date('Y-m-d H:i:s').'开始执行<br>';
        set_time_limit(0);
        ini_set('memory_limit', '500M');
        $model = M('sysuser');
        $data = $model->field('user_id,user_secretlevel')->select();
        foreach($data as $key=>$value){
            $user_secretlevelcode = md5($value['user_id'].$value['user_secretlevel']);
            $model->where("user_id = '{$value['user_id']}'")->setField('user_secretlevelcode', $user_secretlevelcode);
        }
        echo date('Y-m-d H:i:s').'执行完毕<br>';
    }
}