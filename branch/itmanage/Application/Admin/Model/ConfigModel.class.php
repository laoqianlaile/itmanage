<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class ConfigModel extends Model{
    Protected $autoCheckFields = false;

    /**
     * 获取配置项
     * @param string $mark
     * @return array
     */
    public function getConfig($mark = ''){
        $model = M('sysconfig');
        $config = S('systemConfig');
        if(empty($config)){
            $data = $model->field("sc_itemvalue,sc_itemcode")->select();
            $config = [];
            foreach($data as $key=>$value){
                $config[$value['sc_itemcode']] = $value['sc_itemvalue'];
            }
            S('systemConfig', $config);
        }

        if(empty($mark)){
            return $config;
        }else{
            return $config[$mark];
        }
    }

    /**
     * 刷新系统配置
     * @return bool
     */
    public function refreshConfig(){
        S('systemConfig', null);
        $this->getConfig();
        return true;
    }

}
