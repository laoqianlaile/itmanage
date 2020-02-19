<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class DictionaryModel extends Model{
    Protected $autoCheckFields = false;

    /**
     * 获取字典类型
     */
    public function  getDicType(){
        $dicType = M('dic_type')
                    ->field('dic_type_id, type_name')
                    ->where('dic_type_is_hide = 0')
                    ->select();
        return $dicType;
    }

    /**
     * 根据字典类型获取该类型的字典值,建议传入数组一次获取减少查询次数
     * @param string||array $dicType
     * @param bool $isGetAll 是否获取全部密级，将忽略当前项目的密级获取全部
     * @return array
     */
    public function getDicValueByName($dicType){
        $data = [];
        if(empty($dicType)) return false;
        $model = M('dic');
        if(is_string($dicType)){
            $data = $model->field('dic_value as val,dic_name,dic_id')
                ->where("type_name = '%s' and dic_status=0", $dicType )
                ->join('dic_type on dic.dic_type=dic_type.dic_type_id')
                ->order('dic_order asc nulls last,dic_createtime asc')
                ->select();
        }else if(is_array($dicType)){
            $result = $model->field('dic_value as val,dic_name,type_name,dic_id')
                ->where(['type_name' => ['in', $dicType], 'dic_status' => ['eq', 0]] )
                ->join('dic_type on dic.dic_type=dic_type.dic_type_id')
                ->order('dic_order asc nulls last,dic_createtime asc')
                ->select();
            $data = [];
            foreach($result as $key=>$value){
                $data[$value['type_name']][] = [
                    'val' => $value['val'],
                    'dic_name' => $value['dic_name'],
                    'dic_id' => $value['dic_id']
                ];
            }
        }
        return $data;
    }

    /**
     * 根据字典id获取字典
     * @param $id
     * @return bool
     */
    public function getDicById($id){
        if(empty($id)) return false;
        $model = M('dic');
        $data = $model->field('dic_value as val,dic_name,dic_id')
            ->where("dic_id = '%s' ", $id )
            ->find();
        return $data;
    }

    //查字典pid
    public function getDicByPid($pid){
        if($pid){
            $data = M('dic')
                ->field('dic_id,dic_name,dic_value,dic_type')
                ->where("dic_status=0 and dic_pid='%s'", $pid)
                ->select();
            return $data;
        }
    }

    /**
     * 获取当前用户有权限操作的文件密级
     * @param $secretLevel
     * @return array
     */
    public function getPowerSecretLevel($secretLevel = 0){
        if(empty($secretLevel)) $secretLevel = (string) session('user_secretlevel');

        $where['type_name'] = ['eq', '文件密级'];
        $where['dic_value'] = ['elt', $secretLevel];

        $data = M('dic')->where($where)
            ->field('dic_value, dic_name')
            ->join('dic_type on dic.dic_type=dic_type.dic_type_id')
            ->order('dic_value desc')
            ->select();
        return $data;
    }
}
