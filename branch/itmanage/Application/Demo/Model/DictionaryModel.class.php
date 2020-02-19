<?php
namespace Demo\Model;
use Think\Model;
class DictionaryModel extends BaseModel {
    /*
     * 获取密级信息
     */
    function getSecretInfo(){
        $seclevel = S('seclevelInformation');
        if(empty($seclevel)) {
            $seclevel = M('dictionary')->where("d_belongtype = '密级'")->field('d_dictname,d_sortno')->order(array('d_sortno' => 'asc'))->select();
            S('seclevelInformation', $seclevel, 3600);
        }
        return $seclevel;
    }
    /*
     * 获取地区信息
     */
    function getAreaInfo(){
        $area     = M('dictionary')->where("d_belongtype = 'region'")->field('d_atpid,d_dictname')->order(array('d_sortno'=>'asc'))->select();
        return $area;
    }

    /*
     * 根据父ID获取楼宇信息
     */
    function getBuildingByPID($pid){
        $buildinglist = M('dictionary')->where("d_parentid='%s'",$pid)->field('d_dictname,d_atpid')->select();
        return $buildinglist;
    }

    /*
     * 获取所有地区详细信息(全称)
     */
    function getAreaAllInfo(){
        $area = S('areaInfomation');
        if(empty($area)) {
            $parea = $this->getAreaInfo();
            $pids = [];
            foreach ($parea as $item) {
                $pids[] = $item['d_atpid'];
            }
            $pids = implode("','", $pids);
            $pids = "'" . $pids . "'";
            $area = M('dictionary')->where("d_belongtype = 'region' or d_belongtype = 'building' or d_parentid in (" . $pids . ")")->field('d_atpid,d_dictname,d_parentid')->order(array('d_sortno' => 'asc'))->select();
            $areaInfo = [];
            foreach ($area as $key => $val) {
                $areaInfo[$val['d_atpid']] = $val['d_dictname'];
            }
            foreach ($area as $key => $val) {
                if ($val['d_parentid'] != '141159') {
                    $tmp = $val['d_parentid'];
                    $area[$key]['d_dictname'] = $areaInfo[$tmp] . '-' . $val['d_dictname'];
                }
            }
            S('areaInfomation',$area,3600);
        }
        return $area;
    }

    /*
     * 获取所有地区父ID信息
     */
    function getAreaAllPInfo()
    {
        $areaInfo = S('areaPidInfo');
        if(empty($areaInfo)){
            $parea    = $this->getAreaInfo();
            $pids     = [];
            foreach($parea as $item){
                $pids[] = $item['d_atpid'];
            }
            $pids     = implode("','",$pids);
            $pids     = "'".$pids."'";
            $area     = M('dictionary')->where("d_belongtype = 'region' or d_belongtype = 'building' or d_parentid in (".$pids.")")->field('d_atpid,d_dictname,d_parentid')->order(array('d_sortno'=>'asc'))->select();
            $areaInfo = [];
            foreach($area as $key=>$val){
                $areaInfo[$val['d_atpid']] = $val['d_parentid'];
            }
            S('areaPidInfo',$areaInfo,3600*2);
        }
        return $areaInfo;
    }

    /*
     * 获取资产类别信息
     */
    function getEquipmentTypeInfo(){
        $seclevel = M('dictionary')->where("d_belongtype = 'equipmenttype'")->field('d_dictname,d_sortno,d_atpid')->order(array('d_sortno'=>'asc'))->select();
        return $seclevel;
    }

    /*
     * 根据地区id获取信息
     */
    function getAreaInfoByIds($ids){
        $idsTmp   = explode(',',$ids);
        $ids      = "'".implode($idsTmp,"','")."'";
        $area     = M('dictionary')->where("d_belongtype = 'region' or d_belongtype = 'building'")->field('d_atpid,d_dictname,d_parentid')->order(array('d_sortno'=>'asc'))->select();
        $info     = M('dictionary')->where("d_atpid in(".$ids.")")->field('d_atpid,d_dictname,d_parentid')->order(array('d_sortno'=>'asc'))->select();
        $areaInfo = [];
        foreach($area as $key=>$val){
            $areaInfo[$val['d_atpid']] = $val['d_dictname'];
        }
        foreach($info as $key=>$val){
            if($val['d_parentid'] != '141159'){
                $tmp = $val['d_parentid'];
                $info[$key]['d_dictname'] = $areaInfo[$tmp].'-'.$val['d_dictname'];
            }
        }
        return $info;
    }

    /**
     * 根据名称获取ID信息(全称，非模糊)
     */
    function getIDByDicname($name){
        $name = trim($name);
        $id   = M('dictionary')->where("d_dictname = '".$name."'")->field('d_atpid')->getField('d_atpid');
        return $id;
    }

    /**
     * 根据名称获取ID信息(格式：父名称-子名称)
     */
    function getIDByname($name){
        $name = trim($name);
        $tmp  = explode('-',$name);
        $pname = $tmp[0];
        $sname = $tmp[1];
        $pid   = $this->getIDByDicname($pname);
        $sid   = M('dictionary')->where("d_parentid='".$pid."' and d_dictname like '%".$sname."%'")->field('d_atpid')->getField('d_atpid');
        return $sid;
    }

    /**
     * 获取登录到白名单
     */
    function AllowLogin(){
        $res = M('Dictionary')->field('d_dictname')->where("d_belongtype = 'filter'")->select();
        $result = [];
        foreach($res as $val){
            $result[] = strtoupper($val['d_dictname']);
        }
        $result = implode(',',$result);
        return $result;
    }

    /**
     * 获取所有字典表信息（格式：id->名称）
     */
    function getAllDictionaryInfo(){
        $res = M('Dictionary')->field('d_atpid id,d_dictname name')->select();
        $result = [];
        foreach($res as $key=>$val){
            $result[$val['id']] = $val['name'];
        }
        return $result;
    }

    /**
     * 获取字典名称
     */
    function getDictnameById($atpid){
        if(empty($atpid)) return false;
        $atpid    = trim($atpid);
        $dictname = M('dictionary')->where("d_atpid = '".$atpid."'")->field('d_dictname')->getField('d_dictname');
//        echo M('dictionary')->_sql();die;
        return $dictname;
    }

    /**
     * 获取关联关系信息
     * $type不为空时返回数据格式：id=>name
     */
    function getRelations($type = ''){
        $relationInfo = M('dictionary')->where("d_belongtype = '关联关系'")->field('d_atpid,d_dictname')->select();
        if(!empty($type)){
            $relationInfos = [];
            foreach($relationInfo as $key=>$val){
                $relationInfos[$val['d_atpid']] = $val['d_dictname'];
            }
            return $relationInfos;
        }else{
            return $relationInfo;
        }
    }

    public function assignsbtype(){
        $Model    = M('dictionary');
        $data     = $Model->where("d_belongtype='equipmenttype' and d_dictype='terminal' and d_atpstatus is null")->order('d_sortno')->field('d_dictname,d_atpid')->select();
        $areadata = $Model->where("d_belongtype='%s'and d_atpstatus is null","region")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $miji     = $Model->where("d_belongtype='%s'and d_atpstatus is null","密级")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $bmtz     = $Model->where("d_belongtype='%s'and d_atpstatus is null","保密台账")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $zctz     = $Model->where("d_belongtype='%s'and d_atpstatus is null","资产台账")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $nettype  = $Model->where("d_parentid='%s' and d_atpstatus is null","157797")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
        $sbtypelist = $Model->where("d_belongtype = 'factoryinfo' and d_parentid='154117' and d_atpstatus is null")->field('d_dictname,d_atpid')->order('d_sortno asc')->select();
//        dump($nettype);die;
        return [$data,$areadata,$miji,$bmtz,$zctz,$nettype,$sbtypelist];
    }

    /**
     * 获取所有登录到模板信息
     */
    public function getLogintoModel(){
        $modelInfo = M('dictionary')->field('d_dictname,d_detail')->where("d_atpstatus is null and d_belongtype = 'moban'")->select();
        return $modelInfo;
    }

    /**
     * 获取所有交换机模板名称信息
     */
    public function getSWModelname(){
        $modelInfo = M('dictionary')->field('d_dictname,d_atpid')->where("d_atpstatus is null and d_belongtype = 'moban' and d_parentid = 'guidC0484136-284E-4A98-BE7C-1FFEAD67C8BC'")->select();
        return $modelInfo;
    }

}