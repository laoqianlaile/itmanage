<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Demo\Model;
use Think\Model;
class MeetModel extends Model{
    Protected $autoCheckFields = false;


    /**
     * 根据会议名称获取会议id
     * @param $meetName
     * @return bool
     */
    public function getMeetId($meetName){
        if(empty($meetName)) return false;
        $model = M('meetinfo');
        $data = $model->field("mt_id id")
            ->where("mt_name = '%s' ", $meetName)
            ->find();
        if(empty($data)){
            return false;
        }else{
            return $data['id'];
        }
    }

}