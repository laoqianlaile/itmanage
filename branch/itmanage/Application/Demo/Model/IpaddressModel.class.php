<?php
namespace Demo\Model;
use Think\Model;
class IpaddressModel extends BaseModel {
    const TABLENAME = 'ipaddress';

    /**
     * 拼接ip数据主列表
     */
    function getIPInfo($queryparam,$selecttype = '')
    {
        $sql_select="
                select *  from it_ipaddress ip where ip_isdel is null";
        $sql_count="
                select
                    count(1) c
                from it_ipaddress ip where ip_isdel is null";
//            print_r($queryparam);die;
        if ("" != $queryparam['ipstart']){
            $searchcontent = trim($queryparam['ipstart']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_start like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_start like '%".$searchcontent."%'");
//            echo $sql_select;die;
        }
        if ("" != $queryparam['ipend']){
            $searchcontent = trim($queryparam['ipend']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_end like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_end like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['ipmask']){
            $searchcontent = trim($queryparam['ipmask']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_mask like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_mask like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['ipgateway']){
            $searchcontent = trim($queryparam['ipgateway']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_gateway like '%".$searchcontent."%'");
            $sql_count = $this->buildSqls($sql_count,"ip.ip_gateway like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['ipvlanno']){
            $searchcontent = trim($queryparam['ipvlanno']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_vlan_no = '".$searchcontent."'");
            $sql_select = $this->buildSqls($sql_select,"ip.ip_vlan_no like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_vlan_no = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_vlan_no like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['ipbuilding']){
            $searchcontent = trim($queryparam['ipbuilding']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_area like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_area like  '%".$searchcontent."%'");
        }else if ("" != $queryparam['iparea']){
            $searchcontent = trim($queryparam['iparea']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_parea like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_parea like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['ipsecretlevel']){
            $searchcontent = trim($queryparam['ipsecretlevel']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_secret_level = '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_secret_level =  '".$searchcontent."'");
        }
        if ("" != $queryparam['dutydept']){
            $searchcontent = trim($queryparam['dutydept']);
            $sql_select = $this->buildSqls($sql_select,"ip.ip_depart like '%".$searchcontent."%'");
            $sql_count  = $this->buildSqls($sql_count,"ip.ip_depart like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' nulls last';
        } else {
            $sql_select = $sql_select . " order by ip.ip_atplastmodifydatetime desc nulls last";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        $Result   = M('ipaddress')->query($sql_select);
        $Count    = M('ipaddress')->query($sql_count);

        $secInfo  = $this->getSecInfo();
        $areaInfo = $this->getAreaInfo();
        $deptInfo = $this->getDeptInfo();

        foreach($Result as $key=> &$value){
            //拼接密级
            if($value['ip_secret_level'] != ''){
                $tmp = $value['ip_secret_level'];
                $Result[$key]['ip_secret_level'] = $secInfo[$tmp];
            }
            //拼接地区
            if($value['ip_area'] != ''){
                $arr = explode(',',$value['ip_area']);
                foreach($arr as $k=>$v){
                    if(!empty($areaInfo[$v])){
                        if(empty($Result[$key]['ip_areaname'])){
                            $Result[$key]['ip_areaname'] = $areaInfo[$v];
                        }else{
                            $Result[$key]['ip_areaname'] .= '；'.$areaInfo[$v];
                        }
                    }
                }
            }else{
                $Result[$key]['ip_areaname'] = '';
            }
            //拼接部门
            if($value['ip_depart'] != ''){
                $arr = explode(',',$value['ip_depart']);
                foreach($arr as $k=>$v){
                    if(!empty($deptInfo[$v])){
                        if(empty($Result[$key]['ip_deptfullname'])){
                            $Result[$key]['ip_deptfullname'] = $deptInfo[$v]['fullname'];
                        }else{
                            $Result[$key]['ip_deptfullname'] .= '；'.$deptInfo[$v]['fullname'];
                        }
                        if(empty($Result[$key]['ip_deptname'])){
                            $Result[$key]['ip_deptname'] = $deptInfo[$v]['name'];
                        }else{
                            $Result[$key]['ip_deptname'] .= '；'.$deptInfo[$v]['name'];
                        }
                    }
                }
            }else{
                $Result[$key]['ip_deptfullname'] = '';
                $Result[$key]['ip_deptname']     = '';
            }
            if(empty($selecttype)){
                //拼接未/已使用IP数量
                $num  = $this->getBaseNumByID($value['ip_atpid']);
                $ipid = $value['ip_atpid'];
                $Result[$key]['ip_use']    = "<a onclick=loadTo('baseInfouse?ipid=".$ipid."&status=1') target='_self'>".$num['use']."</a>";
                $Result[$key]['ip_notuse'] = "<a onclick=loadTo('baseInfonuse?ipid=".$ipid."&status=0') target='_self'>".$num['notuse']."</a>";
                //拼接总数
                if(!empty($value['ip_start_no']) && !empty($value['ip_end_no'])){
                    $Result[$key]['ip_sum'] = $num['use']+$num['notuse'];
                }else{
                    $Result[$key]['ip_sum'] = '';
                }
            }else{
                //拼接总数
                if(!empty($value['ip_start_no']) && !empty($value['ip_end_no'])){
                    $Result[$key]['ip_sum'] = $value['ip_end_no'] - $value['ip_start_no'] + 1;
                }else{
                    $Result[$key]['ip_sum'] = '';
                }
            }
        }
        return [$Result,$Count];
    }

    /**
     * 查询ip地址是否有重复
     */
    function checkHasIPInfo($ip){
        $map      = [];
        $maps     = [];
        $ip_start = $ip['ip_start'];
        $ip_end   = $ip['ip_end'];
        $map[0][0]['ip_startnum'] = array('elt',$ip_start);
        $map[0][0]['ip_endnum']   = array('egt',$ip_start);
        $map[0][1]['ip_startnum'] = array('elt',$ip_end);
        $map[0][1]['ip_endnum']   = array('egt',$ip_end);
        $map[0]['_logic']         = 'or';
        $map['_logic']            = 'and';

        $maps[0][0][]['ip_startnum']  = array('egt',$ip_start);
        $maps[0][0][]['ip_startnum']  = array('elt',$ip_end);
        $maps[0][0]['_logic']         = 'and';
        $maps[0][1][]['ip_endnum']    = array('egt',$ip_start);
        $maps[0][1][]['ip_endnum']    = array('elt',$ip_end);
        $maps[0][1]['_logic']         = 'and';
        $maps[0]['_logic']            = 'or';
        $map['_logic']            = 'and';

        $res = M('ipaddress')->where($map)->where('ip_isdel is null')->select();
//        $sql = M('ipaddress')->_sql();
//        echo $sql;die;
        if(!empty($res)) return false;
        $res = M('ipaddress')->where($maps)->where('ip_isdel is null')->select();
        if(!empty($res)) return false;
        return true;
    }

    /**
     * IPBase表数据(未使用)
     */
    function getIPBaseInfonuse($queryparam){
        $sql_select="
                select *  from it_ipbase ipb where ipb_ipid = ".$queryparam['ipid']." and (ipb_status != 2 or ipb_status is null) and ipb_isdel is null";
        $sql_count="
                select
                    count(1) c
                from it_ipbase ipb where ipb_ipid = ".$queryparam['ipid']." and (ipb_status != 2 or ipb_status is null) and ipb_isdel is null";
//            print_r($queryparam);die;
        if ('ipb_address' != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by ipb.ipb_addressnum " . $queryparam['sortOrder'] ;
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
        // echo $sql_select;die;
        $Result   = M('ipbase')->query($sql_select);
//                    print_r($Result);die;
        $Count    = M('ipbase')->query($sql_count);

        foreach($Result as $key=>$val){
            if($val['ipb_status'] == ''){
                $Result[$key]['ipb_status'] = '未使用';
            }else if($val['ipb_status'] == '1'){
                $Result[$key]['ipb_status'] = '预分配';
            }
            else if($val['ipb_status'] == '1'){
                $Result[$key]['ipb_status'] = '预分配';
            }
        }
        return [$Result,$Count];
    }

    /*
     * IPBase表数据(已使用)
     */
    function getIPBaseInfouse($queryparam){
        $sql_select="
                select *  from it_ipbase ipb inner join it_terminal tm on ipb.ipb_address = tm.zd_ipaddress where ipb.ipb_ipid = ".$queryparam['ipid']." and ipb.ipb_status is not null and ipb.ipb_isdel is null and tm.zd_atpstatus is null";
        $sql_count="
                select
                    count(1) c
                from it_ipbase ipb inner join it_terminal tm on ipb.ipb_address = tm.zd_ipaddress where ipb.ipb_ipid = ".$queryparam['ipid']." and ipb.ipb_status is not null and ipb.ipb_isdel is null and tm.zd_atpstatus is null";
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by ip.ip_atplastmodifydatetime desc  ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        $Result   = M('ipbase')->query($sql_select);
//                    print_r($Result);die;
        $Count    = M('ipbase')->query($sql_count);

        //ipaddress数据拼接
        $addrResult = M('ipaddress')->where('ip_atpid = '.$queryparam['ipid'])->find();
        //资产类别
        $equipType  = $this->getEquipmentInfo();
//            print_r($equipType);die;
        foreach($Result as $key=>$val){
            $Result[$key]['ip_mask']    = $addrResult['ip_mask'];
            $Result[$key]['ip_gateway'] = $addrResult['ip_gateway'];
            $Result[$key]['ip_valn_no'] = $addrResult['ip_valn_no'];
            if($val['zd_type'] != ''){
                $tmp = $val['zd_type'];
                $Result[$key]['zd_type'] = $equipType[$tmp];
            }
        }
        return [$Result,$Count];
    }

    /*
     * IPBase表查询
     */
    function getSearchData($queryparam){
        $sql_select="
                select *  from it_ipbase ipb inner join it_terminal tm on ipb.ipb_address = tm.zd_ipaddress where  ipb.ipb_status is not null and ipb.ipb_isdel is null and tm.zd_atpstatus is null";
        $sql_count="
                select
                    count(1) c
                from it_ipbase ipb inner join it_terminal tm on ipb.ipb_address = tm.zd_ipaddress where ipb.ipb_status is not null and ipb.ipb_isdel is null and tm.zd_atpstatus is null";
        if ("" != $queryparam['ipstart']){
            $searchcontent = trim($queryparam['ipstart']);
            $searchcontent = $this->IPformat($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"ipb.ipb_addressnum >= '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_addressnum >= '".$searchcontent."'");
//            echo $sql_select;die;
        }
        if ("" != $queryparam['ipend']){
            $searchcontent = trim($queryparam['ipend']);
            $searchcontent = $this->IPformat($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"ipb.ipb_addressnum <= '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_addressnum <= '".$searchcontent."'");
        }
//        if ("" != $queryparam['ipbstatus']){
//            $searchcontent = trim($queryparam['ipbstatus']);
//            if($searchcontent == 3){
//                $sql_select = $this->buildSqls($sql_select,"ipb.ipb_status is null");
//                $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_status is null");
//            }else{
//                $sql_select = $this->buildSqls($sql_select,"ipb.ipb_status = '".$searchcontent."'");
//                $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_status = '".$searchcontent."'");
//            }
//        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by ipb.ipb_atpid asc  ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        $Result   = M('ipbase')->query($sql_select);
//                    print_r($Result);die;
        $Count    = M('ipbase')->query($sql_count);

        //ipaddress数据拼接
        //资产类别
        $equipType  = $this->getEquipmentInfo();
//            print_r($equipType);die;
        foreach($Result as $key=>$val){
            $addrResult = M('ipaddress')->where('ip_atpid = '.$val['ipb_ipid'])->find();
            $Result[$key]['ip_mask']    = $addrResult['ip_mask'];
            $Result[$key]['ip_gateway'] = $addrResult['ip_gateway'];
            $Result[$key]['ip_valn_no'] = $addrResult['ip_valn_no'];
            if($val['zd_type'] != ''){
                $tmp = $val['zd_type'];
                $Result[$key]['zd_type'] = $equipType[$tmp];
            }
        }
        return [$Result,$Count];
    }

    /*
     * IPBase表查询
     */
    function getSearchDatas($queryparam){
        $sql_select="
                select *  from it_ipbase ipb inner join it_ipaddress ip on ipb.ipb_ipid = ip.ip_atpid where ipb.ipb_isdel is null";
        $sql_count="
                select
                    count(1) c
                from it_ipbase ipb where ipb.ipb_isdel is null";
        if ("" != $queryparam['ipstart']){
            $searchcontent = trim($queryparam['ipstart']);
            $searchcontent = $this->IPformat($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"ipb.ipb_addressnum >= '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_addressnum >= '".$searchcontent."'");
//            echo $sql_select;die;
        }
        if ("" != $queryparam['ipend']){
            $searchcontent = trim($queryparam['ipend']);
            $searchcontent = $this->IPformat($searchcontent);
            $sql_select = $this->buildSqls($sql_select,"ipb.ipb_addressnum <= '".$searchcontent."'");
            $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_addressnum <= '".$searchcontent."'");
        }
        if ("" != $queryparam['ipbstatus']){
            $searchcontent = trim($queryparam['ipbstatus']);
            if($searchcontent == 3){
                $sql_select = $this->buildSqls($sql_select,"ipb.ipb_status is null");
                $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_status is null");
            }else{
                $sql_select = $this->buildSqls($sql_select,"ipb.ipb_status = '".$searchcontent."'");
                $sql_count  = $this->buildSqls($sql_count,"ipb.ipb_status = '".$searchcontent."'");
            }
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by ipb.ipb_atpid asc  ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_select;die;
        $Result   = M('ipbase')->query($sql_select);
//                    print_r($Result);die;
        $Count    = M('ipbase')->query($sql_count);

        return [$Result,$Count];
    }

    /*
     * 插入数据
     */
    function insertData($data){
        if(isset($data['ip_secret_name'])) unset($data['ip_secret_name']);
        if(isset($data['ip_areaname'])) unset($data['ip_areaname']);
        if(isset($data['ip_departname'])) unset($data['ip_departname']);
        $ip_start    = $data['ip_start'];
        $ip_end      = $data['ip_end'];
        $ip_startnum = $this->IPformat($ip_start);
        $ip_endnum   = $this->IPformat($ip_end);
        $tmp0        = explode('.',$ip_start);
        $tmp1        = explode('.',$ip_end);
        //拼接地区全称
        $ip_area     = explode(',',$data['ip_area']);
        $ip_pareaarr = [];
        $ip_parea    = '';
        if(!empty($ip_area)){
            $areaInfo = D('Dictionary')->getAreaAllPInfo();
            foreach($ip_area as $k=>$v){
                if($areaInfo[$v] != '141159'){
                    $ip_pareaarr[] = $areaInfo[$v];
                }else{
                    $ip_pareaarr[] = $v;
                }
            }
            $ip_pareaarr = array_unique($ip_pareaarr);
            $ip_parea    = implode(',',$ip_pareaarr);
        }
        $data['ip_parea'] = $ip_parea;

        if($tmp0[2] == $tmp1[2]){
            try{
                M('ipbase')->startTrans();
                M('ipaddress')->add($data);
                $resid = M('ipaddress')->field('MAX(ip_atpid)')->find();
                $database['ipb_ipid']              = $resid['max'];
                $database['ipb_atpcreateuser']     = $data['ip_atpcreateuser'];
                $database['ipb_atpcreatedatetime'] = $data['ip_atpcreatedatetime'];
                //ipbase表添加数据
                for($i=$tmp0[3];$i<=$tmp1[3];$i++){
                    $database['ipb_address'] = $tmp0[0].'.'.$tmp0[1].'.'.$tmp0[2].'.'.$i;
                    $database['ipb_addressnum'] = $this->IPformat($database['ipb_address']);
                    M('ipbase')->add($database);
                }
                $res = M('ipbase')->where("ipb_address in ('$ip_start','$ip_end') and ipb_ipid = ".$resid['max']." and ipb_isdel is null")->field('ipb_atpid')->select();
                if(empty($res)){
                    M('ipbase')->rollback();
                    return false;
                }else{
                    //ipbase表回填ipaddress表ip_start_no，ip_end_no
                    if(count($res) == 1) $res[1]['ipb_atpid'] = $res[0]['ipb_atpid'];
                    if($res[1]['ipb_atpid'] >= $res[0]['ipb_atpid']){
                        $dataip['ip_start_no'] = $res[0]['ipb_atpid'];
                        $dataip['ip_end_no']   = $res[1]['ipb_atpid'];
                    }else{
                        $dataip['ip_start_no'] = $res[1]['ipb_atpid'];
                        $dataip['ip_end_no']   = $res[0]['ipb_atpid'];
                    }
                    M('ipaddress')->where("ip_atpid = '".$resid['max']."'")->save($dataip);
                    //ipbase表数据更改
                    $statusInfo = M('ipbase')->field('ipb_addressnum,max(ipb_status) ipb_status')->where("ipb_addressnum between '".$ip_startnum."' and '".$ip_endnum."' and ipb_status is not null")->group('ipb_addressnum')->select();
                    if(!empty($statusInfo)){
                        foreach($statusInfo as $k=>$v){
                            M('ipbase')->where("ipb_addressnum = '".$k['ipb_addressnum']."'")->save($v);
                        }
                    }
                }
                M('ipbase')->commit();
                return $resid['max'];
            }
            catch(\Exception $e)
            {
                echo $e;
                M('ipbase')->rollback();
                return false;
            }
        }else{
            try{
                M('ipbase')->startTrans();
                M('ipaddress')->add($data);
                $resid = M('ipaddress')->field('MAX(ip_atpid)')->find();
                $database['ipb_ipid']              = $resid['max'];
                $database['ipb_atpcreateuser']     = $data['ip_atpcreateuser'];
                $database['ipb_atpcreatedatetime'] = $data['ip_atpcreatedatetime'];

                for($i=$tmp0[2];$i<=$tmp1[2];$i++){
                    if($i == $tmp0[2]){
                        for($j=$tmp0[3];$j<=255;$j++){
                            $database['ipb_address'] = $tmp0[0].'.'.$tmp0[1].'.'.$i.'.'.$j;
                            $database['ipb_addressnum'] = $this->IPformat($database['ipb_address']);
                            M('ipbase')->add($database);
                        }
                    }else if($i == $tmp1[2]){
                        for($k=0;$k<=$tmp1[3];$k++){
                            $database['ipb_address'] = $tmp0[0].'.'.$tmp0[1].'.'.$i.'.'.$k;
                            $database['ipb_addressnum'] = $this->IPformat($database['ipb_address']);
                            M('ipbase')->add($database);
                        }
                    }else{
                        for($l=0;$l<=255;$l++){
                            $database['ipb_address'] = $tmp0[0].'.'.$tmp0[1].'.'.$i.'.'.$l;
                            $database['ipb_addressnum'] = $this->IPformat($database['ipb_address']);
                            M('ipbase')->add($database);
                        }
                    }
                }
                $res = M('ipbase')->where("ipb_address in ('$ip_start','$ip_end') and ipb_ipid = ".$resid['max']." and ipb_isdel is null")->field('ipb_atpid')->select();
                if(empty($res)){
                    M('ipbase')->rollback();
                    return false;
                }else{
                    if($res[1]['ipb_atpid'] >= $res[0]['ipb_atpid']){
                        $dataip['ip_start_no'] = $res[0]['ipb_atpid'];
                        $dataip['ip_end_no']   = $res[1]['ipb_atpid'];
                    }else{
                        $dataip['ip_start_no'] = $res[1]['ipb_atpid'];
                        $dataip['ip_end_no']   = $res[0]['ipb_atpid'];
                    }
                    M('ipaddress')->where("ip_atpid = '".$resid['max']."'")->save($dataip);
                }
                //ipbase表数据更改
                $statusInfo = M('ipbase')->field('ipb_addressnum,max(ipb_status) ipb_status')->where("ipb_addressnum between '".$ip_startnum."' and '".$ip_endnum."' and ipb_status is not null")->group('ipb_addressnum')->select();
                if(!empty($statusInfo)){
                    foreach($statusInfo as $k=>$v){
                        M('ipbase')->where("ipb_addressnum = '".$k['ipb_addressnum']."'")->save($v);
                    }
                }
                M('ipbase')->commit();
                return $resid['max'];
            }
            catch(\Exception $e)
            {
                echo $e;
                M('ipbase')->rollback();
                return false;
            }
        }
    }

    /**
     * 修改数据
     */
    function editData($data){
        if(empty($data)) return false;
        $diff1 = [];
        $diff2 = [];
        //新数据
        $diff2['ip_mask']         = $data['ip_mask'];
        $diff2['ip_gateway']      = $data['ip_gateway'];
        $diff2['ip_vlan_no']      = $data['ip_vlan_no'];
        $diff2['ip_secret_level'] = $data['ip_secret_level'];
        $diff2['ip_area']         = $data['ip_area'];
        $diff2['ip_depart']       = $data['ip_depart'];
        $diff2['ip_purpose']      = $data['ip_purpose'];
        $res = M('ipaddress')->where('ip_atpid = '.$data['ip_atpid'])->find();
        if(empty($res)) return false;
        //原数据
        $diff1['ip_mask']         = $res['ip_mask'];
        $diff1['ip_gateway']      = $res['ip_gateway'];
        $diff1['ip_vlan_no']      = $res['ip_vlan_no'];
        $diff1['ip_secret_level'] = $res['ip_secret_level'];
        $diff1['ip_area']         = $res['ip_area'];
        $diff1['ip_depart']       = $res['ip_depart'];
        $diff1['ip_purpose']      = $res['ip_purpose'];
        $diff = array_diff($diff1,$diff2);
        if(empty($diff)) return 'error';
        M('ipaddress')->where('ip_atpid = '.$data['ip_atpid'])->save($data);
        return [$diff1,$diff2];
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
     * 获取密级信息
     */
    function getSecInfo(){
        $seclevel = D('Dictionary')->getSecretInfo();
        $secInfo  = [];
        foreach($seclevel as $key=>$val){
            $secInfo[$val['d_sortno']] = $val['d_dictname'];
        }
        return $secInfo;
    }

    /**
     * 获取地区信息
     */
    function getAreaInfo(){
        $area     = D('Dictionary')->getAreaAllInfo();
        $areaInfo = [];
        foreach($area as $key=>$val){
            $areaInfo[$val['d_atpid']] = $val['d_dictname'];
        }
        return $areaInfo;
    }

    /**
     * 获取部门信息
     */
    function getDeptInfo(){
        $dept = D('Depart')->getDeptInfo();
        $deptInfo = [];
        foreach($dept as $key=>$val){
            $deptInfo[$val['id']]['fullname'] = $val['fullname'];
            $deptInfo[$val['id']]['name']     = $val['name'];
        }
        return $deptInfo;
    }

    /**
     * 获取资产类别信息
     */
    function getEquipmentInfo(){
        $equip = D('Dictionary')->getEquipmentTypeInfo();
        $equipInfo = [];
        foreach($equip as $key=>$val){
            $equipInfo[$val['d_atpid']] = $val['d_dictname'];
        }
        return $equipInfo;
    }

    /**
     * 获取某条数据的ipbase表已使用和未使用数据条数
     */
    function getBaseNumByID($id){
        $total  = M('ipbase')->where("ipb_ipid = '$id'")->field('count(1) c')->find();
        $use    = M('ipbase i')->join('it_terminal t on i.ipb_address = t.zd_ipaddress')->where("ipb_ipid = '$id' and ipb_status is not null and zd_atpstatus is null")->field('count(1) use')->find();
        $notuse = [];
        $notuse['notuse'] = $total['c']-$use['use'];
        return array_merge($notuse,$use);
    }

    /**
     * 根据ipb_atpid获取ipbase详细数据
     */
    function getBaseInfoById($ipbid){
        $baseInfo  = M('ipbase')->where("ipb_atpid =".$ipbid)->find();
        $ipid      = $baseInfo['ipb_ipid'];
        $baseInfos = M('ipaddress')->where("ip_atpid =".$ipid)->find();
        return array_merge($baseInfo,$baseInfos);
    }

    /**
     * 根据ipb_atpid修改ipbase使用状态
     */
    function changeBaseStatus($params){
        $ipbid  = $params['ipbid'];
        $status = empty($params['status'])?null:'1';
        if(!$ipbid) return false;
        $ipInfo = M('ipbase')->where('ipb_atpid = '.$ipbid)->find();
        if($ipInfo['ipb_status'] == $status) return 'error';
        $ipb_address = $ipInfo['ipb_address'];
        $atpids = M('ipbase')->where("ipb_address = '$ipb_address'")->field('ipb_atpid')->select();
        $atpids = array_values($atpids);
        $arr['ipb_status']                = $status;
        $arr['ipb_atplastmodifyuser']     = I('session.username');
        $arr['ipb_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
        $res = M('ipbase')->where('ipb_addressnum = '.$ipInfo['ipb_addressnum'])->setField($arr);
        if($res){
            return [$atpids,$ipb_address];
        }else{
            return false;
        }
    }

    /**
     * 根据ipb_address修改ipbase使用状态
     */
    function changeBaseStatusByIp($params){
        $ipb_address  = trim($params['ipb_address']);
        $status = empty($params['status'])?null:'2';
        if(!$ipb_address) return false;
        $atpids = M('ipbase')->where("ipb_address = '$ipb_address'")->field('ipb_atpid')->select();
        $atpids = array_values($atpids);
        $arr['ipb_status']                = $status;
        $arr['ipb_atplastmodifyuser']     = I('session.username');
        $arr['ipb_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
        $res = M('ipbase')->where("ipb_address = '".$ipb_address."'")->setField($arr);
        if($res){
            if($res == 'error'){
                return false;
            }else if($res) {
                //记录日志
                foreach ($atpids as $key => $val) {
                    if($status == ''){
                        $this->recordLog("update", 'IP', "删除资产：IP地址" . $ipb_address . "；状态修改：已使用-未使用", 'ipbase', $val['ipb_atpid']);
                    }else{
                        $this->recordLog("update", 'IP', "资产变更：IP地址" . $ipb_address . "；状态修改：未使用-已使用", 'ipbase', $val['ipb_atpid']);
                    }
                }
                return true;
            }
        }else{
            return false;
        }
    }

    /**
     * ip地址格式化为数值
     */
    function IPformat($ip){
        if(empty($ip)) return false;
        $ip = trim($ip);
        $data = explode('.',$ip);
        $res  = $data[0]*256*256*256+$data[1]*256*256+$data[2]*256+$data[3];
        return $res;
    }

    /*
     * 删除ipaddress数据
     */
    function deleteAddressData($ids){
        set_time_limit(0);
        if(empty($ids)) return false;
        $res = M('ipaddress')->where('ip_atpid = '.$ids)->find();
//        print_r($res);die;
        if(empty($res)) return false;
        $logData                             = [];
        $logData['ip_start']                 = $res['ip_start'];
        $logData['ip_end']                   = $res['ip_end'];
        $idsDel                              = explode(',',$ids);
        $arrip['ip_isdel']                   = 'del';
        $arrip['ip_atplastmodifyuser']       = I('session.username');
        $arrip['ip_atplastmodifydatetime']   = date('Y-m-d H:i:s',time());
        $arripb['ipb_isdel']                 = 'del';
        $arripb['ipb_atplastmodifyuser']     = I('session.username');
        $arripb['ipb_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
        foreach($idsDel as $key=>$val){
            try{
                M('ipbase')->startTrans();
                $res = M('ipaddress')->where('ip_atpid ='.$val)->find();
                if($res['ip_isdel'] == 'del'){
                    continue;
                }else{
                    M('ipaddress')->where("ip_atpid =".$val)->setField($arrip);
//                    $this->recordLog("delete",'IP',"IP地址段：".$res['ip_start']."-".$res['ip_end']."；",'ipaddress',$res['ipb_atpid']);
                    $ip_start_no = $res['ip_startnum'];
                    $ip_end_no   = $res['ip_endnum'];
                    // for($i = $ip_start_no;$i<=$ip_end_no;$i++){
                        M('ipbase')->where("ipb_ipid =".$val)->setField($arripb);
//                        $chengeData = M('ipbase')->where("ipb_addressnum =".$i)->select();
//                        foreach($chengeData as $k=>$v){
//                            $this->recordLog("delete",'IP',"IP地址：".$v['ipb_address']."；",'ipbase',$v['ipb_atpid']);
//                        }
                    // }
                }
                M('ipbase')->commit();
                return $logData;
            }
            catch(\Exception $e)
            {
                echo $e;
                M('ipbase')->rollback();
                return false;
            }
        }
    }

    /*
     * 根据ip_atpid获取ipaddress详细数据
     */
    function getAddressInfoById($ipid){
        $baseInfo = M('ipaddress')->where("ip_atpid =".$ipid)->find();
        return $baseInfo;
    }

    function getSelect($bumen,$iparea,$ipbuilding,$password){

        $sql="select ipb_address,ipb_status from IT_IPADDRESS ip inner join it_ipbase ipb on ipb.ipb_ipid = ip.ip_atpid where ip_area='"."$ipbuilding"."' and ip_parea='"."$iparea"."' and ip_secret_level='"."$password"."' and ipb_status is null ORDER BY rand() limit 1";
        $list=M('it_ipaddress')->query($sql);
        $ip=$list[0]['ipb_address'];
        return $ip;
    }

    function updates($list){
         $sql_up="update IT_IPBASE set ipb_status=1 where ipb_address='"."$list"."'";
        $lists=M('ipbase')->execute($sql_up);
        return lists;
    }

    function getStatus($ip){
        $sql="select ipb_status from it_ipbase where ipb_address='"."$ip"."'";
        $res=M('ipbase')->query($sql);
        return $res;
    }

    function getipAdd($ipaddress){
        $sql="select ipb_address from it_ipbase where ipb_address='"."$ipaddress"."'";
        $res=M('ipbase')->query($sql);
        return $res;
    }

    function getAtpid($number){
        $sql_num="select ip_atpid from it_ipaddress where ip_startnum <= $number and $number <= ip_endnum";
        $list=M('ipaddress')->query($sql_num);
        return $list;
    }





}