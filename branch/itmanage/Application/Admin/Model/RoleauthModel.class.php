<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class RoleauthModel extends Model{


    public function getByRole($rolename){
        $model = M('roleauth');
        if(!empty($rolename))
        $where="role_name like '%$rolename%'";
        if($model->count()>0)
        {
            $roleids=$model
                ->field('ra_roleid,ra_miid,role_name,mi_name')
                ->where($where)
                ->join('sysrole on sysrole.role_id=roleauth.ra_roleid ','left')
                ->join('modelinfo on modelinfo.mi_id=roleauth.ra_miid ','left')
                ->select();
            $roleauth  = [];
            $roleauths = [];
            $roleid    = [];
            //生成 角色名 => array（模块1,模块2,模块3）
            foreach($roleids as $key=>$val){
                if(!empty($val['role_name']) && !empty($val['mi_name'])){
                    $roleauth[$val['role_name']][] = $val['mi_name'];
                    $roleid[$val['role_name']]     = $val['ra_roleid'];
                }
            }
            //生成 角色名 => 模块1,模块2,模块3
            foreach($roleauth as $key=>$val){
                $tmp = [];
                $tmp['role_name'] = $key;
                $tmp['mi_name']   = implode(',',$val);
                $tmp['ra_roleid'] = $roleid[$key];
                $roleauths[]      = $tmp;
            }
        }
        $role=M('sysrole');
        $allid= $role->field('role_id,role_name')
            ->where($where)
            ->select();
        foreach($allid as $key=>$val)
        {
            $b=false;
            foreach($roleauths as $key1=>$val1)
            {
                if($val['role_id']===$val1['ra_roleid'])
                    $b=true;
            }
            if($b)
            continue;
            $tmp = [];
            $tmp['role_name'] = $val['role_name'];
            $tmp['ra_roleid'] = $val['role_id'];
            $roleauths[]      = $tmp;
        }
//        print_r($roleauths);die;
        return $roleauths;
    }
    public function getByModel($modelname){
        $model = M('roleauth');
        if(!empty($modelname))
            $where="mi_name like '%$modelname%'";
        if($model->count()>0)
        {
            $roleids=$model
                ->field('ra_roleid,ra_miid,role_name,mi_name')
                ->where($where)
                ->join('modelinfo on modelinfo.mi_id=roleauth.ra_miid ','left')
                ->join('sysrole on sysrole.role_id=roleauth.ra_roleid ','left')
                ->select();
            $roleauth = [];
            $roleauths = [];
            $modelid    = [];
            foreach($roleids as $key=>$val){
                if(!empty($val['mi_name']) && !empty($val['role_name'])){
                    $roleauth[$val['mi_name']][] = $val['role_name'];
                    $modelid[$val['mi_name']]     = $val['ra_miid'];
                }
            }
            foreach($roleauth as $key=>$val){
                $tmp = [];
                $tmp['mi_name'] = $key;
                $tmp['role_name']   = implode(',',$val);
                $tmp['ra_miid'] = $modelid[$key];
                $roleauths[]      = $tmp;
            }
        }

        $role=M('modelinfo');
        $allid= $role->field('mi_id,mi_name')
            ->where($where)
            ->select();
        foreach($allid as $key=>$val)
        {
            $b=false;
            foreach($roleauths as $key1=>$val1)
            {
                if($val['mi_id']===$val1['ra_miid'])
                    $b=true;
            }
            if($b)
                continue;
            $tmp = [];
            $tmp['mi_name'] = $val['mi_name'];
            $tmp['ra_miid'] = $val['mi_id'];
            $roleauths[]      = $tmp;
        }
        return $roleauths;
    }

    /**
     * 根据角色名称获取角色id
     * @param $roleNames
     * @return array|bool|mixed
     */
    public function getRoleIdsByRoleName($roleNames){
        if(empty($roleNames)) return false;
        $model = M('sysrole');

        if(is_array($roleNames)){
            $ids = $model->where(['role_name' => ['in', $roleNames]])->field('role_id')->select();
            $ids = removeArrKey($ids, 'role_id');
            return $ids;
        }else{
            $id = $model->where(['role_name' => ['eq', $roleNames]])->getField('role_id');
            return [$id];
        }
    }

}