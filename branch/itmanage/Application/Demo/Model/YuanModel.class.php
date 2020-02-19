<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Demo\Model;
use Think\Model;
class YuanModel extends Model{
    Protected $autoCheckFields = false;


    /**
     * 获取层级会议列表
     * @param bool|true $isLevel
     * @return array
     */
    public function  getMeetList($isLevel = true, $where = []){
        $org = M('meetinfo m')->field("mt_id,mt_name,mt_type,to_char(mt_date,'YYYY') mt_date")
            ->join("v_wytodoinfo w on m.mt_id = w.td_meetid")
            ->where($where)
            ->order('mt_name')
            ->group('mt_id, mt_name,mt_type,mt_date')
            ->select();
        if($isLevel === true){
            $org = getLevelData($org, null);
            foreach($org as &$value){
                $value['mt_name'] = str_repeat( '&nbsp;&nbsp;&nbsp',$value['mt_type']).$value['mt_name'];
            }
        }
        return $org;
    }

    public function  getMeetLists($isLevel = true, $where = []){
        $org = M('meetinfo m')->field("mt_id,mt_name,mt_type,to_char(mt_date,'YYYY') mt_date")
            ->join("v_wytodoinfo w on m.mt_id = w.td_meetid",'left')
            ->where($where)
            ->order('mt_name')
            ->group('mt_id, mt_name,mt_type,mt_date')
            ->select();
        if($isLevel === true){
            $org = getLevelData($org, null);
            foreach($org as &$value){
                $value['mt_name'] = str_repeat( '&nbsp;&nbsp;&nbsp',$value['mt_type']).$value['mt_name'];
            }
        }
        return $org;
    }

    public function GetWorkFlow($status,$type,$id,$content){
        M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:mi:ss'");
        $user = M('v_sysuser');
        $userId = session('user_id');
        $dept = $user->where("user_id = '%s'",$userId)->find();
        $data['wf_id'] = makeGuid();
        $data['wf_user'] = $dept['user_realusername'].'('.$dept['org_name'].','.$dept['user_name'].')';
        $data['wf_time'] = date('Y-m-d H:i:s');
        $data['wf_objtype'] = $type;
        $data['wf_objid'] = $id;
        $data['wf_action'] = $status;
        $data['wf_userid'] = $userId;
        $data['wf_content'] = $content;
        return $data;

    }

    public function judge($type,$oldData,$newData){
        $data = C($type);
        $log="";
        foreach($data as $key =>$val){
            if($oldData[$key]!=$newData[$key]){
                $log.= $val.':'.$oldData[$key].'=>'.$newData[$key].';';
            }
        }
        return $log;
    }



}