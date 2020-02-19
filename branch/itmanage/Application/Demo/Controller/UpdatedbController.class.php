<?php
namespace Demo\Controller;
use Think\Controller;
class UpdatedbController extends BaseController
{
    function test(){
        $this->assign('rules','(parseFloat(option1)+parseFloat(option2))*parseFloat(option3)');
        $this->display();
    }

    function modelEdit1(){
        $columns = [
            [
                'field'   => 'option1',
                'title'   => '参数1',
                'width'   => 40,
                'editable'=> [
                    'type'     => 'datetime',
                    'title'    => '参数1',
                    'mode'     => 'inline',
                    'emptytext'=> '0',
                    'validate' => 'number'
                ]
            ],
            [
                'field'   => 'option2',
                'title'   => '参数2',
                'width'   => 40,
                'editable'=> [
                    'type'     => 'text',
                    'title'    => '参数2',
                    'mode'     => 'inline',
                    'emptytext'=> '0',
                    'validate' => 'number'
                ]
            ],
            [
                'field'   => 'option3',
                'title'   => '参数3',
                'width'   => 40,
                'emptytext'=> '0',
                'editable'=> [
                    'type'     => 'text',
                    'title'    => '参数3',
                    'mode'     => 'inline',
                    'validate' => "number"
                ]
            ],
            [
                'field'  => 'sum',
                'title'  => '计算结果',
                'width'  => 40,
                'align'    => 'center',
                'editable' => false
            ],
            [
                'field'    => 'swbat_atpids',
                'title'    => '操作',
                'width'    => 60,
                'align'    => 'center',
                'editable' => false,
                'formatter' => 'delRow'
            ]
        ];
        $this->assign('rules','(parseFloat(option1)+parseFloat(option2))*parseFloat(option3)');
        $this->assign('columns',json_encode($columns));
        $this->display();
    }
    function getOldIPData(){
        $ipInfo = S('oldIpData');
        if(empty($ipInfo)){
            $model     = M()->db(1,'ORACLE_CONFIG');
//        $model = M('view','iptwf_ip_manage_','ORACLE_CONFIG');
            $sql = "select main_id,ip_start,ip_end,ip_mask,ip_gateway,vlan_no,secret_level,to_nchar(area) as area,to_nchar(room) as room,usage,to_char(create_time,'yyyy-mm-dd hh24:mi:ss')  as create_time,to_char(modify_time,'yyyy-mm-dd hh24:mi:ss')  as modify_time from brightsm6.iptwf_ip_manage_view where main_id =3290";
            $ipInfo    = $model->query($sql);
            S('oldIpData',$ipInfo,3600*24);
        }
        return $ipInfo;
    }
    /**
     * 导入IP地址数据
     */
    function inputIPInfo(){
        header('content-type:text/html;charset=utf-8');
        $newipinfo = S('newipinfo');
        if(empty($newipinfo)){
            $ipInfo    = $this->getOldIPData();
            $newipinfo = [];
            $areano    = [];
            $roomno    = [];
            foreach($ipInfo as $key=>$val){
                $newipinfo[$key]['ip_start']                 = $val['ip_start'];
                $newipinfo[$key]['ip_end']                   = $val['ip_end'];
                $newipinfo[$key]['ip_mask']                  = $val['ip_mask'];
                $newipinfo[$key]['ip_gateway']               = $val['ip_gateway'];
                $newipinfo[$key]['ip_atpcreatedatetime']     = $val['create_time'];
//            $newipinfo[$key]['ip_atpcreatuser']          = $val['create_time'];
                $newipinfo[$key]['ip_atplastmodifydatetime'] = $val['modify_time'];
                $newipinfo[$key]['ip_vlan_no']               = ($val['vlan_no'] == 'VLAN997')?997:$val['vlan_no'];
                $newipinfo[$key]['ip_secret_level']          = $val['secret_level'];
                $newipinfo[$key]['ip_olddepart']             = '';
                $newipinfo[$key]['ip_oldarea']               = '';
                $newipinfo[$key]['ip_oldmainid']             = $val['main_id'];
                $room                                        = $val['room'];
                //处理部门
                if($room == ''){
                    $newipinfo[$key]['ip_depart'] = '';
                }else{
                    $room = trim($room,';');
                    $strroom = '';
                    $arrroom = [];
                    $ip_room = explode(';',$room);
                    foreach($ip_room as $k=>$v){
                        if(!empty($v)){
                            $tmp = strpos($v,'-');
                            if($tmp){
                                $tmp  = explode('-',$v);
                                $tmp  = array_reverse($tmp);
                                $name = implode('-',$tmp);
                                $id = D('Depart')->getDeptIdByFullname($name);
                            }else{
                                $id = D('Depart')->getDeptIdByName($v);
                            }
                            if($id != ''){
                                $arrroom[] = $id;
//                            if(empty($strroom)){
//                                $strroom = $id;
//                            }else{
//                                $strroom .= ','.$id;
//                            }
                            }else{
                                $tmproom = trim($v);
                                switch($tmproom){
                                    case '总体部-总体部_领导':
                                        $arrroom[] = '391143117E0A2D2CE054D4C9EF06663E';break;
                                    case '总体部-电子工程技术研究室':
                                        $arrroom[] = '391143117E792D2CE054D4C9EF06663E';break;
                                    case '总体部-通信工程技术研究室':
                                        $arrroom[] = '391143117E792D2CE054D4C9EF06663E';break;
                                    case '总体部-试验保障室':
                                        $arrroom[] = '44C875DB2F505BC8E054D4C9EF06663E';break;
                                    case '总体部-空间科学与深空探测总体研究室':
                                        $arrroom[] = '391143117FA62D2CE054D4C9EF06663E';break;
                                    case '总体部-科研生产处':
                                        $arrroom[] = '391143117F8A2D2CE054D4C9EF06663E';break;
                                    case '动力行政保障部-综合办公室':
                                        $arrroom[] = '3911431181CC2D2CE054D4C9EF06663E';break;
                                    case '总体部-导航卫星总体研究室':
                                        $arrroom[] = '391143117E622D2CE054D4C9EF06663E';break;
                                    case '综合管理层-唐家岭建设指挥部':
                                        $arrroom[] = '391143117E9F2D2CE054D4C9EF06663E';break;
                                    case '载人航天总体部-载人航天总体部_领导':
                                        $arrroom[] = '391143117E092D2CE054D4C9EF06663E';break;
                                    case '天津基地管理委员会-天津基地管理委员会_领导':
                                        $arrroom[] = '39114311807B2D2CE054D4C9EF06663E';break;
                                    case '总体部-空间机械臂系统研究所':
                                        $arrroom[] = '391143117F192D2CE054D4C9EF06663E';break;
                                    case '总体部-保卫处':
                                        $arrroom[] = '44C875DB2F505BC8E054D4C9EF06663E';break;
                                    case '宇航物资保障事业部-元器件采购中心':
                                        $arrroom[] = '391143117E2E2D2CE054D4C9EF06663E';break;
                                    default:
                                        $newipinfo[$key]['ip_olddepart'] .= $tmproom.';';
                                        $roomno[] = $tmproom;
                                        break;
                                }

                            }
                        }
                    }
                }
                $strroom = implode(',',$arrroom);
                $newipinfo[$key]['ip_depart'] = $strroom;
                //处理地点
                $area = $val['area'];
                if($area == ''){
                    $newipinfo[$key]['ip_area'] = '';
                }else{
                    $area = trim($area,';');
                    $strarea = '';
                    $arrarea = [];
                    $ip_area = explode(';',$area);
                    foreach($ip_area as $k=>$v){
                        $tmp = strpos($v,'-');
                        if($tmp){
                            $areaid = D('Dictionary')->getIDByname($v);
                        }else{
                            $areaid = D('Dictionary')->getIDByDicname($v);
                        }
                        if($areaid != ''){
                            $arrarea[] = $areaid;
                        }else{
                            $tmparea = trim($v);
                            switch($tmparea){
                                case '航天城-物资保障楼':
                                    $arrarea[] = '154064';break;
                                case '航天城-航天器部组件环境试验厂房':
                                    $arrarea[] = '154038';break;
                                case '知春路（82号院）-实验楼（教培楼、神州学院）':
                                    $arrarea[] = '154075';break;
                                case '航天城-综合试验厂房':
                                    $arrarea[] = '156274';break;
                                case '航天城-13号楼':
                                    $arrarea[] = '154043';break;
                                case '航天城-第二开闭所':
                                    $arrarea[] = '154072';break;
                                case '航天城-动力小白楼':
                                    $arrarea[] = '154037';break;
                                case '航天城-部组建试验楼':
                                    $arrarea[] = '154038';break;
                                case '航天城-小卫星研发实验楼':
                                    $arrarea[] = '154063';break;
                                case '航天城-物资大库':
                                    $arrarea[] = '154045';break;
                                case '航天城-第二开闭站':
                                    $arrarea[] = '154072';break;
                                default:
                                    $newipinfo[$key]['ip_oldarea'] .= $tmparea.';';
                                    $areano[] = $tmparea;
                                    break;
                            }

                        }
//                    print_r($strarea);
//                    echo "<br/>";
                    }
                }
                $strarea                         = implode(',',$arrarea);
                $newipinfo[$key]['ip_area']      = $strarea;
                $newipinfo[$key]['ip_startnum'] = D('Ipaddress')->IPformat($val['ip_start']);
                $newipinfo[$key]['ip_endnum']   = D('Ipaddress')->IPformat($val['ip_end']);
                $newipinfo[$key]['ip_purpose']   = $val['usage'];
                //拼接地区父id
                $ip_area     = explode(',',$strarea);
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
                $newipinfo[$key]['ip_parea']     = $ip_parea;
//            print_r($ip_parea);echo '<br />';
            }
            $roomno = array_unique($roomno);
            $areano = array_unique($areano);
            S('newipinfo',$newipinfo,3600*24);
        }
        return $newipinfo;
//        print_r($roomno);
//        print_r($areano);
    }

    function insert(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $newipinfo = $this->inputIPInfo();
        //添加数据
//        print_r($newipinfo);die;
        try{
            M('ipaddress')->startTrans();
            foreach($newipinfo as $key=>$item){
//                $sql = "insert into it_ipaddress (ip_start,ip_end,ip_mask,ip_gateway,ip_atpcreatedatetime,ip_atplastmodifydatetime,ip_vlan_no,ip_secret_level,ip_olddepart,ip_oldarea,ip_depart,ip_area,ip_startnum,ip_endnum,ip_purpose,ip_parea) value ('".$item['ip_start']."','".$item['ip_end']."','".$item['ip_mask']."','".$item['ip_gateway']."','".$item['ip_atpcreatedatetime']."','".$item['ip_atplastmodifydatetime']."','".$item['ip_vlan_no']."','".$item['ip_secret_level']."','".$item['ip_olddepart']."','".$item['ip_oldarea']."','".$item['ip_depart']."','".$item['ip_area']."','".$item['ip_startnum']."','".$item['ip_endnum']."','".$item['ip_purpose']."','".$item['ip_parea']."')";
//                $res = M()->table('it_ipaddress')->execute($sql);
                $res = M('ipaddress')->add($item);
                echo $res;
            }
            M('ipaddress')->commit();
            echo 'success';die;
        }
        catch(\Exception $e)
        {
            echo $e;
            M('ipaddress')->rollback();
            return false;
        }
    }

    function getOldIPbaseData(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $ipbaseInfo = S('oldIpbaseDatas');
        if(empty($ipbaseInfo)){
//            $ipInfo = M('ipaddress')->where('ip_oldmainid >= 2393 and ip_oldmainid<2500')->field('ip_oldmainid')->select();
//            $mainids = [];
//            array_walk($ipInfo,function($val,$key) use (&$mainids){ $mainids[] = $val['ip_oldmainid'];});
//            $mainids = implode("','",$mainids);
//            $mainids = "'".$mainids."'";
                $model     = M()->db(1,'ORACLE_CONFIG');
//        $model = M('view','iptwf_ip_manage_','ORACLE_CONFIG');
            $sql = "select main_id,ip_address,ip_status from brightsm6.iptwf_ip_manage_list_view where main_id>=2500";
            $ipbaseInfo    = $model->query($sql);
            S('oldIpbaseDatas',$ipbaseInfo,3600*24);
        }
        return $ipbaseInfo;
    }

    /**
     * 导入Ipbase数据
     */
    function inputIpbaseInfo(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $newIpbase = S('oldIpbaseData2');
        if(empty($newIpbase)) {
            $ipbaseInfo = $this->getOldIPbaseData();
            $newIpbase = [];
            //        $count = count($ipbaseInfo);
            foreach ($ipbaseInfo as $key => $val) {
                $newIpbase[$key]['ipb_oldmainid'] = $val['main_id'];
                $newIpbase[$key]['ipb_address'] = $val['ip_address'];
                $newIpbase[$key]['ipb_addressnum'] = D('Ipaddress')->IPformat($val['ip_address']);
                $newIpbase[$key]['ipb_status'] = ($val['ip_status'] == 3) ? 3 : '';
            }
            S('oldIpbaseData2', $newIpbase, 3600 * 24);
        }
        return $newIpbase;
    }

    function insert1(){
        $newIpbase = $this->inputIpbaseInfo();
//        print_r($newIpbase);die;
        try{
            M('ipbase')->startTrans();
            foreach($newIpbase as $key=>$item){
                $sql = "insert into it_ipbase (ipb_oldmainid,ipb_address,ipb_addressnum,ipb_status) values ('".$item['ipb_oldmainid']."','".$item['ipb_address']."','".$item['ipb_addressnum']."','".$item['ipb_status']."')";
                $res = M()->table('it_ipbase')->execute($sql);
                echo $res;
            }
            M('ipbase')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('ipbase')->rollback();
            return false;
        }
    }

    function getIpData(){
        $ipData = M('ipaddress')->field('ip_atpid,ip_oldmainid')->select();
        return $ipData;
    }

    /**
     * Ipbase 填写ipb_ipid
     */
    function inputIpbipid(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $ipData = $this->getIpData();
//        print_r($ipData);die;
        try{
            M('ipbase')->startTrans();
            foreach($ipData as $key=>$item){
                $sql = "update it_ipbase set ipb_ipid = '".$item['ip_atpid']."' where ipb_oldmainid = '".$item['ip_oldmainid']."'";
//                echo $sql;die;
                $res = M()->table('it_ipbase')->execute($sql);
                echo $res.'<br />';
            }
            M('ipbase')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('ipbase')->rollback();
            return false;
        }
    }

    /**
     * ipaddress 填写 ip_start_no,ip_end_no
     */
    function getIpstartno(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $ipData = $this->getIpData();
        try{
            M('ipbase')->startTrans();
            foreach($ipData as $key=>$item){
                $sql  = "select max(ipb_atpid),min(ipb_atpid) from it_ipbase where ipb_oldmainid = '".$item['ip_oldmainid']."'";
                $res  = M()->table('it_ipbase')->query($sql);
//                print_r($res);
//                echo $res[0]['max'];die;
                $sql1 = "update it_ipaddress set ip_start_no = '".$res[0]['min']."',ip_end_no = '".$res[0]['max']."' where ip_atpid = '".$item['ip_atpid']."'";
//                echo $sql1;die;
                $res1 = M()->table('it_ipbase')->execute($sql1);
                echo $res.'------'.$res1.'<br />';
            }
            M('ipbase')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('ipbase')->rollback();
            return false;
        }
    }

    /**
     * 获取terminal表所有zd_dutydeptname
     */
    function getAllDeptName(){
        $tmnDeptName = S('tmnDeptName');
        if(empty($tmnDeptName)){
            $tmnDeptName = M('terminal')->field('zd_atpid,zd_dutydeptname')->select();//
            S('tmnDeptName',$tmnDeptName,3600*24);
        }
        return $tmnDeptName;
    }

    /**
     * Terminal表填写zd_dutydeptid
     */
    function getTerminalDeptId(){
        $deptIds = S('TerminalDeptId');
        if(empty($deptIds)){
            $tmnDeptName = $this->getAllDeptName();
//        print_r($tmnDeptName);die;
            $deptIds = [];
            $else    = [];
            foreach($tmnDeptName as $key=>$val){
                if(!empty($val['zd_dutydeptname']) && ($val['zd_dutydeptname'] != 'n')) {
                    $deptIds[$key]['zd_atpid'] = $val['zd_atpid'];
                    $deptname = trim($val['zd_dutydeptname'], ';');
                    $tmp = strpos($deptname, '-');
                    if ($tmp !== false) {
                        $tmp      = explode('-',$deptname);
                        $tmp      = array_reverse($tmp);
                        $deptname = implode('-',$tmp);
                        $deptid = D('Depart')->getDeptIdByFullname($deptname);
                    } else {
                        $deptid = D('Depart')->getDeptIdByName($deptname);
                    }
                    if ($deptid != '') {
                        $deptIds[$key]['zd_dutydeptid'] = $deptid;
                    } else {
                        switch ($deptname) {
                            case '总体部-总体部_领导':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E0A2D2CE054D4C9EF06663E';
                                break;
                            case '总体部-电子工程技术研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E792D2CE054D4C9EF06663E';
                                break;
                            case '总体部-通信工程技术研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E792D2CE054D4C9EF06663E';
                                break;
                            case '总体部-试验保障室':
                                $deptIds[$key]['zd_dutydeptid'] = '44C875DB2F505BC8E054D4C9EF06663E';
                                break;
                            case '总体部-空间科学与深空探测总体研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117FA62D2CE054D4C9EF06663E';
                                break;
                            case '总体部-科研生产处':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117F8A2D2CE054D4C9EF06663E';
                                break;
                            case '动力行政保障部-综合办公室':
                                $deptIds[$key]['zd_dutydeptid'] = '3911431181CC2D2CE054D4C9EF06663E';
                                break;
                            case '总体部-导航卫星总体研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E622D2CE054D4C9EF06663E';
                                break;
                            case '综合管理层-唐家岭建设指挥部':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E9F2D2CE054D4C9EF06663E';
                                break;
                            case '载人航天总体部-载人航天总体部_领导':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E092D2CE054D4C9EF06663E';
                                break;
                            case '天津基地管理委员会-天津基地管理委员会_领导':
                                $deptIds[$key]['zd_dutydeptid'] = '39114311807B2D2CE054D4C9EF06663E';
                                break;
                            case '总体部-空间机械臂系统研究所':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117F192D2CE054D4C9EF06663E';
                                break;
                            case '总体部-保卫处':
                                $deptIds[$key]['zd_dutydeptid'] = '44C875DB2F505BC8E054D4C9EF06663E';
                                break;
                            case '宇航物资保障事业部-元器件采购中心':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E2E2D2CE054D4C9EF06663E';
                                break;
                            default:
                                $else[] = $deptname;
                                break;
                        }
                    }
                }
            }
//            $else = array_unique($else);
            S('TerminalDeptId',$deptIds,3600);
        }
        return $deptIds;
    }

    /**
     * Terminal表填写zd_dutydeptid
     */
    function inputTerminalDeptId(){
        header("content-type:text/html;charset=utf-8");
        $deptIds = $this->getTerminalDeptId();
        try{
            M('terminal')->startTrans();
            foreach($deptIds as $key=>$item){
                $sql = "update it_terminal set zd_dutydeptid = '".$item['zd_dutydeptid']."' where zd_atpid = '".$item['zd_atpid']."'";
//                echo $sql;die;
                $res = M()->table('it_terminal')->execute($sql);
                echo $res.'<br />';
            }
            M('terminal')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('terminal')->rollback();
            return false;
        }
    }

    /**
     * 获取USBKey状态
     */
    function GetAccountUsbStatus(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        //获取usbkey所有数据
        $usbInfo = S('USBkeyInfo');
        if(empty($usbInfo)){
            $usbInfo = M('usbkey')->field('u_account,u_isforce')->order('u_atpid')->select();
            S('USBkeyInfo',$usbInfo,3600);
        }
        //获取adinfo所有数据
        $adInfo  = S('adInfo');
        if(!empty($adInfo)){
            $adusbInfo   = M('adinfo')->field('ad_user,ad_usbkey')->order('ad_usbkey')->select();
            $usbstatus   = M('dictionary')->where("d_belongtype = 'ad_usbkeystatus'")->field('d_atpid,d_dictname')->order('d_sortno')->select();
            $adusbstatus = [];
            $adInfo      = [];
            foreach($usbstatus as $key=>$val){
                $dictname = $val['d_dictname'];
                $tmp      = strpos($dictname,'已禁用');
                if($tmp !== false){//已禁用
                    continue;
                }else{//未禁用
                    $tmp1 = strpos($dictname,'Key登录');
                    if($tmp1 !== false){//强制USBKey登录
                        $adusbstatus[$val['d_atpid']] = 0;
                    }else{//不强制
                        $adusbstatus[$val['d_atpid']] = 1;
                    }
                }
            }
            foreach($adusbInfo as $key=>$val){
                $adInfo[$val['ad_user']] = $adusbstatus[$val['ad_usbkey']];
            }
            S('adInfo',$adInfo,3600);
        }
        $newUsbInfo = [];

        foreach($usbInfo as $key=>$val){
            if(($val['u_account'] == '') || ($val['u_account'] == 'yangjiachi') || ($val['u_account'] == 'wangjianmin')){
                unset($usbInfo[$key]);
                continue;
            }
            if(isset($newUsbInfo[$key])) continue;
            if(!isset($adInfo[$val['u_account']])) continue;
            if($adInfo[$val['u_account']] == $val['u_isforce']){
                unset($usbInfo[$key]);
                continue;
            }else{
                $newUsbInfo[$key]['u_account'] = $val['u_account'];
                $newUsbInfo[$key]['newStatus'] = $adInfo[$val['u_account']];
            }
        }
        return $newUsbInfo;
    }

    /**
     * USBKey表填写U_ISFORCE
     */
    function inputUsbkeyIsforce(){
        header("content-type:text/html;charset=utf-8");
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $usbInfo = $this->GetAccountUsbStatus();
        try{
            M('usbkey')->startTrans();
            foreach($usbInfo as $key=>$item){
                $sql = "update it_usbkey set u_isforce = '".$item['u_isforce']."' where u_account = '".$item['u_account']."'";
                $res = M()->table('usbkey')->execute($sql);
                echo $res.'<br />';
            }
            M('usbkey')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('usbkey')->rollback();
            return false;
        }
    }

    /**
     * 旧表全部数据查询
     */
    public function getAllSWNIData(){
        $switchnewinfo = S('SwitchNewInfo');
        if(empty($switchnewinfo)){
            $Model         = M()->db(1,"ORACLE_CONFIG");
            $sql_select    = "select ip_addr sw_ipaddress,interface sw_interface,status sw_status,vlan sw_vlan,showflag sw_showflag,main_area sw_mainarea,main_belong_floor sw_mainbelongfloor,main_room_no sw_mainroomno,mac_address sw_macaddress from brightsm6.iptca_switch_newinfo_view";
            $switchnewinfo = $Model->query($sql_select);
            S('SwitchNewInfo',$switchnewinfo,3600);
        }
//        print_r($switchnewinfo);die;
        return $switchnewinfo;
    }

    /**
     * it_switchnewinfo表数据导入
     */
    public function insertSwitchNIData(){
        header("content-type:text/html;charset=utf-8");
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $switchnewinfo = $this->getAllSWNIData();
        try{
            M('switchnewinfo')->startTrans();
            foreach($switchnewinfo as $key=>$item){
//                $sql = "update it_switchnewinfo set u_isforce = '".$item['u_isforce']."' where u_account = '".$item['u_account']."'";
                $res = M('switchnewinfo')->add($item);
                echo $res.'<br />';
            }
            M('switchnewinfo')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('switchnewinfo')->rollback();
            return false;
        }
    }

    /**
     * 旧表全部数据查询
     */
    public function getAllSWOIData(){
        $switcholdinfo = S('SwitchOldInfo');
        if(empty($switcholdinfo)){
            $Model         = M()->db(1,"ORACLE_CONFIG");
            $sql_select    = "select ip_addr swo_ipaddress,interface swo_interface,status swo_status,vlan swo_vlan,showflag swo_showflag,main_area swo_mainarea,main_belong_floor swo_mainbelongfloor,main_room_no swo_mainroomno,mac_address swo_macaddress from brightsm6.iptca_switch_oldinfo_view";
            $switcholdinfo = $Model->query($sql_select);
            S('SwitchOldInfo',$switcholdinfo,3600);
        }
//        print_r($switcholdinfo);die;
        return $switcholdinfo;
    }

    /**
     * it_switcholdinfo表数据导入
     */
    public function insertSwitchOIData()
    {
        header("content-type:text/html;charset=utf-8");
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $switcholdinfo = $this->getAllSWOIData();
        try{
            M('switcholdinfo')->startTrans();
            M('switcholdinfo')->execute("TRUNCATE it_switcholdinfo");
            foreach($switcholdinfo as $key=>$item){
                $res = M('switcholdinfo')->add($item);
            }
            M('switcholdinfo')->commit();
        }
        catch(\Exception $e)
        {
            echo 'it_switcholdinfo表数据导入失败，详情：'.$e;
            M('switcholdinfo')->rollback();
            return false;
        }
    }

    function insertSwitchnewInfoData(){
        $switchportinfo = S('SwitchElseInfo');
        if(empty($switchportinfo)){
            $Model         = M();
            $sql_select = "SELECT a.swp_atpid,swp_macaddress,swp_ipaddress FROM it_temp a,(
        select upper(swp_macaddress) mac,swp_ipaddress ip from it_temp t where not exists (
		    select upper(zd_macaddress) mac,zd_ipaddress ip from it_terminal te where upper(te.zd_macaddress) = upper(t.swp_macaddress) and te.zd_ipaddress = t.swp_ipaddress  and zd_atpstatus is null
	    )
    ) b where b.mac =  a.swp_macaddress  and b.ip = a.swp_ipaddress;";
            $switchportinfo = $Model->query($sql_select);
            S('SwitchElseInfo',$switchportinfo,3600);
        }
//        print_r($switchportinfo);die;
        return $switchportinfo;
    }

    /**
     * itsupport.switchport表全部数据查询
     */
    public function getAllSWPortData(){
        $switchportinfo = S('SwitchPortInfo');
        if(!empty($switchportinfo)){
//            echo 1234;
            $Model         = M()->db(1,"ORACLE_CONFIG1");
            $sql_select    = "select port swp_interface,location swp_location,status swp_status,fid_vlan swp_vlan,switchip swp_ipaddress,isexlink swp_isexlink,region swp_region,building swp_building,UPPER (remark) swp_macaddress from itsupport.switchport";
            $switchportinfo = $Model->query($sql_select);
            S('SwitchPortInfo',$switchportinfo,3600);
        }
//        print_r($switchportinfo);die;
        return $switchportinfo;
    }

    /**
     * it_temp表数据导入
     */
    public function insertSwitchTempData()
    {
        header("content-type:text/html;charset=utf-8");
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $switchtempinfo = $this->getAllSWPortData();
        try{
            M('temp')->startTrans();
            foreach($switchtempinfo as $key=>$item){
//                $sql = "update it_switchnewinfo set u_isforce = '".$item['u_isforce']."' where u_account = '".$item['u_account']."'";
                $res = M('temp')->add($item);
                echo $res.'<br />';
            }
            M('temp')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('temp')->rollback();
            return false;
        }
    }

    /**
     * it_temp表数据导入
     */
    public function insertTerminalData()
    {
        header("content-type:text/html;charset=utf-8");
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $switchtempinfo = $this->getAllSWPortData();
        try{
            M('temp')->startTrans();
            foreach($switchtempinfo as $key=>$item){
//                $sql = "update it_switchnewinfo set u_isforce = '".$item['u_isforce']."' where u_account = '".$item['u_account']."'";
                $res = M('temp')->add($item);
                echo $res.'<br />';
            }
            M('temp')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('temp')->rollback();
            return false;
        }
    }

    /**
     * 格式化it_temp表数据
     */
    function formatTempData(){
        $model =  M('temp');
        $switchportinfo = $model->select();
        foreach($switchportinfo as $key=>$val){
            $macaddress = trim($val['swp_macaddress']);
            $tmp = substr_count($macaddress,';');
            if($tmp == 0){
                continue;
            }else if($tmp == 1){
                $val['swp_macaddress'] = trim($macaddress,';');
//                $model->where("swp_atpid = '".$val['swp_atpid']."'")->save($val);
            }else{
                $macaddress = trim($macaddress,';');
                $macs       = explode(';',$macaddress);
                print_r($macs);
            }
        }
    }

    /**
     * 获取it_terminal中所有zd_useman不为空且zd_usedeptid为空的zd_atpid,zd_useman
     */
    function getUsedeptNullInfo(){
        $results = M('terminal')->field("zd_atpid,zd_useman")->where("zd_useman is not null and zd_usedeptid is null")->select();
        return $results;
    }

    /**
     * it_terminal表补充为空的zd_usedeptid和zd_usedeptname
     */
    function insertTmnUseDept(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $peronDept = D('Person')->getAllPersonDeptInfo();
        $fullnames = D('Depart')->getAllFullName();
        $results   = $this->getUsedeptNullInfo();
        try{
            M('terminal')->startTrans();
            foreach($results as $key=>$item){
                if(!empty($peronDept[$item['zd_useman']])){
                    $zd_usedeptid = $peronDept[$item['zd_useman']];
                    if(!empty($zd_usedeptid) && !empty($fullnames[$zd_usedeptid])){
                        $zd_usedeptname = $fullnames[$zd_usedeptid];
                    }else{
                        $zd_usedeptname = '';
                    }
                }else{
                    $zd_usedeptid   = '';
                    $zd_usedeptname = '';
                }
                $sql = "update it_terminal set zd_usedeptid = '".$zd_usedeptid."',zd_usedeptname = '".$zd_usedeptname."' where zd_atpid = '".$item['zd_atpid']."'";
//                echo $sql;die;
                $res = M()->table('it_terminal')->execute($sql);
                echo $res.'<br />';
            }
            M('terminal')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('terminal')->rollback();
            return false;
        }
    }

    /**
     * 从运维3.0中同步运维4.0Terminal表所有webservice更新时zd_name，zd_type出错数据
     */
    function updateTerminalErrorInfo(){
        //获取所有bdid不为空且zd_name为1或空数据
        $errorData = M('Terminal')->field('zd_ipaddress')->where("zd_bdid is not null and zd_type is null and zd_atpstatus is null")->select();
        if(!empty($errorData)){
            $errorDatas = [];
            foreach($errorData as $key=>$val){
                $errorDatas[] = $val['zd_ipaddress'];
            }
            $errorDatas = "'".implode($errorDatas,"','")."'";
            $Model         = M()->db(1,"ORACLE_CONFIG");
            $sql_select    = "select main_ip_address,main_device_type from brightsm6.iptam_main where main_ip_address in (".$errorDatas.")";
            $correctData = $Model->query($sql_select);
            $correctDatas = [];
            $configid     = [];
            foreach($correctData as $key=>$val){
                if(!in_array($val['main_device_type'],$configid)) $configid[] = $val['main_device_type'];
                $correctDatas[$val['main_ip_address']] = $val['main_device_type'];
            }
            $configids   = "'".implode("','",$configid)."'";
            $sql_select  = "select id,name from brightsm6.iptsm_config_category2_view where id in (".$configids.")";
            $configData  = $Model->query($sql_select);
            $configDatas = [];
            $yw4config   = ['瘦客户机'=>'154110','打印机'=>'154111','视频终端'=>'154113','扫描仪'=>'251100'];
            foreach($configData as $key=>$val){
                $configDatas[$val['id']] = $yw4config[$val['name']];
            }
            foreach($correctDatas as $key=>$val){
                if(isset($configDatas[$val])){
                    $correctDatas[$key] = $configDatas[$val];
                }
            }
            try{
                M('Terminal')->startTrans();
                foreach($correctDatas as $key=>$item){
                    $sql_update = "update it_terminal set zd_type = '".$item."' where zd_ipaddress = '".$key."' and zd_atpstatus is null";
                    M('Terminal')->execute($sql_update);
                    echo $sql_update."<br/>";
                }
                M('Terminal')->commit();
            }
            catch(\Exception $e)
            {
                M('Terminal')->rollback();
                echo "<br><b>error</b>$e<br>";
            }
        }
    }

    /**
     * 旧表全部数据查询
     */
    public function getAllNDData(){
        $netdeviceinfo = S('NetDeviceInfo');
        if(empty($netdeviceinfo)){
            $Model         = M()->db(1,"ORACLE_CONFIG");
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $sql_select    = "select
            m.main_id           netdevice_atpid,
            main_ip_address     netdevice_ipaddress,
            main_factory_name   netdevice_factory,
            main_model_number   netdevice_model,
            main_area           netdevice_area,
            main_belong_floor   netdevice_building,
            main_room_no        netdevice_room,
            main_usage          netdevice_usage,
            main_status         netdevice_status,
            main_start_use_time netdevice_enabledate,
            main_secret_level   netdevice_secretlevel,
            ifscan              netdevice_isscan,
            main_device_book    netdevice_devicebook,
            main_privacy_book   netdevice_privacybook,
            main_anecode        netdevice_anecode,
            main_device_code    netdevice_code,
            main_seq_no         netdevice_sn,
            main_duty_man_id    netdevice_dutyman,
            main_duty_man       netdevice_dutymanname,
            main_duty_dept_name netdevice_dutydept,
            belong_network      netdevice_net,
            ios_version         netdevice_iso,
            main_memo           netdevice_memo,
            login_way           netdevice_protocol,
            main_name           netdevice_name,
            case
                when is_core = '是' then 1
                when is_core = '否' then 0
                else null
                end netdevice_iscore,
            maintain_status     netdevice_weixiu,
            config_info         netdevice_configinfo,
            up_manage_addr      netdevice_upswitch,
            up_link_port        netdevice_upport
        from brightsm6.iptam_main m
        inner join brightsm6.iptam_networkswitch_view v
        on m.main_id = v.main_id
        where m.main_sub_table_name = 'iptam_networkswitch'";
            $netdeviceinfo = $Model->query($sql_select);
            S('NetDeviceInfo',$netdeviceinfo,3600);
        }
//        print_r($netdeviceinfo);die;
        return $netdeviceinfo;
    }

    /**
     * it_netdevice表数据导入
     */
    public function insertNDData(){
        header("content-type:text/html;charset=utf-8");
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $netdeviceinfo = $this->getAllNDData();
        try{
            M('netdevice')->startTrans();
            M('netdevice')->execute("truncate it_netdevice");
            foreach($netdeviceinfo as $key=>$item){
                if($item['netdevice_isscan'] == ''){
                    $item['netdevice_isscan'] = null;
                }else if($item['netdevice_isscan'] == '0'){
                    $item['netdevice_isscan'] = 0;
                }else if($item['netdevice_isscan'] == '1'){
                    $item['netdevice_isscan'] = 1;
                }
                $item['netdevice_atpcreatedatetime'] = date('Y-m-d H:i:s');
                $item['netdevice_atpcreateuser']     = "系统初始化";
//                $sql = "insert into it_netdevice (netdevice_atpid,netdevice_ipaddress,netdevice_factory,netdevice_model,netdevice_area,netdevice_building,netdevice_room,netdevice_usage,netdevice_status,netdevice_enabledate,netdevice_secretlevel,netdevice_isscan,netdevice_devicebook,netdevice_privacybook,netdevice_anecode,netdevice_code,netdevice_sn,netdevice_dutyman,netdevice_dutymanname,netdevice_dutydept,netdevice_net,netdevice_iso,netdevice_memo,netdevice_protocol,netdevice_name,netdevice_iscore,netdevice_weixiu,netdevice_configinfo,netdevice_upswitch,netdevice_upport,netdevice_atpcreatedatetime,netdevice_atpcreateuser) values ('".$item['netdevice_atpid']."','".$item['netdevice_ipaddress']."','".$item['netdevice_factory']."','".$item['netdevice_model']."','".$item['netdevice_area']."','".$item['netdevice_building']."','".$item['netdevice_room']."','".$item['netdevice_usage']."','".$item['netdevice_status']."','".$item['netdevice_enabledate']."','".$item['netdevice_secretlevel']."','".$item['netdevice_isscan']."','".$item['netdevice_devicebook']."','".$item['netdevice_privacybook']."','".$item['netdevice_anecode']."','".$item['netdevice_code']."','".$item['netdevice_sn']."','".$item['netdevice_dutyman']."','".$item['netdevice_dutymanname']."','".$item['netdevice_dutydept']."','".$item['netdevice_net']."','".$item['netdevice_iso']."','".$item['netdevice_memo']."','".$item['netdevice_protocol']."','".$item['netdevice_name']."','".$item['netdevice_iscore']."','".$item['netdevice_weixiu']."','".$item['netdevice_configinfo']."','".$item['netdevice_upswitch']."','".$item['netdevice_upport']."','".$item['netdevice_atpcreatedatetime']."','".$item['netdevice_atpcreateuser']."')";
                $res = M('netdevice')->add($item);
                echo $res.'<br />';
            }
            M('netdevice')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('netdevice')->rollback();
            return false;
        }
    }

    /**
     * 获取netdevice表所有netdevice_dutydept
     */
    function getAllNDDeptName(){
        $ndDeptName = S('ndDeptName');
        if(empty($ndDeptName)){
            $ndDeptName = M('netdevice')->field('netdevice_atpid,to_char(netdevice_dutydept) netdevice_dutydept')->select();
            S('ndDeptName',$ndDeptName,3600*24);
        }
        return $ndDeptName;
    }

    /**
     * netdevice表填写netdevice_dutydeptid
     */
    function getNDDeptId(){
        header('content-type:text/html;charset=utf-8');
        $deptIds = S('ndDeptId');
        if(empty($deptIds)){
            $ndDeptName = $this->getAllNDDeptName();
//        print_r($ndDeptName);die;
            $deptIds = [];
            $else    = [];
            foreach($ndDeptName as $key=>$val){
                if(!empty($val['netdevice_dutydept']) && ($val['netdevice_dutydept'] != 'n')) {
                    $deptIds[$key]['netdevice_atpid'] = $val['netdevice_atpid'];
                    $deptname = trim($val['netdevice_dutydept'], ';');
                    $tmp = strpos($deptname, '-');
                    if ($tmp !== false) {
                        $tmp      = explode('-',$deptname);
                        $tmp      = array_reverse($tmp);
                        $deptname = implode('-',$tmp);
                        $deptid = D('Depart')->getDeptIdByFullname($deptname);
                    } else {
                        $deptid = D('Depart')->getDeptIdByName($deptname);
                    }
                    if ($deptid != '') {
                        $deptIds[$key]['netdevice_dutydeptid'] = $deptid;
                    } else {
                        switch ($deptname) {
                            case '总体部-总体部_领导':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E0A2D2CE054D4C9EF06663E';
                                break;
                            case '总体部-电子工程技术研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E792D2CE054D4C9EF06663E';
                                break;
                            case '总体部-通信工程技术研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E792D2CE054D4C9EF06663E';
                                break;
                            case '总体部-试验保障室':
                                $deptIds[$key]['zd_dutydeptid'] = '44C875DB2F505BC8E054D4C9EF06663E';
                                break;
                            case '总体部-空间科学与深空探测总体研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117FA62D2CE054D4C9EF06663E';
                                break;
                            case '总体部-科研生产处':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117F8A2D2CE054D4C9EF06663E';
                                break;
                            case '动力行政保障部-综合办公室':
                                $deptIds[$key]['zd_dutydeptid'] = '3911431181CC2D2CE054D4C9EF06663E';
                                break;
                            case '总体部-导航卫星总体研究室':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E622D2CE054D4C9EF06663E';
                                break;
                            case '综合管理层-唐家岭建设指挥部':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E9F2D2CE054D4C9EF06663E';
                                break;
                            case '载人航天总体部-载人航天总体部_领导':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E092D2CE054D4C9EF06663E';
                                break;
                            case '天津基地管理委员会-天津基地管理委员会_领导':
                                $deptIds[$key]['zd_dutydeptid'] = '39114311807B2D2CE054D4C9EF06663E';
                                break;
                            case '总体部-空间机械臂系统研究所':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117F192D2CE054D4C9EF06663E';
                                break;
                            case '总体部-保卫处':
                                $deptIds[$key]['zd_dutydeptid'] = '44C875DB2F505BC8E054D4C9EF06663E';
                                break;
                            case '宇航物资保障事业部-元器件采购中心':
                                $deptIds[$key]['zd_dutydeptid'] = '391143117E2E2D2CE054D4C9EF06663E';
                                break;
                            default:
                                $else[] = $deptname;
                                break;
                        }
                    }
                }
            }
//            $else = array_unique($else);
            S('ndDeptId',$deptIds,3600);
        }
        return $deptIds;
    }

    /**
     * netdevice表填写netdevice_dutydeptid
     */
    function inputNDDeptId(){
        header("content-type:text/html;charset=utf-8");
        $deptIds = $this->getNDDeptId();
        try{
            M('netdevice')->startTrans();
            foreach($deptIds as $key=>$item){
                $sql = "update it_netdevice set netdevice_dutydeptid = '".$item['netdevice_dutydeptid']."' where netdevice_atpid = '".$item['netdevice_atpid']."'";
//                echo $sql;die;
                $res = M()->table('it_terminal')->execute($sql);
                echo $res.'<br />';
            }
            M('netdevice')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('netdevice')->rollback();
            return false;
        }
    }

    /**
     * netdevice表更新loginuser，loginpass
     */
    function inputLoginInfo(){
        $sql = "SELECT netdevice_atpid,netdevice_ipaddress
FROM IT_NETDEVICE where netdevice_status = '在用' and netdevice_protocol = 'Telnet' and netdevice_ipaddress is not null and netdevice_atpstatus is null;";
        $loginInfo = M()->query($sql);
//        print_r($loginInfo);die;
        try{
            M('netdevice')->startTrans();
            foreach($loginInfo as $key=>$item){
                if(!empty($item['netdevice_ipaddress'])){
                    $netdevice_ipaddress = $item['netdevice_ipaddress'];
                    $ippass = substr($netdevice_ipaddress,strrpos($netdevice_ipaddress,'.')+1);
                    $loginpass = 'Castinfo45888'.$ippass;
                    $sql = "update it_netdevice set netdevice_loginuser = 'yunwei',netdevice_loginpass = '".$loginpass."' where netdevice_atpid = '".$item['netdevice_atpid']."'";
                    $res = M()->table('it_terminal')->execute($sql);
                    echo $res.'<br />';
                }
            }
            M('netdevice')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('netdevice')->rollback();
            return false;
        }
    }

    /**
     * it_terminal_hy表查询全部数据
     */
    function getAllMeetingTerminalInfo(){
        header("content-type:text/html;charset=utf-8");
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $MeetTerminalInfos = S('MeetTerminalInfo');
        if(empty($MeetTerminalInfos)){
            $sql = 'SELECT * FROM "ATP"."USER_IPADDR"';
            $MeetTerminalInfo = M()->query($sql);
            $MeetTerminalInfos = [];
            foreach($MeetTerminalInfo as $key=>$val){
                $MeetTerminalInfos[$key]['zd_atpid']         = $this->makeGuid();
                $MeetTerminalInfos[$key]['zd_atpcreatetime'] = date('Y-m-d H:i:s');
                $MeetTerminalInfos[$key]['zd_atpcreateuser'] = '系统初始化';
                $MeetTerminalInfos[$key]['zd_type']          = 'guidDA36A81A-15B6-4D11-9857-B67391CF9C0A';
                $MeetTerminalInfos[$key]['zd_name']          = $val['main_device_type'];
                $MeetTerminalInfos[$key]['zd_status']        = '在用';
                $MeetTerminalInfos[$key]['zd_ipaddress']     = $val['main_ip_address'];
                if($val['main_use_man_id']){
                    $MeetTerminalInfos[$key]['zd_dutyman']     = $val['main_use_man_id'];
                    $MeetTerminalInfos[$key]['zd_dutymanname'] = getRealusername($val['main_use_man_id']);
                }
                $MeetTerminalInfos[$key]['zd_memo']            = $val['device_type'];
            }
            S('MeetTerminalInfo',$MeetTerminalInfos);
        }
        return $MeetTerminalInfos;
    }

    /**
     * it_terminal_hy表批量插入数据
     */
    function insertMeetingTerminalData(){
        header("content-type:text/html;charset=utf-8");
        $MeetTerminalInfo = $this->getAllMeetingTerminalInfo();
        if(!empty($MeetTerminalInfo)){
            try{
                M('terminal_hy')->startTrans();
                foreach($MeetTerminalInfo as $key=>$val){
//                    $sql = "insert into it_terminal_hy (zd_atpid,zd_atpcreatetime,zd_atpcreateuser,zd_type,zd_name,zd_status,zd_ipaddress,zd_demo) values ('".$val['zd_atpid']."','".$val['zd_atpcreatetime']."','".$val['zd_atpcreateuser']."','".$val['zd_type']."','".$val['zd_name']."','".$val['zd_status']."','".$val['zd_ipaddress']."','".$val['zd_demo']."')";
//                    echo $sql;die;
                    $res = M('terminal_hy')->add($val);
                }
                M('terminal_hy')->commit();
                echo 'success';
            }catch(\Exception $e){
//                M('terminal_hy')->rollback();
                echo $e;
                echo M('terminal_hy')->getDbError();
            }
        }
    }

    /**
     * it_terminal_hy表补充为空的zd_dutydeptid和zd_dutydeptname
     */
    function insertTmnHYDutyDept(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $peronDept = D('Person')->getAllPersonDeptInfo();
        $fullnames = D('Depart')->getAllFullName();
        $results   = $this->getDutydeptNullInfo();
        try{
            M('terminal_hy')->startTrans();
            foreach($results as $key=>$item){
                if(!empty($peronDept[$item['zd_dutyman']])){
                    $zd_dutydeptid = $peronDept[$item['zd_dutyman']];
                    if(!empty($zd_dutydeptid) && !empty($fullnames[$zd_dutydeptid])){
                        $zd_dutydeptname = $fullnames[$zd_dutydeptid];
                    }else{
                        $zd_dutydeptname = '';
                    }
                }else{
                    $zd_dutydeptid   = '';
                    $zd_dutydeptname = '';
                }
                $sql = "update it_terminal_hy set zd_dutydeptid = '".$zd_dutydeptid."',zd_dutydeptname = '".$zd_dutydeptname."' where zd_atpid = '".$item['zd_atpid']."'";
//                echo $sql;die;
                $res = M()->table('it_terminal_hy')->execute($sql);
                echo $res.'<br />';
            }
            M('terminal_hy')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('terminal_hy')->rollback();
            return false;
        }
    }

    /**
     * 获取it_terminal_hy中所有zd_dutyman不为空且zd_dutydeptid为空的zd_atpid,zd_dutyman
     */
    function getDutydeptNullInfo(){
        $results = M('terminal_hy')->field("zd_atpid,zd_dutyman")->where("zd_dutyman is not null")->select();
//        print_r($results);die;
        return $results;
    }

    /**
     * it_terminal_hy表补充为带”，“的zd_dutymanname
     */
    function insertTmnHYDutymanname(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $results   = $this->getDutydeptNullInfo();
        try{
            M('terminal_hy')->startTrans();
            foreach($results as $key=>$item){
                $result = explode(',',$item['zd_dutyman']);
                $zd_dutymanname = '';
                foreach($result as $k=>$v){
                    if($zd_dutymanname == ''){
                        $zd_dutymanname = getRealusername($v);
                    }else{
                        $zd_dutymanname .= ','.getRealusername($v);
                    }
                }
                $sql = "update it_terminal_hy set zd_dutymanname = '".$zd_dutymanname."' where zd_atpid = '".$item['zd_atpid']."'";
//                echo $sql;die;
                $res = M()->table('it_terminal_hy')->execute($sql);
                echo $res.'<br />';
            }
            M('terminal_hy')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('terminal_hy')->rollback();
            return false;
        }
    }


    /**
     *更新所有Terminal表中的dutydeptname字段为it_depart表中的fullname
     */
    function UpdateAllTerminalDutyDeptName(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        // 获取所有部门的[id=>fullname]
        $deptFullnameInfo = getFullDeptname();
        // 获取所有需要更新的值
        $oldTData = M('terminal')->where('zd_dutydeptid is not null')->field('zd_atpid,zd_dutydeptid')->select();
        try{
            M('terminal')->startTrans();
            foreach($oldTData as $key=>$val){
                $fullname = $deptFullnameInfo[$val['zd_dutydeptid']];
                $data = [];
                if($fullname){
                    $data['zd_dutydeptname'] = $fullname;
                    $res = M('terminal')->where("zd_atpid = '".$val['zd_atpid']."'")->setField($data);
                    echo $res;
                }
            }
            M('terminal')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('terminal')->rollback();
            return false;
        }
    }
    /**
     * 更新所有Netdevice表中的dutydeptname字段为it_depart表中的fullname
     */
    function UpdateAllNetdeviceDutyDeptName(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        // 获取所有部门的[id=>fullname]
        $deptFullnameInfo = getFullDeptname();
        // 获取所有需要更新的值
        $oldTData = M('netdevice')->where('netdevice_dutydeptid is not null')->field('netdevice_atpid,netdevice_dutydeptid')->select();
//        print_r($oldTData);die;
        try{
            M('netdevice')->startTrans();
            foreach($oldTData as $key=>$val){
                $fullname = $deptFullnameInfo[$val['netdevice_dutydeptid']];
//                echo $fullname;die;
                $data = [];
                if($fullname){
                    $data['netdevice_dutydept'] = $fullname;
                    $res = M('netdevice')->where("netdevice_atpid = '".$val['netdevice_atpid']."'")->setField($data);
                    echo $res;
                }
            }
            M('netdevice')->commit();
            echo 'success';
        }
        catch(\Exception $e)
        {
            echo $e;
            M('netdevice')->rollback();
            return false;
        }
    }

    /**
     * 初始化IPBASENEW表数据
     **/
    function insertIpbasenewData(){
        ini_set('memory_limit','3072M');
        set_time_limit(0);
        $ipaddressData = M('ipaddressnew')->select();
        $Model         = M('ipbasenew');
        // dump($ipaddressData);die;
        try{
            // $Model->startTrans();
            $allIP = [];
            foreach($ipaddressData as $key=>$val){
                for($i = $val['ip_startnum'];$i<=$val['ip_endnum'];$i++){
                    $ipb_address = $this->getIpbyNum($i);
                    if(in_array($ipb_address,$allIP)) continue;
                    $allIP[] = $ipb_address;
                    $data = [];
                    $data['ipb_atpcreateuser']     = $val['ip_atpcreateuser'];
                    $data['ipb_atpcreatedatetime'] = $val['ip_atpcreatedatetime'];
                    $data['ipb_ipid']              = $val['ip_atpid'];
                    $data['ipb_isdel']             = $val['ip_isdel'];
                    // echo $val['ip_startnum']+$key;die;
                    $data['ipb_addressnum']        = $i;
                    $data['ipb_address']           = $ipb_address;
                    $res = $Model->add($data);
                    echo $key.'__'.$i.'<br/>';
                }    
            }
            $Model->commit();
        }catch(Exception $e){
            $Model->rollback();
            echo $e;
        }
    }

    /**
     * 初始化IPBASENEW表ipb_status字段
     **/
    function setIpbStatusByAddresnum(){
        ini_set('memory_limit','256M');
        set_time_limit(0);
        $baseInfo = M('ipbase')->field('ipb_addressnum,ipb_status')->where('ipb_status is not null')->select();
        // dump($baseInfo);die;
        if($baseInfo){
            foreach($baseInfo as $key=>$val){
                $data = [];
                $data['ipb_status'] = $val['ipb_status'];
                M('ipbasenew')->where("ipb_addressnum = '".$val['ipb_addressnum']."'")->setField($data);
                echo $key.'---------------------<br/>';
                dump($data);
            }
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

    /**
     * ip地址数值格式化为地址
     */
    function getIpbyNum($ipnum){
        if(empty($ipnum)) return false;
        $ipnum = trim($ipnum);
        $ip    = [0,0,0,0];
        while (($ipnum - 256*256*256) >= 0) {
            $ipnum -= 256*256*256;
            $ip[0]++;
        }
        while (($ipnum - 256*256) >= 0) {
            $ipnum -= 256*256;
            $ip[1]++;
        }
        while (($ipnum - 256) >= 0) {
            $ipnum -= 256;
            $ip[2]++;
        }
        $ip[3] = $ipnum;
        $ip = implode('.', $ip);
        return $ip;
    }


    /**
     * 更新字典表
     */
    function updateDictionary(){
        $dicIdInfo = M("dictionary")->field("d_atpid")->select();
        $dicIdInfo = removeArrKey($dicIdInfo,'d_atpid');

        $updatedModel = M()->db(1,'ORACLE_CONFIG');
        $updatedInfo = $updatedModel->query("select id,name,pid,ext2 from brightsm6.iptsm_config_category2_view where identify = 'dict'");
        $typekey     = ['factoryinfo','region','equipmenttype'];
        $type        = [
            'factoryinfo'=>'modelnumber',
            'region'=>'building',
            'equipmenttype'=>'factoryinfo'
        ];
        $equippid    = ['154012','154013','154014'];
        $updatedInfos = [];
        foreach($updatedInfo as $key=>$val){
            if(!in_array($val['id'],$dicIdInfo)) $updatedInfos[$val['id']] = $val;
            $updatedInfo[$val['id']] = $val;
        }
        foreach($updatedInfos as $key=>&$val){
            if(empty($val['ext2'])){
                if(in_array($val['pid'],$equippid)){
                    $val['ext2'] = 'equipmenttype';
                }else if(in_array($updatedInfo[$val['pid']]['pid'],$equippid)){
                    $val['ext2'] = 'factoryinfo';
                }else if(in_array($updatedInfo[$updatedInfo[$val['pid']]['pid']]['pid'],$equippid)){
                    $val['ext2'] = 'modelnumber';
                }else if(in_array($updatedInfo[$val['pid']]['ext2'],$typekey)){
                    $val['ext2'] = $type[$updatedInfo[$val['pid']]['ext2']];
                }else if(empty($updatedInfo[$val['pid']]['ext2'])){
                    if($updatedInfo[$val['pid']]['pid'] == '154117'){
                        $val['ext2'] = 'modelnumber';
                    }
                }
            }
        }
        foreach ($updatedInfos as $key=>&$val) {
            $data = [];
            $data['d_atpid']         = $val['id'];
            $data['d_atpcreatetime'] = getDatetime();
            $data['d_atpcreateuser'] = '系统初始化';
            $data['d_dictname']      = $val['name'];
            $data['d_parentid']      = $val['pid'];
            $data['d_belongtype']    = $val['ext2'];
            $res = M("dictionary")->add($data);
            echo $res."-------<br/>";
        }
        echo 'success';
//        dump($updatedInfos);
//        dump($dicIdInfo);die;

    }
}

