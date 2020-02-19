<?php
namespace Demo\Model;
use Think\Model;
class DepartModel extends BaseModel {
    function getDeptInfo(){
        $deptInfo = S('deptInfomation');
        if(empty($deptInfo)) {
            $deptInfo = M('depart')->select();
            S('deptInfomation', $deptInfo, 3600);
        }
        return $deptInfo;
    }

    /**
     * 根据部门id获取信息
     */
    function getDeptInfoByIds($ids){
        $idsTmp   = explode(',',$ids);
        $ids      = "'".implode($idsTmp,"','")."'";
        $info     = M('depart')->where("id in(".$ids.")")->field('id,name,pid,fullname')->order(array('fullnum'=>'asc'))->select();
        return $info;
    }

    /**
     * 根据名称（全名）获取id信息
     */
    function getDeptIdByName($name){
        $name = trim($name);
        $id   = M('depart')->where("name = '".$name."'")->field('id')->getField('id');
//        $sql  = M('depart')->_sql();
        return $id;
    }

    /**
     * 根据fullname（模糊）获取id信息
     */
    function getDeptIdByFullname($name){
        $name = trim($name);
        $id   = M('depart')->where("fullname like '%".$name."%'")->field('id')->getField('id');
//        $sql  = M('depart')->_sql();
        return $id;
    }

    /**
     * 获取所有部门全称（格式：id->fullname）
     */
    function getAllFullName(){
        $deptInfo  = M('depart')->field('id,fullname')->select();
        $fullnames = [];
        foreach($deptInfo as $key=>$val){
            $fullnames[$val['id']] = $val['fullname'];
        }
        return $fullnames;
    }

    /**
     * 获取DEPT/OFFICE名称
     */
    function getDepartment($id){
        if(empty($id)) return null;
        $res  = M('depart')->where("id='%s'",$id)->find();
        if(empty($res)) return null;
        return $res['name'];
    }


    /**
     * 根据部门ID获取子部门ID信息(包含本身)
     * return string
     **/
    public function getDeptSubIdById($id){
        if(empty($id)) return false;
        $ids = M()->query("SELECT * FROM it_depart start with id = '".$id."' connect by prior id=pid;");
        $ids = removeArrKey($ids,'id');
        $ids = "'".implode("','",$ids)."'";
        return $ids;
    }
}