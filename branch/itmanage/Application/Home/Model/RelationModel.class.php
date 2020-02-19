<?php
namespace Home\Model;
use Think\Model;
class RelationModel extends Model {
    // 通过视图获取相对应的关联关系
    public function getViewRelationInfo($atpid,$table='')
    {
        $where['c_id'] = ['eq',$atpid];
        if(!empty($table)) $where['r_table'] = ['eq',$table];
        $info = M('v_relation')
            ->where($where)
            ->select();
        return $info;
    }

    //删除关联关系
    public function delRelation($id,$table){
        $model = M('it_relation');
        if(!empty($id)){
            if (is_array($id)) {
                $whereC['rl_cmainid'] = ['in',$id];
            }else{
                $whereR['rl_rmainid'] = ['eq',$id];
            }
            $whereC['rl_ctable'] = ['eq',$table];
            $whereR['rl_rtable'] = ['eq',$table];
            $model->where($whereC)->delete();
            $model->where($whereR)->delete();
        }
    }
    //查两侧id是否都存在
    public function checkRelationAllId($cid,$rid)
    {
        if(!$cid && !$rid) return false;
        $model = M('v_relation');
        $res = $model
            ->where("c_id = '%s' and r_id = '%s'",$cid,$rid)
            ->select();
        if($res){
            return false;
        }else{
            return true;
        }
    }

}