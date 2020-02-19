<?php
namespace Home\Controller;
use Think\Controller;
//use Think\Upload;
class TerminalController extends BaseController {

    public function index(){
        $mac   = I('get.mac','');
        $arr = ['密级', '地区','使用状态(终端资产)','保密台账','资产类型','资产台账'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('ds_miji', $arrDic['密级']);
        $this->assign('ds_area', $arrDic['地区']);
        $this->assign('status', $arrDic['使用状态(终端资产)']);
        $this->assign('ds_bmtz', $arrDic['保密台账']);
        $this->assign('ds_zctz', $arrDic['资产台账']);
        $this->assign('ds_sbtype', $arrDic['资产类型']);
        $ip = I('get.ip');
        $this->assign('ip', $ip);
        addLog("it_terminal", "用户访问日志",  "访问终端资产页面", "成功");
        $this->assign('macaddress',$mac);
        $this->display();
    }
    public function add(){
        $arr = ['密级', '地区','使用状态(终端资产)','保密台账','资产类型','资产台账'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('ds_miji', $arrDic['密级']);
        $this->assign('ds_area', $arrDic['地区']);
        $this->assign('status', $arrDic['使用状态(终端资产)']);
        $this->assign('ds_bmtz', $arrDic['保密台账']);
        $this->assign('ds_zctz', $arrDic['资产台账']);
        $this->assign('ds_sbtype', $arrDic['资产类型']);
        addLog('it_teiminal','用户访问日志', "访问添加页面", '成功');
        $this->display();
    }

    public function addrelation(){
        $id = I('get.id','');
        $this->assign('relationid',$id);
        $arr = ['密级', '地区','使用状态(终端资产)','保密台账','资产类型','资产台账'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('ds_miji', $arrDic['密级']);
        $this->assign('ds_area', $arrDic['地区']);
        $this->assign('status', $arrDic['使用状态(终端资产)']);
        $this->assign('ds_bmtz', $arrDic['保密台账']);
        $this->assign('ds_zctz', $arrDic['资产台账']);
        $this->assign('ds_sbtype', $arrDic['资产类型']);
        $this->assignrelation();
        $this->display();
    }

    /**
     * Excel导入资产信息
     */
    public function submitimpTmn(){
        if (IS_POST) {
            set_time_limit(0);
            $remark = I('post.beizhu');
            if(!$remark){
                exit(makeStandResult(1, json_encode([['请填写备注！']])));
            }
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728;
            $upload->exts = array('xls','xlsx');
            $upload->rootPath = './Public/uploads/';
            $upload->savePath = '';
            $info = $upload->upload();
            if (!$info) {
                exit(makeStandResult(1, json_encode([[$upload->error]])));
            }
            $filename = './Public/uploads/' . $info["updataexcel2007"]["savepath"] . $info["updataexcel2007"]["savename"];
            vendor("PHPExcel.PHPExcel");
            $objPhpExcel = \PHPExcel_IOFactory::load($filename);
            $excelsheet = $objPhpExcel->getActiveSheet()->toArray(null, true, true, true, true);

            $column = Array("表单编号","设备编码","设备名称","设备类型","厂家","型号","密级","出厂编号","部标编码","状态","IP地址","子网掩码","MAC地址","默认网关","显示器型号","操作系统及版本","配置视频干扰器","使用人","责任人","责任部门","地区","楼宇","房间号","采购日期","维保日期","到保日期","启用日期","管理方式","仪设台账","保密台账","备注","硬盘序列号","操作系统安装日期","是否安装隔离插座");
            $thead  = array_values($excelsheet[1]);
            $diff   = array_diff($thead,$column);
            if(!empty($diff)){
                exit(makeStandResult(2, json_encode([["请按照模板填写导入数据，保持列头一致。"]])));
            }

            $errInfo = [];
            $Model = M("Terminal");
            $cc = count($excelsheet);
            if (count($excelsheet) < 2) exit(makeStandResult(2, json_encode([["表中没有数据，请重新上传！"]])));

            // 获取Excel文件中是否有重复MAC的资产信息
            $excelMAC  = removeArrKey($excelsheet,'M',true);
            $isSameExc = array_count_values($excelMAC);

            // 获取MAC地址在原数据表中重复数据信息
            $map = [];
            $map[]['zd_atpstatus']  = ['exp',"is null"];
            $map[]['zd_macaddress'] = ['exp',"is not null"];
            $hasMac  = $Model->field('zd_atpid,upper(zd_macaddress) zd_macaddress,zd_ipaddress')->where($map)->select();
            $allMac  = [];
            $tmnInfo = [];
            foreach($hasMac as $key=>$val){
                $allMac[]                                    = $val['zd_macaddress'];
                $tmnInfo[$val['zd_macaddress']]['atpid']     = $val['zd_atpid'];
                $tmnInfo[$val['zd_macaddress']]['ipaddress'] = $val['zd_ipaddress'];
            }
            $isSame     = array_count_values($allMac);
            $sameInfo   = [];
            $addData    = []; // 需新增的数据
            $updateData = []; // 需更新的数据

            // 排序”四个字段，导入更新时需要校验以下内容：
            //      1.MAC地址在原数据表中不能有重复数据
            //      2.设备类型不为空，导入信息中的地区、楼宇、厂家、型号需校验后方可写入
            //      3.模板中的人如在表中不存在，需要提示
            //      4.导入信息中责任人需提供域帐号作为唯一标识，导入后运维系统再利用域帐号读取主数据信息自动补填人员的单位和部门信息
            //      5.MAC地址为XXXX.XXXX.XXXX的格式，IP地址、子网掩码、默认网关为XXX.XXX.XXX.XXX的格式
            for($i=2;$i<=$cc;$i++)
            {
                $data = [];
                $zd_macaddress   = strtoupper($excelsheet[$i]['M']);
                if(empty($zd_macaddress)) continue;
                // 检验Excel中重复项
                if($isSameExc[$zd_macaddress] != 1) $sameInfo[] = $zd_macaddress;

                $zd_type              = trim($excelsheet[$i]['D']); // 设备类型
                $zd_factoryname       = trim($excelsheet[$i]['E']); // 厂家
                $zd_modelnumber       = trim($excelsheet[$i]['F']); // 型号
                $zd_secretlevel       = trim($excelsheet[$i]['G']); // 密级
                $zd_ipaddress         = trim($excelsheet[$i]['K']); // IP地址
                $zd_mask              = trim($excelsheet[$i]['L']); // 子网掩码
                $zd_gateway           = trim($excelsheet[$i]['N']); // 默认网关
                $zd_dutyman           = trim($excelsheet[$i]['S']); // 责任人
                $zd_useman            = trim($excelsheet[$i]['R']); // 使用人
                $zd_area              = trim($excelsheet[$i]['U']); // 地区
                $zd_belongfloor       = trim($excelsheet[$i]['V']); // 楼宇

                $data['zd_type']              = $zd_type;
                $data['zd_devicecode']        = trim($excelsheet[$i]['B']); // 设备编码
                $data['zd_seqno']             = trim($excelsheet[$i]['H']); // 出厂编号
                $data['zd_name']              = trim($excelsheet[$i]['C']); // 设备名称
                $data['zd_status']            = trim($excelsheet[$i]['J']); // 状态
                $data['zd_ipaddress']         = $zd_ipaddress;
                $data['zd_macaddress']        = $zd_macaddress;
                $data['zd_usedeptid']         = null;
                $data['zd_usedeptname']       = null;
                $data['zd_useman']            = $zd_useman; // 使用人
                $data['zd_dutydeptid']        = null;
                $data['zd_dutydeptname']      = null;
                $data['zd_dutyman']           = $zd_dutyman;
                $data['zd_area']              = $zd_area;
                $data['zd_belongfloor']       = $zd_belongfloor;
                $data['zd_roomno']            = trim($excelsheet[$i]['W']); // 房间号
                $data['zd_factoryname']       = $zd_factoryname;
                $data['zd_modelnumber']       = $zd_modelnumber;
                $data['zd_purchasetime']      = trim($excelsheet[$i]['X']); // 采购日期
                $data['zd_maintainbegintime'] = trim($excelsheet[$i]['Y']); // 维保日期
                $data['zd_maintainendtime']   = trim($excelsheet[$i]['Z']); // 到保日期
                $data['zd_startusetime']      = trim($excelsheet[$i]['AA']); // 启用日期
                $data['zd_secretlevel']       = $zd_secretlevel;
                $data['zd_isisolate']         = trim($excelsheet[$i]['AH']); // 是否安装隔离插座
                $data['zd_isinstalljammer']   = trim($excelsheet[$i]['Q']); // 配置视频干扰器
                $data['zd_memo']              = trim($excelsheet[$i]['AE']); // 备注
                $data['zd_devicebook']        = trim($excelsheet[$i]['AC']); // 仪设台账
                $data['zd_privacybook']       = trim($excelsheet[$i]['AD']); // 保密台账
                $data['zd_anecode']           = trim($excelsheet[$i]['I']); // 部标编码
                $data['zd_harddiskseq']       = trim($excelsheet[$i]['AF']); // 硬盘序列号
                $data['zd_osinstalltime']     = trim($excelsheet[$i]['AG']); // 操作系统安装日期
                $data['zd_bdid']              = trim($excelsheet[$i]['A']); // 表单编号
                $data['zd_username']          = null;
                $data['zd_dutymanname']       = null;
                $data['zd_managetype']        = trim($excelsheet[$i]['AB']); // 管理方式
                $data['zd_mask']              = $zd_mask;
                $data['zd_gateway']           = $zd_gateway;
                $data['zd_display']           = trim($excelsheet[$i]['O']); // 显示器型号
                $data['zd_os']                = trim($excelsheet[$i]['P']); // 操作系统及版本

//               $data['zd_dutydeptname']      = trim($excelsheet[$i]['T']); // 责任部门

                // 校验导入信息中的设备类型、厂家、型号、地区、楼宇
                // 设备类型不为空，设备类型校验
//                if(empty($zd_type)){
//                    $errInfo[] = "第".$i."行中设备类型不能为空，请修改。";
//                } else{
//                    $zd_type = D('dic')->getDictIDByName($zd_type,'资产类型');
//                    if(empty($zd_type)){
//                        $errInfo[] = "第".$i."行中设备类型：".$data['zd_type']." 校验不通过，请修改。";
//                    }else{
//                        $data['zd_type'] = $zd_type;
//                        if(!empty($zd_factoryname)){
//                            // 厂家校验
//                            $zd_factoryname = getDictIDByName($zd_factoryname,'',$zd_type);
//                            if(empty($zd_factoryname)){
//                                $errInfo[] = "第".$i."行中厂家：".$data['zd_factoryname']." 校验不通过，请修改。";
//                            }else{
//                                $data['zd_factoryname'] = $zd_factoryname;
//                                if(!empty($zd_modelnumber)) {
//                                    // 型号校验
//                                    $zd_modelnumber = getDictIDByName($zd_modelnumber, '', $zd_factoryname);
//                                    if (empty($zd_modelnumber)) {
//                                        $errInfo[] = "第" . $i . "行中型号：" . $data['zd_modelnumber'] . " 校验不通过，请修改。";
//                                    } else {
//                                        $data['zd_modelnumber'] = $zd_modelnumber;
//                                    }
//                                }
//                            }
//                        }else if(!empty($zd_modelnumber)){
//                            $errInfo[] = "第".$i."行中厂家信息为空，型号校验不通过，请修改。";
//                        }
//                    }
//                }

                //地区校验
//                if(!empty($zd_area)){
//                    $zd_area = getDictIDByName($zd_area,'region');
//                    if(empty($zd_area)){
//                        $errInfo[] = "第".$i."行中地区：".$data['zd_area']." 校验不通过，请修改。";
//                    }else{
//                        $data['zd_area'] = $zd_area;
//                        if(!empty($zd_belongfloor)) {
//                            // 楼宇校验
//                            $zd_belongfloor = getDictIDByName($zd_belongfloor, '', $zd_area);
//                            if (empty($zd_belongfloor)) {
//                                $errInfo[] = "第" . $i . "行中楼宇：".$data['zd_belongfloor']." 校验不通过，请修改。";
//                            }else{
//                                $data['zd_belongfloor'] = $zd_belongfloor;
//                            }
//                        }
//                    }
//                }else if(!empty($zd_belongfloor)){
//                    $errInfo[] = "第".$i."行中地区信息为空，楼宇校验不通过，请修改。";
//                }
//
//                //密级校验
//                if(!empty($zd_secretlevel)){
//                    $zd_secretlevel = getDictIDByName($zd_secretlevel,'密级');
//                    if(empty($zd_secretlevel)){
//                        $errInfo[] = "第".$i."行中密级：".$data['zd_secretlevel']." 校验不通过，请修改。";
//                    }else{
//                        $data['zd_secretlevel'] = $zd_secretlevel;
//                    }
//                }
//
//                // 模板中的人如在表中不存在，需要提示
//                if(!empty($zd_dutyman)){
//                    $dutymanInfo = D('org')->getUserNames($zd_dutyman);
//                    if(empty($dutymanInfo)){
//                        $errInfo[] = "第".$i."行中责任人域账号：".$zd_dutyman."在表中不存在，请修改。";
//                    }else{
//                        $data['zd_dutymanname']       = $dutymanInfo['realusername'];
//                        $data['zd_dutydeptid']        = $dutymanInfo['orgid'];
//                        $data['zd_dutydeptname']      = $dutymanInfo['deptname'];
//                    }
//                }
//                // 获取使用人信息
//                if(!empty($zd_useman)){
//                    $usemanInfo = D('org')->getUserNames($zd_useman);
//                    if(empty($usemanInfo)){
//                        $errInfo[] = "第".$i."行中使用人域账号：".$zd_useman."在表中不存在，请修改。";
//                    }else{
//                        $data['zd_username']         = $usemanInfo['realusername'];
//                        $data['zd_usedeptid']        = $usemanInfo['orgid'];
//                        $data['zd_usedeptname']      = $usemanInfo['deptname'];
//                    }
//                }

                // MAC地址为XXXX.XXXX.XXXX的格式，IP地址、子网掩码、默认网关为XXX.XXX.XXX.XXX的格式
                $reg  = '/^(\d|1\d?\d?|2(\d|([0-4]\d|5[0-5]))|[1-9]\d)\.(\d|1\d?\d?|2(\d|([0-4]\d|5[0-5]))|[1-9]\d)\.(\d|1\d?\d?|2(\d|([0-4]\d|5[0-5]))|[1-9]\d)\.(\d|1\d?\d?|2(\d|([0-4]\d|5[0-5]))|[1-9]\d)$/';
                $reg1 = '/^([A-Z,0-9]{4})\.([A-Z,0-9]{4})\.([A-Z,0-9]{4})$/';
                if(!preg_match ($reg1,$zd_macaddress)){
                    $errInfo[] = "第".$i."行中MAC地址：".$zd_macaddress." 格式不正确，请修改。";
                }
                if(!empty($zd_ipaddress)){
                    if(!preg_match ($reg,$zd_ipaddress)){
                        $errInfo[] = "第".$i."行中IP地址：".$zd_ipaddress." 格式不正确，请修改。";
                    }
                }
                if(!empty($zd_mask)){
                    if(!preg_match ($reg,$zd_mask)){
                        $errInfo[] = "第".$i."行中子网掩码：".$zd_mask." 格式不正确，请修改。";
                    }
                }
                if(!empty($zd_gateway)){
                    if(!preg_match ($reg,$zd_gateway)){
                        $errInfo[] = "第".$i."行中默认网关：".$zd_gateway." 格式不正确，请修改。";
                    }
                }

                // 检验MAC地址在原数据表中不能有重复数据
                if(isset($isSame[$zd_macaddress]) && ($isSame[$zd_macaddress] > 1)){
                    array_unshift($errInfo,"第".$i."行MAC地址".$zd_macaddress."在原数据表中存在多条信息，请先修改。");
                }else if(empty($errInfo) && empty($sameInfo)){
                    if($isSame[$zd_macaddress] == 1){
                        $updateData[$zd_macaddress] = $data;
                    }else{
                        $addData[$zd_macaddress]    = $data;
                    }
                }
            }
            if(!empty($sameInfo)){
//                print_r($sameInfo);die;
                $sameInfo = array_unique($sameInfo);
                foreach($sameInfo as $mac){
                    array_unshift($errInfo,"MAC:地址".$mac . "在导入的Excel中有重复，需人工处理。");
                }
            }
            if (empty($errInfo)) {
                if(!empty($updateData) || !empty($addData)){
                    $res = $this->SaveImportData($addData,$updateData,$remark,$tmnInfo);
//                    dump($res);die;
                    if($res['status'] > 0){
                        $resInfo    = [];
                        $resInfo[]  = '导入资产信息成功！';
                        $addInfo    = array_keys($addData);
                        if($addInfo) $resInfo[]  = '新增的资产信息有：'.implode('，',$addInfo);
                        $updateInfo = array_keys($updateData);
                        if($updateInfo) $resInfo[]  = '更新的资产信息有：'.implode('，',$updateInfo);
                        if($res['status'] == 2) $resInfo = array_merge($resInfo,$res['msg']);
                        exit(makeStandResult(0, json_encode($resInfo)));
                    }else{
                        exit(makeStandResult(4, json_encode([[$res['msg']]])));
                    }
                }else{
                    exit(makeStandResult(2, json_encode([["表中没有数据，请重新上传！"]])));
                }
            }else{
                $errInfo = json_encode($errInfo);
//                $errInfo = implode("<br/>",$errInfo);
                exit(makeStandResult(3, $errInfo));
            }

        }
    }

    /**
     * 保存资产数据（Excel导入数据时调用）
     * $addData 需要新增的资产数据
     * $updateData 需要修改的资产数据
     * $remark 记录入日志表的备注信息
     * $tmnInfo 原Termianl表macaddress对应的atpid和ipadress
     */
    public function SaveImportData($addData,$updateData,$remark,$tmnInfo){
        $Model  = M("it_terminal");
        $result = [];
        try {
            $Model->startTrans();
            $DetailData       = [];
            if($addData){
                $zd_atpcreatetime = date('Y-m-d H:i:s',time());
                $zd_atpcreateuser = session('user_id');
                foreach($addData as $macaddress => $data){//添加任务明细
                    $zd_atpid     = makeGuid();
                    $DetailData[] = " select '".$zd_atpid."','".$zd_atpcreatetime."','".$zd_atpcreateuser."','".$data['zd_type']."','".$data['zd_devicecode']."','".$data['zd_seqno']."','".$data['zd_name']."','".$data['zd_status']."','".$data['zd_ipaddress']."','".$data['zd_macaddress']."','".$data['zd_usedeptid']."','".$data['zd_usedeptname']."','".$data['zd_useman']."','".$data['zd_dutydeptid']."','".$data['zd_dutydeptname']."','".$data['zd_dutyman']."','".$data['zd_area']."','".$data['zd_belongfloor']."','".$data['zd_roomno']."','".$data['zd_factoryname']."','".$data['zd_modelnumber']."','".$data['zd_purchasetime']."','".$data['zd_maintainbegintime']."','".$data['zd_maintainendtime']."','".$data['zd_startusetime']."','".$data['zd_secretlevel']."','".$data['zd_isisolate']."','".$data['zd_isinstalljammer']."','".$data['zd_memo']."','".$data['zd_devicebook']."','".$data['zd_privacybook']."','".$data['zd_anecode']."','".$data['zd_harddiskseq']."','".$data['zd_osinstalltime']."','".$data['zd_bdid']."','".$data['zd_username']."','".$data['zd_dutymanname']."','".$data['zd_managetype']."','".$data['zd_mask']."','".$data['zd_gateway']."','".$data['zd_display']."','".$data['zd_os']."' from dual ";
//                    $this->recordLog('add', 'terminal','批量导入，新增数据，MAC地址为'.$macaddress.'，IP地址为'.$data['zd_ipaddress'],'terminal',$zd_atpid,$remark);
                    //修改ip地址使用状态
//                    if(!empty($data['zd_ipaddress'])){
//                        $ipbid  = $data['zd_ipaddress'];
//                        $status = '2';
//                        $res = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$ipbid,'status'=>$status]);
//                        if(!$res) $result[] = "IP地址：".$ipbid."状态修改失败。";
//                    }
                }
            }
            if($updateData){
                $zd_atpcreatetime = date('Y-m-d H:i:s',time());
                $zd_atpcreateuser = session('user_id');
                foreach($updateData as $macaddress => $data){
                    $zd_atpid  = makeGuid();
                    $oldIP     = $tmnInfo[$macaddress]['ipaddress'];
                    $oldID     = $tmnInfo[$macaddress]['atpid'];
                    $oldUpdate                         = [];
                    $oldUpdate['zd_atpstatus']         = 'DEL';
                    $oldUpdate['zd_mainid']            = $zd_atpid;
                    $oldUpdate['zd_atplastmodifyuser'] = $zd_atpcreateuser;
                    $oldUpdate['zd_atplastmodifytime'] = $zd_atpcreatetime;
                    $Model->where("zd_macaddress = '%s' and zd_atpstatus is null",$macaddress)->setField($oldUpdate);
                    $Model->where("zd_mainid = '%s' and zd_atpstatus is not null",$oldID)->setField(['zd_mainid'=>$zd_atpid]);
                    //添加任务明细
                    $DetailData[] = " select '".$zd_atpid."','".$zd_atpcreatetime."','".$zd_atpcreateuser."','".$data['zd_type']."','".$data['zd_devicecode']."','".$data['zd_seqno']."','".$data['zd_name']."','".$data['zd_status']."','".$data['zd_ipaddress']."','".$data['zd_macaddress']."','".$data['zd_usedeptid']."','".$data['zd_usedeptname']."','".$data['zd_useman']."','".$data['zd_dutydeptid']."','".$data['zd_dutydeptname']."','".$data['zd_dutyman']."','".$data['zd_area']."','".$data['zd_belongfloor']."','".$data['zd_roomno']."','".$data['zd_factoryname']."','".$data['zd_modelnumber']."','".$data['zd_purchasetime']."','".$data['zd_maintainbegintime']."','".$data['zd_maintainendtime']."','".$data['zd_startusetime']."','".$data['zd_secretlevel']."','".$data['zd_isisolate']."','".$data['zd_isinstalljammer']."','".$data['zd_memo']."','".$data['zd_devicebook']."','".$data['zd_privacybook']."','".$data['zd_anecode']."','".$data['zd_harddiskseq']."','".$data['zd_osinstalltime']."','".$data['zd_bdid']."','".$data['zd_username']."','".$data['zd_dutymanname']."','".$data['zd_managetype']."','".$data['zd_mask']."','".$data['zd_gateway']."','".$data['zd_display']."','".$data['zd_os']."' from dual ";
//                    $this->recordLog('update', 'terminal','批量导入，更新数据，MAC地址为'.$macaddress.'，IP地址为'.$data['zd_ipaddress'],'terminal',$zd_atpid,$remark);
                    //修改ip地址使用状态
//                    if($oldIP != $data['zd_ipaddress']){
//                        if(!empty($oldIP)){
//                            $status = null;
//                            $res = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$oldIP,'status'=>$status]);
//                            if(!$res) $result[] = "IP地址：".$ipbid."状态修改失败。";
//                        }
//                        if(!empty($data['zd_ipaddress'])){
//                            $status = '2';
//                            $res = D('Ipaddress')->changeBaseStatusByIp(['ipb_address'=>$data['zd_ipaddress'],'status'=>$status]);
//                            if(!$res) $result[] = "IP地址：".$ipbid."状态修改失败。";
//                        }
//                    }
                }
            }
            if($DetailData){
                $DetailDatas = implode('union',$DetailData);
                $sql = "insert into it_terminal (zd_atpid,zd_atpcreateuser,zd_atpcreatetime,zd_type,zd_devicecode,zd_seqno,zd_name,zd_status,zd_ipaddress,zd_macaddress,zd_usedeptid,zd_usedeptname,zd_useman,zd_dutydeptid,zd_dutydeptname,zd_dutyman,zd_area,zd_belongfloor,zd_roomno,zd_factoryname,zd_modelnumber,zd_purchasetime,zd_maintainbegintime,zd_maintainendtime,zd_startusetime,zd_secretlevel,zd_isisolate,zd_isinstalljammer,zd_memo,zd_devicebook,zd_privacybook,zd_anecode,zd_harddiskseq,zd_osinstalltime,zd_bdid,zd_username,zd_dutymanname,zd_managetype,zd_mask,zd_gateway,zd_display,zd_os) ".$DetailDatas;
                $Model->execute($sql);
            }
            $Model->commit();
            if(empty($result)){
                return ['status'=>1];
            }else{
                return ['status'=>2,'msg'=>$result];
            }
        } catch (Exception $e) {
            $Model->rollback();
            return ['status'=>-1,'msg'=>$e];
        }
    }

    public function edit(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("it_terminal");
            $data = $Model->field('zd_name,zd_devicecode,zd_seqno,zd_ipaddress,zd_macaddress,zd_type,zd_factoryname,zd_modelnumber,zd_status,zd_secretlevel,zd_purchasetime,zd_maintainendtime,zd_area,zd_belongfloor,zd_roomno,zd_anecode,zd_bdid,zd_startusetime,zd_privacybook,zd_devicebook,zd_harddiskseq,zd_osinstalltime,zd_isisolate,zd_isinstalljammer,zd_dutyman,zd_dutydeptid,zd_useman,zd_maintainbegintime,zd_memo,zd_atpid,zd_usedeptid,zd_usedeptname,zd_dutydeptname')
                ->where("zd_atpid='%s'", array($id))->find();
            session('list',$data);
            if ($data) {
                $this->assign('data', $data);
            }

            $duty = D('org')->getUserNames($data['zd_dutyman']);
            $dutyman = $duty['realusername'].'('.$duty['domainusername'].')';
            $this->assign('dutyman',$dutyman);
            $use = D('org')->getUserNames($data['zd_useman']);
            $useman = $use['realusername'].'('.$use['domainusername'].')';
            $this->assign('useman',$useman);
            $dutydeptname = D('org')->getDeptName($data['zd_dutydeptid']);
            $this->assign('dutydeptname',$dutydeptname);
        }
        $arr = ['密级', '地区','使用状态(终端资产)','保密台账','资产类型','资产台账'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('ds_miji', $arrDic['密级']);
        $this->assign('ds_area', $arrDic['地区']);
        $this->assign('status', $arrDic['使用状态(终端资产)']);
        $this->assign('ds_bmtz', $arrDic['保密台账']);
        $this->assign('ds_zctz', $arrDic['资产台账']);
        $this->assign('ds_sbtype', $arrDic['资产类型']);
        $this->display('add');
    }
    public function del()
    {
        try {
            $ids = I('post.ids');
            $array = explode(',', $ids);
            if ($array && count($array) > 0) {
                $Model = M("it_terminal");
                foreach ($array as $id) {
                    $data['zd_atpstatus'] = 'DEL';
                    $data['zd_atplastmodifytime'] = date('Y-m-d H:i:s',time());
                    $data['zd_atplastmodifyuser'] = session('user_id');
                    $Model->where("zd_atpid='%s'", $id)->save($data);
                    $ip=$Model->where("zd_atpid='%s'", $id)->getField('zd_ipaddress');
                    D('ip')->DelIpCs([$ip]);
                }
            }
        } catch (\Exception $e) {
            echo "delete muli row fail" . $e;
        }
    }
    public function getData(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_terminal du";
        $sql_count="
                select
                    count(1) c
                from it_terminal du
                ";
        $sql_select = $this->buildSql($sql_select,"zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"zd_atpstatus is null");


        if ("" != $queryparam['sbbm']){
            $searchcontent = trim($queryparam['sbbm']);
            $sql_select = $this->buildSql($sql_select,"zd_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"zd_devicecode like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['ipaddess']){
            $searchcontent = trim($queryparam['ipaddess']);
            $sql_select = $this->buildSql($sql_select,"zd_ipaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"zd_ipaddress like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['macaddess']){
            $searchcontent      = trim($queryparam['macaddess']);
            $searchcontentupper = strtoupper($searchcontent);
            $sql_select = $this->buildSql($sql_select,"(du.zd_macaddress like '%".$searchcontent."%' or upper(du.zd_macaddress) like '%".$searchcontentupper."%')");
            $sql_count = $this->buildSql($sql_count,"(du.zd_macaddress like '%".$searchcontent."%' or upper(du.zd_macaddress) like '%".$searchcontentupper."%')");
        }

        if ("" != $queryparam['seqno']){
            $searchcontent = trim($queryparam['seqno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_seqno like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_seqno like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['sbtype']){
            $searchcontent = trim($queryparam['sbtype']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_type ='".$searchcontent."'");
        }
        if ("" != $queryparam['factory']){
//            $searchcontent = trim($queryparam['factory']);
            $searchcontent = $this->getDicFactort($queryparam['factory'], 'dic_name'); //厂家
            $sql_select = $this->buildSql($sql_select,"du.zd_factoryname ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_factoryname ='".$searchcontent."'");
        }
        if ("" != $queryparam['model']){
//            $searchcontent = trim($queryparam['model']);
            $searchcontent = $this->getDicXingHaoById($queryparam['model'], 'dic_name'); //型号
            $sql_select = $this->buildSql($sql_select,"du.zd_modelnumber ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_modelnumber ='".$searchcontent."'");
        }
        if ("" != $queryparam['area']){
//            $searchcontent = trim($queryparam['area']);
            $searchcontent = $this->getDicById($queryparam['area'], 'dic_name'); //地区
            $sql_select = $this->buildSql($sql_select,"du.zd_area ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_area ='".$searchcontent."'");
        }
        if ("" != $queryparam['building']){
//            $searchcontent = trim($queryparam['building']);
            $searchcontent = $this->getDicLouYuById($queryparam['building'], 'dic_name'); //楼宇
            $sql_select = $this->buildSql($sql_select,"du.zd_belongfloor ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_belongfloor ='".$searchcontent."'");
        }
        if ("" != $queryparam['roomno']){
            $searchcontent = trim($queryparam['roomno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_roomno like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_roomno like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['usedept']){
            $searchcontent = trim($queryparam['usedept']);
//            $searchcontent = D('Depart')->getDeptSubIdById($searchcontent);
            $sql_select = $this->buildSql($sql_select,"du.zd_usedeptid like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_usedeptid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['userman']){
            $searchcontent = trim($queryparam['userman']);
            $sql_select = $this->buildSql($sql_select,"du.zd_useman ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_useman ='".$searchcontent."'");
        }
        if ("" != $queryparam['dutydept']){
            $searchcontent = trim($queryparam['dutydept']);
//            $searchcontent = D('Depart')->getDeptSubIdById($searchcontent);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutydeptid like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_dutydeptid like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['dutyman']){
            $searchcontent = trim($queryparam['dutyman']);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutyman ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_dutyman ='".$searchcontent."'");
        }
        if ("" != $queryparam['isavailable']){
            $searchcontent = trim($queryparam['isavailable']);
            $sql_select = $this->buildSql($sql_select,"du.zd_status ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_status ='".$searchcontent."'");
        }
        if ("" != $queryparam['secretlevel']){
            $searchcontent = trim($queryparam['secretlevel']);
            $sql_select = $this->buildSql($sql_select,"du.zd_secretlevel ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_secretlevel ='".$searchcontent."'");
        }
        if ("" != $queryparam['terminalname']){
            $searchcontent = trim($queryparam['terminalname']);
            $sql_select = $this->buildSql($sql_select,"du.zd_name like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_name like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['terminalcode']){
            $searchcontent = trim($queryparam['terminalcode']);
            $sql_select = $this->buildSql($sql_select,"du.zd_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_devicecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['diskno']){
            $searchcontent = trim($queryparam['diskno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_harddiskseq like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_harddiskseq like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['bubiaono']){
            $searchcontent = trim($queryparam['bubiaono']);
            $sql_select = $this->buildSql($sql_select,"du.zd_anecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_anecode like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'zd_building') $queryparam['sort'] = 'zd_belongfloor';
            if($queryparam['sort'] == 'zd_atpsort'){
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ',zd_atplastmodifytime desc,zd_atpid desc ';
            }else{
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
            }
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        }

        if (null != $queryparam['limit']) {
            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }

        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value){
            $duty = D('org')->getUserNames($value['zd_dutyman']);
            $Result[$key]['zd_dutyman'] = $duty['realusername'].'('.$duty['domainusername'].')';
            $use = D('org')->getUserNames($value['zd_useman']);
            $Result[$key]['zd_useman'] = $use['realusername'].'('.$use['domainusername'].')';

        }
        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }
    public function getRelationdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $Model = M();
        $sql_select="select * from it_relation where rl_cmainid ='".$id."' and rl_atpstatus is null";
        $Result = $Model->query($sql_select);

        foreach($Result as $key=> $value){
            $rl                       = $value['rl_relation'];
            $cmainid                  = $value['rl_cmainid'];
            $rmainid                  = $value['rl_rmainid'];
            $type                     = $value['rl_rtype'];
            $Result[$key]['ip']       = $value['rl_rname'];
            //关联关系
            $Result[$key]['relation'] = M("dictionary")->where("d_atpid='%s'and d_belongtype='关联关系'",$rl)->getField("d_dictname");
            //关联资产类型
            $Result[$key]['type']     = M("dictionary")->where("d_atpid='%s'and d_belongtype='equipmenttype'",$type)->getField("d_dictname");
            //当前资产名称
            $Result[$key]['cipaddress'] = M("terminal")->where("zd_atpid='%s'",$cmainid)->getField("zd_ipaddress");
            //关联资产名称
            $ripaddress                 = M("terminal")->where("zd_atpid='%s'",$rmainid)->getField("zd_ipaddress");
            $Result[$key]['ripaddress'] = $ripaddress;
            //关联资产IP
            $Result[$key]['toripaddress'] = "<a onclick = toRelation('".$rmainid."')>".$ripaddress."</a>";
        }
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    /**
     *  根据rl_atpid删除关联关系
     **/
    public function removeRelationByRLID(){
        $rlid = I('post.rlid','');
        if(empty($rlid)){
            exit(makeStandResult(1,'参数缺失,请重试！'));
        }
        $model = D('Relation');
        $arr  = [];
        $arr['rl_atpstatus'] = 'DEL';
        $arr  = $model->create($arr,2);
        $data = $model->where("rl_atpid='%s'",array($rlid))->find();
        $res = $model->where("rl_atpid='%s'",array($rlid))->save($arr);
        if($res){
            $this->recordLog('delete', 'relation','删除资产关联;rl_cmainid:'.$data['rl_cmainid'].';rl_rmainid:'.$data['rl_rmainid'].';','relation',$rlid);
            exit(makeStandResult(0,''));
        }else{
            exit(makeStandResult(2,'删除失败，请稍后重试！'));
        }
    }
    public function getterminaldata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M();
        $sql_select="
                select * from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype'
                 ";
        $sql_count="
                select
                    count(1) c
                from it_terminal du left join  it_dictionary d on du.zd_type=d.d_atpid where d.d_belongtype='equipmenttype'";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"du.zd_atpstatus is null");


        if ("" != $queryparam['rlsbbm']){
            $searchcontent = trim($queryparam['rlsbbm']);
            $sql_select = $this->buildSql($sql_select,"du.zd_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_devicecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['rlipaddess']){
            $searchcontent = trim($queryparam['rlipaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_ipaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_ipaddress like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['rlmacaddess']){
            $searchcontent = trim($queryparam['rlmacaddess']);
            $sql_select = $this->buildSql($sql_select,"du.zd_macaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_macaddress like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['rlsbtype']){
            $searchcontent = trim($queryparam['rlsbtype']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_type ='".$searchcontent."'");
        }
        if ('zd_atpsort' != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by du.zd_atpsort asc,du.zd_macaddress asc nulls last ";
        }

        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
        $Result = $Model->query($sql_select);
        $Count = $Model->query($sql_count);

        echo json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));

    }
    public function getBinddata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $macupper = M('it_terminal')->where("zd_atpid='%s'",$id)->getField('zd_macaddress');
        $mac = strtolower($macupper);
        $Result = M('it_switchnewinfoT s')->field('s.sw_atpid,s.sw_ipaddress,s.sw_interface,s.sw_vlan,s.sw_status')->where("sw_macaddress='%s'",$mac)->select();
//        $Result = M('it_switchnewinfoT s')
//            ->field("s.sw_atpid,s.sw_ipaddress,s.sw_interface,s.sw_vlan,s.sw_status,d1.d_dictname sw_mainarea,d2.d_dictname sw_mainbelongfloor,n.netdevice_room sw_mainroomno")
//            ->join("left join it_netdevice n on s.sw_ipaddress = n.netdevice_ipaddress")
//            ->join(" left join it_dictionary d1 on n.netdevice_area = d1.d_atpid")
//            ->join("left join it_dictionary d2 on n.netdevice_building = d2.d_atpid")
//            ->where("sw_macaddress='%s'",$mac)
//            ->select();
        foreach($Result as $key=>$val){
            $sw_ipaddress = trim($val['sw_ipaddress']);
            if($sw_ipaddress){
                $areaInfo = M('it_netdevice n')->field('d1.d_dictname sw_mainarea,d2.d_dictname sw_mainbelongfloor,n.netdevice_room sw_mainroomno')->join(" left join it_dictionary d1 on n.netdevice_area = d1.d_atpid")
                    ->join("left join it_dictionary d2 on n.netdevice_building = d2.d_atpid")
                    ->where("netdevice_ipaddress='%s'",$sw_ipaddress)
                    ->find();
                $Result[$key]['sw_mainarea']        = $areaInfo['sw_mainarea'];
                $Result[$key]['sw_mainbelongfloor'] = $areaInfo['sw_mainbelongfloor'];
                $Result[$key]['sw_mainroomno']      = $areaInfo['sw_mainroomno'];
            }
        }
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function getBindolddata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $macupper = M('it_terminal')->where("zd_atpid='%s'",$id)->getField('zd_macaddress');
        $mac = strtolower($macupper);
        $Result = M('it_switcholdinfoT s')->field('s.sw_atpid,s.sw_ipaddress,s.sw_interface,s.sw_vlan,s.sw_status')->where("sw_macaddress='%s'",$mac)->order('to_date(sw_atplastmodifydatetime) desc nulls last')->select();
//        $Result = M('it_switcholdinfoT s')->field("s.sw_atpid,s.sw_ipaddress,s.sw_interface,s.sw_vlan,s.sw_status,d1.d_dictname sw_mainarea,d2.d_dictname sw_mainbelongfloor,n.netdevice_room sw_mainroomno")->join("left join it_netdevice n on s.sw_ipaddress = n.netdevice_ipaddress")->join(" left join it_dictionary d1 on n.netdevice_area = d1.d_atpid")->join("left join it_dictionary d2 on n.netdevice_building = d2.d_atpid")->where("sw_macaddress='%s'",$mac)->order('sw_atplastmodifydatetime asc nulls last')->select();
        $newC   = M('it_switchnewinfoT')->where("sw_macaddress='%s'",$mac)->count();
        $Result = array_slice($Result,$newC);
        foreach($Result as $key=>$val){
            $sw_ipaddress = trim($val['sw_ipaddress']);
            if($sw_ipaddress){
                $areaInfo = M('it_netdevice n')->field('d1.d_dictname sw_mainarea,d2.d_dictname sw_mainbelongfloor,n.netdevice_room sw_mainroomno')->join(" left join it_dictionary d1 on n.netdevice_area = d1.d_atpid")
                    ->join("left join it_dictionary d2 on n.netdevice_building = d2.d_atpid")
                    ->where("netdevice_ipaddress='%s'",$sw_ipaddress)
                    ->find();
                $Result[$key]['sw_mainarea']        = $areaInfo['sw_mainarea'];
                $Result[$key]['sw_mainbelongfloor'] = $areaInfo['sw_mainbelongfloor'];
                $Result[$key]['sw_mainroomno']      = $areaInfo['sw_mainroomno'];
            }
        }
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function getLogdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $id = $queryparam['id'];
        $Model = M();
        $sql_select="
                select l.*,t.*,d.d_dictname d_dictname from it_log l,it_terminal t,it_dictionary d where l.l_mainid = t.zd_atpid and t.zd_type=d.d_atpid and  d.d_belongtype='equipmenttype' and d.d_dictype='terminal' and l.l_tablename like '%terminal' and l.l_mainid ='".$id."'";
        $Result = $Model->query($sql_select);
        foreach($Result as $key=>$value) {
            $optype = $value['l_optype'];
            switch ($optype) {
                case 'add':
                    $Result[$key]['optypename'] = '新增';
                    break;
                case 'del':
                    $Result[$key]['optypename'] = '删除';
                    break;
                case 'delete':
                    $Result[$key]['optypename'] = '删除';
                    break;
                case 'update':
                    $Result[$key]['optypename'] = '修改';
                    break;
                case 'print':
                    $Result[$key]['optypename'] = '打印';
                    break;
                default:
                    $Result[$key]['optypename'] = '';
                    break;
            }
        }
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }
    public function detailForm(){
        $id = $_GET['id'];
        if ($id) {
            $Model = M("it_terminal");
            $data = $Model->where("zd_atpid='%s'", array($id))->find();
            if ($data) {
                $this->assign('data', $data);
            }

            $duty = D('org')->getUserNames($data['zd_dutyman']);
            $dutyman = $duty['realusername'].'('.$duty['domainusername'].')';
            $this->assign('dutyman',$dutyman);
            $use = D('org')->getUserNames($data['zd_useman']);
            $useman = $use['realusername'].'('.$use['domainusername'].')';
            $this->assign('useman',$useman);
            $dutydeptname = D('org')->getDeptName($data['zd_dutydeptid']);
            $this->assign('dutydeptname',$dutydeptname);
            $usedeptname = D('org')->getDeptName($data['zd_usedeptid']);
            $this->assign('usedeptname',$usedeptname);
        }
        $arr = ['密级', '地区','使用状态(终端资产)','保密台账','资产类型','资产台账'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('ds_miji', $arrDic['密级']);
        $this->assign('ds_area', $arrDic['地区']);
        $this->assign('status', $arrDic['使用状态(终端资产)']);
        $this->assign('ds_bmtz', $arrDic['保密台账']);
        $this->assign('ds_zctz', $arrDic['资产台账']);
        $this->assign('ds_sbtype', $arrDic['资产类型']);
        $this->display();
    }

    public function read(){
        $this->display();
    }

    public function getBeiFdata(){
        $queryparam = json_decode(file_get_contents( "php://input"), true);
        $Model = M('it_backuplist');
        $sql_select = 'select * from it_backuplist';
        $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        if (null != $queryparam['limit']) {

            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
        $Result = $Model->query($sql_select);
        echo json_encode(array( 'total' => count($Result),'rows' => $Result));
    }

    public function getdutydept(){
        $Model = M('it_person');
        $dutyman= $_POST['dutyman'];
        $orgid = $Model->where("username='%s'",$dutyman)->getField('orgid');
        $sbtypelist = M('it_depart')->where("id='%s'",$orgid)->field('fullname,id')->select();
        echo json_encode($sbtypelist);
    }

    public function submit(){
        $Model = M('it_terminal');
        $data = $Model->create();
        $data['zd_area'] = $this->getDicById($data['zd_area'], 'dic_name'); //地区
        $data['zd_belongfloor'] = $this->getDicLouYuById($data['zd_belongfloor'], 'dic_name'); //楼宇
        $data['zd_factoryname'] = $this->getDicFactort($data['zd_factoryname'], 'dic_name'); //厂家
        $data['zd_modelnumber'] = $this->getDicXingHaoById($data['zd_modelnumber'], 'dic_name'); //型号
        $useOrg    = D('org')->getDeptList($data['zd_useman']);
        $data['zd_usedeptid'] = $useOrg['id'];
        $data['zd_usedeptname'] = $useOrg['fullname'];
        $DutyOrg    = D('org')->getDeptList($data['zd_dutyman']);
        $data['zd_dutydeptname'] = $DutyOrg['fullname'];
        $ip = $data['zd_ipaddress'];
            if(null==$data['zd_atpid']){

                $Fx = D('ip')->addIpCs([$ip], $data['zd_status']);
                if ($Fx != 'success') {
                    exit(makeStandResult(-1, $Fx . 'IP地址已被使用'));
                }
                $data['zd_atpid'] = makeGuid();
                $data['zd_atpcreatetime'] = date('Y-m-d H:i:s', time());;
                $data['zd_atpcreateuser'] = session('user_id');
                $res = $Model->add($data);
                if (empty($res)) {
                    // 修改日志
                    addLog('it_terminal', '对象添加日志', '添加主键为'.$data['zd_atpid'], '失败',$data['zd_atpid']);
                    $this->ajaxReturn("error");
                } else {
                    // 修改日志
                    addLog('it_terminal',  '对象添加日志',  '添加主键为'.$data['zd_atpid'], '成功',$data['zd_atpid']);
                    $this->ajaxReturn("success");
                }
            }else{

                $ipUp = $Model->where("zd_atpid='%s'",array($data['zd_atpid']))->getField('zd_ipaddress');
                $Fx = D('ip')->saveIpCs([$ip],[$ipUp],$data['zd_status']);
                if($Fx != 'success'){
                    exit(makeStandResult(-1, $Fx.'IP地址已被使用'));
                }
                $list = session('list');
                $content = LogContent($data,$list);
                $data['zd_atplastmodifytime'] = date('Y-m-d H:i:s', time());
                $data['zd_atplastmodifyuser'] = session('user_id');

                $res = $Model->where("zd_atpid='%s'",array($data['zd_atpid']))->save($data);
                if (empty($res)) {
                    // 修改日志
                    addLog('it_terminal', '对象修改日志','修改主键为'.$data['zd_atpid'], '失败',$data['zd_atpid']);
                    $this->ajaxReturn("error");
                } else {
                    // 修改日志
                    if(!empty($content)){
                        addLog('it_terminal', '对象修改日志',  $content , '成功',$data['zd_atpid']);
                    }
                    $this->ajaxReturn("success");
                }

            }

    }
    public function submitrealtion(){
        $mainid = I('post.mainid');
        $relationid = I('post.relationid');
        $rlrelation = I('post.rlrelation');
        $rlatpid = makeGuid();
        $mainipaddress = M('it_terminal')->where("zd_atpid='%s'",$mainid )->getField('zd_ipaddress');
        $data = M('it_terminal')->where("zd_atpid='%s'",$relationid )->find();
        $rip =$data['zd_ipaddress'];
        $rtype =$data['zd_type'];
        $date =date('Y-m-d H:i:s', time());
        $userman = session('username');;
        $sql ="insert into it_relation(rl_atpid,rl_cmainid,rl_cname,rl_rmainid,rl_relation,rl_rname,rl_rtype,rl_rltime,rl_rluser) values('".
            $rlatpid."','".$mainid."','".$mainipaddress."','".$relationid."','".$rlrelation."','". $rip."','".
            $rtype."','".$date."','".$userman."')";
//        $this->recordLog('add', 'relation','关联资产;rl_cmainid:'.$mainipaddress.';rl_rmainid:'.$rip.';','relation',$rlatpid);
        try{
            M()->execute($sql);
        }
        catch (\Exception $e){
            echo $e;
        }
    }

    public function assignrelation(){
        $Model = M('it_dictionary');
        $data =$Model->where("d_belongtype='%s'","关联关系")->field('d_dictname,d_atpid')->select();
        $this->assign('ds_relation',$data);

    }


    public function getbuildingname($id){
        $building =M('it_dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getareaname($id){
        $building =M('it_dictionary')->where("d_atpid='%s'",$id)->field('d_dictname')->find();
        return $building['d_dictname'];
    }
    public function getusername($id){
        $building =M('it_person')->where("id='%s'",$id)->field('realusername')->find();
        return $building['realusername'];
    }

    public function terminal_exp(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $queryparam = I('get.');
        $Model = M();
        $sql_select="
                select * from it_terminal du";
        $sql_count="
                select
                    count(1) c
                from it_terminal du
                ";
        $sql_select = $this->buildSql($sql_select,"zd_atpstatus is null");
        $sql_count = $this->buildSql($sql_count,"zd_atpstatus is null");


        if ("" != $queryparam['sbbm']){
            $searchcontent = trim($queryparam['sbbm']);
            $sql_select = $this->buildSql($sql_select,"zd_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"zd_devicecode like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['ipaddess']){
            $searchcontent = trim($queryparam['ipaddess']);
            $sql_select = $this->buildSql($sql_select,"zd_ipaddress like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"zd_ipaddress like '%".$searchcontent."%'");
        }

        if ("" != $queryparam['macaddess']){
            $searchcontent      = trim($queryparam['macaddess']);
            $searchcontentupper = strtoupper($searchcontent);
            $sql_select = $this->buildSql($sql_select,"(du.zd_macaddress like '%".$searchcontent."%' or upper(du.zd_macaddress) like '%".$searchcontentupper."%')");
            $sql_count = $this->buildSql($sql_count,"(du.zd_macaddress like '%".$searchcontent."%' or upper(du.zd_macaddress) like '%".$searchcontentupper."%')");
        }

        if ("" != $queryparam['seqno']){
            $searchcontent = trim($queryparam['seqno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_seqno like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_seqno like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['sbtype']){
            $searchcontent = trim($queryparam['sbtype']);
            $sql_select = $this->buildSql($sql_select,"du.zd_type ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_type ='".$searchcontent."'");
        }
        if ("" != $queryparam['factory']){
//            $searchcontent = trim($queryparam['factory']);
            $searchcontent = $this->getDicFactort($queryparam['factory'], 'dic_name'); //厂家
            $sql_select = $this->buildSql($sql_select,"du.zd_factoryname ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_factoryname ='".$searchcontent."'");
        }
        if ("" != $queryparam['model']){
//            $searchcontent = trim($queryparam['model']);
            $searchcontent = $this->getDicXingHaoById($queryparam['model'], 'dic_name'); //型号
            $sql_select = $this->buildSql($sql_select,"du.zd_modelnumber ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_modelnumber ='".$searchcontent."'");
        }
        if ("" != $queryparam['area']){
//            $searchcontent = trim($queryparam['area']);
            $searchcontent = $this->getDicById($queryparam['area'], 'dic_name'); //地区
            $sql_select = $this->buildSql($sql_select,"du.zd_area ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_area ='".$searchcontent."'");
        }
        if ("" != $queryparam['building']){
//            $searchcontent = trim($queryparam['building']);
            $searchcontent = $this->getDicLouYuById($queryparam['building'], 'dic_name'); //楼宇
            $sql_select = $this->buildSql($sql_select,"du.zd_belongfloor ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_belongfloor ='".$searchcontent."'");
        }
        if ("" != $queryparam['roomno']){
            $searchcontent = trim($queryparam['roomno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_roomno like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_roomno like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['usedept']){
            $searchcontent = trim($queryparam['usedept']);
//            $searchcontent = D('Depart')->getDeptSubIdById($searchcontent);
            $sql_select = $this->buildSql($sql_select,"du.zd_usedeptid in (".$searchcontent.")");
            $sql_count = $this->buildSql($sql_count,"du.zd_usedeptid in (".$searchcontent.")");
        }
        if ("" != $queryparam['userman']){
            $searchcontent = trim($queryparam['userman']);
            $sql_select = $this->buildSql($sql_select,"du.zd_useman ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_useman ='".$searchcontent."'");
        }
        if ("" != $queryparam['dutydept']){
            $searchcontent = trim($queryparam['dutydept']);
//            $searchcontent = D('Depart')->getDeptSubIdById($searchcontent);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutydeptid in (".$searchcontent.")");
            $sql_count = $this->buildSql($sql_count,"du.zd_dutydeptid in (".$searchcontent.")");
        }
        if ("" != $queryparam['dutyman']){
            $searchcontent = trim($queryparam['dutyman']);
            $sql_select = $this->buildSql($sql_select,"du.zd_dutyman ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_dutyman ='".$searchcontent."'");
        }
        if ("" != $queryparam['isavailable']){
            $searchcontent = trim($queryparam['isavailable']);
            $sql_select = $this->buildSql($sql_select,"du.zd_status ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_status ='".$searchcontent."'");
        }
        if ("" != $queryparam['secretlevel']){
            $searchcontent = trim($queryparam['secretlevel']);
            $sql_select = $this->buildSql($sql_select,"du.zd_secretlevel ='".$searchcontent."'");
            $sql_count = $this->buildSql($sql_count,"du.zd_secretlevel ='".$searchcontent."'");
        }
        if ("" != $queryparam['terminalname']){
            $searchcontent = trim($queryparam['terminalname']);
            $sql_select = $this->buildSql($sql_select,"du.zd_name like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_name like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['terminalcode']){
            $searchcontent = trim($queryparam['terminalcode']);
            $sql_select = $this->buildSql($sql_select,"du.zd_devicecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_devicecode like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['diskno']){
            $searchcontent = trim($queryparam['diskno']);
            $sql_select = $this->buildSql($sql_select,"du.zd_harddiskseq like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_harddiskseq like '%".$searchcontent."%'");
        }
        if ("" != $queryparam['bubiaono']){
            $searchcontent = trim($queryparam['bubiaono']);
            $sql_select = $this->buildSql($sql_select,"du.zd_anecode like '%".$searchcontent."%'");
            $sql_count = $this->buildSql($sql_count,"du.zd_anecode like '%".$searchcontent."%'");
        }
        if (null != $queryparam['sort']) {
            if($queryparam['sort'] == 'zd_building') $queryparam['sort'] = 'zd_belongfloor';
            if($queryparam['sort'] == 'zd_atpsort'){
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ',zd_atplastmodifytime desc,zd_atpid desc ';
            }else{
                $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
            }
        } else {
            $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
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
//        $Count = $Model->query($sql_count);
        foreach($Result as $key=> &$value){
            $duty = D('org')->getUserNames($value['zd_dutyman']);
            $Result[$key]['zd_dutyman'] = $duty['realusername'].'('.$duty['domainusername'].')';
            $use = D('org')->getUserNames($value['zd_useman']);
            $Result[$key]['zd_useman'] = $use['realusername'].'('.$use['domainusername'].')';
            $Result[$key]['zd_dutydeptname'] = D('org')->getDeptName($value['zd_dutydeptid']);
            $Result[$key]['zd_usedeptname'] = D('org')->getDeptName($value['zd_usedeptid']);

        }

        $header = array('设备编码', '设备名称','设备类型', '厂家', '型号', '密级', '出厂编号','部标编码','状态', 'IP地址','子网掩码', 'MAC地址','默认网关','显示器型号','操作系统及版本','配置视频干扰器','使用人','责任人','责任部门','地区', '楼宇', '房间号', '采购日期', '维保日期', '到保日期', '启用日期','管理方式','仪设台账', '保密台账','备注','硬盘序列号','操作系统安装日期','是否安装隔离插座','序列号','交换机IP端口信息','交换机IP地址');
        foreach($header as &$val){
            $val = iconv('utf-8','gbk',$val);
        }
        $filename = date('Ymd').time().rand(0,1000).'.csv';
        $filePath = 'Public/export/'.date('Y-m-d');
        if(!is_dir($filePath)) mkdir($filePath, 0777, true);

        $fp = fopen($filePath.'/'.$filename,'w');
        fputcsv($fp, $header);

//        $Result = changeCoding($Result);

        foreach($Result as $key=>$value) {
            $data = [];
            $data[] = $value['zd_devicecode']; //设备编码
            $data[] = $value['zd_name']; //设备名称
            $data[] = $value['zd_type']; //设备类型
            $data[] = $value['zd_factoryname']; //厂家
            $data[] = $value['zd_modelnumber']; //型号
            $data[] = $value['zd_secretlevel']; //密级
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
            $data[] = $value['zd_useman'] ; //使用人
            $data[] = $value['zd_dutyman'] ; //责任人
            $data[] = $value['zd_dutydeptname']; //责任部门
            $data[] = $value['zd_area']; //地区
            $data[] = $value['zd_belongfloor']; //楼宇
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
            $data[] = $value['zd_swport'] . "\t"; //交换机IP端口信息
            $data[] = $value['zd_swip'] . "\t"; //交换机IP地址
            foreach($data as &$v){
                $v = iconv('utf-8','gbk',$v);
            }
//            print_r($data);die;
            fputcsv($fp, $data);
        }
        $fileFullPath = $filePath.'/'.$filename;
        $relativePath = $_SERVER['SCRIPT_NAME'];
        $pathData = explode('index.php', $relativePath);
        $fileRootPath = $pathData[0];
        header('location:'.$fileRootPath.$fileFullPath);
    }

}