<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class OrgModel extends Model{
    Protected $autoCheckFields = false;


    /**
     * 获取层级部门列表
     * @param bool|true $isLevel
     * @return array
     */
    public function  getOrgList($isLevel = true, $where = []){
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')->field('org_id id,org_name, org_pid pid,org_fullname')->where($where)->order('org_fullnum asc')->select();
        if($isLevel === true){
            $org = getLevelData($org, null);
            foreach($org as &$value){
                $value['org_name'] = str_repeat( '&nbsp;&nbsp;&nbsp',$value['level']).$value['org_name'];
            }
        }
        return $org;
    }

    public function getOrgViewList($isLevel = true, $where = []){
        $where['role_name'] = ['eq', '部领导'];
        $org = M('v_userrole')->field('user_id id,user_name,user_realusername')
            ->where($where)
            ->order('user_sort asc')
            ->select();
        if($isLevel === true){
            $org = getLevelData($org, null);
            foreach($org as &$value){
                $value['user_realusername'] = $value['user_realusername'];
            }
        }
        return $org;
    }

    /**
     * 获取归属部门
     * @param bool|true $isLevel
     * @return array
     */
    public function getAscriptionDept($isLevel = true){
        $where['org_type'] = ['in', ['内部部门', '内部领域']];
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')->field('org_id id,org_name, org_pid pid,org_fullname')
            ->where($where)
            ->order('org_fullnum asc')
            ->select();
        if($isLevel === true){
            $org = getLevelData($org, '0');
            foreach($org as &$value){
                $value['org_name'] = str_repeat( '&nbsp;&nbsp;&nbsp',$value['level']).$value['org_name'];
            }
        }
        return $org;
    }

    public function getOrgId($orgName){
        if(empty($orgName)) return false;
        $where['org_name'] = ['eq', $orgName];
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')->field('org_id id')
            ->where($where)
            ->find();
        if(!empty($org)){
            return $org['id'];
        }else{
            return false;
        }
    }

    /**
     * 根据orgid获取信息
     * @param $orgId
     * @param string $field
     * @return bool
     */
    public function getOrgInfo($orgId, $field = '*'){
        if(empty($orgId)) return false;
        $where['org_id'] = ['eq', $orgId];
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')->field($field)
            ->where($where)
            ->find();
        if(!empty($org)){
            return $org;
        }else{
            return false;
        }
    }

}