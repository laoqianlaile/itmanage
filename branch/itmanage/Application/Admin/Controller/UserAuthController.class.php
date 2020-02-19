<?php
namespace Admin\Controller;

use Think\Controller;

class UserAuthController extends BaseController
{

    /**
     * 角色授权
     */
    public function index(){
        addLog('','用户访问日志','访问用户授权管理','成功');
        $this->display();
    }

    /**
     * 根据角色获取用户
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

        $obj =  $model->field('role_id,role_name')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isThinkPhp){
            $data = $obj->select();
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])->select();
        }

        $userAuthModel = M('userauth');
        foreach ($data as &$value) {
            $id = $value['role_id'];
            $users = $userAuthModel->field("user_realusername|| '(' || org_name|| ')' user_realusername")
                ->where("ua_roleid = '$id' and user_isdelete != 1")
                ->join('sysuser on  userauth.ua_userid = sysuser.user_id')
                ->join("left join view_org o on o.org_id=sysuser.user_orgid")
                ->order("user_sort")
                ->select();
            $users = implode(',', removeArrKey($users, 'user_realusername'));
            if($isThinkPhp) unset($value['role_id']);
            $value['users'] = $users;
        }
        if($isThinkPhp){
            addLog('','对象导出日志','导出用户授权列表','成功');
            $header = array('角色','用户');
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
     * 根据用户获取角色
     */
    public function getDataByUser($isThinkPhp = false){
        if($isThinkPhp){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }
        $where = [];
        $userName = trim($queryParam['real_name']);
        if (!empty($userName)){
            $where['user_name'][] = array('exp', "like '%$userName%' or user_realusername like '%$userName%'");
        }
        $where['user_issystem']=['neq','是'];
        $where['user_isdelete']=['neq',1];
        $model = M('sysuser');
        $count = $model->where($where) ->join("left join view_org o on o.org_id=sysuser.user_orgid")->count();

        $obj = $model->field('user_id,user_realusername,org_name')
            ->join("left join view_org o on o.org_id=sysuser.user_orgid")
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isThinkPhp){
            $data = $obj->select();
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])->select();
        }

        $userAuthModel = M('userauth');
        foreach ($data as &$value) {
            $id = $value['user_id'];
            $roles = $userAuthModel->field('role_name')
                ->where("ua_userid = '$id' and role_name not in ('系统管理员', '安全管理员', '审计管理员') and role_isdefault!='是'")
                ->join('sysrole on  userauth.ua_roleid = sysrole.role_id')
                ->select();
            $roles = implode(',', removeArrKey($roles, 'role_name'));
            $value['roles'] = $roles;
            if($isThinkPhp) unset($value['user_id']);
        }
        if($isThinkPhp){
            addLog('','对象导出日志','导出用户授权列表','成功');
            $header = array('用户','所在单位/部门', '角色');
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
        $userauth = M('userauth');
        if (!empty($roleid)) {
            $userids = $userauth->field('ua_userid')->where("ua_roleid='$roleid'")->select();
            $this->assign('userids', json_encode(removeArrKey($userids, 'ua_userid')));
            $this->assign('roleid', $roleid);
        }
        addLog('','用户访问日志','访问用户授权编辑——角色','成功');
        $this->display();
    }

    public function editByUser()
    {
        $userid = trim(I('get.userid'));
        $userauth = M('userauth');

        if (!empty($userid)) {
            $roleids = $userauth->field('ua_roleid')->where("ua_userid='$userid'")->select();
            $this->assign('roleids', json_encode(removeArrKey($roleids, 'ua_roleid')));
            $this->assign('userid', $userid);
        }
        addLog('','用户访问日志','访问用户授权编辑——用户','成功');
        $this->display();
    }

    public function addByRole()
    {
        $queryParam = I('put.');
        $role_name=trim($queryParam['role_name']);
        $where['role_name'][]=[['neq','系统管理员'],['neq','安全管理员'],['neq','审计管理员']];
        $where['role_isdefault']=['neq','是'];
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

    public function addByUser()
    {
        $queryParam = I('put.');
        $user_name=trim($queryParam['user_name']);
        $where="user_issystem ='否' and user_isdelete!=1";
        if(!empty($user_name))
        {
//            $where['user_name']=['like',"%$user_name%"];
            $where=$where." and (user_name like '%$user_name%' or user_realusername like '%$user_name%')";
        }
        $model = M('sysuser');

//        $where['user_name'][] = [['neq', 'sysadmin'], ['neq', 'sysadmin2'], ['neq', 'secadmin'], ['neq', 'secadmin2'], ['neq', 'auditadmin'], ['neq', 'auditadmin2']];
        $data = $model->field('user_id,user_name,user_realusername')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->count();
        echo json_encode(array('total' => $count, 'rows' => $data));
    }


    /**
     * 添加用户
     */
    public function addUserAuthByUser(){
        $userid = trim(I('post.userid'));
        $roleids = trim(I('post.roleids'));
        if (empty($userid)) exit(makeStandResult(-1, '参数缺少'));

        $roleids = explode(',', $roleids);
        $model = M('userauth');

        $userInfo = M('sysuser')->where("user_id = '%s'", $userid)->field('user_realusername,user_name')->find();
        $username = $userInfo['user_name'];
        $realUserName = $userInfo['user_realusername'];

        $model->startTrans();
        try{
            //获取以前授权的角色
            $oldRoles = $model->where("ua_userid='%s'", $userid)
                ->join("sysrole on userauth.ua_roleid = sysrole.role_id")
                ->field("role_id, role_name")
                ->select();

            //删除以前的授权角色
            $model->where("ua_userid='%s'", $userid)->delete();

            if(!empty($roleids)){
                foreach ($roleids as $key => $val) {
                    if(empty($val)) continue;
                    $data['ua_createtime'] = time();
                    $data['ua_id'] = makeGuid();
                    $data['ua_createuser'] = session('user_id');
                    $data['ua_userid'] = $userid;
                    $data['ua_roleid'] = $val;
                    $model->add($data);
                }
                $model->commit();

                //获取所有要新增的角色信息
                $rolesInfo = M('sysrole')->field('role_id, role_name')
                    ->where(['role_id' => ['in', $roleids]])
                    ->select();

                //老角色为空，把所有用户账号记录下来
                if(empty($oldRoles)){
                    $roleNames = implode(',', removeArrKey($rolesInfo, 'role_name'));
                    addLog('roleauth', '三员操作日志', '给用户(' . $username . ')分配以下角色:'.$roleNames, '成功');
                }else{
                    //老角色不为空，需要把角色变动信息记录下来，新增哪些，删除哪些角色
                    //将两批数据处理成 id=>数据 的形式
                    $initOldRolesInfo = [];
                    foreach($oldRoles as $key=>$value){
                        $initOldRolesInfo[$value['role_id']] = $value['role_name'];
                    }

                    $initAddRolesInfo = [];
                    foreach($rolesInfo as $key=>$value){
                        $initAddRolesInfo[$value['role_id']] = $value['role_name'];
                    }

                    $deleteRolesInfo = array_diff($initOldRolesInfo, $initAddRolesInfo);
                    $addRolesInfo = array_diff($initAddRolesInfo, $initOldRolesInfo);
                    if(empty($deleteRolesInfo) && empty($addRolesInfo)){
                        $logStr = "$realUserName($username)" .'用户授权没有发生变更';
                    }else{
                        $logStr = "$realUserName($username)" .' 用户授权变更如下：';
                        if(!empty($deleteRolesInfo)) $logStr .= " 删除角色" . implode(',', $deleteRolesInfo);
                        if(!empty($addRolesInfo)) $logStr .= " 增加角色" . implode(',', $addRolesInfo);
                    }

                    addLog('roleauth', '三员操作日志', $logStr, '成功');
                }
            }else{
                $model->commit();
                addLog('roleauth', '三员操作日志', '删除用户(' . $username . ')的所有角色', '成功');
            }
            exit(makeStandResult(2, '操作完成'));
        }catch(\Exception $e){
            $model->rollback();
            addLog('roleauth', '三员操作日志', '给用户(' . $username . ')分配角色', '失败');
            exit(makeStandResult(-1, '操作失败'));
        }
    }

    public function roleAuth(){
        $roleId = trim(I('get.roleid'));
        if(empty($roleId)) exit('参数缺失');

        $users = M('userauth')->where("ua_roleid='%s'", $roleId)
            ->field("user_id id,user_realusername || '(' ||user_name||','||org_name||')' text")
            ->join('sysuser on userauth.ua_userid =sysuser.user_id ')
            ->join("left join view_org o on o.org_id=sysuser.user_orgid")
            ->order("user_sort asc")
            ->select();
        $this->assign('users', $users);
        $this->assign('roleid', $roleId);
        $this->display();
    }

    /**
     * 给角色分配用户
     */
    public function addUserAuthByRole(){
        $userids = trim(I('post.userids'));
        $roleid = trim(I('post.roleid'));
        if (empty($roleid)) exit(makeStandResult(-1, '参数缺少'));
        if(!empty($userids)){
            $userids = explode(',', $userids);
        }else{
            $userids = [];
        }
        $model = M('userauth');

        //获取当前角色名称
        $rolename = M('sysrole')->field('role_name')->where("role_id='%s'", $roleid)->find();
        $rolename = $rolename['role_name'];

        $model->startTrans();
        try{
            //获取以前授权的用户
            $oldUsers = $model->where("ua_roleid='%s'", $roleid)
                ->join("sysuser on userauth.ua_userid = sysuser.user_id")
                ->field("user_id, user_name")
                ->select();

            $model->where("ua_roleid='%s'", $roleid)->delete();
            if(!empty($userids)){
                foreach ($userids as $key => $val) {
                    if(empty($val)) continue;
                    $data['ua_createtime'] = time();
                    $data['ua_id'] = makeGuid();
                    $data['ua_createuser'] = session('user_id');
                    $data['ua_roleid'] = $roleid;
                    $data['ua_userid'] = $val;
                    $model->add($data);
                }
                $model->commit();

                //获取所有要新增的用户信息
                $usersInfo = M('sysuser')->field('user_id, user_name')
                    ->where(['user_id' => ['in', $userids]])
                    ->select();

                //老用户为空，把所有用户账号记录下来
                if(empty($oldUsers)){
                    $accounts = implode(',', removeArrKey($usersInfo, 'user_name'));
                    addLog('roleauth', '三员操作日志', '给角色(' . $rolename . ')分配以下用户:'.$accounts, '成功');
                }else{
                    //老用户不为空，需要把人员变动信息记录下来，新增哪些，删除哪些用户
                    //将两批数据处理成 id=>数据 的形式
                    $initOldUsersInfo = [];
                    foreach($oldUsers as $key=>$value){
                        $initOldUsersInfo[$value['user_id']] = $value['user_name'];
                    }
                    $initAddUsersInfo = [];
                    foreach($usersInfo as $key=>$value){
                        $initAddUsersInfo[$value['user_id']] = $value['user_name'];
                    }

                    $deleteUserInfo = array_diff($initOldUsersInfo, $initAddUsersInfo);
                    $addUserInfo = array_diff($initAddUsersInfo, $initOldUsersInfo);
                    if(empty($deleteUserInfo) && empty($addUserInfo)){
                        $logStr = "($rolename)" . '角色授权没有发生变更';
                    }else{
                        $logStr = "($rolename)" .' 角色授权变更如下：';
                        if(!empty($deleteUserInfo)) $logStr .= " 删除用户" . implode(',', $deleteUserInfo);
                        if(!empty($addUserInfo)) $logStr .= " 增加用户" . implode(',', $addUserInfo);
                    }

                    addLog('roleauth', '三员操作日志', $logStr, '成功');
                }
            }else{
                $model->commit();
                addLog('roleauth', '三员操作日志', '删除角色(' . $rolename . ')的所有用户', '成功');
            }
            exit(makeStandResult(1, '操作成功'));
        }catch(\Exception $e){
            $model->rollback();
            addLog('roleauth', '三员操作日志', '给角色(' . $rolename . ')分配用户', '失败');
            exit(makeStandResult(-1, '操作失败'));
        }
    }
}