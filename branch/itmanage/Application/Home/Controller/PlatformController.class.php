<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/24
 * Time: 10:16
 */

namespace Home\Controller;

use Think\Controller;

class PlatformController extends BaseController
{
    public function index()
    {
        addLog("", "用户访问日志", "访问平台管理页面", "成功");
        $arr = ['所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('group', $arrDic['所在班组']);
        $this->display();
    }

    //新增编辑平台
    public function add()
    {
        $id = trim(I('get.pf_atpid'));
        $Objtype = trim(I('get.type'));
        if (!empty($id)) {
            $model = M('platform');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $data = $model
                ->field('pf_atpid,pf_name,pf_keyword,pf_purchase,pf_starttime,pf_url,pf_userrange,pf_status,
                pf_admina,pf_adminb,pf_group,pf_dutyman,pf_dutydept,pf_usernum,pf_version,pf_develop,
                pf_ischeck,pf_companyuser,pf_companytel,pf_function,pf_weblogic,pf_struction,pf_sysadmin,pf_secadmin,
                pf_audit,pf_loginmode,pf_aloginmode,pf_usemode,pf_bz')
                ->where("pf_atpid='%s'", $id)
                ->find();
            //责任人
            $dutuserId = $data['pf_dutyman'];
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

            //系统员
            $sysadminUser = D('org')->getViewPerson($data['pf_sysadmin']);
            if(!empty($sysadminUser)){
                $sysadmin['name'] = $sysadminUser['realusername'] . '(' . $sysadminUser['username'] . ')--' . $sysadminUser['orgname'];
                $sysadmin['username'] = $sysadminUser['username'];
                $this->assign('sysadmin', $sysadmin);
            }

            //安全员
            $secadminUser = D('org')->getViewPerson($data['pf_secadmin']);
            if(!empty($secadminUser)) {
                $secadmin['name'] = $secadminUser['realusername'] . '(' . $secadminUser['username'] . ')--' . $secadminUser['orgname'];
                $secadmin['username'] = $secadminUser['username'];
                $this->assign('secadmin', $secadmin);
            }

            //审计员
            $auditUser = D('org')->getViewPerson($data['pf_audit']);
            if(!empty($auditUser)) {
                $audit['name'] = $auditUser['realusername'] . '(' . $auditUser['username'] . ')--' . $auditUser['orgname'];
                $audit['username'] = $auditUser['username'];
                $this->assign('audit', $audit);
            }

            //管理员A岗
            $userId = $data['pf_admina'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminA['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminA['username'] = $userName['username'];
            }else{
                $adminA = [];
            }
            $this->assign('adminA', $adminA);

            //管理员B岗
            $userId = $data['pf_adminb'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminB['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminB['username'] = $userName['username'];

            }else{
                $adminB = [];
            }
            $this->assign('adminB', $adminB);

            $develope = explode(';',$data['pf_develop']);
            $this->assign('dept', $develope);
        }
        $arr = ['所在班组','使用状态(应用系统)','研制单位','是否','数据存放方式','鉴别方式'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('Objtype', $Objtype);
        $this->assign('zhuangTai', $arrDic['使用状态(应用系统)']);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('develope', $arrDic['研制单位']);
        $this->assign('loginmode', $arrDic['鉴别方式']);
        $this->assign('usemode', $arrDic['数据存放方式']);
        $this->assign('isCheck', $arrDic['是否']);
        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问平台管理界面添加、编辑页面", '成功');
        $this->display();
    }

    //增加数据
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['pf_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $model = M('platform');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['pf_dutydept'] = D('org')->getDeptId($data['pf_dutyman']);
        $data['pf_develop'] = implode(';',$data['pf_develop']);

        //获取部门中文名称
        $dutydeptname= D('org')->getDepartId($data['pf_dutydept']);
        $data['pf_dutydeptname'] =$this->removeStr($dutydeptname['fullname']);

        if (empty($id)) {
            $data['pf_atpid'] = makeGuid();
            $data['pf_atpcreatedatetime'] = $time;
            $data['pf_atpcreateruser'] = $user;

            $list = session('list');
            $content = LogContent($data,$list);
            $res = $model->add($data);

            if (empty($res)) {
                addLog('platform', '对象添加日志','', '失败', '失败');
                exit(makeStandResult(-1, '添加失败'));
            } else {
                addLog('it_sev', '对象添加日志',  $content . '成功', '成功');
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);

            $data['pf_atplastmodifydatetime'] = $time;
            $data['pf_atplastmodifyuser'] = $user;


            $res = $model->where("pf_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                addLog('it_sev', '对象修改日志', '修改主键为'.$id. '失败', '失败');
                exit(makeStandResult(-1, '修改失败'));
            } else {
                addLog('it_sev', '对象修改日志',  $content . '成功', '成功');
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }
    //删除数据
    public function delData()
    {
        $ids = trim(I('post.pf_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('platform');

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
        foreach ($arr as $k => $id) {
            $data['pf_atplastmodifydatetime'] = $time;
            $data['pf_atplastmodifyuser'] = $user;
            $data['pf_atpstatus'] = 'DEL';
            $res = $model->where("pf_atpid='%s'", $id)->save($data);
            $list['rlx_atpstatus'] = 'DEL';
            M('it_relationx')->where("rlx_zyid = '%s'", $id)->save($list);
            if ($res) {
                addLog('platform', '对象删除日志', "删除主键为" . $id . "成功", '成功');
                D('relation')->delRelation($id, 'platform');
            }
        }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));

        } catch (\Exception $e) {
                $model->rollback();
                addLog('it_sev', '对象删除日志',  "删除主键为".$id."失败", '失败');
                exit(makeStandResult(-1, '删除失败'));
            }
    }
    //查询数据
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'pf_name,pf_keyword,pf_purchase,pf_starttime,pf_url,pf_userrange,pf_status,pf_admina,pf_adminb,pf_group,pf_dutyman,pf_dutydept,pf_usernum,pf_version,pf_develop,pf_ischeck,pf_companyuser,pf_companytel,pf_function,pf_weblogic,pf_struction,pf_sysadmin,pf_secadmin,pf_audit,pf_loginmode,pf_aloginmode,pf_usemode,pf_bz';
        } else {
            $filedStr = 'pf_atpid,pf_name,pf_keyword,pf_purchase,pf_starttime,pf_url,pf_userrange,pf_status,pf_admina,pf_adminb,pf_group,pf_dutyman,pf_dutydept,pf_usernum,pf_version,pf_develop,pf_ischeck,pf_companyuser,pf_companytel,pf_function,pf_weblogic,pf_struction,pf_sysadmin,pf_secadmin,pf_audit,pf_loginmode,pf_aloginmode,pf_usemode,pf_bz';
            $queryParam = I('put.');
        }

        //模糊查询
        $where['pf_atpstatus'] = ['exp', 'IS NULL'];
        $pf_name = trim($queryParam['pf_name']);
        if (!empty($pf_name)) $where['pf_name'] = ['like', "%$pf_name%"];

        $pf_ischeck = trim($queryParam['pf_ischeck']);
        if (!empty($pf_ischeck)) $where['pf_ischeck'] = ['like', "%$pf_ischeck%"];

        $pf_group = trim($queryParam['pf_group']);
        if (!empty($pf_group)) $where['pf_group'] = ['like', "%$pf_group%"];

        $pf_dutyman = trim($queryParam['pf_dutyman']);
        if (!empty($pf_dutyman)) $where['pf_dutyman'] = ['like', "%$pf_dutyman%"];

        $pf_url= trim($queryParam['pf_url']);
        if (!empty($pf_url)) $where['pf_url'] = ['like', "%$pf_url%"];

        $pf_keyword= trim($queryParam['pf_keyword']);
        if (!empty($pf_keyword)) $where['pf_keyword'] = ['like', "%$pf_keyword%"];

        $pf_sx = trim($queryParam['pf_sx']);
        if (!empty($pf_sx)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '平台'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');
            if($pf_sx == '1'){
                $where['pf_atpid'] = ['in', $rlx_zyids];
            }else{
                $where['pf_atpid'] = ['not in', $rlx_zyids];
            }
        }
        $pfDutydept = trim($queryParam['pf_dutydept']);
        if (!empty($pfDutydept)) {
            $sql = "select id from it_depart start with id= '$pfDutydept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['pf_dutydept'] = ['in', $ids];
        }

        //查询数据库
        $model = M('platform');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");//转换时间
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if ($isExport) {
            $data = $obj->select();
            $header =['平台名称','搜索关键词','建设途径','上线时间','平台访问地址','用户范围','使用状态','管理员A岗','管理员B岗','所属班组','责任人' ,'责任部门','用户数','版本','研制单位','是否需巡检','厂商联系人','联系方式','功能简介', '中间件(版本)','技术架构','系统员','安全员','审计员','普通用户鉴别方式','管理员鉴别方式','数据存放方式','备注'];
            foreach ($data as $k => &$v) {

                //责任人
                if (!empty($v['pf_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['pf_dutyman']);
                    $v['pf_dutyman'] = $userName['username'];
                    $v['pf_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                }

                //管理员A岗
                if(!empty($v['pf_admina'])){
                    $userName = D('org')->getViewPerson($v['pf_admina']);
                   // $v['pf_admina'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['pf_admina'] = $userName['username'];
                }else{
                    $v['pf_admina'] = '-';
                }
                //管理员B岗
                if(!empty($v['pf_adminb'])){
                    $userName = D('org')->getViewPerson($v['pf_adminb']);
                   // $v['pf_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['pf_adminb'] = $userName['username'];
                }else{
                    $v['pf_adminb'] = '-';
                }

                //系统员
                if(!empty($v['pf_sysadmin'])){
                    $userName = D('org')->getViewPerson($v['pf_sysadmin']);
                    //$v['pf_sysadmin'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['pf_sysadmin'] = $userName['username'];
                }else{
                    $v['pf_sysadmin'] = '-';
                }

                //安全员
                if(!empty($v['pf_secadmin'])){
                    $userName = D('org')->getViewPerson($v['pf_secadmin']);
                    //$v['pf_secadmin'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                     $v['pf_secadmin'] = $userName['username'];
                }else{
                    $v['pf_secadmin'] = '-';
                }

                //审计员
                if(!empty($v['pf_audit'])){
                    $userName = D('org')->getViewPerson($v['pf_audit']);
                    //$v['pf_audit'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                     $v['pf_audit'] = $userName['username'];
                }else{
                    $v['pf_secadmin'] = '-';
                }

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

                //责任人
                if(!empty($v['pf_dutyman'])){
                    $userName = D('org')->getViewPerson($v['pf_dutyman']);
//                    $v['app_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['pf_dutyman'] = $userName['realusername'];

                    //责任人部门
                    $v['pf_dutydept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
                }else{
                    $v['pf_dutyman'] = '-';
                    $v['pf_dutydept'] = '-';
                }
                //管理员A岗
                if(!empty($v['pf_admina'])){
                    $userName = D('org')->getViewPerson($v['pf_admina']);
//                    $v['app_admin'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['pf_admina'] = $userName['realusername'];
                }else{
                    $v['pf_admina'] = '-';
                }
                //管理员B岗
                if(!empty($v['pf_adminb'])){
                    $userName = D('org')->getViewPerson($v['pf_adminb']);
//                    $v['app_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['pf_adminb'] = $userName['realusername'];
                }else{
                    $v['pf_adminb'] = '-';
                }
                //系统员
                $sysadminUser = D('org')->getViewPerson($v['pf_sysadmin']);
                $data[$k]['pf_sysadmin'] = $sysadminUser['realusername'];


                //安全员
                $secadminUser = D('org')->getViewPerson($v['pf_secadmin']);
                $data[$k]['pf_secadmin'] = $secadminUser['realusername'];


                //审计员
                $auditUser = D('org')->getViewPerson($v['pf_audit']);
                $data[$k]['pf_audit'] = $auditUser['realusername'];


                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['pf_atpid'])->count();
                $data[$k]['pfCount'] = $rlxCount;

                $rlxCount = M('checkup')->where("appid = '%s' and atpstatus is null",$v['pf_atpid'])->count();
                $data[$k]['xjCount'] = $rlxCount;
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }
    //批量增加
    public function saveCopyTables()
    {
        $receiveData = $_POST;
        $head = explode(',', $receiveData['head']);
        $data = $receiveData['data'];
        if(empty($data)) exit(makeStandResult(-1, '未接收到数据'));
        $data = json_decode($data,true);

        $reduce = 0; //去掉序号字段
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
        $arr = ['使用状态(应用系统)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $zhuangTai = $arrDic['使用状态(应用系统)'];
        $zhuangTaiArray = array_column($zhuangTai,'dic_name');

        //字段
        $fields = ['pf_name','pf_keyword','pf_purchase','pf_starttime','pf_url','pf_userrange','pf_status','pf_admina','pf_adminb','pf_group','pf_dutyman','pf_dutydept','pf_usernum','pf_version','pf_develop','pf_ischeck','pf_companyuser','pf_companytel','pf_function','pf_weblogic','pf_struction','pf_sysadmin','pf_secadmin','pf_audit','pf_loginmode','pf_aloginmode','pf_usemode','pf_bz'];
        //'平台名称, 搜索关键词, 建设途径, 上线时间, 平台访问地址, 用户范围, 使用状态, 管理员A岗, 管理员B岗, 所属班组, 责任人, 责任部门, 用户数, 版本, 研制单位, 是否需巡检, 厂商联系人, 联系方式, 功能简介, 中间件(版本), 技术架构, 系统员, 安全员, 审计员, 普通用户鉴别方式, 数据存放方式, 备注
        $model = M('platform');
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
                    case 'pf_status': //使用状态
                        $deptNameField ='pf_status';
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
                    case 'pf_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'pf_dutyman';
                        $userInfo = D('org')->getUserNames($v);
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'pf_admin': //管理员A岗
                        $fieldName = '管理员A岗';
                        $userNameField = 'pf_admin';
                        if(!empty($v)){
                            $userInfo = D('org')->getUserNames($v);
                            if(empty($userInfo)){
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                                break;
                            }
                            $arr[$userNameField] = $userInfo['domainusername'];
                        }else{
                            $arr[$userNameField] = $v;
                        }
                        break;
                    case 'pf_adminb': //管理员B岗
                        $fieldName = '管理员B岗';
                        $userNameField = 'pf_adminb';
                        if(!empty($v)){
                            $userInfo = D('org')->getUserNames($v);
                            if(empty($userInfo)){
                                $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                                break;
                            }
                            $arr[$userNameField] = $userInfo['domainusername'];
                        }else{
                            $arr[$userNameField] = $v;
                        }
                        break;
                    case 'pf_dutydept': //使用责任单位
                        $fieldName = '责任单位';
                        $userNameField = 'pf_dutydept';
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
                    case 'pf_starttime': //上线时间
                        $fieldName = '上线时间';
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
            }
            $arr['pf_atpcreatedatetime'] = $time;
            $arr['pf_atpcreateuser'] = $loginUserId;
            $arr['pf_atpid'] = makeGuid();
            $initTables[] = $arr;
        }

        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    //获取部门id
                    $value['pf_dutydept'] = M('it_person')->where("domainusername = '%s'",$value['pf_dutyman'])->getfield('orgid');
                    //获取部门中文名称
                    $dutydeptname= D('org')->getDepartId($value['pf_dutydept']);
                    $value['pf_dutydeptname'] =$this->removeStr($dutydeptname['fullname']);
                    $res = $model->add($value);
                    if (!empty($res)) $successNum += 1;
                }
                $failNum = $exportNum - $successNum;
               addLog('todo', '对象导入日志', '批量导入如下待办事项(' . implode(',', $todoNames) . '),成功' . $successNum . '条数据,失败' . $failNum . '条数据', '成功');
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