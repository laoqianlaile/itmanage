<?php
namespace Home\Model;
use Think\Model;
class DicModel extends Model {

    //查字典pid
    public function getDicByPid(){
        $q = I('post.dic_id');
        if($q){
            $data = M('dic')
                ->field('dic_id,dic_name,dic_value,dic_type')
                ->where("dic_status=0 and dic_pid='%s'", $q)
                ->order('dic_order')
                ->select();
            return json_encode(array('q' =>$q, 'results' => $data,'code'=>1));
        }
    }
    //查字典name
    public function getDicByName(){
        $q = I('post.dic_name');
        if($q) {
            $data = M('dic')
                ->field('dic_id,dic_name,dic_value,dic_pid,dic_type')
                ->where("dic_status=0 and dic_name='%s'", $q)
                ->order('dic_order')
                ->select();
            return json_encode(array('q' =>$q, 'results' => $data,'code'=>1));
        }
    }

    //查楼宇
    public function getDicLouYu($pid)
    {
        if($pid) {

            $data = M('dic_louyu')
                ->where("dic_status=0 and dic_pid='%s'", $pid)
                ->order('dic_name')
                ->select();

            return $data;
        }
    }
    //查设备类型id
    public function getTypeId($id=''){
        if($id) {
            if(!empty($id)) $where['dic_name'] = ['eq',$id];
            $where['dic_status'] = ['eq','0'];
            $data = M('dic')
                ->where($where)
                ->find();
            return $data;
        }

    }
    //查资产名称id
    public function getTypeIId($id=''){
        if($id) {
            if(!empty($id)) $where['name'] = ['eq',$id];
           // $where['dic_status'] = ['eq','0'];
            $data = D('v_atpidall')
                ->where($where)
                ->find();
            return $data['atpid'];
        }

    }

    //查资产名称
    public function getTypeName($id){
        if($id) {
            if(!empty($id)) $where['atpid'] = ['eq',$id];
            //$where['dic_status'] = ['eq','0'];
            $data = D('v_atpidall')
                ->where($where)
                ->find();
            return $data['name'];
        }
    }

    //查资产名称(模糊查询)
    public function getTypeNames(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.name id,p.name text from  v_atpidall_checkup p where
                    (p.name like '%".$q."%') ";
        $result=$Model->query($sql_select);
        return json_encode(array('q' =>$q, 'results' => $result));
    }


    //查资产名称(模糊查询)
    public function getTypeIp(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.ip id,p.ip text from  v_atpidall p where
                    (p.ip like '%".$q."%') ";
        $result=$Model->query($sql_select);
        return json_encode(array('q' =>$q, 'results' => $result));
    }
    //查厂家名字
    public function getDicFactoryName($id){
        if($id) {
            if(!empty($id)) $where['dic_id'] = ['eq',$id];
            $where['dic_status'] = ['eq','0'];
            $data = M('dic_factory')
                ->where($where)
                ->find();
            return $data['dic_name'];
        }
    }
    //查型号名字
    public function getDicXingHaoName($id){
        if($id) {
            if(!empty($id)) $where['dic_id'] = ['eq',$id];
            $where['dic_status'] = ['eq','0'];
            $data = M('dic_xinghao')
                ->where($where)
                ->find();
            return $data['dic_name'];
        }
    }
    //查地区名字
    public function getDicAreaName($id){
        if($id) {
            if(!empty($id)) $where['dic_id'] = ['eq',$id];
            $where['dic_status'] = ['eq','0'];
            $data = M('dic')
                ->where($where)
                ->find();
            return $data['dic_name'];
        }
    }
    //查楼宇名字
    public function getDicLouYuName($id){
        if($id) {
            if(!empty($id)) $where['dic_id'] = ['eq',$id];
            $where['dic_status'] = ['eq','0'];
            $data = M('dic_louyu')
                ->where($where)
                ->find();
            return $data['dic_name'];
        }
    }


    //查厂家
    public function getDicFactory($pid='')
    {
        if($pid) {
            if(!empty($pid)) $where['dic_pid'] = ['eq',$pid];
            $where['dic_status'] = ['eq','0'];
            $data = M('dic_factory')
                ->where($where)
                ->order('dic_name')
                ->select();
            return $data;
        }
    }

    //查型号
    public function getDicXingHao($pid='')
    {
        if($pid) {
            if(!empty($pid)) $where['dic_type'] = ['eq',$pid];
            $where['dic_status'] = ['eq','0'];
            $data = M('dic_xinghao')
                ->where($where)
                ->order('dic_name')
                ->select();
            return $data;
        }
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
                ->order('dic_name asc')
                ->select();
        }else if(is_array($dicType)){
            $result = $model->field('dic_value as val,dic_name,type_name,dic_id')
                ->where(['type_name' => ['in', $dicType], 'dic_status' => ['eq', 0]] )
                ->join('dic_type on dic.dic_type=dic_type.dic_type_id')
                ->order('dic_name asc')
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
    public function getDicValueByNames($dicType){
        $data = [];
        if(empty($dicType)) return false;
        $model = M('dic');
        if(is_string($dicType)){
            $data = $model->field('dic_value as val,dic_name,dic_id')
                ->where("type_name = '%s' and dic_status=0", $dicType )
                ->join('dic_type on dic.dic_type=dic_type.dic_type_id')
                ->order('dic_order asc')
                ->select();
        }else if(is_array($dicType)){
            $result = $model->field('dic_value as val,dic_name,type_name,dic_id')
                ->where(['type_name' => ['in', $dicType], 'dic_status' => ['eq', 0]] )
                ->join('dic_type on dic.dic_type=dic_type.dic_type_id')
                ->order('dic_order asc')
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

    public function getFactoryList($dicType){
        if(empty($dicType)) return false;
        $model  =M('dic');
        $typeId = $model->where("dic_name = '%s'",$dicType)->getField('dic_id');
        $factoryList = M('dic_factory')->where("dic_type = '%s'",$typeId)->order('dic_name')->select();

        return $factoryList;
    }

    public function assignsbtype(){
        $Model    = M('dic');
        $type     = $Model->where("d_belongtype='equipmenttype' and d_dictype='terminal' and d_atpstatus is null")->order('d_sortno')->field('d_dictname,d_atpid')->select();
        $area     = $Model->where("d_belongtype='%s'and d_atpstatus is null","region")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $miji     = $Model->where("d_belongtype='%s'and d_atpstatus is null","密级")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $bmtz     = $Model->where("d_belongtype='%s'and d_atpstatus is null","保密台账")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $zctz     = $Model->where("d_belongtype='%s'and d_atpstatus is null","资产台账")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $nettype  = $Model->where("d_parentid='%s' and d_atpstatus is null","157797")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $sbtypelist = $Model->where("d_belongtype = 'factoryinfo' and d_parentid='154117' and d_atpstatus is null")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
//        dump($nettype);die;
        return [$type,$area,$miji,$bmtz,$zctz,$nettype,$sbtypelist];
    }

    public function getDictIDByName($name,$type){
        $model = M('dic d');
        $res = $model->join('dic_type t on d.dic_type = t.dic_type_id')->where("type_name = '".$type."' and dic_name = '".$name."'")->find();
        return $res;
    }

}