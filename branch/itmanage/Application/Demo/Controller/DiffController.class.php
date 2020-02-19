<?php
namespace Demo\Controller;
use Think\Controller;
class DiffController extends BaseController
{
    /**
     * 交换机差异数据表
     * 交换机端口上绑定了MAC地址，而该地址在资产库（Terminal表）中不存在。
     */
    function switchesDiff(){
        $this->display();
    }

    function switchesData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $diffInfo   = D('Switchport')->switchesData($queryparam);
        echo json_encode(array( 'total' => $diffInfo[1],'rows' => $diffInfo[0]));
    }

    /**
     * 域控差异数据表
     * 域控中“登陆到”中的计算机名和对应域帐号，与资产库中的计算机名（ZD_NAME）和使用人域帐号（ZD_DUTYMAN）的差异数据。
     */
    function AdDiff(){
        $this->display();
    }

    function AdData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $diffInfo   = D('Adinfo')->AdData($queryparam);
         echo $diffInfo;
    }

    /**
     * 责任人部门处室差异表
     * 比对域控中计算机的使用人部门、处室与资产库中的使用人部门、处室（ZD_DUTYDEPTNAME表）中的差异
     */
    function dutyDeptDiff(){
        $this->display();
    }

    function dutyDeptDiffData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $diffInfo = D('Terminal')->dutyDeptDiffData($queryparam);
        $diffInfo = array_values($diffInfo);
        $limit = $queryparam['limit'];
        $offset = empty($queryparam['offset']) ? 0:$queryparam['offset'];
//        print_r($adDiffData);die;
        $diffInfo = array_values($diffInfo);
        if(!empty($diffInfo)){
            $list   = array_slice($diffInfo, $offset, $limit);
            $result = array(
                'total' => count($diffInfo),
                'rows' => $list
            );
        }
        if(empty($result)){
            exit($this->makeStandResult(-1, '获取数据失败'));
        }else{
            exit(json_encode($result));
        }
    }

    /**
     * 使用人部门信息差异表
     */
    function userDeptDiff(){
        $this->display();
    }

    function userDeptDiffData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $diffInfo = D('Terminal')->userDeptDiffData($queryparam);
        $diffInfo = array_values($diffInfo);
        $limit = $queryparam['limit'];
        $offset = empty($queryparam['offset']) ? 0:$queryparam['offset'];
//        print_r($adDiffData);die;
        $diffInfo = array_values($diffInfo);
        if(!empty($diffInfo)){
            $list   = array_slice($diffInfo, $offset, $limit);
            $result = array(
                'total' => count($diffInfo),
                'rows' => $list
            );
        }else{
            $result = array(
                'total' => 0,
                'rows' => []
            );
        }
        if(empty($result)){
            exit($this->makeStandResult(-1, '获取数据失败'));
        }else{
            exit(json_encode($result));
        }
    }

    /**
     * 交换机端口接入数量为0表
     * 资产库中的某一MAC地址（ZD_MACADDRESS表）在交换机端口上无绑定信息
     */
    public function portInZero(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }

    function portInZeroData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $diffInfo   = D('Terminal')->portInZeroData($queryparam);
        $limit      = $queryparam['limit'];
        $offset     = empty($queryparam['offset']) ? 0:$queryparam['offset'];

        if(!empty($diffInfo)){
            $list   = array_slice($diffInfo, $offset, $limit);
            $result = array(
                'total' => count($diffInfo),
                'rows' => $list
            );
        }else{
            $result = array(
                'total' => 0,
                'rows' => []
            );
        }
        if(empty($result)){
            exit($this->makeStandResult(-1, '获取数据失败'));
        }else{
            exit(json_encode($result));
        }
    }

    /**
     * 交换机端口接入数量大于1表
     * 资产库中的某一MAC地址（ZD_MACADDRESS表）在交换机端口上有多个绑定信息
     */
    public function portInOne(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }

    function portInOneData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
//        $queryparam = I('get.');
        $diffInfo   = D('Terminal')->portInOneData($queryparam);
//        print_r($diffInfo);
        $limit      = $queryparam['limit'];
        $offset     = empty($queryparam['offset']) ? 0:$queryparam['offset'];

        if(!empty($diffInfo)){
            $list   = array_slice($diffInfo, $offset, $limit);
            $result = array(
                'total' => count($diffInfo),
                'rows' => $list
            );
        }else{
            $result = array(
                'total' => 0,
                'rows' => []
            );
        }
        if(empty($result)){
            exit($this->makeStandResult(-1, '获取数据失败'));
        }else{
            exit(json_encode($result));
        }
    }

    public function getbuilding(){
        $Model = M('dictionary');
        $area= $_POST['area'];
        $buildinglist = $Model->where("d_parentid='%s'",$area)->field('d_dictname,d_atpid')->select();
        echo json_encode($buildinglist);
    }

    public function oldMacInfo(){
        $mac = I('get.macaddress');
        if(empty($mac) || (strpos($mac,'-') !== false)){
            echo "<script>alert('MAC地址错误！');</script>";
            return false;
        }else{
            $mac        = strtolower($mac);
            $macoldinfo = M('switcholdinfo')->where("swo_macaddress = '".$mac."'")->select();
            $this->assign('macinfo',$macoldinfo);
        }
        $this->display('oldmacinfo');
    }

    public function gtOneMacInfo(){
        $mac = I('get.macaddress');
        $this->assign('mac',$mac);
        $this->display();
    }

    function gtOneMacInfoData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $macaddress = strtolower(trim($queryparam['macaddress']));
        $result     = D('Switchnewinfo')->gtOneMacInfoData($macaddress,$queryparam);
        echo $result;
    }

    /**
     *  交换机端口DOWN表
     * （交换机端口被管理员DOWN却有MAC地址绑定）:查找switchport表的status和mac字段
     */
    public function SWPortDown(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }

    function SWPortDownData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
//        $queryparam = I('get.');
        $diffInfo   = D('Switchnewinfo')->SWPortDownData($queryparam);

        if(!empty($diffInfo)){
            $list   = $diffInfo[0];
            $result = array(
                'total' => $diffInfo[1],
                'rows' => $list
            );
        }else{
            $result = array(
                'total' => 0,
                'rows' => []
            );
        }
        if(empty($result)){
            exit($this->makeStandResult(-1, '获取数据失败'));
        }else{
            exit(json_encode($result));
        }
    }
    /**
     *  交换机端口UP表
     * （交换机端口为UP但没有MAC地址绑定）：查找switchport表的status和mac字段
     */
    public function SWPortUp(){
        $Datas = D('Dictionary')->assignsbtype();
        $this->assign('ds_sbtype',$Datas[0]);
        $this->assign('ds_area',$Datas[1]);
        $this->assign('ds_miji',$Datas[2]);
        $this->assign('ds_bmtz',$Datas[3]);
        $this->assign('ds_zctz',$Datas[4]);
        $this->display();
    }

    function SWPortUpData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
//        $queryparam = I('get.');
        $diffInfo   = D('Switchnewinfo')->SWPortUpData($queryparam);

        if(!empty($diffInfo)){
            $list   = $diffInfo[0];
            $result = array(
                'total' => $diffInfo[1],
                'rows' => $list
            );
        }else{
            $result = array(
                'total' => 0,
                'rows' => []
            );
        }
        if(empty($result)){
            exit($this->makeStandResult(-1, '获取数据失败'));
        }else{
            exit(json_encode($result));
        }
    }

    public function bdywDiff(){
        $this->display();
    }
    public function zrywDiff(){
        $this->display();
    }

    public function ywscanDiff(){
        $this->display();
    }

    function bdywData(){
        set_time_limit(0);
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $starttime  = trim($queryparam['starttime'])?str_replace("-","",trim($queryparam['starttime'])):'20150101';
        $endtime    = trim($queryparam['endtime'])?str_replace("-","",trim($queryparam['endtime'])):date('Ymd');
        // 撤销入网
        $delFormType = [20,122,21,503];
        // 变更入网
        $updFormType = [117,308,15,502];
        // 不比设备名称的列
        $noComputerName = [110,18,501,119,122,308];
        $formType    = C('FORMTYPE');
        $where      = [];
        $macaddressSearch = strtoupper(trim($queryparam['macaddress']));
        if($macaddressSearch){
            $where[2]['y.MACAddress']      = ['like',"%$macaddressSearch%"];
            $where[2]['y.SKMACAddress']    = ['like',"%$macaddressSearch%"];
            $where[2]['y.OldMACAddress']   = ['like',"%$macaddressSearch%"];
            $where[2]['y.OldSKMACAddress'] = ['like',"%$macaddressSearch%"];
            $where[2]['_logic'] = 'or';
        }
        $ipaddressSearch = trim($queryparam['ipaddress']);
        if(trim($queryparam['ipaddress'])){
            $where[3]['y.IPAddress']      = ['like',"%".$ipaddressSearch."%"];
            $where[3]['y.SKIPAddress']    = ['like',"%".$ipaddressSearch."%"];
            $where[3]['y.OldIPAddress']   = ['like',"%".$ipaddressSearch."%"];
            $where[3]['y.OldSKIPAddress'] = ['like',"%".$ipaddressSearch."%"];
            $where[3]['_logic'] = 'or';
        }
        if(trim($queryparam['formno'])) $where[0]['y.FormNo'] = ['like',"%".trim($queryparam['formno'])."%"];
        if(trim($queryparam['userman'])){
            $where[1]['y.ChargePersonADID'] = ['like',"%".trim($queryparam['userman'])."%"];
            $where[1]['y.ChargePerson'] = ['like',"%".trim($queryparam['userman'])."%"];
            $where[1]['_logic'] = 'or';
        }
        $bdModel    = M('view_yunwei y',' ','BD_CONFIG');

        $bdData     = $bdModel->field("y.*")->join("INNER JOIN (SELECT MACAddress,SKMACAddress,OldMACAddress,OldSKMACAddress,max(FlowFinishTime) FlowFinishTime FROM view_yunwei WHERE CONVERT(varchar(100), FlowFinishTime, 112) <= '$endtime' AND CONVERT(varchar(100), FlowFinishTime, 112) >= '$starttime' GROUP BY MACAddress,SKMACAddress,OldMACAddress,OldSKMACAddress) s  ON y.MACAddress = s.MACAddress AND y.FlowFinishTime = s.FlowFinishTime")->where($where)->order($queryparam['sort']." ".$queryparam['sortOrder'])->cache(true)->select();
//        echo $bdModel->_sql();die;
        $mac        = removeArrKeyNotNull($bdData,'macaddress',true);
        $skmac      = removeArrKeyNotNull($bdData,'skmacaddress',true);
        $oldmac     = removeArrKeyNotNull($bdData,'oldmacaddress',true);
        $oldskmac   = removeArrKeyNotNull($bdData,'oldskmacaddress',true);
        $macs       = array_merge($mac,$skmac,$oldmac,$oldskmac);
        $macs       = array_unique($macs);
       // dump($macs);die;

        $where = [];
        $where[0]['zd_atpstatus']  = ['exp',' is null'];
        $ywDatas         = [];
        $ywData          = [];
        if(count($macs)>=100){
            $begin = 0;
            while(count($macs)>=$begin){
                $macsTmp = array_slice($macs,$begin,100);
                $where[0]['upper(zd_macaddress)'] = ['in',$macsTmp];
                $tmnInfoTmp = M('terminal')->field('upper(zd_macaddress) zd_macaddress,zd_ipaddress,zd_useman,zd_name,zd_area,zd_belongfloor,zd_roomno')->where($where)->select();
                $ywDatas = array_merge($ywDatas,$tmnInfoTmp);
                $begin += 100;
            }
        }else if(count($macs)>0){
            $where[0]['upper(zd_macaddress)'] = ['in',$macs];
            $ywDatas = M('terminal')->field('upper(zd_macaddress) zd_macaddress,zd_ipaddress,zd_useman,zd_name,zd_area,zd_belongfloor,zd_roomno')->where($where)->select();
        }
        foreach($ywDatas as $val){
            $macaddressYW = trim($val['zd_macaddress']);
            $macaddressYW = strtoupper($macaddressYW);
            if(!empty($ywData[$macaddressYW])){
                $ywData[$macaddressYW] = 1;
            }else{
                $ywData[$macaddressYW] = $val;
            }
        }
        $data     = [];
        $num      = 0;
        $compared = [];
        foreach($bdData as $key=>$val){
            $formsign         = $val['formsign'];
            $macaddress       = trim($val['macaddress']);
            $ipaddress        = trim($val['ipaddress']);
            $skmacaddress     = trim($val['skmacaddress']);
            $oldmacaddress    = trim($val['oldmacaddress']);
            $oldskmacaddress  = trim($val['oldskmacaddress']);
            $chargePerson  = trim($val['chargepersonadid']);
            $chargePerson  = substr($chargePerson,0,strpos($chargePerson,"@"));
            if(in_array($formsign,$delFormType)){ // 表单类型为撤网
                $isMatch       = true;
                if($macaddressSearch && strpos($macaddress,$macaddressSearch) === false) $isMatch = false;
                if($ipaddressSearch && strpos($ipaddress,$ipaddressSearch) === false) $isMatch = false;
                if($isMatch && $macaddress && !in_array($macaddress,$compared)){
                    if($ywData[$macaddress]){
                        // 若表单系统中的mac为删除数据，而在运维4.0中仍能被查询到，则差异表中整条数据显示为绿色
                        $tmp = [];
                        $tmp['diffSign']      = 'success';
                        $tmp['macaddress']    = $macaddress;
                        $tmp['ipaddress']     = $ipaddress;
                        $tmp['formno']        = trim($val['formno']);
                        $tmp['formsign']      = $formType[$formsign];
                        $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                        $tmp['zd_macaddress'] = $macaddress;
                        $tmp['jharea']        = trim($val['jharea']);
                        $tmp['buildingname']  = trim($val['buildingname']);
                        $tmp['roomno']        = trim($val['roomno']);
                        $data[$num]           = $tmp;
                        $num ++;
                    }
                }
                if($formsign == '122'){ //  表单为涉密网设备撤销入网申请表(多了刷卡器的ip，mac删除)
                    if($skmacaddress){
                        $skipaddress   = $val['skipaddress'];
                        $isMatch       = true;
                        if($macaddressSearch && strpos($skmacaddress,$macaddressSearch) === false) $isMatch = false;
                        if($ipaddressSearch && strpos($skipaddress,$ipaddressSearch) === false) $isMatch = false;
                        if($isMatch && $skmacaddress && !in_array($skmacaddress,$compared)){
                            if($ywData[$skmacaddress]){
                                // 若表单系统中的mac为删除数据，而在运维4.0中仍能被查询到，则差异表中整条数据显示为绿色
                                $tmp = [];
                                $tmp['diffSign']      = 'success';
                                $tmp['macaddress']    = $skmacaddress;
                                $tmp['ipaddress']     = $skipaddress;
                                $tmp['formno']        = trim($val['formno']);
                                $tmp['formsign']      = $formType[$formsign];
                                $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                                $tmp['zd_macaddress'] = $skmacaddress;
                                $tmp['jharea']        = trim($val['jharea']);
                                $tmp['buildingname']  = trim($val['buildingname']);
                                $tmp['roomno']        = trim($val['roomno']);
                                $data[$num]           = $tmp;
                                $num ++;
                            }
                        }
                    }
                }
            }else{
                if(in_array($formsign,$updFormType)){ // 表单类型为变更
                    if(!empty($oldmacaddress) && ($oldmacaddress != $macaddress) && !empty($macaddress)){
                        $oldipaddress   = $val['oldipaddress'];
                        $isMatch       = true;
                        if($macaddressSearch && strpos($oldmacaddress,$macaddressSearch) === false) $isMatch = false;
                        if($ipaddressSearch && strpos($oldipaddress,$ipaddressSearch) === false) $isMatch = false;
                        if($isMatch && $oldmacaddress && !in_array($oldmacaddress,$compared)){
                            if($ywData[$oldmacaddress]){
                                // 若表单系统中的mac为删除数据，而在运维4.0中仍能被查询到，则差异表中整条数据显示为绿色
                                $tmp = [];
                                $tmp['diffSign']      = 'success';
                                $tmp['macaddress']    = $oldmacaddress;
                                $tmp['ipaddress']     = $oldipaddress;
                                $tmp['formno']        = trim($val['formno']);
                                $tmp['formsign']      = $formType[$formsign];
                                $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                                $tmp['zd_macaddress'] = $oldmacaddress;
                                $tmp['jharea']        = trim($val['jharea']);
                                $tmp['buildingname']  = trim($val['buildingname']);
                                $tmp['roomno']        = trim($val['roomno']);
                                $data[$num]           = $tmp;
                                $num ++;
                            }
                        }
                    }
                    if($formsign == '308'){ //  表单为涉密网设备变更入网申请(多了刷卡器的ip，mac删除)
                        if(!empty($oldskmacaddress) && ($oldskmacaddress != $skmacaddress) && !empty($skmacaddress)){
                            $oldskipaddress   = $val['oldskipaddress'];
                            $isMatch       = true;
                            if($macaddressSearch && strpos($oldskmacaddress,$macaddressSearch) === false) $isMatch = false;
                            if($ipaddressSearch && strpos($oldskipaddress,$ipaddressSearch) === false) $isMatch = false;
                            if($isMatch && $oldskmacaddress && !in_array($oldskmacaddress,$compared)){
                                if($ywData[$oldskmacaddress]){
                                    // 若表单系统中的mac为删除数据，而在运维4.0中仍能被查询到，则差异表中整条数据显示为绿色
                                    $tmp = [];
                                    $tmp['diffSign']      = 'success';
                                    $tmp['macaddress']    = $oldskmacaddress;
                                    $tmp['ipaddress']     = $oldskipaddress;
                                    $tmp['formno']        = trim($val['formno']);
                                    $tmp['formsign']      = $formType[$formsign];
                                    $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                                    $tmp['zd_macaddress'] = $oldskmacaddress;
                                    $tmp['jharea']        = trim($val['jharea']);
                                    $tmp['buildingname']  = trim($val['buildingname']);
                                    $tmp['roomno']        = trim($val['roomno']);
                                    $data[$num]           = $tmp;
                                    $num ++;
                                }
                            }
                        }
                    }
                }
                $isMatch       = true;
                if($macaddressSearch && strpos($macaddress,$macaddressSearch) === false) $isMatch = false;
                if($ipaddressSearch && strpos($ipaddress,$ipaddressSearch) === false) $isMatch = false;
                if($isMatch && $macaddress && !in_array($macaddress,$compared)) {
                    if (!is_array($ywData[$macaddress])) {
                        // 若表单系统中的mac在运维4.0中不止一条数据，则差异表中整条数据显示为红色
                        // 若表单系统中的mac在运维系统中不存在，则认为是新增数据，差异表中整条数据均显示红色
                        $tmp = [];
                        $tmp['diffSign']      = 'danger';
                        $tmp['macaddress']    = $macaddress;
                        $tmp['ipaddress']     = $ipaddress;
                        $tmp['formno']        = trim($val['formno']);
                        $tmp['formsign']      = $formType[$formsign];
                        $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                        $tmp['zd_macaddress'] = $macaddress;
                        $tmp['jharea']        = trim($val['jharea']);
                        $tmp['buildingname']  = trim($val['buildingname']);
                        $tmp['roomno']        = trim($val['roomno']);
                        $data[$num]           = $tmp;
                        $num ++;
                    } else {
                        $diffSign = [];
                        $zd_area = getDictname(trim($ywData[$macaddress]['zd_area']));
                        $zd_belongfloor = getDictname(trim($ywData[$macaddress]['zd_belongfloor']));
                        // ip地址
                        if (trim($val['ipaddress']) != trim($ywData[$macaddress]['zd_ipaddress'])) {
                            $diffSign[] = 'ipaddress';
                        }
                        // 使用人账号
                        if ($chargePerson != trim($ywData[$macaddress]['zd_useman'])) {
                            $diffSign[] = 'chargepersons';
                        }
                        // 设备名称
                        if (!in_array($formsign, $noComputerName)) {
                            if (trim($val['computername']) != trim($ywData[$macaddress]['zd_name'])) {
                                $diffSign[] = 'computername';
                            }
                        }
                        // 地区
                        if (trim($val['jharea']) != $zd_area) {
                            $diffSign[] = 'jharea';
                        }
                        // 楼宇
                        if (trim($val['buildingname']) != $zd_belongfloor) {
                            $diffSign[] = 'buildingname';
                        }
                        // 房间号
                        if (trim($val['roomno']) != trim($ywData[$macaddress]['zd_roomno'])) {
                            $diffSign[] = 'roomno';
                        }
                        if ($diffSign) {
                            $tmp = [];
                            $tmp['diffSigns']     = $diffSign;
                            $tmp['macaddress']    = $macaddress;
                            $tmp['ipaddress']     = $ipaddress;
                            $tmp['formno']        = trim($val['formno']);
                            $tmp['formsign']      = $formType[$formsign];
                            $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                            $tmp['zd_macaddress'] = $macaddress;
                            $tmp['jharea']        = trim($val['jharea']);
                            $tmp['buildingname']  = trim($val['buildingname']);
                            $tmp['roomno']        = trim($val['roomno']);
                            $data[$num]           = $tmp;
                            $num ++;
                        }
                    }
                }
                if($formsign == '119') { //  表单为涉密网设备入网申请表(多了刷卡器的ip，mac判断)
                    $skipaddress   = trim($val['skipaddress']);
                    $isMatch       = true;
                    if($macaddressSearch && strpos($skmacaddress,$macaddressSearch) === false) $isMatch = false;
                    if($ipaddressSearch && strpos($skipaddress,$ipaddressSearch) === false) $isMatch = false;
                    if($isMatch && $skmacaddress && !in_array($skmacaddress,$compared)) {
                        if (!is_array($ywData[$skmacaddress])) {
                            // 若表单系统中的mac在运维4.0中不止一条数据，则差异表中整条数据显示为红色
                            // 若表单系统中的mac在运维系统中不存在，则认为是新增数据，差异表中整条数据均显示红色
                            $tmp = [];
                            $tmp['diffSign']      = 'danger';
                            $tmp['macaddress']    = $skmacaddress;
                            $tmp['ipaddress']     = $skipaddress;
                            $tmp['formno']        = trim($val['formno']);
                            $tmp['formsign']      = $formType[$formsign];
                            $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                            $tmp['zd_macaddress'] = $skmacaddress;
                            $tmp['jharea']        = trim($val['jharea']);
                            $tmp['buildingname']  = trim($val['buildingname']);
                            $tmp['roomno']        = trim($val['roomno']);
                            $data[$num]           = $tmp;
                            $num ++;
                        } else {
                            
                            $diffSign = [];
                            $zd_area = getDictname(trim($ywData[$skmacaddress]['zd_area']));
                            $zd_belongfloor = getDictname(trim($ywData[$skmacaddress]['zd_belongfloor']));
                            // ip地址
                            if (trim($val['skipaddress']) != trim($ywData[$skmacaddress]['zd_ipaddress'])) {
                                $diffSign[] = 'ipaddress';
                            }
                            // 使用人账号
                            if ($chargePerson != trim($ywData[$skmacaddress]['zd_useman'])) {
                                $diffSign[] = 'chargepersons';
                            }
                            // 设备名称
                            if (!in_array($formsign, $noComputerName)) {
                                if (trim($val['computername']) != trim($ywData[$skmacaddress]['zd_name'])) {
                                    $diffSign[] = 'computername';
                                }
                            }
                            // 地区
                            if (trim($val['jharea']) != $zd_area) {
                                $diffSign[] = 'jharea';
                            }
                            // 楼宇
                            if (trim($val['buildingname']) != $zd_belongfloor) {
                                $diffSign[] = 'buildingname';
                            }
                            // 房间号
                            if (trim($val['roomno']) != trim($ywData[$skmacaddress]['zd_roomno'])) {
                                $diffSign[] = 'roomno';
                            }
                            if ($diffSign) {
                                $tmp = [];
                                $tmp['diffSigns']     = $diffSign;
                                $tmp['macaddress']    = $skmacaddress;
                                $tmp['ipaddress']     = $skipaddress;
                                $tmp['formno']        = trim($val['formno']);
                                $tmp['formsign']      = $formType[$formsign];
                                $tmp['chargepersons'] = trim($val['chargeperson'])."(".$chargePerson.")";
                                $tmp['zd_macaddress'] = $skmacaddress;
                                $tmp['jharea']        = trim($val['jharea']);
                                $tmp['buildingname']  = trim($val['buildingname']);
                                $tmp['roomno']        = trim($val['roomno']);
                                $data[$num]           = $tmp;
                                $num ++;
                            }
                        }
                    }
                }
            }
            if($macaddress && !in_array($macaddress,$compared)) $compared[] = $macaddress;
            if($skmacaddress && !in_array($skmacaddress,$compared)) $compared[] = $skmacaddress;
            if($oldmacaddress && !in_array($oldmacaddress,$compared)) $compared[] = $oldmacaddress;
            if($oldskmacaddress && !in_array($oldskmacaddress,$compared)) $compared[] = $oldskmacaddress;
            if($oldskmacaddress && !in_array($oldskmacaddress,$compared)) $compared[] = $oldskmacaddress;
        }
        echo json_encode(array( 'total' => count($data),'rows' => $data));
    }
    function zrywData(){
        set_time_limit(0);
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $token      = S('HFZR_TOKEN');
        if(empty($token)){
            $token = file_get_contents("http://10.78.86.65/httpapi/term/token");
            $token = json_decode($token,true);
            if($token['errno'] != '0') exit(json_encode(array( 'total' => 0,'rows' => [],'error'=>'获取TOKEN失败，错误信息：'.$token['errmsg'])));
            $token = $token['token'];
            S('HFZR_TOKEN',$token);
        }
        $zrDatas  = S('zrDatas');
        $today    = date('Y-m-d');
        $typename = ['个人电脑','刷卡器','打印机'];
        // 获取所有六个月内typename=”个人电脑”or”刷卡器”or”打印机”且mac不为空的画方准入数据
        if(empty($zrDatas) || ($zrDatas['today'] != $today)){
            $allMAC   = [];
            $zrDatas['today'] = $today;
            foreach($typename as $cpttype){
                $timeStrict = strtotime("-6 month");// 六个月前的日期
                $curpage = 1;
                while($curpage){
                    $zrData = $this->getZRData($token,$curpage,$cpttype);
                    if($zrData['errno'] != 0) exit(json_encode(array( 'total' => 0,'rows' => [],'error'=>'获取画方准入数据失败，错误信息：'.$zrData['errmsg'])));
                    $total  = $zrData['count'];
                    $zrData = $zrData['data'];
                    $zrDataFilter = [];
                    foreach($zrData as $key=>$val){
                        $thisonline = $val['mac_info'][0]['thisonline'];
                        $ip         = trim($val['mac_info'][0]['ip']);
                        $ownername  = trim($val['ownername']);
                        $name       = trim($val['name']);
                        if(empty($thisonline)) continue;
                        $thisonline = strtotime($thisonline);
                        if($thisonline < $timeStrict) continue;
                        $mac = strtoupper(trim($val['mac_info'][0]['mac']));
                        if(empty($mac)) continue;
                        $macArr = explode(':',$mac);
                        $macStr = '';
                        foreach($macArr as $num=>$mac){
                            if($num%2){
                                if($num == count($macArr)-1){
                                    $macStr .= $mac;
                                }else{
                                    $macStr .= $mac.".";
                                }
                            }else{
                                $macStr .= $mac;
                            }
                        }
                        $tmp = [];
                        $tmp['ip']          = $ip;
                        $tmp['mac']         = $macStr;
                        $tmp['ownername']   = $ownername;
                        $tmp['name']        = $name;
                        $tmp['typename']    = trim($val['typename']);
                        $tmp['thisonline']  = trim($val['mac_info'][0]['thisonline']);
                        $zrDataFilter[]     = $tmp;
                        $allMAC[]           = $macStr;
                    }
                    $zrDatas = array_merge($zrDatas,$zrDataFilter);
                    if($curpage*200<$total){
                        $curpage++;
                    }else{
                        break;
                    }
                }
            }
            $zrDatas['allMac'] = $allMAC;
            S('zrDatas',$zrDatas);
        }
//        dump($zrDatas);die;
        // 获取运维数据
        $allMAC = $zrDatas['allMac'];
        $where = [];
        $where[0]['zd_atpstatus']  = ['exp',' is null'];
        $ywDatas         = [];
        $ywData          = [];
        if(count($allMAC)>=100){
            $begin = 0;
            while(count($allMAC)>=$begin){
                $macsTmp = array_slice($allMAC,$begin,100);
                $where[0]['upper(t.zd_macaddress)'] = ['in',$macsTmp];
                $tmnInfoTmp = M('terminal t')->join("left join it_dictionary d on t.zd_type=d.d_atpid")->field('upper(t.zd_macaddress) zd_macaddress,t.zd_ipaddress,t.zd_useman,t.zd_name,d.d_dictname zd_type')->where($where)->select();
                $ywDatas = array_merge($ywDatas,$tmnInfoTmp);
                $begin += 100;
            }
        }else if(count($allMAC)>0){
            $where[0]['upper(zd_macaddress)'] = ['in',$allMAC];
            $ywDatas = M('terminal t')->join("left join it_dictionary d on t.zd_type=d.d_atpid")->field('upper(t.zd_macaddress) zd_macaddress,t.zd_ipaddress,t.zd_useman,t.zd_name,d.d_dictname zd_type')->where($where)->select();
        }
        foreach($ywDatas as $val){
            $macaddressYW = trim($val['zd_macaddress']);
            $macaddressYW = strtoupper($macaddressYW);
            if(!empty($ywData[$macaddressYW])){
                $ywData[$macaddressYW] = 1;
            }else{
                $ywData[$macaddressYW] = $val;
            }
        }
//        dump($ywData);die;
        $data = [];
        unset($zrDatas['today']);
        unset($zrDatas['allMac']);
        // 根据搜索条件过滤,比对差异
        foreach($zrDatas as $key=>$val){
            $ip         = $val['ip'];
            $mac        = $val['mac'];
            $ownername  = $val['ownername'];
            $name       = $val['name'];
            $typename   = $val['typename'];
            if(trim($queryparam['ipaddress']) && (strpos($ip,trim($queryparam['ipaddress'])) === false)) continue;
            if(trim($queryparam['macaddress']) && (strpos($mac,trim($queryparam['ipaddress'])) === false)) continue;
            if(trim($queryparam['userman']) && (strpos($ownername,trim($queryparam['userman'])) === false))  continue;
            if(trim($queryparam['cptname']) && (strpos($name,trim($queryparam['cptname'])) === false))  continue;
//            if(trim($queryparam['cptname']) && (strpos($mac,trim($queryparam['ipaddress'])) === false))  continue;
            if (!is_array($ywData[$mac])) {
                // 以此mac为条件查找运维系统中对应数据，若未查到则将此条数据显示在差异表中，整条数据标识为红色
                $val['diffSign']      = 'danger';
                $data[]               = $val;
//                dump($val);die;
            } else {
                $diffSign = [];
                // ip地址
                if ($ip != trim($ywData[$mac]['zd_ipaddress'])) {
                    $diffSign[] = 'ip';
                }
                // 使用人账号
                if ($ownername != trim($ywData[$mac]['zd_useman'])) {
                    $diffSign[] = 'ownername';
                }
                // 设备名称
                if ($name != trim($ywData[$mac]['zd_name'])) {
                    $diffSign[] = 'name';
                }
                if ($diffSign) {
                    $val['diffSigns']     = $diffSign;
                    $data[]               = $val;
                }
            }
        }
//        var_dump($data);die;
        echo json_encode(array( 'total' => count($data),'rows' => $data));
    }
    function getZRData($token,$curpage,$cpttype){
        $option = [];
        $option['token']     = $token;
        $option['itemflags'] = '1';
        $option['curpage']   = $curpage;
        $option['limit']     = '200';
        $option['where']['typename'] = $cpttype;
        $zrData = post('http://10.78.86.65/httpapi/term/get',$option);
        $zrData = json_decode($zrData,true);
        return $zrData;
    }
    function ywscanData(){
        set_time_limit(0);
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $where = [];
        if(trim($queryparam['switchip'])) $where[0]['st_switchip'] = ['like',"%".trim($queryparam['switchip'])."%"];
        if(trim($queryparam['ipaddress'])) $where[0]['st_ipaddress'] = ['like',"%".trim($queryparam['ipaddress'])."%"];
        if(trim($queryparam['macaddress'])) $where[0]['upper(st_macaddress)'] = ['like',"%".strtoupper(trim($queryparam['macaddress']))."%"];
        if(trim($queryparam['vlanno'])) $where[0]['st_vlan'] = ['like',"%".trim($queryparam['vlanno'])."%"];
        if(trim($queryparam['age'])) $where[0]['st_age'] = ['like',"%".trim($queryparam['age'])."%"];
        // 扫描交换机数据
        $scanData = M('scanterminal_old s')->field('s.st_atpid,s.st_atpcreatetime,s.st_switchip,s.st_switchtype,s.st_ipaddress,s.st_age,s.st_vlan,upper(s.st_macaddress) st_macaddress')->where($where)->order($queryparam['sort']." ".$queryparam['sortOrder'])->cache(true)->select();
        if(empty($scanData)) exit(json_encode(array( 'total' => -1,'rows' => [])));
        $scanDataIsRepeat = removeArrKeyNotNull($scanData,'st_macaddress');
        $scanDataIsRepeat = array_count_values($scanDataIsRepeat);
        // 运维系统数据
        $ywDatas = M('terminal')->field('upper(zd_macaddress) zd_macaddress,zd_ipaddress')->where("zd_atpstatus is null and zd_macaddress is not null")->order("zd_macaddress asc")->cache(true)->select();
        $ywData = [];
        foreach($ywDatas as $key=>$value){
            $ywData[$value['zd_macaddress']] = $value['zd_ipaddress'];
        }
//        $ywDataIsRepeat = removeArrKeyNotNull($ywDatas,'zd_macaddress');
//        $ywDataIsRepeat = array_count_values($ywDataIsRepeat);
        $data = [];
        $num = 0;
        $compared = [];
        foreach($scanData as $key=>$value){
            $macaddress = strtoupper(trim($value['st_macaddress']));
            $st_atpcreatetime = trim($value['st_atpcreatetime']);
            if(in_array($macaddress,$compared)) continue;
            if($scanDataIsRepeat[$macaddress] != 1){
                // 重复的MAC均以蓝色标识整条数据
                $tmp                     = [];
                $tmp['diffSign']         = 'info';
                $tmp['st_macaddress']    = $macaddress;
                $tmp['st_atpcreatetime'] = substr($st_atpcreatetime,0,strpos($st_atpcreatetime,'.'));
                $tmp['st_switchip']      = trim($value['st_switchip']);
                $tmp['st_switchtype']    = trim($value['st_switchtype']);
                $tmp['st_ipaddress']     = trim($value['st_ipaddress']);
                $tmp['st_age']           = trim($value['st_age']);
                $tmp['st_vlan']          = trim($value['st_vlan']);
                $data[$num]              = $tmp;
                $num ++;
            }else if(empty($ywData[$macaddress])){
                // 逐条取出IT_SCANTERMINAL_OLD表中不重复的MAC，在运维系统的资产数据中进行查找，如未找到则差异表中整条数据标识红色
                $tmp                     = [];
                $tmp['diffSign']         = 'danger';
                $tmp['st_macaddress']    = $macaddress;
                $tmp['st_atpcreatetime'] = substr($st_atpcreatetime,0,strpos($st_atpcreatetime,'.'));
                $tmp['st_switchip']      = trim($value['st_switchip']);
                $tmp['st_switchtype']    = trim($value['st_switchtype']);
                $tmp['st_ipaddress']     = trim($value['st_ipaddress']);
                $tmp['st_age']           = trim($value['st_age']);
                $tmp['st_vlan']          = trim($value['st_vlan']);
                $data[$num]              = $tmp;
                $num ++;
            }else{
                $ywipadress = trim($ywData[$macaddress]);
                if($ywipadress != trim($value['st_ipaddress'])){
                    // 如找到且运维IP与扫描IP不同则将不同的字段标识红色
                    $diffSign[]              = 'st_ipaddress';
                    $tmp                     = [];
                    $tmp['diffSigns']        = $diffSign;
                    $tmp['st_macaddress']    = $macaddress;
                    $tmp['st_atpcreatetime'] = substr($st_atpcreatetime,0,strpos($st_atpcreatetime,'.'));
                    $tmp['st_switchip']      = trim($value['st_switchip']);
                    $tmp['st_switchtype']    = trim($value['st_switchtype']);
                    $tmp['st_ipaddress']     = trim($value['st_ipaddress']);
                    $tmp['st_age']           = trim($value['st_age']);
                    $tmp['st_vlan']          = trim($value['st_vlan']);
                    $data[$num]              = $tmp;
                    $num ++;
                }
            }
            $compared[] = $macaddress;
        }
//        $emptyMac = ['--','---','/'];
//        foreach($ywDatas as $key=>$value){
//            $macaddress = strtoupper(trim($value['zd_macaddress']));
//            if(in_array($macaddress,$compared)) continue;
//            if(in_array($macaddress,$emptyMac)) continue;
//            if($ywDataIsRepeat[$macaddress] != 1) continue;
//            $tmp                     = [];
//            $tmp['diffSign']         = 'success';
//            $tmp['st_macaddress']    = $macaddress;
//            $data[$num]              = $tmp;
//            $num ++;
//        }
        echo json_encode(array( 'total' => count($data),'rows' => $data));
    }

    /**
     * 根据表单编号获取打开信息
    */
    function getBDOpenInfo(){
        $formno = I('post.formno');
        if(empty($formno)) return null;
        $taskInfo = M('task')->field('t_status,t_nameid,t_biaodanname,t_biaodanurl')->where("t_rwid = '%s'",$formno)->find();
        echo json_encode($taskInfo);
    }
}

