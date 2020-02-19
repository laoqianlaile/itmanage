<?php
namespace Demo\Controller;
use Think\Controller;
class ExportController extends BaseController
{

    /**
    * 互联网计算机台账导出
    */
    public function hlwjsj_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam  = I('get.');
        $hlwjsjInfo  = D('Hlwjsj')->getData($queryparam, 1);
        $Result      = $hlwjsjInfo[0];
        $data = array();
        if($Result){
            foreach($Result as $key=>$value){
                $data[$key][] = $value['hlwjsj_code'];
                $data[$key][] = $value['hlwjsj_name'];
                $data[$key][] = $value['hlwjsj_ip'];
                $data[$key][] = $value['hlwjsj_mac'];
                $data[$key][] = $value['hlwjsj_usage'];
                $data[$key][] = $value['hlwjsj_dept'];
                $data[$key][] = $value['hlwjsj_didian'];
                $data[$key][] = $value['hlwjsj_jtxkz'];
                $data[$key][] = $value['hlwjsj_status'];
                $data[$key][] = $value['hlwjsj_factory'];
                $data[$key][] = $value['hlwjsj_model'];
                $data[$key][] = $value['hlwjsj_os'];
                $data[$key][] = $value['hlwjsj_qiyong'];
                $data[$key][] = $value['hlwjsj_osdate'];
                $data[$key][] = $value['hlwjsj_disknum'];
                $data[$key][] = $value['hlwjsj_dutyman'];
            } 
            $tableheader = Array("序号","编号","名称","IP地址","MAC地址","用途","所属部门","放置地点","集团许可证编号","使用情况","厂家","型号","操作系统及版本","启用时间","操作系统安装日期","硬盘序列号","责任人");
            excelExport($tableheader,$data,false);
        }
    }

    /**
    * 红钥匙和打印卡台账导出
    */
    public function hyshdyk_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam  = I('get.');
        $hyshdykInfo = D('Hyshdyk')->getData($queryparam, 1);
        $Result      = $hyshdykInfo[0];
        $data = array();
        if($Result){
            foreach($Result as $key=>$value){
                $data[$key][] = $value['hyshdyk_code'];
                $data[$key][] = $value['hyshdyk_name'];
                $data[$key][] = $value['hyshdyk_secret'];
                $data[$key][] = $value['hyshdyk_dept'];
                $data[$key][] = $value['hyshdyk_status'];
                $data[$key][] = $value['hyshdyk_dutyman'];
            } 
            $tableheader = array("序号","编号","名称","密级（涉密专用）","所属部门","使用情况","责任人");
            excelExport($tableheader,$data,false);
        }
    }

    /**
     * 交换机台账数据导出
     */
    public function netdevice_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffInfo   = D('Netdevice')->getNDData($queryparam, 1);
        $Result     = $diffInfo[0];

        // 子网掩码 默认网关   用途 远程管理协议	远程管理主机 日志服务器	上联交换机IP地址

        $data = array();
        foreach($Result as $key=>$value){
            $data[$key][] = $value['netdevice_ipaddress'];
            $data[$key][] = $value['netdevice_mask'];
            $data[$key][] = $value['netdevice_gateway'];
            $data[$key][] = $value['netdevice_area'];
            $data[$key][] = $value['netdevice_building'];
            $data[$key][] = $value['netdevice_room'];
            $data[$key][] = $value['netdevice_factory'];
            $data[$key][] = $value['netdevice_model'];
            $data[$key][] = $value['netdevice_issan'];
            $data[$key][] = $value['netdevice_status'];
            $data[$key][] = $value['netdevice_usage'];
            $data[$key][] = $value['netdevice_protocol'];
            $data[$key][] = $value['netdevice_managehost'];
            $data[$key][] = $value['netdevice_logserver'];
            $data[$key][] = $value['netdevice_upswitch'];
        }
        $tableheader = array('序号', 'IP地址','子网掩码','默认网关', '地区', '楼宇', '房间号', '厂家', '型号', '扫描状态', '状态','用途','远程管理主机','远程管理主机','日志服务器','上联交换机IP地址');
        excelExport($tableheader,$data,false);
    }

    /**
     * 交换机差异数据导出
     */
    function sw_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffInfo   = D('Switchport')->switchesData($queryparam);
        $Result     = $diffInfo[0];

        $data = array();
        foreach($Result as $key=>$value){
            $data[$key][] = $value['sw_macaddress'];
            $data[$key][] = $value['sw_ipaddress'];
            $data[$key][] = $value['sw_interface'];
        }
        $tableheader = array('序号', 'MAC地址', 'IP地址', '端口信息');
        excelExport($tableheader,$data,false);
    }

    /**
     * 域控差异数据表
     */
    function ad_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $adDiffData = D('Adinfo')->getAdDiffData();
        if(!empty($queryparam['ad_user'])){
            $ad_user = trim($queryparam['ad_user']);
            if(empty($adDiffData[$ad_user])){
                $adDiffData = [];
            }else{
                $adDiffData = array($adDiffData[$ad_user]);
            }
        }
        if(!empty($queryparam['ad_dld'])){
            $ad_dld = trim($queryparam['ad_dld']);
            $ad_dld = strtoupper($ad_dld);
            $tmp    = [];
            foreach($adDiffData as $key=>$val){
                $tmp1 = strpos($val['ad_dld'],$ad_dld);
                if($tmp1 === false){
                    continue;
                }else{
                    $tmp[] = $val;
                }
            }
            $adDiffData = $tmp;
        }
        $adDiffData = array_values($adDiffData);

        $data = array();
        foreach($adDiffData as $key=>$value){
            $data[$key][] = $value['ad_user'];
            $data[$key][] = $value['ad_dld'];
            $data[$key][] = $value['zd_user'];
            $data[$key][] = $value['zd_dld'];
        }
        $tableheader = array('序号', '域控使用人', '域控机器名称', '资产使用人', '资产机器名称');
        excelExport($tableheader,$data,false);
    }

    /**
     * 责任人部门处室差异表
     */
    function duty_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffData   = D('Terminal')->dutyDeptDiffData($queryparam);
        $diffData   = array_values($diffData);

        $data = array();
        foreach($diffData as $key=>$value){
            $data[$key][] = $value['type'];
            $data[$key][] = $value['name'];
            $data[$key][] = $value['code'];
            $data[$key][] = $value['mac'];
            $data[$key][] = $value['ip'];
            $data[$key][] = $value['area'];
            $data[$key][] = $value['floor'];
            $data[$key][] = $value['room'];
            $data[$key][] = $value['user'];
            $data[$key][] = $value['zd_dept'];
            $data[$key][] = $value['ad_dept'];
            $data[$key][] = $value['ad_office'];
            $data[$key][] = $value['memo'];
        }
        $tableheader = array('序号', '设备类别', '设备名称', '设备编码', 'MAC地址', 'IP地址', '所在地区', '所在楼宇', '房间号', '使用人', '资产使用人部门处室', '域控使用人部门', '域控使用人处室', '备注');
        excelExport($tableheader,$data,false);
    }

    /**
     * 使用人部门信息差异表
     */
    function user_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffData   = D('Terminal')->userDeptDiffData($queryparam);
        $diffData   = array_values($diffData);

        $data = array();
        foreach($diffData as $key=>$value){
            $data[$key][] = $value['type'];
            $data[$key][] = $value['name'];
            $data[$key][] = $value['ip'];
            $data[$key][] = $value['user'];
            $data[$key][] = $value['zd_dept'];
            $data[$key][] = $value['ad_dept'];
            $data[$key][] = $value['ad_office'];
        }
        $tableheader = array('序号', '类型', '机器名称', 'IP地址', '使用人', '资产使用人部门处室', '域控使用人部门', '域控使用人处室');
        excelExport($tableheader,$data,false);
    }

    /**
     * 交换机端口接入数量为0表
     */
    function swzero_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffData   = D('Terminal')->portInZeroData($queryparam);

        $data = array();
        foreach($diffData as $key=>$value){
            $data[$key][] = $value['zd_typename'];
            $data[$key][] = $value['zd_name'];
            $data[$key][] = $value['zd_macaddress'];
            $data[$key][] = $value['zd_areaname'];
            $data[$key][] = $value['zd_belongfloorname'];
            $data[$key][] = $value['zd_roomno'];
            $data[$key][] = $value['zd_username'];
            $data[$key][] = $value['zd_dutydeptname'];
            $data[$key][] = $value['zd_memo'];
            $data[$key][] = $value['zd_status'];
        }

        $tableheader = array('序号', '设备类别', '设备名称', 'MAC地址', '所在地区', '所在楼宇', '房间号', '使用人', '责任部门', '备注', '设备状态');
        excelExport($tableheader,$data,false);
    }

    /**
     * 交换机端口接入数量大于1表
     */
    function swone_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffData   = D('Terminal')->portInOneData($queryparam);

        $data = array();
        foreach($diffData as $key=>$value){
            $data[$key][] = $value['zd_typename'];
            $data[$key][] = $value['zd_name'];
            $data[$key][] = $value['zd_devicecode'];
            $data[$key][] = $value['zd_macaddress'];
            $data[$key][] = $value['zd_ipaddress'];
            $data[$key][] = $value['zd_areaname'];
            $data[$key][] = $value['zd_belongfloorname'];
            $data[$key][] = $value['zd_roomno'];
            $data[$key][] = $value['zd_username'];
            $data[$key][] = $value['zd_dutydeptname'];
            $data[$key][] = $value['zd_memo'];
        }
        $tableheader = array('序号', '设备类别', '设备名称','设备编码', 'MAC地址','IP地址', '所在地区', '所在楼宇', '房间号', '使用人', '责任部门', '备注');
        excelExport($tableheader,$data,false);
    }

    /**
     * 交换机端口DOWN表
     */
    function swdown_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffData   = D('SwitchnewinfoT')->SWPortDownData($queryparam);
        $diffData = $diffData[0];

        $data = array();
        foreach($diffData as $key=>$value){
            $data[$key][] = $value['sw_ipaddress'];
            $data[$key][] = $value['sw_interface'];
            $data[$key][] = $value['sw_macaddress'];
            $data[$key][] = $value['sw_status'];
            $data[$key][] = $value['sw_mainarea'];
            $data[$key][] = $value['sw_mainbelongfloor'];
//            $data[$key][] = $value['zd_belongfloorname'];
        }
        $tableheader = array('序号', '交换机IP', '交换机端口','MAC', '状态','地区', '楼宇');
        excelExport($tableheader,$data,false);
    }
    /**
     * 交换机端口UP表
     */
    function swup_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $diffData   = D('SwitchnewinfoT')->SWPortUpData($queryparam);
        $diffData = $diffData[0];

        $data = array();
        foreach($diffData as $key=>$value){
            $data[$key][] = $value['sw_ipaddress'];
            $data[$key][] = $value['sw_interface'];
            $data[$key][] = $value['sw_macaddress'];
            $data[$key][] = $value['sw_status'];
            $data[$key][] = $value['sw_mainarea'];
            $data[$key][] = $value['sw_mainbelongfloor'];
//            $data[$key][] = $value['zd_belongfloorname'];
        }
        $tableheader = array('序号', '交换机IP', '交换机端口','MAC', '状态','地区', '楼宇');
        excelExport($tableheader,$data,false);
    }

    /**
     *工单查询打印
     */
    public function gd_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Result     = D('Work')->getGDData($queryparam);
        $Result     = $Result[0];

        $data = array();
        foreach($Result as $key=>$value){
//            $data[$key][] = $key+1;
            $data[$key][] = $value['rw_workid'];
            $data[$key][] = $value['rw_name'];
            $data[$key][] = $value['rw_phone'];
            $data[$key][] = $value['rw_problemtype'];
            $data[$key][] = $value['rw_receipttime'];
            $data[$key][] = $value['rw_resource'];
            $data[$key][] = $value['rw_type'];
            $data[$key][] = $value['dealperson'];
            $data[$key][] = $value['rw_dealtime'];
            $data[$key][] = $value['rw_status'];
            $data[$key][] = $value['rw_count'];
            $data[$key][] = $value['rw_process'];
            $data[$key][] = $value['rw_problemdes'];
        }
        $tableheader = array('序号', '工单号', '申请人', '电话','问题分类','任务单接收时间', '任务来源','工单类型','处理人','工单处理时间','处理状态','处理次数','处理情况','任务描述');
        excelExport($tableheader,$data,false);
    }

    /**
     *表单查询打印
     */
    public function bd_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Result     = D('Task')->getBDData($queryparam,1);
        $Result     = $Result[0];
        foreach($Result as $key=> &$value){

            //处理状态
            switch($value['t_status']){
                case "0":
                    $value['statusprint'] ="未处理";
                    break;
                case "1":
                    $value['statusprint'] ="处理中";
                    break;
                case "2":
                    $value['statusprint'] ="已完成";
                    break;
                default:
                    $value['statusprint'] ="-";
                    break;
            }
            //表单类型
            switch($value['t_biaodanname']){
                case "110":
                    $value['bdname'] ="涉密网计算机入网";
                    break;
                case "501":
                    $value['bdname'] ="涉密网测试用机入网";
                    break;
                case "119":
                    $value['bdname'] ="涉密网设备入网";
                    break;
                case "15":
                    $value['bdname'] ="涉密网计算机变更入网";
                    break;
                case "502":
                    $value['bdname'] ="涉密网测试用机变更入网";
                    break;
                case "308":
                    $value['bdname'] ="涉密网设备变更入网";
                    break;
                case "21":
                    $value['bdname'] ="涉密网计算机撤销入网";
                    break;
                case "503":
                    $value['bdname'] ="涉密网测试用机撤销入网";
                    break;
                case "122":
                    $value['bdname'] ="涉密网设备撤销入网";
                    break;
                case "18":
                    $value['bdname'] ="涉密网计算机新增入网申请";
                    break;
                case "105":
                    $value['bdname'] ="总体部涉密计算机特殊需求申请";
                    break;
                case "115":
                    $value['bdname'] ="涉密网计算机安全防护特殊需求申请";
                    break;
                case "118":
                    $value['bdname'] ="总体部涉密人员密级审定表";
                    break;
                case "134":
                    $value['bdname'] ="涉密网应用系统帐号申请";
                    break;
                case "135":
                    $value['bdname'] ="涉密网公共机登录权限申请";
                    break;
                case "136":
                    $value['bdname'] ="涉密网公共机登录权限撤销申请";
                    break;
                case "137":
                    $value['bdname'] ="涉密网测试用机域账号申请";
                    break;
                case "138":
                    $value['bdname'] ="涉密网测试用机域账号撤销申请";
                    break;
                case "506":
                    $value['bdname'] ="UsbKey管理表";
                    break;
                case "508":
                    $value['bdname'] ="临时域账号申请";
                    break;
                case "526":
                    $value['bdname'] ="综合管理层涉密计算机处理审批登记";
                    break;
                default:
                    $value['bdname'] ="-";
                    break;
            }
            //申请人姓名根据t_nameid查
            if(!empty($value['t_nameid'])){
                $userid                 = trim($value['t_nameid']);
                $Result[$key]['t_name'] = getRealusername($userid);
            }
            //处理人姓名
            if($value['t_person'] != ''){
                if($value['t_person'] != '系统'){
                    $personid = trim($value['t_person']);
                    $Result[$key]['t_personname'] = getRealusername($personid);
                }else{
                    $Result[$key]['t_personname'] = '系统';
                }
            }else{
                $Result[$key]['t_personname'] = '';
            }

            //MAC地址
            if(!empty($value['t_mac'])) {
                //去掉特殊字符
                $t_mac = strtoupper(substr(preg_replace("/[^\da-zA-Z]/",'',$value['t_mac']),0,12));
                //加点
                $value['t_mac'] = substr(implode('.',str_split($t_mac,4)),0,-1);
            }else{
                $value['t_mac'] = '-';
            }

            //设备编号
            if(empty($value['zd_devicecode'])) {
                $value['zd_devicecode'] = '-';
            }
            //厂家
            if(!empty($value['zd_factoryname'])){
                $value['zd_factoryname'] = getDictname($value['zd_factoryname']);
            }else{
                $value['zd_factoryname'] = '-';
            }
            //型号
            if(!empty($value['zd_modelnumber'])){
                $value['zd_modelnumber'] = getDictname($value['zd_modelnumber']);
            }else{
                $value['zd_modelnumber'] = '-';
            }
            //部门与处室
            if(!empty($value['zd_usedeptname'])) {
                //去掉
                if(mb_strstr($value['zd_usedeptname'],'五院本级') !== false){
                    //去掉 -五院本级-中国航天科技集团公司第五研究院
                    $zd_usedeptmane = mb_substr($value['zd_usedeptname'],0,-21);
                }else{
                    //去掉 -中国航天科技集团公司第五研究院
                    $zd_usedeptmane = mb_substr($value['zd_usedeptname'],0,-16);
                }
                //以下三个顺序不能错
                $arr_usedeptmane = explode('-',$zd_usedeptmane);
                //部门
                $value['bumen'] = array_pop($arr_usedeptmane);
                //处室
                $value['chushi'] = implode('-',$arr_usedeptmane);
            }else{
                $value['bumen'] = $value['chushi'] = '-';
            }

            //密级
            switch($value['zd_secretlevel']){
                case '1':
                    $value['zd_secretlevel'] = '秘密';
                    break;
                case '2':
                    $value['zd_secretlevel'] = '机密';
                    break;
                case '3':
                    $value['zd_secretlevel'] = '绝密';
                    break;
                case '4':
                    $value['zd_secretlevel'] = '内部';
                    break;
                case '5':
                    $value['zd_secretlevel'] = '非密';
                    break;
                default:
                    $value['zd_secretlevel'] ="-";
                    break;
            }

        }
//        var_dump($Result);die;
        $data = array();
        foreach($Result as $key=>$value){
            $data[$key][] = $value['t_taskid'];         //工单号
            $data[$key][] = $value['t_nameid'];         //申请人帐号
            $data[$key][] = $value['t_name'];           //申请人
            $data[$key][] = $value['t_phone'];          //电话
            $data[$key][] = $value['bdname'];           //表单类型
            $data[$key][] = $value['t_rwid'];           //表单号
            $data[$key][] = $value['t_problemtype'];    //节点名称
            $data[$key][] = $value['t_ip'];             //IP地址
            $data[$key][] = $value['t_mac'];            //MAC地址
            $data[$key][] = $value['t_arrivetime'];     //工单接收时间
            $data[$key][] = $value['statusprint'];      //处理状态
            $data[$key][] = $value['t_personname'];     //处理人
            $data[$key][] = $value['t_atplastmodifydatetime'];//处理时间
//            $data[$key][] = $value['rw_process'];
//            $data[$key][] = $value['rw_problemdes'];

            $data[$key][] = $value['zd_devicecode'];    //设备编号
            $data[$key][] = $value['zd_factoryname'];   //厂家
            $data[$key][] = $value['zd_modelnumber'];   //型号
            $data[$key][] = $value['zd_secretlevel'];   //密级
            $data[$key][] = $value['bumen'];   //部门
            $data[$key][] = $value['chushi'];   //处室
        }
//        var_dump($data);die;
        $tableheader = array('序号', '工单号', '申请人帐号','申请人', '电话','表单类型','表单号','节点名称', 'IP地址','MAC地址','工单接收时间','处理状态','处理人','处理时间','设备编号','厂家','型号','密级','部门','处室');
        excelExport($tableheader,$data,true);
    }

    public function getrealname($username){
        $realusername =M('person')->where("username='%s'",$username)->getField('realusername');
        if(empty($realusername)){
            $realusername =M('ywperson')->where("yw_account='%s'",$username)->getField('yw_name');
        }
        return $realusername;
    }

    /**
     * IP导出
     */
    public function ip_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $res        = D('Ipaddress')->getIPInfo($queryparam, 1);
        $Result     = $res[0];
//        print_r($Result);die;

        $data = array();
        foreach($Result as $key=>$value){
//            $data[$key][] = $key+1;
            $data[$key][] = $value['ip_start'];
            $data[$key][] = $value['ip_end'];
            $data[$key][] = $value['ip_mask'];
            $data[$key][] = $value['ip_gateway'];
            $data[$key][] = $value['ip_vlan_no'];
            $data[$key][] = $value['ip_secret_level'];
            $data[$key][] = $value['ip_areaname'];
            $data[$key][] = $value['ip_deptname'];
            $data[$key][] = $value['ip_purpose'];
            $data[$key][] = $value['ip_sum'];
        }
        $tableheader = array('序号', '起始IP', '结束IP', '子网掩码','网关','VLAN号', '密级','地区-楼宇','部门','用途','总数');
        excelExport($tableheader,$data,false);
    }

    /**
     * 通信站导出
     */
    public function txz_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Model      = M();
        $sql_select = "select *  from it_tongxinzhanjob du";

        if ("" != $queryparam['tongxinid']){
            $searchcontent = trim($queryparam['tongxinid']);
            $sql_select = $this->buildSql($sql_select,"du.txz_tongxinid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['taskid']){
            $searchcontent = trim($queryparam['taskid']);
            $sql_select = $this->buildSql($sql_select,"du.txz_taskid like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['username']){
            $searchcontent = trim($queryparam['username']);
            $sql_select = $this->buildSql($sql_select,"(du.txz_username like '%".$searchcontent."%' or du.txz_user like '%".$searchcontent."%')");
        }
        if ("" != $queryparam['workid']){
            $searchcontent = trim($queryparam['workid']);
            $sql_select = $this->buildSql($sql_select,"du.txz_workid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['status']){
            $searchcontent = trim($queryparam['status']);
            $sql_select = $this->buildSql($sql_select,"du.txz_status ='".$searchcontent."'");
        }
        if ("" != $queryparam['account']){
            $searchcontent = trim($queryparam['account']);
            $sql_select = $this->buildSql($sql_select,"du.txz_account like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['submittime']){
            $searchcontent = trim($queryparam['submittime']);
            $sql_select = $this->buildSql($sql_select,"du.txz_submittime >= '%".$searchcontent."%'");
        }
        if ("" != $queryparam['completetime']){
            $searchcontent = trim($queryparam['completetime']);
            $sql_select = $this->buildSql($sql_select,"du.txz_completetime <= '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        }

//        echo $sql_select;die;
        $Result = $Model->query($sql_select);

        $data = array();
        foreach($Result as $key=>$value){
            $data[$key][] = $key+1;
            $data[$key][] = $value['txz_tongxinid'];
            $data[$key][] = $value['txz_taskid'];
            $data[$key][] = $value['txz_username'];
            $data[$key][] = $value['txz_type'];
            $data[$key][] = $value['txz_submittime'];
            $data[$key][] = $value['txz_accountname'];
            $data[$key][] = $value['txz_status'];
            $data[$key][] = $value['txz_workid'];
            $data[$key][] = $value['txz_completetime'];
            $data[$key][] = $value['txz_taccountname'];
        }

        vendor("PHPExcel.PHPExcel");
        $excel = new \PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G','H','I','J','K');
        $tableheader = array('序号', '通信站派工单号', '任务单号', '申请人','协作类型','提交时间', '派工人','状态','一线工单','完成时间','通信站实施人');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }

        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }
        $write = new \PHPExcel_Writer_Excel2007($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="expexcel.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * 资产管理导出
     */
    public function terminal_exp(){//terminal_exp
        ini_set('memory_limit','512M');
        set_time_limit(0);
        ini_set('memory_limit','256M');
        $queryparam = I('get.');
//        print_r($queryparam);die;
        $Model  = M();
        $sql_select="
                select * from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype' and d.d_dictype='terminal'";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");

        if ("" != $queryparam['sbbm']){
            $searchcontent = trim($queryparam['sbbm']);
            $sql_select = $this->buildSql($sql_select,"du.zd_devicecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['ipaddess']){
            $searchcontent = trim($queryparam['ipaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_ipaddress like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['macaddess']){
            $searchcontent = trim($queryparam['macaddess']);
            $searchcontent = strtoupper($searchcontent);
            $sql_select = $this->buildSql($sql_select,"du.zd_macaddress like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['seqno']){
            $searchcontent = trim($queryparam['seqno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_seqno like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['sbtype']){
            $searchcontent = trim($queryparam['sbtype']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
        }
        if ((null != $queryparam['factory']) && ('null' != $queryparam['factory'])){
            $searchcontent = trim($queryparam['factory']);
            $sql_select = $this->buildSql($sql_select,"du.zd_factoryname ='".$searchcontent."'");
        }
        if ((null != $queryparam['model']) && ('null' != $queryparam['model'])){
            $searchcontent = trim($queryparam['model']);
            $sql_select = $this->buildSql($sql_select,"du.zd_modelnumber ='".$searchcontent."'");
        }
        if ("" != $queryparam['area']){
            $searchcontent = trim($queryparam['area']);
            $sql_select = $this->buildSql($sql_select,"du.zd_area ='".$searchcontent."'");
        }
        if ((null != $queryparam['building']) && ('null' != $queryparam['building'])){
            $searchcontent = trim($queryparam['building']);
            $sql_select = $this->buildSql($sql_select,"du.zd_belongfloor ='".$searchcontent."'");
        }
        if ("" != $queryparam['roomno']){
            $searchcontent = trim($queryparam['roomno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_roomno like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['usedept']){
            $searchcontent = trim($queryparam['usedept']);
            $searchcontent = D('Depart')->getDeptSubIdById($searchcontent);
            $sql_select = $this->buildSql($sql_select,"du.zd_usedeptid in (".$searchcontent.")");
        }
        if ("" != $queryparam['userman']){
            $searchcontent = trim($queryparam['userman']);
            $sql_select = $this->buildSql($sql_select,"du.zd_useman ='".$searchcontent."'");
        }
        if ("" != $queryparam['dutydept']){
            $searchcontent = trim($queryparam['dutydept']);
            $searchcontent = D('Depart')->getDeptSubIdById($searchcontent);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutydeptid in (".$searchcontent.")");
        }
        if ("" != $queryparam['dutyman']){
            $searchcontent = trim($queryparam['dutyman']);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutyman ='".$searchcontent."'");
        }
        if ("" != $queryparam['isavailable']){
            $searchcontent = trim($queryparam['isavailable']);
            $sql_select = $this->buildSql($sql_select,"du.zd_status ='".$searchcontent."'");
        }
        if ("" != $queryparam['secretlevel']){
            $searchcontent = trim($queryparam['secretlevel']);
            $sql_select = $this->buildSql($sql_select,"du.zd_secretlevel ='".$searchcontent."'");
        }
        if ("" != $queryparam['terminalname']){
            $searchcontent = trim($queryparam['terminalname']);
            $sql_select = $this->buildSql($sql_select,"du.zd_name like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['diskno']){
            $searchcontent = trim($queryparam['diskno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_harddiskseq like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['bubiaono']){
            $searchcontent = trim($queryparam['bubiaono']);
            $sql_select = $this->buildSql($sql_select,"du.zd_anecode like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        }
        // echo $sql_select;die;
        $Result = $Model->query($sql_select);
//        print_r($Result);die;
        $dictname = D('Dictionary')->getAllDictionaryInfo();

        foreach($Result as $key=> $value){
            $Result[$key]['zd_area'] = getDictname($value['zd_area']);
            $Result[$key]['zd_building'] = getDictname($value['zd_belongfloor']);
            if(!empty($value['zd_factoryname'])){
                $Result[$key]['factoryname'] = $dictname[$value['zd_factoryname']];
            }else{
                $Result[$key]['factoryname'] = '';
            }
            if(!empty($value['zd_modelnumber'])){
                $Result[$key]['model'] = $dictname[$value['zd_modelnumber']];
            }else{
                $Result[$key]['model'] = '';
            }
            switch($value['zd_secretlevel']){
                case '1':$Result[$key]['secretlevel'] = '秘密';break;
                case '2':$Result[$key]['secretlevel'] = '机密';break;
                case '3':$Result[$key]['secretlevel'] = '绝密';break;
                case '4':$Result[$key]['secretlevel'] = '内部';break;
                case '5':$Result[$key]['secretlevel'] = '非密';break;
                case '6':$Result[$key]['secretlevel'] = '无';break;
                default:$Result[$key]['secretlevel'] = '';break;
            }

            $mac = strtolower($value['zd_macaddress']);
            $portInfo = M('switchnewinfoT')->field('sw_interface,sw_ipaddress')->where("sw_macaddress='%s'",$mac)->find();
            if(!empty($portInfo)){
                $Result[$key]['zd_swport'] = $portInfo['sw_interface'];
                $Result[$key]['zd_swip']   = $portInfo['sw_ipaddress'];
            }else{
                $Result[$key]['zd_swport'] = '';
                $Result[$key]['zd_swip']   = '';
            }
            // 处理责任部门
            $dutydeptname = $value['zd_dutydeptname'];
            $dutydeptname = explode('-',$dutydeptname);
            $tmp = $dutydeptname[count($dutydeptname)-1];
            if(strpos($tmp,'中国航天') !== false){
                unset($dutydeptname[count($dutydeptname)-1]);
                $dutydeptname = array_reverse($dutydeptname);
                $Result[$key]['zd_dutydeptname'] = implode('-',$dutydeptname);
            }
        }

        // $header = array('序号','设备类型', '设备编码', '出厂编号', 'IP地址', 'MAC地址', '设备名称', '设备状态', '厂家', '型号', '密级','部标编码','使用人', '使用人账号','使用人部门','责任人','责任人账号','责任部门','地区', '楼宇', '房间号', '采购日期', '维保日期', '到保日期', '启用日期','管理方式','仪设台账', '保密台账','备注','硬盘序号','OS安装时间','是否隔离','是否安装干扰器','交换机IP端口信息','交换机IP地址');
        //		默认网关	主机型号	显示器型号						操作系统及版本		配置视频干扰器

        $header = array('设备编码', '设备名称','设备类型', '厂家', '型号', '密级', '出厂编号','部标编码','状态', 'IP地址','子网掩码', 'MAC地址','默认网关','显示器型号','操作系统及版本','配置视频干扰器','使用人','责任人','责任部门','地区', '楼宇', '房间号', '采购日期', '维保日期', '到保日期', '启用日期','管理方式','仪设台账', '保密台账','备注','硬盘序列号','操作系统安装日期','是否安装隔离插座','序列号','交换机IP端口信息','交换机IP地址');
        foreach($header as &$val){
            $val = iconv('utf-8','gbk',$val);
        }
        $filename = date('Ymd').time().rand(0,1000).'.csv';
        $filePath = 'Public/export/'.date('Y-m-d');
        if(!is_dir($filePath)) mkdir($filePath, 0777, true);

        $fp = fopen($filePath.'/'.$filename,'w');
        fputcsv($fp, $header);

        $Result = changeCoding($Result);

        foreach($Result as $key=>$value){
            $data = [];
            $data[] = $value['zd_devicecode']; //设备编码
            $data[] = $value['zd_name']; //设备名称
            $data[] = $value['d_dictname']; //设备类型
            $data[] = $value['factoryname']; //厂家
            $data[] = $value['model']; //型号
            $data[] = $value['secretlevel']; //密级
            $data[] = $value['zd_seqno']; //出厂编号
            $data[] = $value['zd_anecode']; //部标编码
            // $data[] = $value['zd_devicecode']; //设备编码
            $data[] = $value['zd_status']; //设备状态
            $data[] = $value['zd_ipaddress']; //IP地址
            $data[] = $value['zd_mask']; //子网掩码
            $data[] = $value['zd_macaddress']; //MAC地址
            $data[] = $value['zd_gateway']; //默认网关
            $data[] = $value['zd_display']; //显示器型号
            $data[] = $value['zd_os']; //操作系统及版本
            $data[] = $value['zd_isinstalljammer']; //配置视频干扰器
            $data[] = $value['zd_username'].'('.$value['zd_useman'].')'; //使用人
            $data[] = $value['zd_dutymanname'].'('.$value['zd_dutyman'].')'; //责任人
            $data[] = $value['zd_dutydeptname']; //责任部门
            $data[] = $value['zd_area']; //地区
            $data[] = $value['zd_building']; //楼宇
            $data[] = $value['zd_roomno']; //房间号
            $data[] = $value['zd_purchasetime']; //采购日期
            $data[] = $value['zd_maintainbegintime']; //维保日期
            $data[] = $value['zd_maintainendtime']; //到保日期
            $data[] = $value['zd_startusetime']; //启用日期
            $data[] = $value['zd_managetype']; //管理方式
            $data[] = $value['zd_devicebook']; //仪设台账
            $data[] = $value['zd_privacybook']; //保密台账
            $data[] = $value['zd_memo']; //备注
            $data[] = $value['zd_harddiskseq']; //硬盘序号
            $data[] = $value['zd_osinstalltime']; //OS安装时间
            $data[] = $value['zd_isisolate']; //是否隔离
            $data[] = $value['zd_atpid']; //序列号

            // $data[] = $value['zd_useman']."\t"; //使用人账号
            // $data[] = $value['zd_usedeptname']."\t"; //使用人部门
            // $data[] = $value['zd_dutyman']."\t"; //责任人账号
           
            // $data[] = $value['zd_isinstalljammer']."\t"; //是否安装干扰器
            $data[] = $value['zd_swport']."\t"; //交换机IP端口信息
            $data[] = $value['zd_swip']."\t"; //交换机IP地址

            fputcsv($fp, $data);
        }

        $fileFullPath = $filePath.'/'.$filename;
        $relativePath = $_SERVER['SCRIPT_NAME'];
        $pathData = explode('index.php', $relativePath);
        $fileRootPath = $pathData[0];
        header('location:'.$fileRootPath.$fileFullPath);
    }
    /**
     * 服务器导出
     */
    public function server_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Model = M('server');
        $where=[];
        $where[0]['server_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['servername'])))
        {
            $where[0]['lower(server_name)']=['like',"%".strtolower(trim($queryparam['servername']))."%"];
        }
        if(!empty(trim($queryparam['ipaddress'])))
        {
            $where[0]['server_ip']=['like',"%".trim($queryparam['ipaddress'])."%"];
        }
        if(!empty(trim($queryparam['zwym'])))
        {
            $where[0]['server_mask']=['like',"%".trim($queryparam['zwym'])."%"];
        }
        if(!empty(trim($queryparam['mrwg'])))
        {
            $where[0]['server_gateway']=['like',"%".trim($queryparam['mrwg'])."%"];
        }
        if(!empty(trim($queryparam['factory'])))
        {
            $where[0]['server_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['xh']))&&$queryparam['xh']!='null')
        {
            $where[0]['server_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(server_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['server_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['serveros'])))
        {

            $where[0]['server_os']=['like',"%".trim($queryparam['serveros'])."%"];
        }
        if(!empty(trim($queryparam['serverdatabase'])))
        {
            $where[0]['lower(server_database)']=['like',"%".strtolower(trim($queryparam['serverdatabase']))."%"];
        }
        if(!empty(trim($queryparam['area'])))
        {
            $where[0]['server_area']=['like',"%".trim($queryparam['area'])."%"];
        }
        if(!empty(trim($queryparam['building']))&&$queryparam['building']!='null')
        {
            $where[0]['server_building']=['like',"%".trim($queryparam['building'])."%"];
        }
        if(!empty(trim($queryparam['room'])))
        {
            $where[0]['server_room']=['like',"%".trim($queryparam['room'])."%"];
        }
        $Result=$Model->field('server_name,server_ip,server_mask,server_gateway,server_factory,server_model,server_usage,server_secret,server_os,server_database,server_building,server_room,server_area,server_num,server_dept,server_dutyman,server_dutymanid,server_osdate,server_disknum,server_macaddress,server_jifang,server_qiyong,server_status')->where($where)->select();
        //print_r($Result);die;
        $Count=$Model->where($where)->count();
        foreach($Result as $key=> &$value){
            $value['server_model']=$this->getdicname($value['server_model']);
            $value['server_factory']=$this->getdicname($value['server_factory']);
            $value['server_building']=$this->getdicname($value['server_building']);
            $value['server_area']=$this->getdicname($value['server_area']);
        }
//        foreach($Result as $key=> &$value){
//            $value['server_place']     = $value['server_building'].'-'.$value['server_room'];
//        }
        $this->recordLog('export', 'account',"导出$Count"."条",'server','');
        //服务器名称	IP地址	子网掩码	默认网关	厂家	型号	用途	密级	操作系统及版本	数据库及版本	楼宇	房间号	区域名	编号	所属部门	责任人	责任人账号	操作系统安装日期	硬盘序列号	MAC地址	机房区域	启用时间	使用情况
        $header = array('服务器名称','IP地址','子网掩码','默认网关','厂家','型号','用途','密级','操作系统及版本','数据库及版本','楼宇','房间号','区域名','编号','所属部门','责任人','责任人账号','操作系统安装日期','硬盘序列号','MAC地址','机房区域','启用时间','使用情况');
        if( $Count > 1000){
            csvExport($header, $Result, true);
        }else{
            excelExport($header, $Result, true);

        }
    }
    /**
     * 办公自动化
     */

    public function bgzdh_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Model = M('bgzdh');
        $where=[];
        $where[0]['bgzdh_atpstatus']=['exp','is null'];
        if(trim($queryparam['bgzdhcode']))
        {
            $where[0]['bgzdh_code']=['like',"%".strtolower(trim($queryparam['bgzdhcode']))."%"];
        }
        if(trim($queryparam['bgzdhname']))
        {
            $where[0]['bgzdh_name']=['like',"%".trim($queryparam['bgzdhname'])."%"];
        }
        if(trim($queryparam['bgzdhdept']))
        {
            $where[0]['bgzdh_dept']=['like',"%".trim($queryparam['bgzdhdept'])."%"];
        }
        if(trim($queryparam['bgzdhdidian']))
        {
            $where[0]['bgzdh_didian']=['like',"%".trim($queryparam['bgzdhdidian'])."%"];
        }
        if(trim($queryparam['factory']))
        {
            $where[0]['bgzdh_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(trim($queryparam['xh'])&&$queryparam['xh']!='null')
        {
            $where[0]['bgzdh_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(trim($queryparam['yt']))
        {
            $where[0]['bgzdh_usage']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(trim($queryparam['secretlevel']))
        {
            $where[0]['bgzdh_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(trim($queryparam['bgzdhdutyman']))
        {
            $where[0]['bgzdh_dutyman']=['like',"%".trim($queryparam['bgzdhdutyman'])."%"];
        }
        if(trim($queryparam['bgzdhip']))
        {
            $where[0]['bgzdh_ip']=['like',"%".trim($queryparam['bgzdhip'])."%"];
        }
        if(trim($queryparam['bgzdhmac']))
        {
            $where[0]['bgzdh_mac']=['like',"%".trim($queryparam['bgzdhmac'])."%"];
        }


        $Result=$Model->field('bgzdh_code,bgzdh_name,bgzdh_model,bgzdh_factory,bgzdh_secret,bgzdh_usage,bgzdh_dept,bgzdh_didian,bgzdh_dutyman,bgzdh_ip,bgzdh_mac,bgzdh_qiyong,bgzdh_status')->where($where)->select();
        $Count=$Model->where($where)->count();
        //$this->recordLog('export', 'account',"导出$Count"."条",'server','');
        $header = array('编号','名称','型号','厂家','密级','用途','所属部门','放置地点','责任人','IP地址（联网设备）','MAC地址（联网设备）','启用时间','使用情况');
        if( $Count > 1000){
            csvExport($header, $Result, false);
        }else{
            excelExport($header, $Result, false);

        }

    }

    /**
     * 存储设备
     */
    public function ccsb_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Model = M('ccsb');
        $where=[];
        $where[0]['ccsb_atpstatus']=['exp','is null'];
        if(trim($queryparam['ccsbname']))
        {
            $where[0]['ccsb_name']=['like',"%".trim($queryparam['ccsbname'])."%"];
        }
        if(trim($queryparam['ccsbdept']))
        {
            $where[0]['ccsb_dept']=['like',"%".trim($queryparam['ccsbdept'])."%"];
        }
        if(trim($queryparam['ccsbdidian']))
        {
            $where[0]['ccsb_didian']=['like',"%".trim($queryparam['ccsbdidian'])."%"];
        }
        if(trim($queryparam['factory']))
        {
            $where[0]['ccsb_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(trim($queryparam['xh']))
        {
            $where[0]['ccsb_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(trim($queryparam['yt']))
        {
            $where[0]['lower(ccsb_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(trim($queryparam['secretlevel']))
        {
            $where[0]['ccsb_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }

        $Result=$Model->field('ccsb_code,ccsb_name,ccsb_factory,ccsb_model,ccsb_secret,ccsb_usage,ccsb_dept,ccsb_didian,ccsb_status,ccsb_sn,ccsb_qiyong,ccsb_dutyman')->where($where)->select();
        $Count=$Model->where($where)->count();
        //$this->recordLog('export', 'account',"导出$Count"."条",'server','');
        $header = array('编号','名称','厂家','型号','密级','用途','所属部门','放置地点','使用情况','序列号','启用时间','责任人');
        if( $Count > 1000){
            csvExport($header, $Result, false);
        }else{
            excelExport($header, $Result, false);

        }

    }


    /**
     * 安全保密台账导出
     */
    public function secproducts_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');

        $Model = M('secproducts');
        $where=[];
        $where[0]['secproducts_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['secproductsname'])))
        {
            $where[0]['lower(secproducts_name)']=['like',"%".strtolower(trim($queryparam['secproductsname']))."%"];
        }
        if(!empty(trim($queryparam['ipaddress'])))
        {
            $where[0]['secproducts_ip']=['like',"%".trim($queryparam['ipaddress'])."%"];
        }
        if(!empty(trim($queryparam['factory']))&&$queryparam['factory']!='null')
        {
            $where[0]['secproducts_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(secproducts_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['amount'])))
        {

            $where[0]['secproducts_num']=['like',"%".trim($queryparam['amount'])."%"];
        }
        if(!empty(trim($queryparam['zsnum'])))
        {
            $where[0]['lower(secproducts_certsn)']=['like',"%".strtolower(trim($queryparam['zsnum']))."%"];
        }
        if(!empty(trim($queryparam['area'])))
        {
            $where[0]['secproducts_area']=['like',"%".trim($queryparam['area'])."%"];
        }
        if(!empty(trim($queryparam['building']))&&$queryparam['building']!='null')
        {
            $where[0]['secproducts_building']=['like',"%".trim($queryparam['building'])."%"];
        }
        if(!empty(trim($queryparam['room'])))
        {
            $where[0]['secproducts_room']=['like',"%".trim($queryparam['room'])."%"];
        }
        if(!empty(trim($queryparam['xh'])))
        {
            $where[0]['lower(secproducts_model)']=['like',"%".strtolower(trim($queryparam['xh']))."%"];
        }
        $Result=$Model->field('secproducts_name,secproducts_ip,secproducts_factory,secproducts_model,secproducts_building,secproducts_room,secproducts_num,secproducts_usage,secproducts_certsn,secproducts_area')->where($where)->select();
        //print_r($Result);die;
        $Count=$Model->where($where)->count();
        foreach($Result as $key=> &$value){
            $value['secproducts_building']=$this->getdicname($value['secproducts_building']);
            $value['secproducts_area']=$this->getdicname($value['secproducts_area']);
        }
        $this->recordLog('export', 'account',"导出$Count"."条",'secproducts','');
        $header = array('产品名称','IP地址','厂家','型号','楼宇','房间号','数量','用途','证书编号','区域名');
        if( $Count > 1000){
            csvExport($header, $Result, true);
        }else{
            excelExport($header, $Result, true);

        }
    }

    /**
     * 应用系统与信息资源导出
     */
    public function application_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');

        $Model = M('application');
        $where=[];
        $where[0]['application_atpstatus']=['exp','is null'];
        //系统名称 密级 安装位置 用途 访问方式 开发单位
        if(!empty(trim($queryparam['applicationname'])))
        {
            $where[0]['lower(application_name)']=['like',"%".strtolower(trim($queryparam['applicationname']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['application_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['applicationhost'])))
        {
            $where[0]['lower(application_host)']=['like',"%".strtolower(trim($queryparam['applicationhost']))."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(application_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['applicationusemode'])))
        {
            $where[0]['lower(application_usemode)']=['like',"%".strtolower(trim($queryparam['applicationusemode']))."%"];
        }
        if(!empty(trim($queryparam['applicationdeveloper'])))
        {
            $where[0]['lower(application_developer)']=['like',"%".strtolower(trim($queryparam['applicationdeveloper']))."%"];
        }

//        if(!empty(trim($queryparam['applicationinfo'])))
//        {
//            $where[0]['lower(application_info)']=['like',"%".strtolower(trim($queryparam['applicationinfo']))."%"];
//        }
//
//        if(!empty(trim($queryparam['applicationmode'])))
//        {
//            $where[0]['lower(application_mode)']=['like',"%".strtolower(trim($queryparam['applicationmode']))."%"];
//        }
//
//        if(!empty(trim($queryparam['applicationuserscope'])))
//        {
//            $where[0]['lower(application_userscope)']=['like',"%".strtolower(trim($queryparam['applicationuserscope']))."%"];
//        }
//
//        if(!empty(trim($queryparam['applicationaloginmode'])))
//        {
//            $where[0]['lower(application_aloginmode)']=['like',"%".strtolower(trim($queryparam['applicationaloginmode']))."%"];
//        }
//        if(!empty(trim($queryparam['applicationloginmode'])))
//        {
//            $where[0]['lower(application_loginmode)']=['like',"%".strtolower(trim($queryparam['applicationloginmode']))."%"];
//        }
//        if(!empty(trim($queryparam['applicationqx'])))
//        {
//            $where[0]['lower(application_accessauthority)']=['like',"%".strtolower(trim($queryparam['applicationqx']))."%"];
//        }

        $Result=$Model->field('application_name,application_secret,application_info,application_host,application_mode,application_usage,application_userscope,application_usemode,application_aloginmode,application_loginmode,application_accessauthority,application_developer')->where($where)
            ->select();
//        print_r($Model->);die;
        $Count=$Model->where($where)->count();
        //系统名称	密级	信息资源	安装位置	存放形式	用途	用户范围	访问方式	管理员鉴别方式	普通用户鉴别方式	访问权限	开发单位
        $this->recordLog('export', 'account',"导出$Count"."条",'application','');
        $header = array('系统名称','密级','信息资源','安装位置','存放方式','用途','用户范围','访问方式','管理员鉴别方式','普通用户鉴别方式','访问权限','开发单位');
        if( $Count > 1000){
            csvExport($header, $Result, true);
        }else{
            excelExport($header, $Result, true);

        }
    }

    /**
     *涉密单机台账导出
     */
    public function smdj_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');

        $Model = M('smdj');
        $where=[];
        $where[0]['smdj_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['smdjname'])))
        {
            $where[0]['lower(smdj_name)']=['like',"%".strtolower(trim($queryparam['smdjname']))."%"];
        }
        if(!empty(trim($queryparam['factory'])))
        {
            $where[0]['smdj_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['xh'])))
        {
            $where[0]['smdj_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(smdj_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['smdj_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['didian'])))
        {
            $where[0]['smdj_didian']=['like',"%".trim($queryparam['didian'])."%"];
        }
        if(!empty(trim($queryparam['dept'])))
        {
            $where[0]['smdj_dept']=['like',"%".trim($queryparam['dept'])."%"];
        }

        $Result=$Model->field('smdj_name,smdj_code,smdj_factory,smdj_model,smdj_secret,smdj_usage,smdj_dept,smdj_didian,smdj_dutyman,smdj_status,smdj_ps,smdj_area,smdj_sn,smdj_qiyong,smdj_osdate,smdj_disknum')->where($where)
            ->select();
//        print_r($Model->);die;
        $Count=$Model->where($where)->count();
        //$this->recordLog('export', 'account',"导出$Count"."条",'application','');
        $header = array("编号","名称","厂家","型号","密级","用途","所属部门","放置地点","责任人","使用情况","其他说明","区域","设备序列号","启用时间","操作系统安装日期","硬盘序列号");
        if( $Count > 1000){
            csvExport($header, $Result, true);
        }else{
            excelExport($header, $Result, true);

        }
    }

    /**
     *涉密单机台账导出
     */
    public function sxsb_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');

        $Model = M('sxsb');
        $where=[];
        $where[0]['sxsb_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['sxsbname'])))
        {
            $where[0]['lower(sxsb_name)']=['like',"%".strtolower(trim($queryparam['sxsbname']))."%"];
        }
        if(!empty(trim($queryparam['factory'])))
        {
            $where[0]['sxsb_factory']=['like',"%".trim($queryparam['factory'])."%"];
        }
        if(!empty(trim($queryparam['xh'])))
        {
            $where[0]['sxsb_model']=['like',"%".trim($queryparam['xh'])."%"];
        }
        if(!empty(trim($queryparam['yt'])))
        {
            $where[0]['lower(sxsb_usage)']=['like',"%".strtolower(trim($queryparam['yt']))."%"];
        }
        if(!empty(trim($queryparam['secretlevel'])))
        {
            $where[0]['sxsb_secret']=['like',"%".trim($queryparam['secretlevel'])."%"];
        }
        if(!empty(trim($queryparam['didian'])))
        {
            $where[0]['sxsb_didian']=['like',"%".trim($queryparam['didian'])."%"];
        }
        if(!empty(trim($queryparam['dept'])))
        {
            $where[0]['sxsb_dept']=['like',"%".trim($queryparam['dept'])."%"];
        }

        $Result=$Model->field('sxsb_name,sxsb_code,sxsb_factory,sxsb_model,sxsb_secret,sxsb_usage,sxsb_dept,sxsb_didian,sxsb_dutyman,sxsb_status,sxsb_sn,sxsb_qiyong')->where($where)
            ->select();
//        print_r($Model->);die;
        $Count=$Model->where($where)->count();
        //$this->recordLog('export', 'account',"导出$Count"."条",'application','');
        $header = array("编号","名称","厂家","型号","密级","用途","所属部门","放置地点","责任人","使用情况","设备序列号","启用时间");
        if( $Count > 1000){
            csvExport($header, $Result, true);
        }else{
            excelExport($header, $Result, true);

        }
    }
    /**
     * 邮件漫游登录导出
     */
    public function roam_exp(){
        ini_set('memory_limit','1024M');
        set_time_limit(0);
        $queryparam = I('get.');
        $res        = D('Roam')->getRoamInfo($queryparam,1);
        $Result     = $res[0];
        $tableheader = array('被登录帐号','被登录人姓名','被登录人部门','登录时间','登录设备IP地址','登录设备责任人帐号','登录设备责任人姓名','登录设备责任人部门','系统名称','登录类型','设备类型','分类');
        csvExport($tableheader,$Result,true);
    }

    /**
     * 公用计算机管理导出
     */
    public function terminalhy_exp(){
        set_time_limit(0);
        ini_set('memory_limit','256M');
        $queryparam = I('get.');
//        print_r($queryparam);die;
        $Model  = M();
        $sql_select="
                select * from it_terminal_hy du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype' and d.d_dictype='terminal'";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");

        if ("" != $queryparam['ipaddess']){
            $searchcontent = trim($queryparam['ipaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_ipaddress like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['area']){
            $searchcontent = trim($queryparam['area']);
            $sql_select = $this->buildSql($sql_select,"du.zd_area ='".$searchcontent."'");
        }
        if ((null != $queryparam['building']) && ('null' != $queryparam['building'])){
            $searchcontent = trim($queryparam['building']);
            $sql_select = $this->buildSql($sql_select,"du.zd_belongfloor like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['secretlevel']){
            $searchcontent = trim($queryparam['secretlevel']);
            $sql_select = $this->buildSql($sql_select,"du.zd_secretlevel ='".$searchcontent."'");
        }
        if ("" != $queryparam['terminalname']){
            $searchcontent = trim($queryparam['terminalname']);
            $sql_select = $this->buildSql($sql_select,"du.zd_name like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        }
//      echo $sql_select;die;
        $Result = $Model->query($sql_select);
//        print_r($Result);die;
        $dictname = D('Dictionary')->getAllDictionaryInfo();

        foreach($Result as $key=> $value){
            $Result[$key]['zd_area'] = getDictname($value['zd_area']);
            $Result[$key]['zd_building'] = getDictname($value['zd_belongfloor']);
            if(!empty($value['zd_factoryname'])){
                $Result[$key]['factoryname'] = $dictname[$value['zd_factoryname']];
            }else{
                $Result[$key]['factoryname'] = '';
            }
            if(!empty($value['zd_modelnumber'])){
                $Result[$key]['model'] = $dictname[$value['zd_modelnumber']];
            }else{
                $Result[$key]['model'] = '';
            }
            switch($value['zd_secretlevel']){
                case '1':$Result[$key]['secretlevel'] = '秘密';break;
                case '2':$Result[$key]['secretlevel'] = '机密';break;
                case '3':$Result[$key]['secretlevel'] = '绝密';break;
                case '4':$Result[$key]['secretlevel'] = '内部';break;
                case '5':$Result[$key]['secretlevel'] = '非密';break;
                case '6':$Result[$key]['secretlevel'] = '无';break;
                default:$Result[$key]['secretlevel'] = '';break;
            }

            $mac = strtolower($value['zd_macaddress']);
            $portInfo = M('switchnewinfoT')->field('sw_interface,sw_ipaddress')->where("sw_macaddress='%s'",$mac)->find();
            if(!empty($portInfo)){
                $Result[$key]['zd_swport'] = $portInfo['sw_interface'];
                $Result[$key]['zd_swip']   = $portInfo['sw_ipaddress'];
            }else{
                $Result[$key]['zd_swport'] = '';
                $Result[$key]['zd_swip']   = '';
            }
        }

        $header = array('设备编码', '设备名称','设备类型', '厂家', '型号', '密级', '出厂编号','部标编码', '状态', 'IP地址', 'MAC地址','责任人','责任部门','地区', '楼宇', '房间号', '启用日期','备注');
        foreach($header as &$val){
            $val = iconv('utf-8','gbk',$val);
        }
        $filename = date('Ymd').time().rand(0,1000).'.csv';
        $filePath = 'Public/export/'.date('Y-m-d');
        if(!is_dir($filePath)) mkdir($filePath, 0777, true);

        $fp = fopen($filePath.'/'.$filename,'w');
        fputcsv($fp, $header);

        $Result = changeCoding($Result);

//        $this->recordLoghy('export', 'tmnhy',"导出数据".count($Result)."条",'terminal_hy','');
        foreach($Result as $key=>$value){
            $data = [];
            $data[] = $value['zd_devicecode']."\t"; //设备编码
            $data[] = $value['zd_name']."\t"; //设备名称
            $data[] = $value['d_dictname']."\t"; //设备类型
            $data[] = $value['factoryname']."\t"; //厂家
            $data[] = $value['model']."\t"; //型号
            $data[] = $value['secretlevel']."\t"; //密级
            $data[] = $value['zd_seqno']."\t"; //出厂编号
            $data[] = $value['zd_anecode']."\t"; //部标编码
            $data[] = $value['zd_status']."\t"; //设备状态
            $data[] = $value['zd_ipaddress']."\t"; //IP地址
            $data[] = $value['zd_macaddress']."\t"; //MAC地址
            $data[] = $value['zd_dutymanname']."\t"; //责任人
            $data[] = $value['zd_dutydeptname']."\t"; //责任部门
            $data[] = $value['zd_area']."\t"; //地区
            $data[] = $value['zd_building']."\t"; //楼宇
            $data[] = $value['zd_roomno']."\t"; //房间号
            $data[] = $value['zd_startusetime']."\t"; //启用日期
            $data[] = $value['zd_memo']."\t"; //备注
            fputcsv($fp, $data);
        }
        $fileFullPath = $filePath.'/'.$filename;
        $relativePath = $_SERVER['SCRIPT_NAME'];
        $pathData = explode('index.php', $relativePath);
        $fileRootPath = $pathData[0];
        header('location:'.$fileRootPath.$fileFullPath);
    }

    public function getareaname($id){
        $building =M('dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }

    public function getbuildingname($id){
        $building =M('dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getdicname($id){
        $building =M('dictionary')->where("d_atpid='%s' and d_atpstatus is null",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
}

