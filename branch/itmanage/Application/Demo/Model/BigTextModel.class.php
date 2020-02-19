<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Demo\Model;
use Think\Model;
class BigTextModel extends Model{
    Protected $autoCheckFields = false;

    //定义表名
    const TABLE_NAME = 'bigtext||clob';

    //定义表字段
    const TABLE_FIELD = 'bt_id, bt_title, bt_content, bt_createuser, bt_createtime';

    /**
     * 根据主键获取数据
     * @param $id
     * @return array
     */
    public function getDataById($id){
        if(empty($id)) return false;
        //初始化表连接
        $model = M(self::TABLE_NAME);

        //查找主键对应信息
        $data = $model->where("bt_id = '%s'", $id)->field(self::TABLE_FIELD)->find();
        return $data;
    }

    /**
     * 添加、保存数据
     * @param $data
     * @param string $id
     * @return bool|mixed
     */
    public function saveData($data, $id = ''){
        //初始化表连接
        $model = M(self::TABLE_NAME);

        //过滤数据
        $data  = $model->create($data);

        if(!empty($id)){
            $res = $model->where("bt_id = '%s'", $id)->save($data);
        }else{
            $data['bt_id'] = makeGuid();
            $data['bt_createtime'] = time();
            $data['bt_createuser'] = session('user_id');
            $res = $model->add($data);
        }
        return $res;
    }

    /**
     * 删除数据
     * @param $id
     * @return bool|mixed
     */
    public function deleteData($id){
        if(empty($id)) return false;
        //初始化表连接
        $model = M(self::TABLE_NAME);

        $res = $model->where("bt_id = '%s'", $id)->delete();
        return $res;
    }
}