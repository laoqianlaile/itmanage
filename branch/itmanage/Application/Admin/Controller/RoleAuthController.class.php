<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class RoleAuthController extends BaseController{

    /**
     * 角色授权
     */
    public function index()
    {
        addLog('', '用户访问日志', '访问模块授权管理', '成功');
        $this->display();
    }

    /**
     * 根据角色获取模块
     */
    public function getDataByRole($isThinkPhp = false){
        if($isThinkPhp){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $roleName = trim($queryParam['real_name']);
        $where['role_name'][] = [['not in',['系统管理员', '安全管理员', '审计管理员','系统配置管理员']], ['like',"%$roleName%"]];
        $where['role_isdefault'] = ['neq','是'];
        $model = M('sysrole');
        $count = $model->where($where)->count();

        $obj = $model->field('role_id,role_name')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isThinkPhp){
            $data = $obj->select();
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])->select();
        }

        $roleAuthModel = M('roleauth');
        foreach ($data as &$value) {
            $id = $value['role_id'];
            $models = $roleAuthModel->field('mi_name')
                ->where("ra_roleid = '$id' and mi_isdefault!='是'")
                ->join('modelinfo on  roleauth.ra_miid = modelinfo.mi_id')
                ->select();
            $models = implode(',', removeArrKey($models, 'mi_name'));
            $value['models'] = $models;
            if($isThinkPhp) unset($value['role_id']);
        }

        if($isThinkPhp){
            addLog('','对象导出日志','导出模块授权列表','成功');
            $header = array('角色','模块');
            if($count <= 0){
                exit(makeStandResult(-1, '没有需要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            echo json_encode(array('total' => $count, 'rows' => $data));
        }
    }

    /**
     * 根据模块获取角色
     */
    public function getDataByModel($isThinkPhp = false){
        if($isThinkPhp){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $where = [];
        $modelName = trim($queryParam['real_name']);
        if (!empty($modelName)) $where['mi_name'] = array('like', "%$modelName%");

        $where['mi_issystem'] =['neq','是'];
        $where['mi_type'][] = [['eq', '网站菜单'], ['exp', 'is null'], 'or'];
        $model = M('modelinfo');
        $count = $model->where($where)->count();

        $obj = $model->field('mi_id,mi_name')->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");
        if($isThinkPhp){
            $data = $obj->select();
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])->select();
        }

        $roleAuthModel = M('roleauth');
        foreach ($data as &$value) {
            $id = $value['mi_id'];
            $roles = $roleAuthModel->field('role_name')->where("ra_miid = '$id' and role_name not in ('系统管理员','安全管理员','审计管理员','系统配置管理员') and role_isdefault!='是'")->join('sysrole on  roleauth.ra_roleid = sysrole.role_id')->select();
            $roles = implode(',', removeArrKey($roles, 'role_name'));
            $value['roles'] = $roles;
            if($isThinkPhp) unset($value['mi_id']);
        }
        if($isThinkPhp){
            addLog('','对象导出日志','导出模块授权列表','成功');
            $header = array('模块','角色');
            if($count <= 0){
                exit(makeStandResult(-1, '没有需要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            echo json_encode(array('total' => $count, 'rows' => $data));
        }
    }


    public function editByRole()
    {
        $roleid = trim(I('get.roleid'));
        $roleauth = M('roleauth');
        if (!empty($roleid)) {
            $modelid = $roleauth->field('ra_miid')->where("ra_roleid='$roleid'")->select();
            $this->assign('modelids', json_encode(removeArrKey($modelid, 'ra_miid')));
            $this->assign('roleid', $roleid);
        }
        addLog('', '用户访问日志', '访问模块授权编辑——角色', '成功');
        $this->display();
    }

    public function editByModel()
    {
        $modelid = trim(I('get.modelid'));
        $roleauth = M('roleauth');

        if (!empty($modelid)) {
            $roleid = $roleauth->field('ra_roleid')->where("ra_miid='%s'", $modelid)->select();
            $this->assign('roleids', json_encode(removeArrKey($roleid, 'ra_roleid')));
            $this->assign('modelid', $modelid);
        }
        addLog('', '用户访问日志', '访问角色授权编辑——模块', '成功');
        $this->display();
    }

    public function addByRole()
    {
        $queryParam = I('put.');
        $role_name=trim($queryParam['role_name']);
        $where['role_name'][] = [['not in',['系统管理员', '安全管理员', '审计管理员','系统配置管理员']], ['like',"%$role_name%"]];
        $where['role_issystem']=['neq','是'];
        if(!empty($role_name))
        {
            $where['role_name'][]=['like',"%$role_name%"];
        }
        $model = M('sysrole');
        $data = $model->field('role_id,role_name')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->count();
        echo json_encode(array('total' => $count, 'rows' => $data));
    }

    public function addByModel()
    {
        $queryParam = I('put.');
        $where['mi_issystem'] = array('neq', '是');
        $where['mi_type'][] = [['eq', '网站菜单'], ['exp', 'is null'], 'or'];
        $mi_name=trim($queryParam['mi_name']);
        if(!empty($mi_name))
        {
            $where['mi_name'][]=['like',"%$mi_name%"];
        }
        $model = M('modelinfo');
        $data = $model->field('mi_id,mi_name')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->count();
        echo json_encode(array('total' => $count, 'rows' => $data));
    }


    /**
     * 添加模块
     */
    public function addRoleAuthByModel()
    {
        $modelid = I('post.modelid');
        $roleids = I('post.roleids');
        if (empty($modelid)) exit(makeStandResult(-1, '参数缺少'));

        $roleid = explode(',', $roleids);
        $model = M('roleauth');
        $mo = M('modelinfo');
        $rolenames = M('sysrole')->field('role_name')
            ->where(['role_id' => ['in' , $roleid]])
            ->select();

        //获取当前模块名称
        $modelName = $mo->field('mi_name')->where("mi_id='%s'", $modelid)->getField('mi_name');
        $model->startTrans();
        try{
            //获取旧权限
            $oldRoles = $model->where("ra_miid='%s'", $modelid)
                ->join("sysrole on roleauth.ra_roleid = sysrole.role_id ")
                ->field('role_name')
                ->select();

            $model->where("ra_miid='%s'", $modelid)->delete();
            foreach ($roleid as $key => $val) {
                $data['ra_createtime'] = time();
                $data['ra_id'] = makeGuid();
                $data['ra_createuser'] = session('user_id');
                $data['ra_miid'] = $modelid;
                $data['ra_roleid'] = $val;
                $model->add($data);
            }
            $model->commit();
            if(empty($rolenames)){
                $oldRoles = implode(',', removeArrKey($oldRoles, 'role_name'));
                addLog('roleauth', '三员操作日志', '删除模块('.$modelName.')的所有角色权限：'.$oldRoles, '成功');
            }else{
                $rolenames = implode(',', removeArrKey($rolenames, 'role_name'));
                addLog('roleauth', '三员操作日志', '给模块('.$modelName.')授权以下角色：'. $rolenames, '成功');
            }
            exit(makeStandResult(2, '操作完成'));
        }catch(Exception $e){
            $model->rollback();
            addLog('roleauth', '三员操作日志', '给模块('.$modelName.')授权', '失败');
            exit(makeStandResult(-1, '操作失败'));
        }
    }

    public function addRoleAuthByRole()
    {
        $modelids = I('post.modelids');
        $roleid = trim(I('post.roleid'));
        if (empty($roleid)) exit(makeStandResult(-1, '参数缺少'));
        $modelid = explode(',', $modelids);
        $model = M('roleauth');
        $mo = M('modelinfo');

        $modelnames = $mo->field('mi_name')->where(['mi_id'=> ['in' , $modelid] ])->select();
        $roleName =  M('sysrole')->where("role_id = '%s'", $roleid)->getField('role_name');
        try{
            //获取旧权限
            $oldModels = $model->where("ra_roleid='%s'", $roleid)
                ->join("modelinfo on roleauth.ra_miid = modelinfo.mi_id ")
                ->field('mi_name')
                ->select();

            $model->where("ra_roleid='$roleid'")->delete();
            foreach ($modelid as $key => $val) {
                $data['ra_createtime'] = time();
                $data['ra_id'] = makeGuid();
                $data['ra_createuser'] = session('user_id');

                $data['ra_roleid'] = $roleid;
                $data['ra_miid'] = $val;
                $model->add($data);
            }
            $model->commit();
            if(empty($modelnames)){
                $oldModels = implode(',', removeArrKey($oldModels, 'mi_name'));
                addLog('roleauth', '三员操作日志', '删除角色('.$roleName.')的所有模块权限：'.$oldModels, '成功');
            }else{
                $modelnames = implode(',', removeArrKey($modelnames, 'mi_name'));
                addLog('roleauth', '三员操作日志', '给角色('.$roleName.')授权以下模块：'. $modelnames, '成功');
            }
            exit(makeStandResult(2, '操作完成'));
        }catch(Exception $e){
            $model->rollback();
            addLog('roleauth', '三员操作日志', '给角色('.$roleName.')授权', '失败');
            exit(makeStandResult(-1, '操作失败'));
        }
    }
}