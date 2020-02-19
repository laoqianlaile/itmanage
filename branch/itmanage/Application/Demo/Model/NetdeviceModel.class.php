<?php
namespace Demo\Model;
use Think\Model;
class NetdeviceModel extends BaseModel {
    /**
     * 根据地区获取信息
     */
    public function getNetDeviceByArea($area){
        $switchConfiguresw = C('SwitchConfiguresw');
        $where = [];
        if($switchConfiguresw){
            foreach($switchConfiguresw as $key=>$val){
                $where[1][$key] = $val;
            }
        }
        if($area){
            $where[0]['netdevice_area'] = ['eq',$area];
            $NetDeviceInfo = M('Netdevice')->field('netdevice_atpid,netdevice_ipaddress,netdevice_mask,netdevice_factory,netdevice_loginuser,netdevice_loginpass')->where($where)->where("netdevice_atpstatus is null and netdevice_ipaddress is not null and netdevice_status='在用'")->order('netdevice_ipaddress')->select();
        }else{
            $NetDeviceInfo = M('Netdevice')->field('netdevice_atpid,netdevice_ipaddress,netdevice_mask,netdevice_factory,netdevice_loginuser,netdevice_loginpass')->where($where)->where("netdevice_atpstatus is null and netdevice_ipaddress is not null and netdevice_status='在用'")->order('netdevice_ipaddress')->select();
            // echo M('Netdevice')->_sql();die;
        }
        return $NetDeviceInfo;
    }

    /**
     * 根据楼宇获取信息
     */
    public function getNetDeviceByBuilding($building){
        $switchConfiguresw = C('SwitchConfiguresw');
        $where = [];
        if($switchConfiguresw){
            foreach($switchConfiguresw as $key=>$val){
                $where[1][$key] = $val;
            }
        }
        $where[0]['netdevice_building'] = ['eq',$building];
        $NetDeviceInfo = M('Netdevice')->field('netdevice_atpid,netdevice_ipaddress,netdevice_mask,netdevice_factory,netdevice_loginuser,netdevice_loginpass')->where($where)->where("netdevice_atpstatus is null and netdevice_ipaddress is not null")->order('netdevice_ipaddress')->select();
        return $NetDeviceInfo;
    }

    /**
     * 获取资产台账数据
     */
    public function getNDData($queryparam,$selecttype = ''){
        $Model      = M();
        $sql_select = "
                select * from it_netdevice du";
        $sql_count  = "
                select
                    count(1) c
                from it_netdevice du";
        $sql_select = $this->buildSqls($sql_select,"du.netdevice_atpstatus is null");
        $sql_count  = $this->buildSqls($sql_count,"du.netdevice_atpstatus is null");


        if ("" != $queryparam['ipaddress']){
            $searchcontent = trim($queryparam['ipaddress']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_ipaddress like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_ipaddress like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['macaddess']){
            $searchcontent = trim($queryparam['macaddess']);
            $searchcontent = strtoupper($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_mask like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_mask like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['factory']){
            $searchcontent = trim($queryparam['factory']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_factory ='".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_factory ='".$searchcontent."'");
        }
        if (("" != $queryparam['model']) && ("null" != $queryparam['model'])){
            $searchcontent = trim($queryparam['model']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_model ='".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_model ='".$searchcontent."'");
        }
        if ("" != $queryparam['area']){
            $searchcontent = trim($queryparam['area']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_area ='".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_area ='".$searchcontent."'");
        }
        if (("" != $queryparam['building']) && ("null" != $queryparam['building'])){
            $searchcontent = trim($queryparam['building']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_building ='".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_building ='".$searchcontent."'");
        }
        if ("" != $queryparam['roomno']){
            $searchcontent = trim($queryparam['roomno']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_room like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_room like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['upport']){
            $searchcontent = trim($queryparam['upport']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_upport like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_upport like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['name']){
            $searchcontent = trim($queryparam['name']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_name like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_name like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['code']){
            $searchcontent = trim($queryparam['code']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_anecode like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_anecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['sn']){
            $searchcontent = trim($queryparam['sn']);
            $searchcontent = strtoupper($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_sn like '%".$searchcontent."%'
                 or upper(du.netdevice_sn) like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_sn like '%".$searchcontent."%'
                or upper(du.netdevice_sn) like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['net']){
            $searchcontent = trim($queryparam['net']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_net like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_net like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['dutyman']){
            $searchcontent = trim($queryparam['dutyman']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_dutyman = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_dutyman = '".$searchcontent."'");
        }
        if ("" != $queryparam['dutydept']){
            $searchcontent = trim($queryparam['dutydept']);
            $searchcontent = D('Depart')->getDeptSubIdById($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_dutydeptid in (".$searchcontent.")");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_dutydeptid in (".$searchcontent.")");
        }
        if ("" != $queryparam['usage']){
            $searchcontent = trim($queryparam['usage']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_usage like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_usage like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['protocol']){
            $searchcontent = trim($queryparam['protocol']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_protocol like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_protocol like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['isscan']){
            $searchcontent = trim($queryparam['isscan']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_isscan = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_isscan = '".$searchcontent."'");
        }
        if ("" != $queryparam['status']){
            $searchcontent = trim($queryparam['status']);
            if($searchcontent == ''){
                $sql_select = $this->buildSqls($sql_select,"du.netdevice_status is null");
                $sql_count  = $this->buildSqls($sql_count,"du.netdevice_status is null");
            }else{
                $sql_select = $this->buildSqls($sql_select,"du.netdevice_status ='".$searchcontent."'");
                $sql_count  = $this->buildSqls($sql_count,"du.netdevice_status ='".$searchcontent."'");
            }
        }
        if ("" != $queryparam['secretlevel']){
            $searchcontent = trim($queryparam['secretlevel']);
            $sql_select = $this->buildSqls($sql_select,"du.netdevice_secretlevel ='".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"du.netdevice_secretlevel ='".$searchcontent."'");
        }

        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
            $sql_select .= " nulls last";
        } else {
            $sql_select = $sql_select . " order by du.netdevice_atpid  asc  ";
        }

        if (null != $queryparam['limit']) {
            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
       // echo $sql_select;die;
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value){
            $value['netdevice_area']        = getDictname($value['netdevice_area']);
            $value['netdevice_building']    = getDictname($value['netdevice_building']);
            $value['netdevice_factory']     = getDictname($value['netdevice_factory']);
            $value['netdevice_model']       = getDictname($value['netdevice_model']);
            $value['netdevice_net']         = getDictname($value['netdevice_net']);
            $value['netdevice_secretlevel'] = getDictname($value['netdevice_secretlevel']);
            $value['netdevice_privacybook'] = getDictname($value['netdevice_privacybook']);
            $value['netdevice_devicebook']  = getDictname($value['netdevice_devicebook']);
            if($value['netdevice_isscan'] === '1'){
                $value['netdevice_isscan'] = '扫描';
            }else if($value['netdevice_isscan'] === '0'){
                $value['netdevice_isscan'] = '不扫描';
            }
            if(!empty($value['netdevice_atplastmodifyuser'])){
                $value['netdevice_atplastmodifyuser'] = getRealusername($value['netdevice_atplastmodifyuser']);
            }
//            if($selecttype){
//                // 处理责任部门
//                $dutydeptname = $value['netdevice_dutydept'];
//                $dutydeptname = explode('-',$dutydeptname);
//                $tmp = $dutydeptname[count($dutydeptname)-1];
//                if(strpos($tmp,'中国航天') !== false){
//                    unset($dutydeptname[count($dutydeptname)-1]);
//                    $dutydeptname = array_reverse($dutydeptname);
//                    $Result[$key]['netdevice_dutydept'] = implode('-',$dutydeptname);
//                }
//            }
        }
        return [$Result,$Count];
    }
    /**
     * 根据IP地址获取厂家
     */
    function getFactory($ipaddress){
        $factory = M('netdevice')->where("netdevice_ipaddress ='".$ipaddress."'")->getField('netdevice_factory');
        return $factory;
    }
}