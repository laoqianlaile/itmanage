<?php
namespace Home\Model;
use Think\Model;
class BaseModel extends Model {
    function buildSqlPage($sql,$start,$length)
    {
        $oraclestart = $start;
        $oracleend = $start + $length;
        return "
Select * From
(
  Select fp.*,Rownum RN From
  (
  $sql
  ) fp
  Where Rownum<=$oracleend
)
Where RN>$oraclestart";
    }

    function buildSqls($sql,$condition)
    {
        if($this->containString($sql," where "))
        {
            return $sql." and ".$condition;
        }
        else
        {
            return $sql." where ".$condition;
        }
    }

    function containString($input,$splite)
    {
        $tmparray = explode($splite, $input);
        if (count($tmparray) > 1) {
            return true;
        } else {
            return false;
        }
    }

    function makeGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return 'guid'.trim(com_create_guid(), '{}');
        }
        return 'guid'.sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    /**
     * 记录操作日志
     * @param $type
     * @param $module
     * @param $content
     * @return mixed
     */
    public function recordLog($type, $module, $content,$table = '',$atpid = ''){
        $optime = date('Y-m-d H:i:s',time());
        $data = array(
            'l_atpid'      => $this->makeGuid(),
            'l_optime'     => $optime,
            'l_ipaddress'  => get_client_ip(),
            'l_optype'     => $type,
            'l_opuserid'   => session('user_name'),
            'l_opusername' => session('realusername'),
            'l_modulename' => $module,
            'l_detail'     => $content,
            'l_tablename'  => $table,
            'l_mainid'     => $atpid
        );
        $res = M('log')->add($data);
        return $res;
    }

    public function getDicFactort($id,$field = ''){
        if(empty($id)) return false;
        $model = M('dic_factory');
        $data = $model
            ->where("dic_status = '0' and dic_id = '%s' ", $id )
            ->find();
        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }
    //查型号字典
    public function getDicXingHaoById($id,$field = ''){
        if(empty($id)) return false;
        $where['dic_id'] = ['eq',$id];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic_xinghao')
            ->where($where)
            ->order('dic_order')
            ->find();

        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }
    //根据字典id获取字典
    public function getDicById($id,$field = ''){
        if(empty($id)) return false;
        $model = M('dic');
        $data = $model
            ->where("dic_status = '0' and dic_id = '%s' ", $id )
            ->find();
        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }

    //查楼宇字典
    public function getDicLouYuById($id,$field = ''){
        if(empty($id)) return false;
        $where['dic_id'] = ['eq',$id];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic_louyu')
            ->where($where)
            ->order('dic_order')
            ->find();

        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }
}