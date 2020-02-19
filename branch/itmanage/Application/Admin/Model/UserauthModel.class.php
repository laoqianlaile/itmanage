<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;

use Think\Model;

class UserauthModel extends Model
{


    public function getByRole($rolename)
    {
        $model = M('userauth');
        if (!empty($rolename))
            $where = "role_name like '%$rolename%'";
        if ($model->count() > 0) {
            $roleids = $model
                ->field('ua_roleid,ua_userid,role_name,user_name')
                ->where($where)
                ->join('sysrole on sysrole.role_id=userauth.ua_roleid ','left')
                ->join('sysuser on sysuser.user_id=userauth.ua_userid ', 'left')
                ->select();
            $userauth = [];
            $userauths = [];
            $roleid = [];
            //生成 角色名 => array（模块1,模块2,模块3）
            foreach ($roleids as $key => $val) {
                if (!empty($val['role_name']) && !empty($val['user_name'])) {
                    $userauth[$val['role_name']][] = $val['user_name'];
                    $roleid[$val['role_name']] = $val['ua_roleid'];
                }
            }
            //生成 角色名 => 模块1,模块2,模块3
            foreach ($userauth as $key => $val) {
                $tmp = [];
                $tmp['role_name'] = $key;
                $tmp['user_name'] = implode(',', $val);
                $tmp['ua_roleid'] = $roleid[$key];
                $userauths[] = $tmp;
            }
        }
        $role = M('sysrole');
        $allid = $role->field('role_id,role_name')
            ->where($where)
            ->select();
        foreach ($allid as $key => $val) {
            $b = false;
            foreach ($userauths as $key1 => $val1) {
                if ($val['role_id'] === $val1['ua_roleid'])
                    $b = true;
            }
            if ($b)
                continue;
            $tmp = [];
            $tmp['role_name'] = $val['role_name'];
            $tmp['ua_roleid'] = $val['role_id'];
            $userauths[] = $tmp;
        }
        return $userauths;
    }

    public function getByUser($username)
    {
        $model = M('userauth');
        if (!empty($username))
            $where = "user_name like '%$username%'";
        if ($model->count() > 0) {
            $roleids = $model
                ->field('ua_roleid,ua_userid,role_name,user_name')
                ->where($where)
                ->join('sysuser on sysuser.user_id=userauth.ua_userid ', 'left')
                ->join('sysrole on sysrole.role_id=userauth.ua_roleid ', 'left')
                ->select();
            $userauth = [];
            $userauths = [];
            $userid = [];
            foreach ($roleids as $key => $val) {
                if (!empty($val['user_name']) && !empty($val['role_name'])) {
                    $userauth[$val['user_name']][] = $val['role_name'];
                    $userid[$val['user_name']] = $val['ua_userid'];
                }
            }
            foreach ($userauth as $key => $val) {
                $tmp = [];
                $tmp['user_name'] = $key;
                $tmp['role_name'] = implode(',', $val);
                $tmp['ua_userid'] = $userid[$key];
                $userauths[] = $tmp;
            }
        }
        $role = M('sysuser');
        $allid = $role->field('user_id,user_name')
            ->where($where)
            ->select();
        foreach ($allid as $key => $val) {
            $b = false;
            foreach ($userauths as $key1 => $val1) {
                if ($val['user_id'] === $val1['ua_userid'])
                    $b = true;
            }
            if ($b)
                continue;
            $tmp = [];
            $tmp['user_name'] = $val['user_name'];
            $tmp['ua_userid'] = $val['user_id'];
            $userauths[] = $tmp;
        }
        return $userauths;
    }

    /**
     * 给用户分配角色
     * @param $roleId
     * @param $userId
     * @return bool|mixed3
     */
    public function addRoleToUser($roleId, $userId){
        if(empty($roleId)) return false;
        if(empty($userId)) $userId = session('user_id');
        $model = M('userauth');

        $where['ua_roleid'] = $roleId;
        $where['ua_userid'] = $userId;

        $data = $model->where($where)->getField('ua_id');
        if(empty($data)){
            $arr = $where;
            $arr['ua_id'] = makeGuid();
            $arr['ua_createtime'] = time();
            $arr['ua_createuser'] = session('user_id');
            $res = $model->add($arr);
            return $res;
        }else{
            return true;
        }
    }

    /**
     * 删除用户的角色
     * @param $roleId
     * @param $userId
     * @return bool|mixed
     */
    public function deleteUserRole($roleId, $userId){
        if(empty($roleId)) return false;
        if(empty($userId)) $userId = session('user_id');
        $model = M('userauth');

        $where['ua_roleid'] = $roleId;
        $where['ua_userid'] = $userId;

        $res = $model->where($where)->delete();
        return $res;
    }
}