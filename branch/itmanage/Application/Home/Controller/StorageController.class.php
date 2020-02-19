<?php
namespace Home\Controller;
use Think\Controller;
class StorageController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","访问集中存储设备管理页面","成功");
        //字典
        $arr = ['密级', '厂家', '使用状态(集中存储设备)', '资产来源', '地区','设备类型(集中存储设备)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $factory = D('Dic')->getFactoryList('集中存储设备');
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('changJia', $factory);
        $this->assign('zhuangTai', $arrDic['使用状态(集中存储设备)']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('sbtype', $arrDic['设备类型(集中存储设备)']);

        $this->display();
    }    

    /**
    * 集中存储设备管理添加或修改
    */
    public function add(){
        $id = trim(I('get.sto_atpid'));
        $Objtype = trim(I('get.type'));
        if(!empty($id)){
            $model = M('storage');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('sto_atpid,sto_gateway,sto_dutyman,sto_remark,sto_useman,sto_assetsource,sto_purchasetime,sto_wwn,sto_type,sto_area,sto_usedept,sto_atpstatus,sto_startusetime,sto_ip,sto_belongfloor,sto_assetdutydept,sto_mask,sto_mac,sto_jigui,sto_roomno,sto_disknum,sto_modelnumber,sto_sn,sto_factory,sto_submac,sto_devicecode,sto_hba,sto_assetusedept,sto_jiwei,sto_status,sto_secretlevel,sto_subip,sto_disksn,sto_dutydept,sto_anecode,sto_gxip')
                ->where("sto_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //使用人
            $userId = $data['sto_useman'];
            if (!empty($userId)) {
                $userName = D('org')->getViewPerson($userId);
                $userMan['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $userMan['username'] = $userName['username'];

                $userDept = $this->removeStr($userName['orgfullname']); //去掉字符串
            } else {
                $userMan = [];
                $userDept = '';
            }
            $this->assign('userman', $userMan);
            $this->assign('userDept', $userDept);

            //责任人
            $dutuserId = $data['sto_dutyman'];
            if (!empty($dutuserId)) {
                $dutuserName = D('org')->getViewPerson($dutuserId);
                $dutuser['name'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')--' . $dutuserName['orgname'];
                $dutuser['username'] = $dutuserName['username'];

                $dutyDept = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
            } else {
                $dutuser = [];
                $dutyDept = '';
            }
            $this->assign('dutuser', $dutuser);
            $this->assign('dutyDept', $dutyDept);

            //资产责任单位
            $deptId = $data['sto_assetdutydept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getDepartId($deptId);
                $dutydept['name'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //使用责任单位
            $deptId = $data['sto_assetusedept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getDepartId($deptId);

                $usedept['name'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                $usedept['id'] = $deptInfo['id'];
            } else {
                $usedept = [];
            }
            $this->assign('usedept', $usedept);

        }
        $arr = ['密级', '地区', '厂家', '型号', '使用状态(集中存储设备)', '资产来源','设备类型(集中存储设备)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('Objtype', $Objtype);
        $factory = D('Dic')->getFactoryList('集中存储设备');
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->assign('changJia', $factory);
        $this->assign('xingHao', $arrDic['型号']);
        $this->assign('zhuangTai', $arrDic['使用状态(集中存储设备)']);
        $this->assign('ziYuanLaiYuan', $arrDic['资产来源']);
        $this->assign('sbType', $arrDic['设备类型(集中存储设备)']);

        $this->assign('data', $data);

        addLog('','用户访问日志',"访问集中存储设备管理添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['sto_atpid']);

        //验证ip
        if (!empty($data['sto_ip'])) {
            if ($this->checkAddress($data['sto_ip'], 'IP') === false) exit(makeStandResult(-1, 'ip地址有误'));
        } else {
            exit(makeStandResult(-1, 'ip地址不能为空'));
        }
        //子网掩码
        if (!empty($data['sto_mask'])) {
            if ($this->checkAddress($data['sto_mask'], 'IP') === false) exit(makeStandResult(-1, '子网掩码有误'));
        }
        //子IP地址
        if (!empty($data['sto_subip'])) {
            $sto_subips  = str_replace(',',';',$data['sto_subip']);
            $sto_subips = explode(';',$sto_subips);
            foreach($sto_subips as $ip) {
                if ($this->checkAddress($ip, 'IP') === false) exit(makeStandResult(-1, '子IP地址有误'));
            }
        }

        //验证mac
        if (!empty($data['sto_mac'])) {
            if ($this->checkAddress($data['sto_mac'], 'MAC') === false) exit(makeStandResult(-1, 'mac地址有误'));
        }
        //子mac地址有误
        if (!empty($data['sto_submac'])) {
            $sto_submacs  = str_replace(',',';',$data['sto_submac']);
            $sto_submacs = explode(';',$sto_submacs);
            foreach($sto_submacs as $ip) {
                if ($this->checkAddress($ip, 'MAC') === false) exit(makeStandResult(-1, '子mac地址有误'));
            }
        }

        //验证硬盘数量
        $sto_disksn = $data['sto_disksn'];
        if (!empty($sto_disksn)) {
            if ($data['sto_disknum'] != count(explode(';', $sto_disksn))) {
                exit(makeStandResult(-1, '硬盘数量与硬盘序列号不符合，请以 ; 隔开'));
            }
        }
        //验证HBA口数
        $sto_wwnno = $data['sto_wwnno'];
        if (!empty($sto_wwnno)) {
            if ($data['sto_hbanum'] != count(explode(';', $sto_wwnno))) {
                exit(makeStandResult(-1, 'HBA口数量与WWN号不符合，请以 ; 隔开'));
            }
        }

        $sto_subips  = str_replace(',',';',$data['sto_subip']);
        $subips = explode(';',$sto_subips);
        $stoip = [$data['sto_ip']];
        $ip = array_merge($subips,$stoip);
        $ip = array_filter($ip);
        $ips = array_count_values($ip);
        foreach($ips as $key =>$val){
            if($val > 1){
                exit(makeStandResult(-1, '页面中填写的'.$key.'重复，请修改后提交'));
            }
        }
        $model = M('storage');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['sto_mac'] = strtoupper($data['sto_mac']);
        $data['sto_submac'] = strtoupper($data['sto_submac']);
        $data['sto_area'] = $this->getDicById($data['sto_area'], 'dic_name'); //地区
        $data['sto_belongfloor'] = $this->getDicLouYuById($data['sto_belongfloor'], 'dic_name'); //楼宇
        $data['sto_factory'] = $this->getDicFactort($data['sto_factory'], 'dic_name') ; //厂家
        $data['sto_modelnumber'] = $this->getDicXingHaoById($data['sto_modelnumber'], 'dic_name'); //型号
        if(empty($id)){
            $model->startTrans();
            try {
                //验证地址是否已被使用
                $Fx = D('ip')->addIpCs($ip, $data['sto_status']);
                if ($Fx != 'success') {
                    exit(makeStandResult(-1, $Fx . 'IP地址已被使用'));
                }
                $data['sto_atpid'] = makeGuid();

                $data['sto_createtime'] = $time;
                $data['sto_createuser'] = $user;
                $data = $model->create($data);
                $res = $model->add($data);
                $model->commit();
                // 修改日志
                addLog('storage', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            } catch (\Exception $e) {
                $model->rollback();
                addLog('storage', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }
        }else{
            try {
                $sevList = $model->where("sto_atpid = '%s'",$id)->find();
                $subipsUp  = str_replace(',',';',$sevList['sto_subip']);
                $subipsUp = explode(';',$subipsUp);
                $ipIlopUp = [$sevList['sto_ip']];
                $ipUp = array_merge($subipsUp,$ipIlopUp);
                $ipUp = array_filter($ipUp);
                //验证ip是否已被使用
                $Fx = D('ip')->saveIpCs($ip,$ipUp,$data['sto_status']);
                if($Fx != 'success'){
                    exit(makeStandResult(-1, $Fx.'IP地址已被使用'));
                }
                $data = $model->create($data);
                $list = session('list');
                $content = LogContent($data,$list);
                $data['sto_modifytime'] = $time;
                $data['sto_modifyuser'] = $user;
                $res = $model->where("sto_atpid='%s'", $id)->save($data);
                $model->commit();
                if(!empty($content)) {
                    // 修改日志
                    addLog('storage', '对象修改日志', $content, '成功', $id);
                }
                exit(makeStandResult(1,'修改成功'));
            } catch (\Exception $e) {
                $model->rollback();
                // 修改日志
                addLog('storage', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }


//            if(empty($res)){
//                // 修改日志
//                addLog('storage', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
//                exit(makeStandResult(-1,'修改失败'));
//            }else{
//                //添加光纤交换机关联关系
//                $this->changeRelationNet($data['sto_atpid'], '', $data['sto_ip'], $data['sto_type'], 'storage', $stoGxipId);
//                //添加机柜关联关系
//                $this->changeRelationJigui($data['sto_atpid'], '', $data['sto_ip'], $data['sto_type'], 'storage', $stoJiguiId);
//                // 修改日志
//                addLog('storage', '对象修改日志', 'update', '修改xxx'. '成功','成功');
//                exit(makeStandResult(1,'修改成功'));
//            }
        }
    }    

    /**
     * 获取集中存储设备管理数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'sto_devicecode,sto_anecode,sto_type,sto_ip,sto_mask,sto_gateway,sto_subip,sto_mac,sto_submac,sto_factory,sto_modelnumber,sto_sn,sto_status,sto_secretlevel,sto_assetsource,sto_assetdutydept,sto_assetusedept,sto_purchasetime,sto_startusetime,sto_disknum,sto_disksn,sto_hba,sto_wwn,sto_gxip,sto_area,sto_belongfloor,sto_roomno,sto_jigui,sto_jiwei,sto_dutyman,sto_dutydept,sto_useman,sto_usedept,sto_remark';
        }else{
            $filedStr = 'sto_devicecode,sto_anecode,sto_type,sto_ip,sto_mask,sto_gateway,sto_subip,sto_mac,sto_submac,sto_factory,sto_modelnumber,sto_sn,sto_status,sto_secretlevel,sto_assetsource,sto_assetdutydept,sto_assetusedept,sto_purchasetime,sto_startusetime,sto_disknum,sto_disksn,sto_hba,sto_wwn,sto_gxip,sto_area,sto_belongfloor,sto_roomno,sto_jigui,sto_jiwei,sto_dutyman,sto_dutydept,sto_useman,sto_usedept,sto_remark, sto_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['sto_atpstatus'] = ['exp', 'IS NULL'];
        $stoDevicecode = strtolower(trim($queryParam['sto_devicecode']));
        if(!empty($stoDevicecode)) $where['lower(sto_devicecode)'] = ['like', "%$stoDevicecode%"];
        
        $stoAnecode = strtolower(trim($queryParam['sto_anecode']));
        if(!empty($stoAnecode)) $where['lower(sto_anecode)'] = ['like', "%$stoAnecode%"];
        
        $stoIp = trim($queryParam['sto_ip']);
        if(!empty($stoIp)) {
            $where[0]['sto_ip'] = ['like', "%$stoIp%"];
            $where[0]['sto_subip'] = ['like', "%$stoIp%"];
            $where[0]['_logic'] = 'OR';
        }
        
        $stoName = strtolower(trim($queryParam['sto_mac']));
        if(!empty($stoName)) {
            $where[1]['lower(sto_mac)'] = ['like', "%$stoName%"];
            $where[1]['lower(sto_submac)'] = ['like', "%$stoName%"];
            $where[1]['_logic'] = 'OR';
        }
        
        $stoFactory = trim($queryParam['sto_factory']);
        if(!empty($stoFactory)) {
            $stoFactory = $this->getDicFactort($stoFactory, 'dic_name');
            $where['sto_factory'] = ['like', "%$stoFactory%"];
        }
        
        $stoModelnumber = trim($queryParam['sto_modelnumber']);
        if(!empty($stoModelnumber)) {
            $stoModelnumber = $this->getDicXingHaoById($stoModelnumber, 'dic_name');
            $where['sto_modelnumber'] = ['like', "%$stoModelnumber%"];
        }
        
        $stoSn = trim($queryParam['sto_sn']);
        if(!empty($stoSn)) $where['sto_sn'] = ['like', "%$stoSn%"];
        
        $stoStatus = trim($queryParam['sto_status']);
        if(!empty($stoStatus)) $where['sto_status'] = ['like', "%$stoStatus%"];
        
        $stoSecretlevel = trim($queryParam['sto_secretlevel']);
        if(!empty($stoSecretlevel)) $where['sto_secretlevel'] = ['like', "%$stoSecretlevel%"];
        
        $stoAssetsource = trim($queryParam['sto_assetsource']);
        if(!empty($stoAssetsource)) $where['sto_assetsource'] = ['like', "%$stoAssetsource%"];
        
        // $stoGxip = trim($queryParam['sto_gxip']);
        // if(!empty($stoGxip)) $where['sto_gxip'] = ['like', "%$stoGxip%"];
        
        $stoArea = trim($queryParam['sto_area']);
        if(!empty($stoArea)) {
            $stoArea = $this->getDicById($stoArea, 'dic_name');
            $where['sto_area'] = ['like', "%$stoArea%"];
        }
        
        $stoBelongfloor = trim($queryParam['sto_belongfloor']);
        if(!empty($stoBelongfloor)) {
            $stoBelongfloor = $this->getDicLouYuById($stoBelongfloor, 'dic_name');
            $where['sto_belongfloor'] = ['like', "%$stoBelongfloor%"];
        }
        
        // $stoJigui = trim($queryParam['sto_jigui']);
        // if(!empty($stoJigui)) $where['sto_jigui'] = ['like', "%$stoJigui%"];
        
        $stoDutyman = trim($queryParam['sto_dutyman']);
        if(!empty($stoDutyman)) {

            $where['sto_dutyman'] = ['like', "%$stoDutyman%"];
        }
        
        $stoDutydept = trim($queryParam['sto_dutydept']);
        if(!empty($stoDutydept)) {

            $where['sto_dutydept'] = ['like', "%$stoDutydept%"];
        }
        
        $stoUseman = trim($queryParam['sto_useman']);
        if(!empty($stoUseman)) $where['sto_useman'] = ['like', "%$stoUseman%"];
        
        $stoUsedept = trim($queryParam['sto_usedept']);
        if(!empty($stoUsedept)) $where['sto_usedept'] = ['like', "%$stoUsedept%"];
        
        $model = M('storage');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['设备编码','部标编码','设备类型','IP地址','子网掩码','默认网关','子IP地址','MAC地址','子MAC地址','厂家','型号','出厂编号','使用状态','密级','资产来源','资产责任单位（仪设台账）','使用责任单位（保密台账）','采购日期','上线/下线日期（启用日期）','硬盘块数','硬盘序列号','HBA口数','WWN号','光纤交换机IP地址','地区','楼宇','房间号','机柜','机位','责任人','责任部门','使用人','使用部门','备注'];
            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['sto_area'] = !empty($v['sto_area']) ? $this->getDicById($v['sto_area'], 'dic_name') : '-'; //地区
//                $v['sto_belongfloor'] = !empty($v['sto_belongfloor']) ? $this->getDicLouYuById($v['sto_belongfloor'], 'dic_name') : '-'; //楼宇
//                $v['sto_factory'] = !empty($v['sto_factory']) ? $this->getDicById($v['sto_factory'], 'dic_name') : '-'; //厂家
//                $v['sto_modelnumber'] = !empty($v['sto_modelnumber']) ? $this->getDicXingHaoById($v['sto_modelnumber'], 'dic_name') : '-'; //型号
//                $v['sto_status'] = !empty($v['sto_status']) ? $this->getDicById($v['sto_status'], 'dic_name') : '-'; //状态
//                $v['sto_secretlevel'] = !empty($v['sto_secretlevel']) ? $this->getDicById($v['sto_secretlevel'], 'dic_name') : '-'; //密级
//                $v['sto_assetsource'] = !empty($v['sto_assetsource']) ? $this->getDicById($v['sto_assetsource'], 'dic_name') : '-'; //资产来源

                //使用人
                if (!empty($v['sto_useman'])) {
                    $userName = D('org')->getViewPerson($v['sto_useman']);
                    $v['sto_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['sto_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sto_useman'] = '-';
                    $v['sto_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sto_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sto_dutyman']);
                    $v['sto_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['sto_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sto_dutyman'] = '-';
                    $v['sto_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['sto_assetdutydept'])) {
                    $deptInfo = D('org')->getDepartId($v['sto_assetdutydept']);
                    $v['sto_assetdutydept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                } else {
                    $v['sto_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['sto_assetusedept'])) {
                    $deptInfo = D('org')->getDepartId($v['sto_assetusedept']);
                    $v['sto_assetusedept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                } else {
                    $v['sto_assetusedept'] = '-';
                }
            }
            if($count <= 0){
              exit(makeStandResult(-1, '没有要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach ($data as $k => &$v) {

                //使用人
                if (!empty($v['sto_useman'])) {
                    $userName = D('org')->getViewPerson($v['sto_useman']);
                    $v['sto_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //使用人部门
                    $v['sto_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sto_useman'] = '-';
                    $v['sto_usedept'] = '-';
                }
                //责任人
                if (!empty($v['sto_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['sto_dutyman']);
                    $v['sto_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                    //责任人部门
                    $v['sto_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                } else {
                    $v['sto_dutyman'] = '-';
                    $v['sto_dutydept'] = '-';
                }
                //资产责任单位
                if (!empty($v['sto_assetdutydept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['sto_assetdutydept']);
                    $v['sto_assetdutydept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['sto_assetdutydept'] = '-';
                }

                //使用责任单位
                if (!empty($v['sto_assetusedept'])) {
                    $deptInfo = D('org')->getOrgInfo($v['sto_assetusedept']);
                    $v['sto_assetusedept'] = $this->removeStr($deptInfo['org_fullname']); //去掉字符串
                } else {
                    $v['sto_assetusedept'] = '-';
                }
                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['sto_atpid'])->count();
                $data[$k]['stoCount'] = $rlxCount;
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }

    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.sto_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('storage');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['sto_modifytime'] = $time;
                $data['sto_modifyuser'] = $user;
                $data['sto_atpstatus'] = 'DEL';
                $res = $model->where("sto_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('storage', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    //删除关联关系
                    // D('relation')->delRelation($id, 'storage');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('storage', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }
//
//    //ajax验证ip查重
//    public function ajaxIpCheck()
//    {
//        $ip = I('post.sto_ip');
//        $res = D('sev')->stoIpChecking($ip);
//        if ($res) {
//            exit(makeStandResult(-1, '输入的ip重复'));
//        } else {
//            exit(makeStandResult(1, '成功'));
//        }
//    }

    /**
     * 批量增加
     */
    public function saveCopyTables()
    {
        $receiveData = $_POST;
        $head = explode(',', $receiveData['head']);
        $data = $receiveData['data'];
        if (empty($data)) exit(makeStandResult(-1, '未接收到数据'));
        $data = json_decode($data, true);

        $reduce = 0;
        if ($head[0] == '序号') {
            foreach ($data as &$value) {
                unset($value[0]);
            }
            unset($value);
            $reduce = 1;
        }

        $time = date('Y-m-d H:i:s');
        $loginUserId = session('user_id');

        //字典
        $arrField = ['密级', '资产来源', '地区', '厂家', '使用状态'];
        $arrDic = D('Dic')->getDicValueByName($arrField);
        //当前资产字典id
        $res = M('dic')->field('dic_id')
            ->where("dic_status=0 and dic_type='DIC000004' and dic_name='集中存储设备'")
            ->find();
        $stoDicId = $res['dic_id'];

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi, 'dic_name');
        $diQu = $arrDic['地区'];
        $diQuArray = array_column($diQu, 'dic_name');

        $changJia = D('Dic')->getFactoryList('集中存储设备');
        $changJiaArray = array_column($changJia, 'dic_name');

        $ziYuanLaiYuan = $arrDic['资产来源'];
        $ziYuanLaiYuanArray = array_column($ziYuanLaiYuan, 'dic_name');
        $zhuangTai = $arrDic['使用状态'];
        $zhuangTaiArray = array_column($zhuangTai, 'dic_name');

        //字段
        $fields = ['sto_devicecode', 'sto_anecode','sto_type', 'sto_sn', 'sto_ip', 'sto_mask', 'sto_gateway', 'sto_subip', 'sto_mac', 'sto_submac', 'sto_factory', 'sto_modelnumber', 'sto_area', 'sto_belongfloor', 'sto_roomno', 'sto_status', 'sto_secretlevel', 'sto_assetsource', 'sto_assetdutydept', 'sto_assetusedept', 'sto_purchasetime', 'sto_startusetime', 'sto_disknum', 'sto_disksn', 'sto_hba', 'sto_wwn', 'sto_jiwei', 'sto_dutyman', 'sto_useman', 'sto_remark'];
        //'sto_devicecode', 'sto_anecode', 'sto_sn', 'sto_ip', 'sto_mask', 'sto_gateway', 'sto_subip', 'sto_mac', 'sto_submac', 'sto_factory', 'sto_modelnumber', 'sto_area', 'sto_belongfloor', 'sto_roomno', 'sto_status', 'sto_secretlevel', 'sto_assetsource', 'sto_assetdutydept', 'sto_assetusedept', 'sto_purchasetime', 'sto_startusetime', 'sto_disknum', 'sto_disksn', 'sto_hba', 'sto_wwn', 'sto_jiwei', 'sto_dutyman', 'sto_useman', 'sto_remark'
        //设备编码,部标编码,出厂编号,IP地址,子网掩码,默认网关,子IP地址,MAC地址,子MAC地址,厂家,型号,地区,楼宇,房间号,使用状态,密级,资产来源,资产责任单位,使用责任单位,采购日期,上线/下线日期,硬盘块数,硬盘序列号,HBA口数,WWN号,机位,责任人,使用人,备注

        $model = M('storage');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $error = '';
        $initTables = []; //初始化新增数组
        $todoNames = [];
        $exportNum = count($data);

        foreach ($data as $key => $value) {

            $lineNum = $key + 1; //表格行号
            $arr = [];
            foreach ($value as $k => $v) {
                $field = $fields[$k - $reduce];
                switch ($field) {
                    case 'sto_factory': //厂家
                        $deptNameField = 'sto_factory';
                        $fieldName = '厂家';
                        if (!empty($v)) {
                            if (!in_array($v, $changJiaArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $changJiaArray);
                            }
                            $dicId = $changJia[$k]['dic_id'];
                            $dicName = $changJia[$k]['dic_name'];
                            $arr[$deptNameField] = $dicName;
                            //型号
                            $xingHaoData = $v;
                            //查字典
                            $xingHao = $this->getDicXingHaoByPid($dicId, $stoDicId);
                            $xingHaoArray = array_column($xingHao, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'sto_modelnumber': //型号
                        $deptNameField = 'sto_modelnumber';
                        $fieldName = '型号';
                        if (!empty($xingHaoData)) {
                            if (!empty($v)) {
                                if (!in_array($v, $xingHaoArray)) {
                                    $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                    break;
                                } else {
                                    $k = array_search($v, $xingHaoArray);
                                }
                                $arr[$deptNameField] = $xingHao[$k]['dic_name'];
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        } else {
                            if (!empty($v)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 需要填写厂家<br>";
                                break;
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        }
                        break;

                    case 'sto_secretlevel': //密级
                        $deptNameField = 'sto_secretlevel';
                        $fieldName = '密级';
                        if (!empty($v)) {
                            if (!in_array($v, $miJiArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $miJiArray);
                            }
                            $dicId = $miJi[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'sto_assetsource': //资产来源
                        $deptNameField = 'sto_assetsource';
                        $fieldName = '资产来源';
                        if (!empty($v)) {
                            if (!in_array($v, $ziYuanLaiYuanArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $ziYuanLaiYuanArray);
                            }
                            $dicId = $ziYuanLaiYuan[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    
                    case 'sto_area': //地区
                        $deptNameField = 'sto_area';
                        $fieldName = '地区';
                        if (!empty($v)) {
                            if (!in_array($v, $diQuArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $diQuArray);
                            }
                            $dicId = $diQu[$k]['dic_id'];
                            $dicName = $diQu[$k]['dic_name'];
                            $arr[$deptNameField] = $dicName;
                            //楼宇
                            $diQuData = $v;
                            //查字典
                            $louYu = $this->getDicLouYuByPid($dicId);
                            $louYuArray = array_column($louYu, 'dic_name');
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;

                    case 'sto_belongfloor': //楼宇
                        $deptNameField = 'sto_belongfloor';
                        $fieldName = '楼宇';
                        if (!empty($diQuData)) {
                            if (!empty($v)) {
                                if (!in_array($v, $louYuArray)) {
                                    $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                    break;
                                } else {
                                    $k = array_search($v, $louYuArray);
                                }
                                $arr[$deptNameField] = $louYu[$k]['dic_name'];
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        } else {
                            if (!empty($v)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 需要填写地区<br>";
                                break;
                            } else {
                                $arr[$deptNameField] = $v;
                            }
                        }
                        break;
                    case 'sto_status': //使用状态
                        $deptNameField = 'sto_status';
                        $fieldName = '使用状态';
                        if (!empty($v)) {
                            if (!in_array($v, $zhuangTaiArray)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            } else {
                                $k = array_search($v, $zhuangTaiArray);
                            }
                            $dicId = $zhuangTai[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        } else {
                            $arr[$deptNameField] = $v;
                        }
                        break;


                    case 'sto_sn': //出厂编号
                        $arr[$field] = $v;
                        break;
                    case 'sto_type': //设备类型
                        $arr[$field] = $v;
                        break;
                    case 'sto_disknum': //硬盘块数
                        $arr[$field] = $v;
                        break;
                    case 'sto_disksn': //硬盘序列号
                        $arr[$field] = $v;
                        break;
                    case 'sto_hba': //HBA口数
                        $arr[$field] = $v;
                        break;
                    case 'sto_wwnno': //WWN号
                        $arr[$field] = $v;
                        break;
                    case 'sto_roomno': //房间号
                        $arr[$field] = $v;
                        break;
                    case 'sto_jiwei': //机位
                        $arr[$field] = $v;
                        break;

                    case 'sto_remark': //备注
                        $arr[$field] = $v;
                        break;

                    case 'sto_devicecode': //设备编码
                        $arr[$field] = $v;
                        break;
                    case 'sto_anecode': //部标编码
                        $arr[$field] = $v;
                        break;

                    case 'sto_mac': //MAC地址
                        $fieldName = 'MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sto_submac': //子MAC地址
                        $fieldName = '子MAC地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'MAC') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    
                    case 'sto_ip': //IP地址
                        $fieldName = 'IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                            //ip查重
                            $res = D('sev')->stoIpChecking($v);
                            if ($res) {
                                $error .= "第{$lineNum} 行 {$fieldName} 重复<br>";
                                break;
                            }
                        } else {
                            $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                            break;
                        }
                        $arr[$field] = $v;
                        break;
                    case 'sto_subip': //子IP地址
                        $fieldName = '子IP地址';
                        if (!empty($v)) {
                            if ($this->checkAddress($v, 'IP') === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 格式不对<br>";
                                break;
                            }
                        }
                        $arr[$field] = $v;
                        break;
                    

                    case 'sto_purchasetime': //采购日期
                        $fieldName = '采购日期';
                        if (!empty($v)) {
                            $strTime = strtotime($v);
                            if ($strTime === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        } else {
                            $arr[$field] = '';
                        }
                        break;
                    case 'sto_startusetime': //上线/下线日期
                        $fieldName = '上线/下线日期';
                        if (!empty($v)) {
                            $strTime = strtotime($v);
                            if ($strTime === false) {
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        } else {
                            $arr[$field] = '';
                        }
                        break;
                    

                    case 'sto_useman': //使用人
                        $fieldName = '使用人';
                        $userNameField = 'sto_useman';

                        $userInfo = D('org')->getUserNames($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'sto_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'sto_dutyman';
                        $userInfo = D('org')->getUserNames($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'sto_assetdutydept': //资产责任单位
                        $fieldName = '资产责任单位';
                        $userNameField = 'sto_assetdutydept';
                        if(!empty($v)){
                            $orgId = D('org')->getOrgId($v);
                            if (empty($orgId)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写单位全称)<br>";
                                break;
                            }
                            $arr[$userNameField] = $orgId;
                        }else{
                            $arr[$field] = $v;
                        }
                        break;
                    case 'sto_assetusedept': //使用责任单位
                        $fieldName = '使用责任单位';
                        $userNameField = 'sto_assetusedept';
                        if(!empty($v)){
                            $orgId = D('org')->getOrgId($v);
                            if (empty($orgId)) {
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写单位全称)<br>";
                                break;
                            }
                            $arr[$userNameField] = $orgId;
                        }else{
                            $arr[$field] = $v;
                        }
                        break;
                    
                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['sto_createtime'] = $time;
                $arr['sto_createuser'] = $loginUserId;

                $arr['sto_atpid'] = makeGuid();
            }
            $initTables[] = $arr;
        }

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    $res = $model->add($value);
                    if (!empty($res)) $successNum += 1;
                }
                $failNum = $exportNum - $successNum;
                addLog('todo', '对象导入日志', 'add', '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
                $model->commit();
                exit(makeStandResult(1, '添加成功'));
            } else {
                exit(makeStandResult(-1, $error));
            }
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '添加失败'));
        }
    }
}