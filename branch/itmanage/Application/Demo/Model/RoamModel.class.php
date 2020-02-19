<?php
namespace Demo\Model;
use Think\Model;
class RoamModel extends BaseModel {
    const TABLENAME = '"ATP"."ROAMINGUSER"'; 

    /**
     * 拼接ip数据主列表
     */
    function getRoamInfo($queryparam,$querytype=0)
    {
        if(!$querytype){
            $sql_select="
            select *,rowid from ".self::TABLENAME." r";
        }else{
            $sql_select="
            select username,realusername,orgfullname,logintime,ipaddr,mainuserid,mainusername,mainuserorg,systemname,result,maindevicetype,devicetype,status,zcbg_count,bd_count from ".self::TABLENAME." r";
        }
        
        $sql_count="
                select
                    count(1) c
                from ".self::TABLENAME." r";
//            print_r($queryparam);die;
        if ("" != $queryparam['result']){
            $searchcontent = trim($queryparam['result']);
            $sql_select = $this->buildSqls($sql_select,"r.result = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"r.result = '".$searchcontent."'");
//            echo $sql_select;die;
        }
        if ("" != $queryparam['systemname']){
            $searchcontent = trim($queryparam['systemname']);
            $sql_select = $this->buildSqls($sql_select,"r.systemname = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"r.systemname = '".$searchcontent."'");
        }

        if ("" != $queryparam['begintime']){
            $searchcontent = trim($queryparam['begintime']);
            $sql_select = $this->buildSqls($sql_select,"r.logintime > '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"r.logintime > '".$searchcontent."'");
        }
        if ("" != $queryparam['endtime']){
            $searchcontent = trim($queryparam['endtime']);
            $sql_select = $this->buildSqls($sql_select,"r.logintime < '".$searchcontent."'");
            $sql_count = $this->buildSqls($sql_count,"r.logintime < '".$searchcontent."'");
        }
        if ("" != $queryparam['mainuserid']){
            $searchcontent = trim($queryparam['mainuserid']);
            $sql_select = $this->buildSqls($sql_select,"r.mainuserid like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"r.mainuserid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['ipaddr']){
            $searchcontent = trim($queryparam['ipaddr']);
            $sql_select = $this->buildSqls($sql_select,"r.ipaddr like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"r.ipaddr like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['maindevicetype']){
            $searchcontent = trim($queryparam['maindevicetype']);
            $sql_select = $this->buildSqls($sql_select,"r.maindevicetype = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"r.maindevicetype = '".$searchcontent."'");
        }
        if ("" != $queryparam['devicetype']){
            $searchcontent = trim($queryparam['devicetype']);
            $sql_select = $this->buildSqls($sql_select,"r.devicetype = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"r.devicetype = '".$searchcontent."'");
        }
        if ("" != $queryparam['status']){
            $searchcontent = trim($queryparam['status']);
            $sql_select = $this->buildSqls($sql_select,"r.status = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"r.status = '".$searchcontent."'");
        }
        if ("" != $queryparam['username']){
            $searchcontent = trim($queryparam['username']);
            $sql_select = $this->buildSqls($sql_select,"r.username like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"r.username like  '%".$searchcontent."%'");
        }
        if ("" != $queryparam['realusername']){
            $searchcontent = trim($queryparam['realusername']);
            $sql_select = $this->buildSqls($sql_select,"r.realusername like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"r.realusername like  '%".$searchcontent."%'");
        }
        if ("" != $queryparam['orgfullname']){
            $searchcontent = trim($queryparam['orgfullname']);
            $sql_select = $this->buildSqls($sql_select,"r.orgfullname like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"r.orgfullname like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' nulls last';
        } else {
            $sql_select = $sql_select . " order by r.logintime desc  ";
        }

        if (null != $queryparam['limit'])
        {
            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        if($querytype == 2){
            $Count    = M()->query($sql_count);
            return $Count;
        }else{
            $Result   = M()->query($sql_select);
        $Count    = M()->query($sql_count);

        foreach($Result as $key=> $value){
           // 处理被登录人部门
            $orgfullname = $value['orgfullname'];
            $orgfullname = explode('-',$orgfullname);
            $tmp = $orgfullname[count($orgfullname)-1];
            if(strpos($tmp,'中国航天') !== false){
                unset($orgfullname[count($orgfullname)-1]);
                $orgfullname = array_reverse($orgfullname);
                $Result[$key]['orgfullname'] = implode('-',$orgfullname);
            }
            
               // 处理登录设备责任人部门
            $mainuserorg = $value['mainuserorg'];
            $mainuserorg = explode('-',$mainuserorg);
            $tmp = $mainuserorg[count($mainuserorg)-1];
            if(strpos($tmp,'中国航天') !== false){
                unset($mainuserorg[count($mainuserorg)-1]);
                $mainuserorg = array_reverse($mainuserorg);
                $Result[$key]['mainuserorg'] = implode('-',$mainuserorg);
            }
        }
        return [$Result,$Count];
        } 
    }
}