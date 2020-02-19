<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class UserModel extends Model{
    Protected $autoCheckFields = false;

    /**
     * 获取用户信息
     * @param array $ids
     * @return mixed
     */
    public function getUsers($ids = array()){
        $model = M('sysuser');

        $where = '';
        if(!empty($ids)) {
            $idStr = "'".implode("','",$ids)."'";
            $where .= " and  user_id in ($idStr)";
        }
        $data = $model->field("user_id id,user_realusername as text")
            ->where("user_issystem != '是'and user_isdelete = '0'  and user_enable ='启用'  $where")
            ->select();
        return $data;
    }

    /**
     * @param $userName
     * @return bool
     */
    public function getUserId($userName){
        if(empty($userName)) return false;
        $model = M('sysuser');
        $data = $model->field("user_id id")
            ->where("user_name = '%s' ", $userName)
            ->find();
        if(empty($data)){
            return false;
        }else{
            return $data['id'];
        }
    }

    /**
     * 判断是否是项目管理员
     * @param $userId
     * @return bool
     */
    public function judgeProjectManager($userId){
        $model = M();
        $data = $model->query("select prj_id from project where prj_chargemanid = '$userId' union select pt_prjid as prj_id from project_transactor where pt_userid = '$userId' ");
        if(empty($data)){
            return false;
        }else{
            return true;
        }
    }

    public function getUserRole($role){
        $data = [];
        if(empty($role)) return false;
        $user = M('sysuser u');
        if(is_string($role)){
            $data=$user->field('user_id,user_realusername,user_name')
                ->join('userauth a on u.user_id=a.ua_userid','left')
                ->join('sysrole r on a.ua_roleid=r.role_id','left')
                ->where("role_name = '%s'",$role)
                ->order("user_sort")
                ->select();
        }else if(is_array($role)){
            $result=$user->field('role_name,user_id,user_realusername,user_name')
                ->join('userauth a on u.user_id=a.ua_userid','left')
                ->join('sysrole r on a.ua_roleid=r.role_id','left')
                ->where(['role_name' => ['in', $role]])
                ->order("user_sort")
                ->select();

            foreach($result as $key=>$value){
                $data[$value['role_name']][] = [
                    'user_id' => $value['user_id'],
                    'user_realusername' => $value['user_realusername'],
                    'user_name' => $value['user_name']
                ];
            }
        }
        return $data;
    }

    /**
     * 根据真实姓名查询用户信息
     * @param $account
     * @return bool
     */
    public function getUserInfoByRealName($account, $filed = '*'){
        if(empty($account)) return false;
        $model = M('sysuser u');
        $data = $model->field($filed)
            ->join('userauth a on u.user_id=a.ua_userid','left')
            ->join('sysrole r on a.ua_roleid=r.role_id','left')
            ->where("user_realusername = '%s' and user_issystem != '是'and user_isdelete = '0'  and user_enable ='启用' and role_name='科研部员工'", $account)
            ->limit(2)
            ->select();
        if(empty($data)){
            return false;
        }else{
            return $data;
        }
    }
    /**
     * 根据真实姓名查询用户信息
     * @param $account
     * @return bool
     */
    public function getUserInfoByRealNames($account, $filed = '*'){
        if(empty($account)) return false;
        $model = M('sysuser u');
        $data = $model->field($filed)
            ->where("user_realusername = '%s' and user_issystem != '是'and user_isdelete = '0'  and user_enable ='启用'", $account)
            ->limit(2)
            ->select();
        if(empty($data)){
            return false;
        }else{
            return $data;
        }
    }

    /**
     * 根据真实姓名查询用户信息
     * @param $account
     * @return bool
     */
    public function getUserInfoByRealNameRole($account, $filed = '*'){
        if(empty($account)) return false;
        $model = M('sysuser u');
        $data = $model->field($filed)
            ->join('userauth a on u.user_id=a.ua_userid','left')
            ->join('sysrole r on a.ua_roleid=r.role_id','left')
            ->where("user_realusername = '%s' and user_issystem != '是'and user_isdelete = '0'  and user_enable ='启用' and role_name='科研部部领导'", $account)
            ->limit(2)
            ->select();
        if(empty($data)){
            return false;
        }else{
            return $data;
        }
    }

    /**
     * 根据真实姓名查询用户信息
     * @param $account
     * @return bool
     */
    public function getUserInfoByRealNameChu($account, $filed = '*'){
        if(empty($account)) return false;
        $model = M('sysuser u');
        $data = $model->field($filed)
            ->join('userauth a on u.user_id=a.ua_userid','left')
            ->join('sysrole r on a.ua_roleid=r.role_id','left')
            ->where("user_realusername = '%s' and user_issystem != '是'and user_isdelete = '0'  and user_enable ='启用' and role_name='科研部处室领导'", $account)
            ->limit(2)
            ->select();
        if(empty($data)){
            return false;
        }else{
            return $data;
        }
    }
}