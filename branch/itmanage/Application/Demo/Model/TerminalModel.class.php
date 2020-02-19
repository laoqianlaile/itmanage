<?php
namespace Demo\Model;
use Think\Model;
class TerminalModel extends BaseModel {
    protected $_auto = Array(
        ["zd_atpid","makeGuid",1,"function"],
        ["zd_atpcreateuser","getUserId",1,"function"],
        ["zd_atpcreatetime","getDatetime",1,"function"],
        ["zd_atplastmodifyuser","getUserId",2,"function"],
        ["zd_atplastmodifytime","getDatetime",2,"function"]
    );
    /**
     * 获取zd_dutyman,zd_name信息
     */
    function zdUserName(){
        $tmnInfo  = M('terminal')->field('zd_dutyman user,zd_name name')->where("zd_name is not null and zd_name != '-' and zd_atpstatus is null")->select();
        $diffInfo = [];
        foreach($tmnInfo as $key=>$val){
            if(!isset($diffInfo[$val['user']])){
                $diffInfo[$val['user']] = strtoupper($val['name']);
            }else{
                $diffInfo[$val['user']] .= ','.strtoupper($val['name']);
            }
        }
        return $diffInfo;
    }

    function UsbKeyName(){
        $tmnInfo  = M('usbkey')->field('u_account user,u_code name')->select();
        $diffInfo = [];
        foreach($tmnInfo as $key=>$val){
            if(!isset($diffInfo[$val['user']])){
                $diffInfo[$val['user']] = strtoupper($val['name']);
            }else{
                $diffInfo[$val['user']] .= ','.strtoupper($val['name']);
            }
        }
        return $diffInfo;
    }

    /**
     * 获取所有资产信息(责任人不为空)
     */
    function getAllTmnInfo(){
//        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $TmnInfo = S('TmnInfo');
        if(empty($TmnInfo)){
            $TmnInfo = M('terminal')->where('zd_dutyman is not null and zd_atpstatus is null')->field('zd_type,zd_name,zd_devicecode,zd_macaddress,zd_ipaddress,zd_area,zd_belongfloor,zd_roomno,zd_dutyman,zd_dutydeptid,zd_dutydeptname,zd_memo,zd_useman,zd_usedeptname,zd_usedeptid')->select();
            $dictInfo = D('Dictionary')->getAllDictionaryInfo();
            $deptInfo = D('Depart')->getAllFullName();
            foreach($TmnInfo as $key=>$val){
                if(!empty($val['zd_type'])){
                    $TmnInfo[$key]['zd_typename'] = $dictInfo[$val['zd_type']];
                }
                if(!empty($val['zd_dutydeptid'])){
                    $TmnInfo[$key]['zd_dutydeptfullname'] = $deptInfo[$val['zd_dutydeptid']];
                }
                if(!empty($val['zd_usedeptid'])){
                    $TmnInfo[$key]['zd_usedeptfullname'] = $deptInfo[$val['zd_dutydeptid']];
                }
                if(!empty($val['zd_area'])){
                    $TmnInfo[$key]['zd_areaname'] = $dictInfo[$val['zd_area']];
                }
                if(!empty($val['zd_belongfloor'])){
                    $TmnInfo[$key]['zd_belongfloorname'] = $dictInfo[$val['zd_belongfloor']];
                }
            }
            S('TmnInfo',$TmnInfo,3600);
        }
        return $TmnInfo;
    }

    /**
     * 责任人部门处室差异表数据
     */
    function dutyDeptDiffData($queryparam){
        //所有资产信息(责任人不为空)
        $TmnInfo = $this->getAllTmnInfo();
        //域控信息
        $adInfo  = D('Adinfo')->getAdUserInfo();
        $diffData = [];
        foreach($TmnInfo as $key=>$val){
            if(!empty($val['zd_dutydeptfullname'])){
                $fullname = trim($val['zd_dutydeptfullname']);
            }else if(!empty($val['zd_dutydeptname'])){
                $fullname = trim($val['zd_dutydeptname']);
            }else{
                $fullname = '';
            }
            if(!empty($adInfo[$val['zd_dutyman']]) && !empty($fullname)){
                $dept   = $adInfo[$val['zd_dutyman']]['ad_dept'];
                $office = $adInfo[$val['zd_dutyman']]['ad_office'];
                $sign1  = strpos($fullname,$dept);
                $sign2  = strpos($fullname,$office);
                if(($sign1 === false) || ($sign2 === false)){
                    if(!empty($queryparam['user'])){
                        $user = trim($queryparam['user']);
                        if($user != $val['zd_dutyman']) continue;
                    }
                    if(!empty($queryparam['ip'])){
                        $ip  = trim($queryparam['ip']);
                        $tmp = strpos($val['zd_ipaddress'],$ip);
                        if($tmp === false) continue;
                    }
                    $diffData[$key]['type']      = $val['zd_typename'];
                    $diffData[$key]['name']      = $val['zd_name'];
                    $diffData[$key]['code']      = $val['zd_devicecode'];
                    $diffData[$key]['mac']       = $val['zd_macaddress'];
                    $diffData[$key]['ip']        = $val['zd_ipaddress'];
                    $diffData[$key]['area']      = $val['zd_areaname'];
                    $diffData[$key]['floor']     = $val['zd_belongfloorname'];
                    $diffData[$key]['room']      = $val['zd_roomno'];
                    $diffData[$key]['user']      = $val['zd_dutyman'];
                    $diffData[$key]['zd_dept']   = $fullname;
                    $diffData[$key]['ad_dept']   = $dept;
                    $diffData[$key]['ad_office'] = $office;
                    $diffData[$key]['demo']      = $val['zd_memo'];
                }
            }
        }
        return $diffData;
    }

    /**
     * 使用人部门信息差异表
     */
    function userDeptDiffData($queryparam){
        //所有资产信息(责任人不为空)
        $TmnInfo = $this->getAllTmnInfo();
        //域控信息
        $adInfo  = D('Adinfo')->getAdUserInfo();
        $diffData = [];
        foreach($TmnInfo as $key=>$val){
            if(!empty($val['zd_usedeptfullname'])){
                $fullname = trim($val['zd_usedeptfullname']);
            }else if(!empty($val['zd_usedeptname'])){
                $fullname = trim($val['zd_usedeptname']);
            }else{
                $fullname = '';
            }
            if(!empty($adInfo[$val['zd_useman']])){
                $dept   = $adInfo[$val['zd_useman']]['ad_dept'];
                $office = $adInfo[$val['zd_useman']]['ad_office'];
                $sign1  = strpos($fullname,$dept);
                $sign2  = strpos($fullname,$office);

                if(($sign1 === false) || ($sign2 === false) || empty($fullname)){
                    if(!empty($queryparam['user'])){
                        $user = trim($queryparam['user']);
                        if($user != $val['zd_useman']) continue;
                    }
                    if(!empty($queryparam['name'])){
                        $ip  = trim($queryparam['name']);
                        $tmp = strpos($val['zd_name'],$ip);
                        if($tmp === false) continue;
                    }
                    $diffData[$key]['type']      = $val['zd_typename'];
                    $diffData[$key]['name']      = $val['zd_name'];
//                    $diffData[$key]['code']      = $val['zd_devicecode'];
                    $diffData[$key]['mac']       = $val['zd_macaddress'];
                    $diffData[$key]['ip']        = $val['zd_ipaddress'];
//                    $diffData[$key]['area']      = $val['zd_areaname'];
//                    $diffData[$key]['floor']     = $val['zd_belongfloorname'];
//                    $diffData[$key]['room']      = $val['zd_roomno'];
                    $diffData[$key]['user']      = $val['zd_dutyman'];
                    $diffData[$key]['zd_dept']   = $fullname;
                    $diffData[$key]['ad_dept']   = $dept;
                    $diffData[$key]['ad_office'] = $office;
//                    $diffData[$key]['demo']      = $val['zd_memo'];

                }
            }
        }
        return $diffData;
    }

    /**
     * 获取所有资产信息(责任人不为空)
     *
     */
    function getTmnInfo($queryparam){
//        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $cachename = md5(json_encode($queryparam));
        $TmnInfo = S($cachename);
        if(empty($TmnInfo)){
            $Model = M();
            $sql_select="
                select * from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype' and
                 d.d_dictype='terminal'";
            $sql_select = $sql_select." and du.zd_atpstatus is null";
            if($queryparam['mac_address'] != ''){
                $sql_select = $sql_select." and du.zd_macaddress in (".$queryparam['mac_address'].")";
            }

            if ("" != $queryparam['ipaddess']){
                $searchcontent = trim($queryparam['ipaddess']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_ipaddress like '%".$searchcontent."%'");
            }
            if ("" != $queryparam['macaddress']){
                $searchcontent = trim($queryparam['macaddress']);
                $searchcontent = strtoupper($searchcontent);
                $sql_select = $this->buildSqls($sql_select,"du.zd_macaddress like '%".$searchcontent."%'");
            }
            if ("" != $queryparam['sbtype']){
                $searchcontent = trim($queryparam['sbtype']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_type ='".$searchcontent."'");
            }
            if ("" != $queryparam['factory']){
                $searchcontent = trim($queryparam['factory']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_factoryname ='".$searchcontent."'");
            }
            if ("" != $queryparam['model']){
                $searchcontent = trim($queryparam['model']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_modelnumber ='".$searchcontent."'");
            }
            if ("" != $queryparam['area']){
                $searchcontent = trim($queryparam['area']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_area ='".$searchcontent."'");
            }
            if (("" != $queryparam['building']) && ($queryparam['building'] != 'null')){
                $searchcontent = trim($queryparam['building']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_belongfloor ='".$searchcontent."'");
            }
            if ("" != $queryparam['userman']){
                $searchcontent = trim($queryparam['userman']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_useman ='".$searchcontent."'");
            }
            if ("" != $queryparam['dutydept']){
                $searchcontent = trim($queryparam['dutydept']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_dutydeptid ='".$searchcontent."'");
            }
            if ("" != $queryparam['isavailable']){
                $searchcontent = trim($queryparam['isavailable']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_status ='".$searchcontent."'");
            }
            if ("" != $queryparam['terminalname']){
                $searchcontent = trim($queryparam['terminalname']);
                $sql_select = $this->buildSqls($sql_select,"du.zd_name like '%".$searchcontent."%'");
            }
            if (null != $queryparam['sort']) {
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
            } else {
                $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
            }
//            echo $sql_select;die;
            $TmnInfo = $Model->query($sql_select);
            $dictInfo = D('Dictionary')->getAllDictionaryInfo();
            $deptInfo = D('Depart')->getAllFullName();
            foreach($TmnInfo as $key=> $val){
                if(!empty($val['zd_type'])){
                    $TmnInfo[$key]['zd_typename'] = $dictInfo[$val['zd_type']];
                }
                if(!empty($val['zd_dutydeptid'])){
                    $TmnInfo[$key]['zd_dutydeptfullname'] = $deptInfo[$val['zd_dutydeptid']];
                }
                if(!empty($val['zd_area'])){
                    $TmnInfo[$key]['zd_areaname'] = $dictInfo[$val['zd_area']];
                }
                if(!empty($val['zd_belongfloor'])){
                    $TmnInfo[$key]['zd_belongfloorname'] = $dictInfo[$val['zd_belongfloor']];
                }
            }
            S($cachename,$TmnInfo,3600);
        }
        return $TmnInfo;
    }

    /**
     * 限定差异数据
     */
    function getPortZeroId(){
        $model = M();
        $sql   = "select upper(zd_macaddress) mac from it_terminal t where not exists (select upper(sw_macaddress) from it_switchnewinfo_t s where upper(t.zd_macaddress) = upper(s.sw_macaddress)) and zd_atpstatus is null";// and zd_macaddress not like '%-%'
        $res   = $model->query($sql);
        $macaddresses = '';
        if(!empty($res)){
            $macaddress  = [];
            foreach($res as $zdmac){
                $mactmp = trim($zdmac['mac']);
                if(!empty($mactmp)) $macaddress[] = $mactmp;// && (strpos($mactmp,'-') === false)
            }
            $macaddresses = implode("','",$macaddress);
            $macaddresses = "'".$macaddresses."'";
        }
        return $macaddresses;
    }

    /**
     * 交换机端口接入数量为0表
     */
    function portInZeroData($queryparam){
        $macaddresses = $this->getPortZeroId($queryparam);
        if(!empty($macaddresses)){
            $queryparam['mac_address'] = $macaddresses;
        }

        //所有资产信息(责任人不为空)
        $TmnInfo = $this->getTmnInfo($queryparam);
        return $TmnInfo;
    }

    /**
     * 限定绑定MAC数量大于1数据
     */
    function getPortOneId(){
        $model = M();
        $sql   = "SELECT sw_macaddress,count(*) c FROM it_switchnewinfo_t where sw_macaddress is not null group by sw_macaddress having count(*) >1";
        $res   = $model->query($sql);
        $macaddresses = '';
        if(!empty($res)){
            $macaddress  = [];
            foreach($res as $zdmac){
                $mactmp = strtoupper(trim($zdmac['sw_macaddress']));
                if(!empty($mactmp)) $macaddress[] = $mactmp;// && (strpos($mactmp,'-') === false)
            }
            $macaddresses = implode("','",$macaddress);
            $macaddresses = "'".$macaddresses."'";
        }
        return $macaddresses;
    }


    /**
     * 交换机端口接入数量大于1表
     */
    function portInOneData($queryparam){
        $macaddresses = $this->getPortOneId($queryparam);
//        echo $macaddresses;die;
        if(!empty($macaddresses)){
            $queryparam['mac_address'] = $macaddresses;
        }

        //所有资产信息(责任人不为空)
        $TmnInfo = $this->getTmnInfo($queryparam);
        return $TmnInfo;
    }


}