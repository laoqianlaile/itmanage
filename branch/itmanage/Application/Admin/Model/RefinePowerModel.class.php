<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class RefinePowerModel extends Model{
    Protected $autoCheckFields = false;

    /**
     * 指定配置项，指定角色，读取角色在细化页面的授权信息，角色默认为当前登录人的角色信息
     * @param string $type
     * @param $roleIds
     * @return array|bool
     */
    public function getViewPowers($type = 'SevRoleViewConfig', $roleIds ){
        if(empty($roleIds)) $roleIds = explode(',', session('roleids'));
        $config = C($type);
        if(empty($config)) return false;
        $initData = [];
        foreach($config as $menuName => $menu){
            foreach($menu['roles'] as $roleId => $roleName){
                if(in_array($roleId, $roleIds)) $initData[$menuName] = $menu['path'];
            }
        }
        return $initData;
    }
}