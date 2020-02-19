<?php
namespace Demo\Model;
use Think\Model;
class SwitchportModel extends BaseModel {

    /**
     * 获取交换机表所有数据
     */
    function getAllSwitchData(){
        $switchInfo = S('switchportData');
        if(empty($switchInfo)){
            $switchInfo    = M('switchport')->select();
            S('switchportData',$switchInfo,3600*24);
        }
        return $switchInfo;
    }

    /**
     * 获取交换机表MAC为空数据
     */
//    function getSwitchNoMACData(){
//        $switchInfo    = M('switchport')->where('SWPORT_MACADDRESS')->select();
//        return $switchInfo;
//    }

    function switchesData($queryparam){
        $model      = M();
        $sql_select = "SELECT s.sw_atpid sw_atpid,upper(s.sw_macaddress) sw_macaddress,s.sw_ipaddress,s.sw_interface from IT_SWITCHNEWINFO_T s where not exists( SELECT * FROM IT_TERMINAL t where upper(s.sw_macaddress) = upper(t.zd_macaddress)) and s.sw_macaddress is not null";
        $sql_count  = "SELECT count(*) c from IT_SWITCHNEWINFO_T s where not exists( SELECT * FROM IT_TERMINAL t where upper(s.sw_macaddress) = upper(t.zd_macaddress)) and s.sw_macaddress is not null";
        if ("" != $queryparam['macaddress']){
            $searchcontent = trim($queryparam['macaddress']);
            $searchcontent = strtolower($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"s.sw_macaddress = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"s.sw_macaddress = '".$searchcontent."'");
        }
        if ("" != $queryparam['ipaddress']){
            $searchcontent = trim($queryparam['ipaddress']);
            $sql_select = $this->buildSqls($sql_select,"s.sw_ipaddress like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"s.sw_ipaddress like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['port']){
            $searchcontent = trim($queryparam['port']);
            $sql_select = $this->buildSqls($sql_select,"s.sw_interface like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"s.sw_interface like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by s.sw_atpid desc  ";
        }

        if (null != $queryparam['limit']) {
            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        $diffInfo  = $model->query($sql_select);
        $Count     = $model->query($sql_count);
        return [$diffInfo,$Count[0]['c']];
    }
}