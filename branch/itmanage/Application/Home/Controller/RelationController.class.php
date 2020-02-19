<?php
namespace Home\Controller;
use Think\Controller;
class RelationController extends BaseController {    
//关联关系
    public function index(){
        addLog("","用户访问日志","访问关联关系页面","成功");
        $this->display();
    }    

    /**
    * 关联关系添加或修改
    */
    public function add(){
        $id = trim(I('get.rl_atpid'));
        //字典
        $arr = ['关联关系'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('guanLianGuanXi', $arrDic['关联关系']);

        addLog('','用户访问日志',"访问关联关系添加、编辑页面",'成功');
        if(!empty($id)){
            $model = M('it_relation');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('rl_atpid,rl_cname,rl_rtype,rl_atplastmodifyuser,rl_atpremark,rl_atpcreateuser,rl_ctype,rl_rmainid,rl_relation,rl_atpcreatetime,rl_atpstatus,rl_rtable,rl_cmainid,rl_ctable,rl_rname,rl_atplastmodifytime')
                ->where("rl_atpid='%s'", $id)
                ->find();
            $this->assign('data', $data);
            $this->display('edit');
        }else{
            $this->display();
        }
    }

    /**
     * 数据添加、修改
     */
    public function addData(){
        $post = I('post.');
        $id = trim($post['rl_atpid']);
        // 这里根据实际需求,进行字段的过滤
        // $post['relation'];

        $model = M('it_relation');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $Mid = $post['Mid'];
        $Mtable = $post['Mtable'];
        $Mtype = $post['Mtype'];
        $Sid = $post['Sid'];
        $Stable = $post['Stable'];
        $Stype = $post['Stype'];
        
        //获取主表字段映射
        $Minfo = $this -> fieldMapping($Mtable,$Mtype);
        //通过id去相应的表查数据
        if (strpos($Mid, ',') !== false) {
            $Mid = explode(',', $Mid);
            $where[$Minfo['id']] = ['in', $Mid];
        } else {
            $where[$Minfo['id']] = ['eq', $Mid];
        }
        $Mdata = M($Mtable)
            ->where($where)
            ->select();
        //获取从表字段映射
        $Sinfo = $this -> fieldMapping($Stable,$Stype);
        //通过id去相应的表查数据
        $where = '';
        if (strpos($Sid, ',') !== false) {
            $Sid = explode(',', $Sid);
            $where[$Sinfo['id']] = ['in', $Sid];
        } else {
            $where[$Sinfo['id']] = ['eq', $Sid];
        }
        $Sdata = M($Stable)
            ->where($where)
            ->select();

        //验证是否都有值
        if(empty($Mdata) && empty($Sdata))exit(makeStandResult(-1,'数据错误'));
        if(empty($id)){
            $n=0;
            //遍历每一条，将两个表的数据合并
            foreach ($Mdata as $Mk => $Mv) {
                foreach ($Sdata as $Sk => $Sv) {
                    //查两侧id是否都存在
                    $src = D('relation')->checkRelationAllId($Mv[$Minfo['id']],$Sv[$Sinfo['id']]);
                    //如果为false 则跳过当前循环
                    if(!$src){
                        $n++;
                        continue;
                    }
                    $data['rl_atpid'] = makeGuid();
                    $data['rl_atpcreatetime'] = $time;
                    $data['rl_atpcreateuser'] = $user;
                    $data['rl_cmainid'] = $Mv[$Minfo['id']];//设备atpid(主）
                    $data['rl_cname'] = !empty($Minfo['name'])?$Mv[$Minfo['name']]:'';//设备名称（主）
                    $data['rl_cip'] = !empty($Minfo['ip'])?$Mv[$Minfo['ip']]:'';//设备ip（主）
                    $data['rl_ctype'] = $Minfo['type'];//关联设备类型（主）
                    $data['rl_ctable'] = $Minfo['table'];//C的table名
                    // $data['rl_relation'] = $post['relation'];//关联关系ID
                    $data['rl_rmainid'] = $Sv[$Sinfo['id']];//设备atpid（从）
                    $data['rl_rname'] = !empty($Sinfo['name'])?$Sv[$Sinfo['name']]:'';//设备名称(从)
                    $data['rl_rip'] = !empty($Sinfo['ip'])?$Sv[$Sinfo['ip']]:'';//设备ip(从)
                    $data['rl_rtype'] = $Sinfo['type'];//关联设备类型（从）
                    $data['rl_rtable'] = $Sinfo['table'];//R的table名
                    // dump($data);die;
                    $data = $model->create($data);
                    $res = $model->add($data);
                }
            }
            if(empty($res)){
                // 修改日志
//                addLog('it_relation', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败,'.$n.'条关系重复'));
            }else{
                // 修改日志
//                addLog('it_relation', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功,'.$n.'条关系重复'));
            }
        }else{
            $n=0;
            //验证是否都有值且只有一条
            if(count($Mdata) !== 1 || count($Sdata) !== 1)exit(makeStandResult(-1,'数据错误'));
            //遍历每一条，将两个表的数据合并
            foreach ($Mdata as $Mk => $Mv) {
                foreach ($Sdata as $Sk => $Sv) {
                    //查两侧id是否都存在
                    $src = D('relation')->checkRelationAllId($Mv[$Minfo['id']],$Sv[$Sinfo['id']]);
                    //如果为false 则跳过当前循环
                    if(!$src){
                        $n++;
                        continue;
                    }
                    $data['rl_atplastmodifytime'] = $time;
                    $data['rl_atplastmodifyuser'] = $user;
                    $data['rl_cmainid'] = $Mv[$Minfo['id']];//设备atpid(主）
                    $data['rl_cname'] = !empty($Minfo['name'])?$Mv[$Minfo['name']]:'';//设备名称（主）
                    $data['rl_cip'] = !empty($Minfo['ip'])?$Mv[$Minfo['ip']]:'';//设备ip（主）
                    $data['rl_ctype'] = $Minfo['type'];//关联设备类型（主）
                    $data['rl_ctable'] = $Minfo['table'];//C的table名
                    // $data['rl_relation'] = $post['relation'];//关联关系ID
                    $data['rl_rmainid'] = $Sv[$Sinfo['id']];//设备atpid（从）
                    $data['rl_rname'] = !empty($Sinfo['name'])?$Sv[$Sinfo['name']]:'';//设备名称(从)
                    $data['rl_rip'] = !empty($Sinfo['ip'])?$Sv[$Sinfo['ip']]:'';//设备ip(从)
                    $data['rl_rtype'] = $Sinfo['type'];//关联设备类型（从）
                    $data['rl_rtable'] = $Sinfo['table'];//R的table名
//                    dump($data);die;
                    $data = $model->create($data);
                    $res = $model->where("rl_atpid='%s'", $id)->save($data);
                }
            }

            if(empty($res)){
                // 修改日志
                // addLog('it_relation', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败,'.$n.'条关系重复'));
            }else{
                // 修改日志
//                addLog('it_relation', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功,'.$n.'条关系重复'));
            }
        }
    }

    /**
     * 获取关联关系数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'rl_cmainid,rl_cname,rl_cip,rl_ctype,rl_ctable,rl_relation,rl_rmainid,rl_rname,rl_rip,rl_rtype,rl_rtable';
        }else{
            $filedStr = 'rl_cmainid,rl_cname,rl_cip,rl_ctype,rl_ctable,rl_relation,rl_rmainid,rl_rname,rl_rip,rl_rtype,rl_rtable, rl_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        
        $rlCname = trim($queryParam['rl_cname']);
        if(!empty($rlCname)) $where['rl_cname'] = ['like', "%$rlCname%"];
        
        $rlRelation = trim($queryParam['rl_relation']);
        if(!empty($rlRelation)) $where['rl_relation'] = ['like', "%$rlRelation%"];
        
        $rlRname = trim($queryParam['rl_rname']);
        if(!empty($rlRname)) $where['rl_rname'] = ['like', "%$rlRname%"];
        
        $rlCtype = trim($queryParam['rl_ctype']);
        if(!empty($rlCtype)) $where['rl_ctype'] = ['like', "%$rlCtype%"];
        
        $rlRtype = trim($queryParam['rl_rtype']);
        if(!empty($rlRtype)) $where['rl_rtype'] = ['like', "%$rlRtype%"];
        
        $rlCtable = trim($queryParam['rl_ctable']);
        if(!empty($rlCtable)) $where['rl_ctable'] = ['like', "%$rlCtable%"];
        
        $rlRtable = trim($queryParam['rl_rtable']);
        if(!empty($rlRtable)) $where['rl_rtable'] = ['like', "%$rlRtable%"];
        
        $model = M('it_relation');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
//            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['设备atpid(主）','设备名称（主）','关联设备类型（主）','运维3.0的CNAME','C的table名','关联关系ID','设备atpid（从）','设备名称(从)','关联设备类型（从）','运维3.0的RNAME','R的table名'];
            if($count <= 0){
              exit(makeStandResult(-1, '没有要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            $data = $obj
                ->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
//            var_dump($data);die;
            foreach ($data as $k => $v) {
                //
            }

            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }

    /*
     * 获取公共资产数据
     * */
    public function getListData(){

        $queryParam = I('put.');

        $table = I('get.table');
        //根据表设置表前缀
        $pre = $this -> setTablePrefix($table);

//        dump(I('post.'));die;
        $filedStr = 'ip,mac,name,atpid';
        //批量添加表前缀
        $arr = explode(',',$filedStr);
        $arr = $this->batchAddPrefix($arr,$pre);
        $filedStr = implode(',',$arr);

        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $ip = trim($queryParam['ip']);
        if(!empty($ip)) $where['ip'] = ['like', "%$ip%"];

        $mac = trim($queryParam['mac']);
        if(!empty($mac)) $where['mac'] = ['like', "%$mac%"];

        $name = trim($queryParam['name']);
        if(!empty($name)) $where['name'] = ['like', "%$name%"];
        //批量添加表前缀
        $where = $this->batchAddPrefix($where,$pre);

//dump($where);die;
        $model = M($table);
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
//            ->order("$queryParam[sort] $queryParam[sortOrder]");


        $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        exit(json_encode(array( 'total' => $count,'rows' => $data)));

    }

    /*
 * 公共资产列表
 * */
    public function assetsList(){
        $get = I('get.');
        //主从表
        $flag = $get['flag'];
        $this->assign('flag',$flag);
        //限制单选
        $astrict = $get['astrict']?$get['astrict']:'true';
        $this->assign('astrict',$astrict);
        //表名
        $table = $get['table'];
        $this->assign('table',$table);

        //根据表设置表前缀
        $pre = $this -> setTablePrefix($table);
        $this->assign('pre',$pre);

        if(!$flag)exit('选择主从表');
        if(!$table)exit('表名有误');
//        dump($flag);die;
        $this->display();
    }

    /*
     * 服务器表
     * 必填
     * flag 主从表标识
     * flag=M 主表，左表
     * flag=S 从表，右表
     * flag=N 任意值，代表需要引用当前页面
     * 必填
     * astrict 限制是否单选的条件 默认为true
     * astrict=true 为多选 astrict=false 为单选
     * name 条件，用在多维度数据在一张表时，需要的区分条件
     * 可选
     * id 需要返回关联关系的主键id
     * 可选
     * tar_id 需要返回父级的标签id
     * 可选
     * tar_name 需要返回父级的标签name
     *
     * */
    public function sevAssetsList(){
        $get = I('get.');
        //主从表
        $flag = $get['flag'];
        $this->assign('flag',$flag);
        if(!$flag)exit('选择主从表');
        //限制单选
        $astrict = $get['astrict']?$get['astrict']:'true';
        $this->assign('astrict',$astrict);
        //条件
        $where = $get['name']?$get['name']:'';
        $this->assign('where',$where);
        //返回父级的标签id
        $tar_id = isset($get['tar_id'])?$get['tar_id']:'';
        $this->assign('tar_id',$tar_id);
        $tar_name = isset($get['tar_name'])?$get['tar_name']:'';
        $this->assign('tar_name',$tar_name);
        //查关系表
        $sevId = isset($get['id'])?$get['id']:'';
        if(!empty($sevId)){
            $optionInfo = D("relation")->getViewRelationInfo($sevId,'it_sev');
        }else{
            $optionInfo = [];
        }
        // var_dump($optionInfo);die;
        $this->assign('optionInfo',$optionInfo);

        //字典
        $arr = ['密级','地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
//        dump($flag);die;
        $this->display();
    }

    /*
     * 服务器数据
     * */
    public function getSevListData(){
        $queryParam = I('put.');

        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['sev_atpstatus'] = ['exp','IS NULL'];

        $sevType = trim(I('get.where'));
        if(!empty($sevType)) $where['sev_type'] = ['eq', $sevType];

        $sevSecretlevel = trim($queryParam['sev_secretlevel']);
        if(!empty($sevSecretlevel)) $where['sev_secretlevel'] = ['like', "%$sevSecretlevel%"];

        $sevArea = trim($queryParam['sev_area']);
        if (!empty($sevArea)) {
            $sevArea = $this->getDicById($sevArea, 'dic_name') ; //地区
            $where['sev_area'] = ['like', "%$sevArea%"];
        }

        $sevBelongfloor = trim($queryParam['sev_belongfloor']);
        if (!empty($sevBelongfloor)) {
            $sevBelongfloor = $this->getDicLouYuById($sevBelongfloor, 'dic_name') ; //楼宇
            $where['sev_belongfloor'] = ['like', "%$sevBelongfloor%"];
        }

        $sevRoomno = trim($queryParam['sev_roomno']);
        if(!empty($sevRoomno)) $where['sev_roomno'] = ['like', "%$sevRoomno%"];

        $sevDutyman = trim($queryParam['sev_dutyman']);
        if(!empty($sevDutyman)) $where['sev_dutyman'] = ['like', "%$sevDutyman%"];

        $sevUseman = trim($queryParam['sev_useman']);
        if(!empty($sevUseman)) $where['sev_useman'] = ['like', "%$sevUseman%"];

        $sevName = trim($queryParam['sev_name']);
        if(!empty($sevName)) $where['sev_name'] = ['like', "%$sevName%"];

        $sevType = trim($queryParam['sev_type']);
        if(!empty($sevType)) $where['sev_type'] = ['like', "%$sevType%"];

        $sevIp = trim($queryParam['sev_ip']);
        if(!empty($sevIp)) $where['sev_ip'] = ['like', "%$sevIp%"];

        $sevMac = trim($queryParam['sev_mac']);
        if(!empty($sevMac)) $where['sev_mac'] = ['like', "%$sevMac%"];

        $model = M('it_sev');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model
            ->where($where);
//            ->order("$queryParam[sort] $queryParam[sortOrder]");


        $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        foreach ($data as $k => &$v) {
            //翻译字典
//            $v['sev_area'] = !empty($v['sev_area'])?$this ->getDicById($v['sev_area'],'dic_name'):'-';//地区
//            $v['sev_factory'] = !empty($v['sev_factory'])?$this ->getDicById($v['sev_factory'],'dic_name'):'-';//厂家
//            $v['sev_status'] = !empty($v['sev_status'])?$this ->getDicById($v['sev_status'],'dic_name'):'-';//状态
//            $v['sev_secretlevel'] = !empty($v['sev_secretlevel'])?$this ->getDicById($v['sev_secretlevel'],'dic_name'):'-';//密级
//            $v['sev_assetsource'] = !empty($v['sev_assetsource'])?$this ->getDicById($v['sev_assetsource'],'dic_name'):'-';//资产来源
//            $v['sev_os'] = !empty($v['sev_os'])?$this ->getDicById($v['sev_os'],'dic_name'):'-';//操作系统
//            $v['sev_net'] = !empty($v['sev_net'])?$this ->getDicById($v['sev_net'],'dic_name'):'-';//所属网络

            //使用人
            if(!empty($v['sev_useman'])){
                $userName = D('org')->getViewPerson($v['sev_useman']);
                $v['sev_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                //使用人部门
                $v['sev_usedept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
            }else{
                $v['sev_useman'] = '-';
                $v['sev_usedept'] = '-';
            }
            //责任人
            if(!empty($v['sev_dutyman'])){
                $userName = D('org')->getViewPerson($v['sev_dutyman']);
                $v['sev_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                //责任人部门
                $v['sev_dutydept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
            }else{
                $v['sev_dutyman'] = '-';
                $v['sev_dutydept'] = '-';
            }
        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }

    /*
     * 应用系统资产表
     * 必填
     * flag 主从表标识
     * flag=M 主表，左表
     * flag=S 从表，右表
     * flag=N 任意值，代表需要引用当前页面
     * 必填
     * astrict 限制是否单选的条件 默认为true
     * astrict=true 为多选 astrict=false 为单选
     * name 条件，用在多维度数据在一张表时，需要的区分条件
     * 可选
     * id 需要返回关联关系的主键id
     * 可选
     * tar_id 需要返回父级的标签id
     * 可选
     * tar_name 需要返回父级的标签name
     *
     * */
    public function appAssetsList(){
        $get = I('get.');
        //主从表
        $flag = $get['flag'];
        $this->assign('flag',$flag);
        if(!$flag)exit('选择主从表');
        //限制单选
        $astrict = $get['astrict']?$get['astrict']:'true';
        $this->assign('astrict',$astrict);

        //条件
        $where = $get['name']?$get['name']:'';
        $this->assign('where',$where);
        //返回父级的标签id
        $tar_id = isset($get['tar_id'])?$get['tar_id']:'';
        $this->assign('tar_id',$tar_id);
        $tar_name = isset($get['tar_name'])?$get['tar_name']:'';
        $this->assign('tar_name',$tar_name);
        //查关系表
        $appId = isset($get['id'])?$get['id']:'';
        if(!empty($appId)){
            $optionInfo = D("relation")->getViewRelationInfo($appId,'it_application');
        }else{
            $optionInfo = [];
        }
        $this->assign('optionInfo',$optionInfo);

        //字典
        $arr = ['密级','地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);

        addLog("","用户访问日志","","访问交换机管理页面","成功");
        $this->display();
    }

    /*
     * 应用系统数据
     * */
    public function getAppListData(){
        $queryParam = I('put.');

        $filedStr = 'app_name,app_host,app_atpid';

        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $where['app_atpstatus'] = ['exp', 'IS NULL'];
        $ip = trim($queryParam['app_host']);
        if(!empty($ip)) $where['app_host'] = ['like', "%$ip%"];

        $name = trim($queryParam['app_name']);
        if(!empty($name)) $where['app_name'] = ['like', "%$name%"];

        $model = M('it_application');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
//            ->order("$queryParam[sort] $queryParam[sortOrder]");


        $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        foreach ($data as $k => &$v) {
            //翻译字典
            $v['sev_area'] = !empty($v['sev_area'])?$this ->getDicById($v['sev_area'],'dic_name'):'-';//地区
            $v['sev_belongfloor'] = !empty($v['sev_belongfloor'])?$this ->getDicLouYuById($v['sev_belongfloor'],'dic_name'):'-';//楼宇
            $v['sev_factory'] = !empty($v['sev_factory'])?$this ->getDicById($v['sev_factory'],'dic_name'):'-';//厂家
            $v['sev_modelnumber'] = !empty($v['sev_modelnumber'])?$this ->getDicXingHaoById($v['sev_modelnumber'],'dic_name'):'-';//型号
            $v['sev_status'] = !empty($v['sev_status'])?$this ->getDicById($v['sev_status'],'dic_name'):'-';//状态
            $v['sev_secretlevel'] = !empty($v['sev_secretlevel'])?$this ->getDicById($v['sev_secretlevel'],'dic_name'):'-';//密级
            $v['sev_assetsource'] = !empty($v['sev_assetsource'])?$this ->getDicById($v['sev_assetsource'],'dic_name'):'-';//资产来源
            $v['sev_os'] = !empty($v['sev_os'])?$this ->getDicById($v['sev_os'],'dic_name'):'-';//操作系统
            $v['sev_net'] = !empty($v['sev_net'])?$this ->getDicById($v['sev_net'],'dic_name'):'-';//所属网络

            //使用人
            if(!empty($v['sev_useman'])){
                $userName = D('org')->getViewPerson($v['sev_useman']);
                $v['sev_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                //使用人部门
                $v['sev_usedept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
            }else{
                $v['sev_useman'] = '-';
                $v['sev_usedept'] = '-';
            }
            //责任人
            if(!empty($v['sev_dutyman'])){
                $userName = D('org')->getViewPerson($v['sev_dutyman']);
                $v['sev_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                //责任人部门
                $v['sev_dutydept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
            }else{
                $v['sev_dutyman'] = '-';
                $v['sev_dutydept'] = '-';
            }
        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }

    /*
     * 应用系统资产表
     * 必填
     * flag 主从表标识
     * flag=M 主表，左表
     * flag=S 从表，右表
     * flag=N 任意值，代表需要引用当前页面
     * 必填
     * astrict 限制是否单选的条件 默认为true
     * astrict=true 为多选 astrict=false 为单选
     * name 条件，用在多维度数据在一张表时，需要的区分条件
     * 可选
     * id 需要返回关联关系的主键id
     * 可选
     * tar_id 需要返回父级的标签id
     * 可选
     * tar_name 需要返回父级的标签name
     *
     * */
    public function fireList(){
        $get = I('get.');
        //主从表
        $flag = $get['flag'];
        $this->assign('flag',$flag);
        if(!$flag)exit('选择主从表');
        //限制单选
        $astrict = $get['astrict']?$get['astrict']:'true';
        $this->assign('astrict',$astrict);

        //条件
//        $where = $get['name']?$get['name']:'';
//        $this->assign('where',$where);
        //返回父级的标签id
        $tar_id = isset($get['tar_id'])?$get['tar_id']:'';
        $this->assign('tar_id',$tar_id);
        $tar_name = isset($get['tar_name'])?$get['tar_name']:'';
        $this->assign('tar_name',$tar_name);
        //查关系表
        $appId = isset($get['id'])?$get['id']:'';
        if(!empty($appId)){
            $optionInfo = D("relation")->getViewRelationInfo($appId,'firewall');
        }else{
            $optionInfo = [];
        }
        $this->assign('optionInfo',$optionInfo);

        //字典
        $arr = ['厂家', '地区'];
        $factory = D('Dic')->getFactoryList('防毒墙');
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('changJia', $factory);
        $this->assign('diQu', $arrDic['地区']);

        addLog("","用户访问日志","","访问交换机管理页面","成功");
        $this->display();
    }

    /*
     * 应用系统数据
     * */
    public function getFileListData(){
        $queryParam = I('put.');
        $filedStr = 'fw_devicecode,fw_anecode,fw_ip,fw_mask,fw_gateway,fw_mac,fw_name,fw_usage,fw_factory,fw_modelnumber,fw_sn,fw_status,fw_secretlevel,fw_assetsource,fw_assetdutydept,fw_assetusedept,fw_purchasetime,fw_startusetime,fw_area,fw_belongfloor,fw_roomno,fw_dutyman,fw_dutydept,fw_useman,fw_usedept,fw_net,fw_remark,fw_yxq, fw_atpid';

        $where['fw_atpstatus'] = ['exp', 'IS NULL'];
        $rqDevicecode = trim($queryParam['fw_devicecode']);
        if(!empty($rqDevicecode)) $where['fw_devicecode'] = ['like', "%$rqDevicecode%"];

        $rqAnecode = trim($queryParam['fw_anecode']);
        if(!empty($rqAnecode)) $where['fw_anecode'] = ['like', "%$rqAnecode%"];

        $rqIp = trim($queryParam['fw_ip']);
        if(!empty($rqIp)) $where['fw_ip'] = ['like', "%$rqIp%"];

        $rqMac = trim($queryParam['fw_mac']);
        if(!empty($rqMac)) $where['fw_mac'] = ['like', "%$rqMac%"];

        $rqName = trim($queryParam['fw_name']);
        if(!empty($rqName)) $where['fw_name'] = ['like', "%$rqName%"];

        $rqFactory = trim($queryParam['fw_factory']);
        if(!empty($rqFactory)) {
            $rqFactory = $this->getDicById($rqFactory, 'dic_name'); //厂家
            $where['fw_factory'] = ['like', "%$rqFactory%"];
        }

        $rqModelnumber = trim($queryParam['fw_modelnumber']);
        if(!empty($rqModelnumber)) {
            $rqModelnumber = $this->getDicXingHaoById($rqModelnumber, 'dic_name'); //型号
            $where['fw_modelnumber'] = ['like', "%$rqModelnumber%"];
        }

        $rqArea = trim($queryParam['fw_area']);
        if(!empty($rqArea)) {
            $rqArea = $this->getDicById($rqArea, 'dic_name');
            $where['fw_area'] = ['like', "%$rqArea%"];
        }

        $rqBelongfloor = trim($queryParam['fw_belongfloor']);
        if(!empty($rqBelongfloor)) {
            $rqBelongfloor = $this->getDicLouYuById($rqBelongfloor, 'dic_name');
            $where['fw_belongfloor'] = ['like', "%$rqBelongfloor%"];
        }

        $rqRoomno = trim($queryParam['fw_roomno']);
        if(!empty($rqRoomno)) $where['fw_roomno'] = ['like', "%$rqRoomno%"];

        $model = M('firewall');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
//            ->order("$queryParam[sort] $queryParam[sortOrder]");


        $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        foreach ($data as $k => &$v) {
            //使用人
            if (!empty($v['fw_useman'])) {
                $userName = D('org')->getViewPerson($v['fw_useman']);
                $v['fw_useman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                //使用人部门
                $v['fw_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
            } else {
                $v['fw_useman'] = '-';
                $v['fw_usedept'] = '-';
            }
            //责任人
            if (!empty($v['fw_dutyman'])) {
                $userName = D('org')->getViewPerson($v['fw_dutyman']);
                $v['fw_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';

                //责任人部门
                $v['fw_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
            } else {
                $v['fw_dutyman'] = '-';
                $v['fw_dutydept'] = '-';
            }
        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }


    /*
     * 交换机资产表
     * 必填
     * flag 主从表标识
     * flag=M 主表，左表
     * flag=S 从表，右表
     * flag=N 任意值，代表需要引用当前页面
     * 必填
     * astrict 限制是否单选的条件 默认为true
     * astrict=true 为多选 astrict=false 为单选
     * 可选
     * name 条件，用在多维度数据在一张表时，需要的区分条件
     * 可选
     * id 需要返回关联关系的主键id
     * 可选
     * tar_id 需要返回父级的标签id
     * 可选
     * tar_name 需要返回父级的标签name
     *
     * */
    public function netAssetsList(){
        $get = I('get.');
        //主从表
        $flag = $get['flag'];
        if(!$flag)exit('选择主从表');
        $this->assign('flag',$flag);
        //限制单选
        $astrict = $get['astrict']?$get['astrict']:'true';
        $this->assign('astrict',$astrict);
        //条件
        $where = $get['name']?$get['name']:'';
        $this->assign('where',$where);
        //返回父级的标签id
        $tar_id = isset($get['tar_id'])?$get['tar_id']:'';
        $this->assign('tar_id',$tar_id);
        $tar_name = isset($get['tar_name'])?$get['tar_name']:'';
        $this->assign('tar_name',$tar_name);
        //查关系表
        $sevId = isset($get['id'])?$get['id']:'';
        if(!empty($sevId)){
            $optionInfo = D("relation")->getViewRelationInfo($sevId,'it_netdevice');
        }else{
            $optionInfo = [];
        }
        $this->assign('optionInfo',$optionInfo);

        //字典
        $arr = ['密级','使用状态','所属网络','厂家','地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('suoShuWangLuo', $arrDic['所属网络']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);
        addLog("","用户访问日志","","访问交换机管理页面","成功");
        $this->display();
    }

    /*
     * 交换机数据
     * */
    public function getNetListData(){
        $queryParam = I('put.');


        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $netType = trim(I('get.where'));
        if(!empty($netType)) $where['net_type'] = ['eq', $netType];

        $netIpaddress = trim($queryParam['net_ipaddress']);
        if(!empty($netIpaddress)) $where['net_ipaddress'] = ['like', "%$netIpaddress%"];

        $netFactory = trim($queryParam['net_factory']);
        if (!empty($netFactory)) {
            $netFactory = $this->getDicById($netFactory, 'dic_name') ; //厂家
            $where['net_factory'] = ['like', "%$netFactory%"];
        }

        $netModel = trim($queryParam['net_model']);
        if (!empty($netModel)) {
            $netModel = $this->getDicXingHaoById($netModel, 'dic_name') ; //型号
            $where['net_model'] = ['like', "%$netModel%"];
        }

        $netArea = trim($queryParam['net_area']);
        if (!empty($netArea)) {
            $netArea =  $this->getDicById($netArea, 'dic_name') ; //地区
            $where['net_area'] = ['like', "%$netArea%"];
        }

        $netBuilding = trim($queryParam['net_building']);
        if (!empty($netBuilding)) {
            $netBuilding = $this->getDicLouYuById($netBuilding, 'dic_name') ; //楼宇
            $where['net_building'] = ['like', "%$netBuilding%"];
        }

        $netRoom = trim($queryParam['net_room']);
        if(!empty($netRoom)) $where['net_room'] = ['like', "%$netRoom%"];

        $netUsage = trim($queryParam['net_usage']);
        if(!empty($netUsage)) $where['net_usage'] = ['like', "%$netUsage%"];

        $netProtocol = trim($queryParam['net_protocol']);
        if(!empty($netProtocol)) $where['net_protocol'] = ['like', "%$netProtocol%"];

        $netStatus = trim($queryParam['net_status']);
        if(!empty($netStatus)) $where['net_status'] = ['like', "%$netStatus%"];

        $netSecretlevel = trim($queryParam['net_secretlevel']);
        if(!empty($netSecretlevel)) $where['net_secretlevel'] = ['like', "%$netSecretlevel%"];

        $netAnecode = trim($queryParam['net_anecode']);
        if(!empty($netAnecode)) $where['net_anecode'] = ['like', "%$netAnecode%"];

        $netSn = trim($queryParam['net_sn']);
        if(!empty($netSn)) $where['net_sn'] = ['like', "%$netSn%"];

        $netDutydept = trim($queryParam['net_dutydept']);
        if(!empty($netDutydept)) $where['net_dutydept'] = ['like', "%$netDutydept%"];

        $netNet = trim($queryParam['net_net']);
        if(!empty($netNet)) $where['net_net'] = ['like', "%$netNet%"];

        $netDutyman = trim($queryParam['net_dutyman']);
        if(!empty($netDutyman)) $where['net_dutyman'] = ['like', "%$netDutyman%"];

        $model = M('it_netdevice');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->where($where);
//            ->order("$queryParam[sort] $queryParam[sortOrder]");

        $data = $obj
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();

        foreach ($data as $k => &$v) {
            $v['net_isscan'] = $v['net_isscan']==1?'扫描':'不扫描';
            //翻译字典
            $v['net_area'] = !empty($v['net_area'])?$this ->getDicById($v['net_area'],'dic_name'):'-';//地区
            $v['net_building'] = !empty($v['net_building'])?$this ->getDicLouYuById($v['net_building'],'dic_name'):'-';//楼宇
            $v['net_factory'] = !empty($v['net_factory'])?$this ->getDicById($v['net_factory'],'dic_name'):'-';//厂家
            $v['net_model'] = !empty($v['net_model'])?$this ->getDicXingHaoById($v['net_model'],'dic_name'):'-';//型号
            $v['net_status'] = !empty($v['net_status'])?$this ->getDicById($v['net_status'],'dic_name'):'-';//使用状态
            $v['net_secretlevel'] = !empty($v['net_secretlevel'])?$this ->getDicById($v['net_secretlevel'],'dic_name'):'-';//密级
            $v['net_source'] = !empty($v['net_source'])?$this ->getDicById($v['net_source'],'dic_name'):'-';//资产来源
            $v['net_net'] = !empty($v['net_net'])?$this ->getDicById($v['net_net'],'dic_name'):'-';//所属网络

            //使用人
            $userId = $v['net_useman'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $v['net_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];

                //使用人部门
                $v['net_usedept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
            }else{
                $v['net_useman'] = '-';
                $v['net_usedept'] = '-';
            }

            //责任人
            $dutuserId = $v['net_dutymanname'];
            if(!empty($dutuserId)){
                $dutuserName = D('org')->getViewPerson($dutuserId);
                $v['net_dutymanname'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')--' . $dutuserName['orgname'];

                //责任人部门
                $v['net_dutydept'] = $this -> removeStr($dutuserName['orgfullname']);//去掉字符串
            }else{
                $v['net_dutymanname'] = '-';
                $v['net_dutydept'] = '-';
            }

        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }

    /*
     * 机柜资产表
     * 必填
     * flag 主从表标识
     * flag=M 主表，左表
     * flag=S 从表，右表
     * flag=N 任意值，代表需要引用当前页面
     * 必填
     * astrict 限制是否单选的条件 默认为true
     * astrict=true 为多选 astrict=false 为单选
     * name 条件，用在多维度数据在一张表时，需要的区分条件
     * 可选
     * id 需要返回关联关系的主键id
     * 可选
     * tar_id 需要返回父级的标签id
     * 可选
     * tar_name 需要返回父级的标签name
     *
     * */
    public function jiguiAssetsList(){
        $get = I('get.');
        //主从表
        $flag = $get['flag'];
        if(!$flag)exit('选择主从表');
        $this->assign('flag',$flag);
        //限制单选
        $astrict = $get['astrict']?$get['astrict']:'true';
        $this->assign('astrict',$astrict);
        //条件
        $where = $get['name']?$get['name']:'';
        $this->assign('where',$where);
        //返回父级的标签id
        $tar_id = isset($get['tar_id'])?$get['tar_id']:'';
        $this->assign('tar_id',$tar_id);
        $tar_name = isset($get['tar_name'])?$get['tar_name']:'';
        $this->assign('tar_name',$tar_name);
        //查关系表
        $sevId = isset($get['id'])?$get['id']:'';
        if(!empty($sevId)){
            $optionInfo = D("relation")->getViewRelationInfo($sevId,'jigui');
        }else{
            $optionInfo = [];
        }
        $this->assign('optionInfo',$optionInfo);

        //字典
        $arr = ['使用状态','厂家','地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('zhuangTai', $arrDic['使用状态']);
        $this->assign('changJia', $arrDic['厂家']);
        $this->assign('diQu', $arrDic['地区']);
        addLog("","用户访问日志","","访问交换机管理页面","成功");
        $this->display();
    }

    /*
     * 机柜数据
     * */
    public function getJiguiListData(){
        $queryParam = I('put.');


        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $jgType = trim(I('get.where'));
        if(!empty($jgType)) $where['net_type'] = ['eq', $jgType];

        $jgDevicecode = trim($queryParam['jg_devicecode']);
        if (!empty($jgDevicecode)) $where['jg_devicecode'] = ['like', "%$jgDevicecode%"];

        $jgAnecode = trim($queryParam['jg_anecode']);
        if (!empty($jgAnecode)) $where['jg_anecode'] = ['like', "%$jgAnecode%"];

        $jgName = trim($queryParam['jg_name']);
        if (!empty($jgName)) $where['jg_name'] = ['like', "%$jgName%"];

        $jgFactory = trim($queryParam['jg_factory']);
        if (!empty($jgFactory)) {
            $jgFactory = $this->getDicById($jgFactory, 'dic_name');
            $where['jg_factory'] = ['like', "%$jgFactory%"];
        }

        $jgModelnumber = trim($queryParam['jg_modelnumber']);
        if (!empty($jgModelnumber)) {
            $jgModelnumber = $this->getDicXingHaoById($jgModelnumber, 'dic_name');
            $where['jg_modelnumber'] = ['like', "%$jgModelnumber%"];
        }

        $jgStatus = trim($queryParam['jg_status']);
        if (!empty($jgStatus)) $where['jg_status'] = ['like', "%$jgStatus%"];

        $jgArea = trim($queryParam['jg_area']);
        if (!empty($jgArea)) {
            $jgArea = $this->getDicById($jgArea, 'dic_name');
            $where['jg_area'] = ['like', "%$jgArea%"];
        }

        $jgBelongfloor = trim($queryParam['jg_belongfloor']);
        if (!empty($jgBelongfloor)) {
            $jgBelongfloor = $this->getDicLouYuById($jgBelongfloor, 'dic_name');
            $where['jg_belongfloor'] = ['like', "%$jgBelongfloor%"];
        }

        $jgRoomno = trim($queryParam['jg_roomno']);
        if (!empty($jgRoomno)) $where['jg_roomno'] = ['like', "%$jgRoomno%"];

        $model = M('jigui');
        $count = $model->where($where)->count();
        $obj = $model
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        $data = $obj
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();

        foreach ($data as $k => &$v) {
            //翻译字典
//            $v['jg_area'] = !empty($v['jg_area']) ? $this->getDicById($v['jg_area'], 'dic_name') : '-'; //地区
//            $v['jg_belongfloor'] = !empty($v['jg_belongfloor']) ? $this->getDicLouYuById($v['jg_belongfloor'], 'dic_name') : '-'; //楼宇
//            $v['jg_factory'] = !empty($v['jg_factory']) ? $this->getDicById($v['jg_factory'], 'dic_name') : '-'; //厂家
//            $v['jg_modelnumber'] = !empty($v['jg_modelnumber']) ? $this->getDicXingHaoById($v['jg_modelnumber'], 'dic_name') : '-'; //型号
//            $v['jg_status'] = !empty($v['jg_status']) ? $this->getDicById($v['jg_status'], 'dic_name') : '-'; //使用状态

            //使用人
            $userId = $v['jg_useman'];
            if (!empty($userId)) {
                $userName = D('org')->getViewPerson($userId);
                $v['jg_useman']  = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];

                //使用人部门
                $v['jg_usedept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
            } else {
                $v['jg_useman'] = '-';
                $v['jg_usedept'] = '-';
            }

            //责任人
            $dutuserId = $v['jg_dutyman'];
            if (!empty($dutuserId)) {
                $dutuserName = D('org')->getViewPerson($dutuserId);
                $v['jg_dutyman'] = $dutuserName['realusername'] . '(' . $dutuserName['username'] . ')--' . $dutuserName['orgname'];

                //责任人部门
                $v['jg_dutydept'] = $this->removeStr($dutuserName['orgfullname']); //去掉字符串
            } else {
                $v['jg_dutyman'] = '-';
                $v['jg_dutydept'] = '-';
            }
        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }
    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.rl_atpid'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['rl_atpid'] = ['in', $id];
        }else{
            $where['rl_atpid'] = ['eq', $id];
        }

        $model = M('it_relation');
        // 获取旧数据记录日志
        //$oldData = $model->where($where)->field('td_name')->select();
        //$names = implode(',', removeArrKey($oldData, 'td_name'));
        $res = $model->where($where)->delete();
        if($res){
            // 修改日志
            addLog('it_relation', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('it_relation', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }

    //根据表设置字段前缀 公用方法
    public function setTablePrefix($table){
        switch($table){
            case 'it_sev':
                $pre = 'sev_';
                break;
            case 'it_application':
                $pre = 'app_';
                break;
            default:
                echo '请选择正确的表';
                return false;
        }
        return $pre;
    }

    //字段映射表,仅用于存储关联关系
    public function fieldMapping($table,$type){
        switch($table){
            case 'it_sev':
                $data['id'] = 'sev_atpid';
                $data['name'] = 'sev_name';
                $data['ip'] = 'sev_ip';
                $data['type'] = $type;
                $data['table'] = 'it_sev';
                $data['where'] = 'sev_type';
                break;
            case 'it_application':
                $data['id'] = 'app_atpid';
                $data['name'] = 'app_name';
                $data['ip'] = 'app_host';
                $data['type'] = $type;
                $data['table'] = 'it_application';
                $data['where'] = '';
                break;
            case 'it_netdevice':
                $data['id'] = 'net_atpid';
                $data['name'] = '';
                $data['ip'] = 'net_ipaddress';
                $data['type'] = $type;
                $data['table'] = 'it_netdevice';
                $data['where'] = 'net_type';
                break;
            default:
                exit(makeStandResult(-1, '请选择正确的表'));
                return false;
        }
        return $data;
    }

    //批量添加表前缀
    public function batchAddPrefix($arr,$pre){
        if(is_array($arr)){
            array_walk($arr,function (&$v)use($pre){
                $v = $pre.$v;
            });
            return $arr;
        }
        return false;
    }
}