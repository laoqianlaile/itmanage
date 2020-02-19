<?php
namespace Home\Controller;

use Think\Controller;

class AppController extends BaseController
{
//应用系统管理
    public function index()
    {
        $arr = ['密级','所在班组','使用状态(应用系统)','是否','分类(应用系统)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $arrs = ['责任单位(应用系统)'];
        $arrsDic = D('Dic')->getDicValueByNames($arrs);

        $this->assign('miJi', $arrDic['密级']);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('status', $arrDic['使用状态(应用系统)']);
        $this->assign('company', $arrsDic['责任单位(应用系统)']);
        $this->assign('is', $arrDic['是否']);
        $this->assign('type', $arrDic['分类(应用系统)']);

        addLog("it_application", "用户访问日志",  "访问应用系统页面", "成功");
        $this->display();
    }

    /**
     * 应用系统添加或修改
     */
    public function add()
    {
        $id = trim(I('get.app_atpid'));
        $Objtype = trim(I('get.type'));
        if (!empty($id)) {
            $model = M('it_application');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            $data = $model
                ->field('app_isyuan,app_iszhongxin,app_loginmode,app_mode,app_usemode,app_bz,app_adminb,app_secret,app_sname,app_lxjb,app_aloginmode,app_userscope,app_status,app_info,app_dutydept,app_usedate,app_developplat,app_dutyman,app_atpid,app_name,app_accessauthority,app_url,app_admin,app_group,app_keyword,app_usernum,app_version,app_ischeck,app_function,app_weblogic,app_platform,app_sysadmin,app_secadmin,app_audit,app_structure,app_jstj,app_develope,app_dutycompany,app_toptype')
                ->where("app_atpid='%s'", $id)
                ->find();
            session('list',$data);
            //责任人
            $userId = $data['app_dutyman'];
//            dump($userid);die;
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $userMan['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $userMan['username'] = $userName['username'];
            }else{
                $userMan = [];
            }

            $userDept = D('org') -> getDepartId($data['app_dutydept']);//去掉字符串
            $userDept = $this->removeStr($userDept['fullname']);
            $this->assign('userMan', $userMan);
            $this->assign('app_dutydept', $userDept);

            //系统员
            $sysadminUser = D('org')->getViewPerson($data['app_sysadmin']);
            if(!empty($sysadminUser)){
                $sysadmin['name'] = $sysadminUser['realusername'] . '(' . $sysadminUser['username'] . ')--' . $sysadminUser['orgname'];
                $sysadmin['username'] = $sysadminUser['username'];
                $this->assign('sysadmin', $sysadmin);
            }

            //安全员
            $secadminUser = D('org')->getViewPerson($data['app_secadmin']);
            if(!empty($secadminUser)) {
                $secadmin['name'] = $secadminUser['realusername'] . '(' . $secadminUser['username'] . ')--' . $secadminUser['orgname'];
                $secadmin['username'] = $secadminUser['username'];
                $this->assign('secadmin', $secadmin);
            }

            //审计员
            $auditUser = D('org')->getViewPerson($data['app_audit']);
            if(!empty($auditUser)) {
                $audit['name'] = $auditUser['realusername'] . '(' . $auditUser['username'] . ')--' . $auditUser['orgname'];
                $audit['username'] = $auditUser['username'];
                $this->assign('audit', $audit);
            }

            //管理员A岗
            $userId = $data['app_admin'];
//            dump($userid);die;
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $dutymanA['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $dutymanA['username'] = $userName['username'];

            }else{
                $dutymanA = [];
            }
            $this->assign('dutymanA', $dutymanA);

            //管理员b岗
            $userId = $data['app_adminb'];
//            dump($userid);die;
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $dutymanB['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $dutymanB['username'] = $userName['username'];

            }else{
                $dutymanB = [];
            }
            $this->assign('dutymanB', $dutymanB);

            //开发单位
            $deptId = $data['app_developer'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getDepartId($deptId);

                $dutydept['name'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                $dutydept['id'] = $deptInfo['id'];
            } else {
                $dutydept = [];
            }
            $this->assign('dutydept', $dutydept);

            //用户单位
            $deptId = $data['app_userdept'];
            if (!empty($deptId)) {
                $deptInfo = D('org')->getDepartId($deptId);

                $usedept['name'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                $usedept['id'] = $deptInfo['id'];
            } else {
                $usedept = [];
            }
            $this->assign('usedept', $usedept);
            $develope = explode(';',$data['app_develope']);
            $this->assign('dept', $develope);

            //物理服务器IP地址
            $relation = D("relation")->getViewRelationInfo($id,'it_sev');
            $app_host = '';
            foreach ($relation as $v) {
                $app_host .= '('.$v['r_ip'].'),';
            }
            $app_host = substr($app_host,0,-1);
            $this->assign('app_host', $app_host);
            //隐藏的id值
            $app_host_id = implode(',',array_column($relation,'r_id'));
            $this->assign('app_host_id', $app_host_id);

            $this->assign('data', $data);
        }
        $arr = ['密级','使用状态(应用系统)','所在班组','是否','访问方式','数据存放方式','鉴别方式','研制单位','分类(应用系统)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $arrs = ['责任单位(应用系统)'];
        $arrsDic = D('Dic')->getDicValueByNames($arrs);

        $this->assign('Objtype', $Objtype);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('zhuangTai', $arrDic['使用状态(应用系统)']);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('ischeck', $arrDic['是否']);
        $this->assign('mode', $arrDic['访问方式']);
        $this->assign('usemode', $arrDic['数据存放方式']);
        $this->assign('loginmode', $arrDic['鉴别方式']);
        $this->assign('develope', $arrDic['研制单位']);
        $this->assign('dutycompany', $arrsDic['责任单位(应用系统)']);
        $this->assign('is', $arrDic['是否']);
        $this->assign('type', $arrDic['分类(应用系统)']);


        addLog('it_application','用户访问日志', "访问应用系统添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['app_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $model = M('it_application');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        // if(!empty($data['app_host_id'])){
        //     $host = explode(',', $data['app_host_id']);
        //     foreach ($host as $v) {
        //         if($this->checkAddress($v,'IP') === false) exit(makeStandResult(-1,'ip地址有误'));
        //     }
        // }else{
        //     exit(makeStandResult(-1,'ip地址不能为空'));
        // }

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
//        $appHostId = explode(',',$data['app_host_id']);
        $data['app_develope'] = implode(';',$data['app_develope']);
        if (empty($id)) {
            $data['app_atpid'] = makeGuid();
            $data['app_host'] = 'it_application';
            $data = $model->create($data);
            $data['app_atpcreatedatetime'] = $time;
            $data['app_atpcreateuser'] = $user;


            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('it_application', '对象添加日志', '添加主键为'.$data['app_atpid'], '失败',$data['app_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('it_application',  '对象添加日志',  '添加主键为'.$data['app_atpid'], '成功',$data['app_atpid']);
                //添加服务器关联关系
//                $this -> changeRelationSev($data['app_atpid'],$data['app_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);

            $data['app_atplastmodifydatetime'] = $time;
            $data['app_atplastmodifyuser'] = $user;

            $res = $model->where("app_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('it_application', '对象修改日志','修改主键为'.$id, '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
                if(!empty($content)){
                    addLog('it_application', '对象修改日志',  $content , '成功',$id);
                }
                //添加服务器关联关系
//                $this -> changeRelationSev($data['app_atpid'],$data['app_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }

    /**
     * 获取应用系统数据
     */
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'app_name,app_secret,app_keyword,app_jstj,app_url,app_dutycompany,app_dutyman,app_dutydept,app_group,app_admin,app_adminb,app_sname,app_status,app_lxjb,app_developplat,app_structure,app_weblogic,app_platform,app_usedate,app_interface,app_bz,app_info,app_mode,app_function,app_userscope,app_usernum,app_version,app_develope,app_ischeck,app_sysadmin,app_secadmin,app_audit,app_usemode,app_loginmode,app_accessauthority,app_aloginmode,app_isyuan,app_iszhongxin,app_toptype';
        } else {
            $filedStr = 'app_name,app_host,app_secret,app_dutyman,app_dutydept,app_group,app_admin,app_adminb,app_url,app_sname,app_status,app_lxjb,app_userdept,app_developplat,app_usedate,app_interface,app_bz,app_info,app_mode,app_function,app_userscope,app_usemode,app_loginmode,app_accessauthority,app_aloginmode,app_develope, app_atpid,app_keyword,app_usernum,app_version,app_ischeck,app_weblogic,app_platform,app_sysadmin,app_secadmin,app_audit,app_structure,app_accessadd,app_jstj,app_dutycompany,app_iszhongxin,app_isyuan,app_toptype';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['app_atpstatus'] = ['exp', 'IS NULL'];
        $appDb = trim($queryParam['app_db']);
        if (!empty($appDb)) $where['app_db'] = ['like', "%$appDb%"];

        $app_ischeck = trim($queryParam['app_ischeck']);
        if (!empty($app_ischeck)) $where['app_ischeck'] = ['like', "%$app_ischeck%"];


        $app_group = trim($queryParam['app_group']);
        if (!empty($app_group)) $where['app_group'] = ['like', "%$app_group%"];

        $app_status = trim($queryParam['app_status']);
        if (!empty($app_status)) $where['app_status'] = ['like', "%$app_status%"];

        $appUrl = strtolower(trim($queryParam['app_url']));
        if (!empty($appUrl)) $where['lower(app_url)'] = ['like', "%$appUrl%"];

        $appDutyman = trim($queryParam['app_dutyman']);
        if (!empty($appDutyman)) $where['app_dutyman'] = ['like', "%$appDutyman%"];

        $dutycompany = trim($queryParam['app_dutycompany']);
        if (!empty($dutycompany)) $where['app_dutycompany'] = ['like', "%$dutycompany%"];

        $app_isyuan = trim($queryParam['app_isyuan']);
        if (!empty($app_isyuan)) $where['app_isyuan'] = ['like', "%$app_isyuan%"];

        $app_iszhongxin = trim($queryParam['app_iszhongxin']);
        if (!empty($app_iszhongxin)) $where['app_iszhongxin'] = ['like', "%$app_iszhongxin%"];

        $app_toptype = trim($queryParam['app_toptype']);
        if (!empty($app_toptype)) $where['app_toptype'] = ['like', "%$app_toptype%"];

        $app_sx = trim($queryParam['app_sx']);
        if (!empty($app_sx)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '应用系统'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');
            if($app_sx == '1'){
                $where['app_atpid'] = ['in', $rlx_zyids];
            }else{
                $where['app_atpid'] = ['not in', $rlx_zyids];
            }
        }

        $appDutydept = trim($queryParam['app_dutydept']);
        if (!empty($appDutydept)) {
            $sql = "select id from it_depart start with id= '$appDutydept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['app_dutydept'] = ['in', $ids];
        }


        $appAdmin = trim($queryParam['app_admin']);
        if (!empty($appAdmin))
        {
            $where[0]['app_admin'] = ['like', "%$appAdmin%"];
            $where[0]['app_adminb'] = ['like', "%$appAdmin%"];
            $where[0]['_logic'] = 'OR';
        }
//        $where['app_admin'] = ['like', "%$appAdmin%"];

        $appName = strtolower(trim($queryParam['app_name']));
        if (!empty($appName)) $where['lower(app_name)'] = ['like', "%$appName%"];

        $appSecret = trim($queryParam['app_secret']);
        if (!empty($appSecret)) $where['app_secret'] = ['like', "%$appSecret%"];

        $appHost = trim($queryParam['app_host']);
        if (!empty($appHost)) $where['app_host'] = ['like', "%$appHost%"];
        $model = M('it_application');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");


        if ($isExport) {
            $data = $obj->select();

            $header = ['系统名称', '密级','关键字','建设途径','应用系统URL', '责任单位','责任人', '责任部门', '所属班组','管理员A岗', '管理员B岗', '系统简称', '使用状态', '立项级别', '技术平台','技术架构','中间件（版本）','开发平台', '上线日期', '系统接口', '备注', '信息资源', '访问方式', '功能简介', '用户范围','用户数','版本','研制单位','是否需巡检','系统员','安全员','审计员', '数据存放方式', '普通用户鉴别方式', '访问权限', '管理员鉴别方式','是否院管','是否中心管','分类'];
            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['app_status'] = !empty($v['app_status'])?$this ->getDicById($v['app_status'],'dic_name'):'-';//使用状态
//                $v['app_secret'] = !empty($v['app_secret'])?$this ->getDicById($v['app_secret'],'dic_name'):'-';//密级

                //责任人
                if(!empty($v['app_dutyman'])){
                    $userName = D('org')->getViewPerson($v['app_dutyman']);
//                    $v['app_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['app_dutyman'] = $userName['realusername'];

                }else{
                    $v['app_dutyman'] = '-';
                }

                $userDept = D('org') -> getDepartId($v['app_dutydept']);//去掉字符串
                $userDept = $this->removeStr($userDept['fullname']);
                $data[$k]['app_dutydept'] = $userDept;

                //管理员A岗
                if(!empty($v['app_admin'])){
                    $userName = D('org')->getViewPerson($v['app_admin']);
//                    $v['app_admin'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['app_admin'] = $userName['realusername'];
                }else{
                    $v['app_admin'] = '-';
                }
                //管理员B岗
                if(!empty($v['app_adminb'])){
                    $userName = D('org')->getViewPerson($v['app_adminb']);
//                    $v['app_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['app_adminb'] = $userName['realusername'];
                }else{
                    $v['app_adminb'] = '-';
                }

//                //用户单位
//                if (!empty($v['app_userdept'])) {
//                    $deptInfo = D('org')->getDepartId($v['app_userdept']);
//                    $v['app_userdept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
//                } else {
//                    $v['app_userdept'] = '-';
//                }

                //系统员
                $sysadminUser = D('org')->getViewPerson($v['app_sysadmin']);
                $data[$k]['app_sysadmin'] = $sysadminUser['realusername'];


                //安全员
                $secadminUser = D('org')->getViewPerson($v['app_secadmin']);
                $data[$k]['app_secadmin'] = $secadminUser['realusername'];


                //审计员
                $auditUser = D('org')->getViewPerson($v['app_audit']);
                $data[$k]['app_audit'] = $auditUser['realusername'];

            }
            if ($count <= 0) {
                exit(makeStandResult(-1, '没有要导出的数据'));
            } else if ($count > 1000) {
                csvExport($header, $data, true);
            } else {
                excelExport($header, $data, true);
            }
        } else {
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();

            foreach ($data as $k => &$v) {
                //翻译字典
//                $v['app_status'] = !empty($v['app_status'])?$this ->getDicById($v['app_status'],'dic_name'):'-';//使用状态
//                $v['app_secret'] = !empty($v['app_secret'])?$this ->getDicById($v['app_secret'],'dic_name'):'-';//密级

                //责任人
                if(!empty($v['app_dutyman'])){
                    $userName = D('org')->getViewPerson($v['app_dutyman']);
//                    $v['app_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['app_dutyman'] = $userName['realusername'];

                }else{
                    $v['app_dutyman'] = '-';
                }
                $userDept = D('org') -> getDepartId($v['app_dutydept']);//去掉字符串
                $userDept = $this->removeStr($userDept['fullname']);
                $data[$k]['app_dutydept'] = $userDept;
                //管理员A岗
                if(!empty($v['app_admin'])){
                    $userName = D('org')->getViewPerson($v['app_admin']);
//                    $v['app_admin'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['app_admin'] = $userName['realusername'];
                }else{
                    $v['app_admin'] = '-';
                }
                //管理员B岗
                if(!empty($v['app_adminb'])){
                    $userName = D('org')->getViewPerson($v['app_adminb']);
//                    $v['app_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['app_adminb'] = $userName['realusername'];
                }else{
                    $v['app_adminb'] = '-';
                }
                //开发单位

                //用户单位
                if (!empty($v['app_userdept'])) {
                    $deptInfo = D('org')->getDepartId($v['app_userdept']);
                    $v['app_userdept'] = $this->removeStr($deptInfo['fullname']); //去掉字符串
                } else {
                    $v['app_userdept'] = '-';
                }
                //物理服务器IP地址
                $appAll = D("relation")->getViewRelationInfo($v['app_atpid'],'it_sev');
                if(!empty($appAll)){
                    $v['app_host'] = '';
                    foreach ($appAll as $appIp) {
                        $v['app_host'] .= '('.$appIp['r_ip'].'),';
                    }
                    $v['app_host'] = substr($v['app_host'],0,-1);
                }else{
                    $v['app_host'] = '-';
                }

                //系统员
                $sysadminUser = D('org')->getViewPerson($v['app_sysadmin']);
                $data[$k]['app_sysadmin'] = $sysadminUser['realusername'];


                //安全员
                $secadminUser = D('org')->getViewPerson($v['app_secadmin']);
                $data[$k]['app_secadmin'] = $secadminUser['realusername'];


                //审计员
                $auditUser = D('org')->getViewPerson($v['app_audit']);
                $data[$k]['app_audit'] = $auditUser['realusername'];

                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['app_atpid'])->count();
                $data[$k]['appCount'] = $rlxCount;

                $rlxCount = M('checkup')->where("appid = '%s' and atpstatus is null",$v['app_atpid'])->count();
                $data[$k]['xjCount'] = $rlxCount;
            }

            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }

//    /**
//     * 判断是否催在关联数据
//     */
//     public function isGldel(){
//         $ids = trim(I('post.app_atpid'));
//         $ids = explode(',',$ids);
//         $where['rlx_zyid']= ['in',$ids];
//         $where['rlx_atpstatus'] = ['exp','is null'];
//         $model = M('it_relationx');
//         $res = $model->where($where)->select();
//         if(!empty($res)){
//             exit(makeStandResult(1, '存在关联'));
//         }else{
//             exit(makeStandResult(-1, '不存在关联'));
//         }
//
//     }


    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.app_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('it_application');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['app_atplastmodifydatetime'] = $time;
                $data['app_atplastmodifyuser'] = $user;
                $data['app_atpstatus'] = 'DEL';
                $res = $model->where("app_atpid='%s'", $id)->save($data);
                $list['rlx_atpstatus'] = 'DEL';
                M('it_relationx')->where("rlx_zyid = '%s'",$id)->save($list);
                if ($res) {
                    // 修改日志
                    addLog('it_application', '对象删除日志', "删除主键为".$id, '成功',$id);
                    //删除关联关系
                    D('relation')->delRelation($id, 'it_application');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('it_application', '对象删除日志', "删除主键为".$ids, '失败',$id);
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    /**
     * 批量增加
     */
    public function saveCopyTables()
    {
        $receiveData = $_POST;
        $head = explode(',', $receiveData['head']);
        $data = $receiveData['data'];
        if(empty($data)) exit(makeStandResult(-1, '未接收到数据'));
        $data = json_decode($data,true);

        $reduce = 0;
        if($head[0] == '序号'){
            foreach($data as &$value){
                unset($value[0]);
            }
            unset($value);
            $reduce = 1;
        }
        $time = date('Y-m-d H:i:s');
        $loginUserId = session('user_id');

        //字典
        $arr = ['密级','使用状态(应用系统)'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $miJi = $arrDic['密级'];
        $miJiArray = array_column($miJi,'dic_name');
        $zhuangTai = $arrDic['使用状态(应用系统)'];
        $zhuangTaiArray = array_column($zhuangTai,'dic_name');

        //字段
        $fields = ['app_name', 'app_secret', 'app_url','app_dutycompany', 'app_dutyman', 'app_admin', 'app_adminb',  'app_childname', 'app_sname', 'app_status', 'app_lxjb', 'app_userdept', 'app_developplat', 'app_usedate', 'app_interface', 'app_bz', 'app_info', 'app_mode', 'app_function', 'app_userscope', 'app_usemode', 'app_loginmode', 'app_accessauthority', 'app_developer', 'app_aloginmode'];
        //系统名称,密级,服务器IP地址,应用系统URL,责任人,管理员A岗,管理员B岗,数据库,子系统,简称,状态,立项级别,用户单位,技术平台,上线日期,系统接口,重点项目,备注,信息资源,访问方式,主要功能,用户范围,存放方式,普通用户鉴别方式,访问权限,开发单位,管理员鉴别方式

        $model = M('it_application');
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
                    case 'app_secret': //密级
                        $deptNameField = 'app_secret';
                        $fieldName = '密级';
                        if(!empty($v)){
                            if(!in_array($v,$miJiArray)){
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            }else{
                                $k = array_search($v,$miJiArray);
                            }
                            $dicId = $miJi[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        }else{
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'app_status': //使用状态
                        $deptNameField ='app_status';
                        $fieldName = '使用状态';
                        if(!empty($v)){
                            if(!in_array($v,$zhuangTaiArray)){
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            }else{
                                $k = array_search($v,$zhuangTaiArray);
                            }
                            $dicId = $zhuangTai[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        }else{
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'app_url': //应用系统URL
                        $arr[$field] = $v;
                        break;
                    case 'app_childname': //子系统
                        $arr[$field] = $v;
                        break;
                    case 'app_sname': //简称
                        $arr[$field] = $v;
                        break;
                    case 'app_lxjb': //立项级别
                        $arr[$field] = $v;
                        break;
                    case 'app_developplat': //技术平台
                        $arr[$field] = $v;
                        break;
                    case 'app_interface': //系统接口
                        $arr[$field] = $v;
                        break;
                    case 'app_bz': //备注
                        $arr[$field] = $v;
                        break;
                    case 'app_info': //信息资源
                        $arr[$field] = $v;
                        break;
                    case 'app_mode': //访问方式
                        $arr[$field] = $v;
                        break;
                    case 'app_function': //功能简介
                        $arr[$field] = $v;
                        break;
                    case 'app_userscope': //用户范围
                        $arr[$field] = $v;
                        break;
                    case 'app_usemode': //存放方式
                        $arr[$field] = $v;
                        break;
                    case 'app_loginmode': //普通用户鉴别方式
                        $arr[$field] = $v;
                        break;
                    case 'app_accessauthority': //访问权限
                        $arr[$field] = $v;
                        break;
                    case 'app_sname': //管理员鉴别方式
                        $arr[$field] = $v;
                        break;
                   case 'app_name': //服务器名称
                       $fieldName = '服务器名称';
                       if (empty($v)) {
                           $error .= "第{$lineNum} 行 {$fieldName} 不能为空<br>";
                       }
                       $arr[$field] = $v;
                       break;

                    case 'app_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'app_dutyman';
                        $userInfo = D('org')->getUserName($v);
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['username'];
                        break;
                    case 'app_admin': //管理员A岗
                        $fieldName = '管理员A岗';
                        $userNameField = 'app_admin';
                        if(!empty($v)){
                            $userInfo = D('org')->getUserName($v);
                            if(empty($userInfo)){
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                                break;
                            }
                            $arr[$userNameField] = $userInfo['username'];
                        }else{
                            $arr[$userNameField] = $v;
                        }
                        break;
                    case 'app_adminb': //管理员B岗
                        $fieldName = '管理员B岗';
                        $userNameField = 'app_adminb';
                        if(!empty($v)){
                            $userInfo = D('org')->getUserName($v);
                            if(empty($userInfo)){
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                                break;
                            }
                            $arr[$userNameField] = $userInfo['username'];
                        }else{
                            $arr[$userNameField] = $v;
                        }
                        break;
                    case 'app_developer': //开发单位
                        $fieldName = '开发单位';
                        $userNameField = 'app_developer';
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
                    case 'app_userdept': //用户单位
                        $fieldName = '用户单位';
                        $userNameField = 'app_userdept';
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

                    case 'app_usedate': //上线日期
                        $fieldName = '上线日期';
                        if(!empty($v)){
                            $strTime = strtotime($v);
                            if($strTime === false){
                                $error .= "第{$lineNum} 行 {$fieldName} 时间格式不对<br>";
                                break;
                            }
                            $arr[$field] = date('Y-m-d', $strTime);
                        }else{
                            $arr[$field] = '';
                        }
                        break;

                    default:
                        $arr[$field] = $v;
                        break;
                }
                $arr['app_atpcreatedatetime'] = $time;
                $arr['app_atpcreateuser'] = $loginUserId;
                $arr['app_atpid'] = makeGuid();
            }
            $initTables[] = $arr;
        }
//        dump($error);die;
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
                addLog('it_application', '对象导入日志', '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
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