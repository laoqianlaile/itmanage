<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/10
 * Time: 10:31
 */

namespace Home\Controller;

use Think\Controller;

class ToolsController extends BaseController
{
    //工具软件管理
    public function index()
    {
        addLog("", "用户访问日志", "访问工具软件页面", "成功");
        $arr = ['所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('group',$arrDic['所在班组']);
        $this->display();
    }
    //新增编辑工具
    public function add()
    {
        $id = trim(I('get.tl_atpid'));
        $Objtype = trim(I('get.type'));
        if (!empty($id)) {
            $model = M('tools');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $data = $model
                ->field('tl_atpid,tl_name,tl_shortname,tl_version,tl_keyword,tl_purchase,tl_projectlevel,tl_url,tl_onlinetime,tl_userrange,tl_status,tl_adminA,tl_adminB,tl_group,tl_dutyman,tl_dutydept,tl_develop,tl_environment,tl_function,tl_remark,tl_plats,tl_platd,tl_language,tl_ischeck')
                ->where("tl_atpid='%s'", $id)
                ->find();
            //责任人
            $dutuserId = $data['tl_dutyman'];
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

            //管理员A岗
            $userId = $data['tl_admina'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminA['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminA['username'] = $userName['username'];

            }else{
                $adminA = [];
            }
            $this->assign('adminA', $adminA);

            //管理员B岗
            $userId = $data['tl_adminb'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminB['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminB['username'] = $userName['username'];

            }else{
                $adminB = [];
            }
            $this->assign('adminB', $adminB);

            $develope = explode(';',$data['tl_develop']);
            $this->assign('dept', $develope);
        }
        $arr = ['所在班组','使用状态(工具软件)','建设途径','技术平台','研制单位','是否'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('Objtype', $Objtype);
        $this->assign('ischeck', $arrDic['是否']);
        $this->assign('zhuangTai', $arrDic['使用状态(工具软件)']);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('tujing', $arrDic['建设途径']);
        $this->assign('jishu', $arrDic['技术平台']);
        $this->assign('develope', $arrDic['研制单位']);

        $this->assign('data', $data);

        addLog('', '用户访问日志', "访问工具软件管理添加、编辑页面", '成功');
        $this->display();
    }
    //增加数据
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['tl_atpid']);

        // 这里根据实际需求,进行字段的过滤
        $model = M('tools');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['tl_dutydept'] = D('org')->getDeptId($data['tl_dutyman']);
        $data['tl_develop'] = implode(';',$data['tl_develop']);

        //获取部门中文名称
        $dutydeptname= D('org')->getDepartId($data['tl_dutydept']);
        $data['tl_dutydeptname'] =$this->removeStr($dutydeptname['fullname']);

        if (empty($id)) {
            $data['tl_atpid'] = makeGuid();
            $data['tl_atpcreatedatetime'] = $time;
            $data['tl_atpcreateuser'] = $user;

            $list = session('list');
            $content = LogContent($data,$list);

            $res = $model->add($data);
            if (empty($res)) {
                addLog('tools', '对象添加日志', '', '失败', '失败');
                exit(makeStandResult(-1, '添加失败'));
            } else {
                addLog('tools', '对象添加日志'.$content. '成功', '成功');
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);

            $data['tl_atpmodifydatetime'] = $time;
            $data['tl_atpmodifyuser'] = $user;

            $res = $model->where("tl_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                //修改日志
                addLog('tools', '对象修改日志', '修改主键为'.$id. '失败', '失败');
                exit(makeStandResult(-1, '修改失败'));
            } else {
                //修改日志
                addLog('tools', '对象修改日志',  $content . '成功', '成功');
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }
    //删除数据
    public function delData()
    {
        $ids = trim(I('post.tl_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('tools');

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
        foreach ($arr as $k => $id) {
            $data['tl_atpmodifydatetime'] = $time;
            $data['tl_atpmodifyuser'] = $user;
            $data['tl_atpstatus']  = 'DEL';
            $res =  $model->where("tl_atpid='%s'", $id)->save($data);
            $list['rlx_atpstatus'] = 'DEL';
            M('it_relationx')->where("rlx_zyid = '%s'",$id)->save($list);
            if($res){
                addLog('tools', '对象删除日志',  "删除主键为".$id."成功", '成功');
                D('relation')->delRelation($id, 'tools');
            }
        }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('tools', '对象删除日志',  "删除主键为".$id."失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }
    //查询数据
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'tl_name,tl_shortname,tl_version,tl_keyword,tl_purchase,tl_projectlevel,tl_url,tl_onlinetime,tl_userrange,tl_status,tl_admina,tl_adminb,tl_group,tl_dutyman,tl_dutydept,tl_develop,tl_environment,tl_function,tl_remark,tl_plats,tl_platd,tl_language,tl_ischeck';
        } else {
            $filedStr = 'tl_atpid,tl_name,tl_shortname,tl_version,tl_keyword,tl_purchase,tl_projectlevel,tl_url,tl_onlinetime,tl_userrange,tl_status,tl_admina,tl_adminb,tl_group,tl_dutyman,tl_dutydept,tl_develop,tl_environment,tl_function,tl_remark,tl_plats,tl_platd,tl_language,tl_ischeck';
            $queryParam = I('put.');
        }

        //模糊查询
        $where['tl_atpstatus'] = ['exp', 'IS NULL'];
        $tl_name = trim($queryParam['tl_name']);
        if (!empty($tl_name)) $where['tl_name'] = ['like', "%$tl_name%"];

        $tl_ischeck = trim($queryParam['tl_ischeck']);
        if (!empty($tl_ischeck)) $where['tl_ischeck'] = ['like', "%$tl_ischeck%"];

        $tl_shortname = trim($queryParam['tl_shortname']);
        if (!empty($tl_shortname)) $where['tl_shortname'] = ['like', "%$tl_shortname%"];

        $tl_keyword = trim($queryParam['tl_keyword']);
        if (!empty($tl_keyword)) $where['tl_keyword'] = ['like', "%$tl_keyword%"];

        $tl_admina= trim($queryParam['tl_admina']);
        if (!empty($tl_admina)) $where['tl_admina'] = ['like', "%$tl_admina%"];

        $tl_adminb = trim($queryParam['tl_adminb']);
        if (!empty($tl_adminB)) $where['tl_adminb'] = ['like', "%$tl_adminb%"];

        $tl_group = trim($queryParam['tl_group']);
        if (!empty($tl_group)) $where['tl_group'] = ['like', "%$tl_group%"];

        $tl_dutyman = trim($queryParam['tl_dutyman']);
        if (!empty( $tl_dutyman)) $where['tl_dutyman'] = ['like', "%$tl_dutyman%"];

        $tl_sx = trim($queryParam['tl_sx']);
        if (!empty($tl_sx)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '工具软件'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');
            if($tl_sx == '1'){
                $where['tl_atpid'] = ['in', $rlx_zyids];
            }else{
                $where['tl_atpid'] = ['not in', $rlx_zyids];
            }
        }
        $tlDutydept = trim($queryParam['tl_dutydept']);
        if (!empty($tlDutydept)) {
            $sql = "select id from it_depart start with id= '$tlDutydept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['tl_dutydept'] = ['in', $ids];
        }
        //查询数据库
        $model = M('tools');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");//转换时间
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if ($isExport) {
            $data = $obj->select();
            $header =['工具名称','简称','版本','搜索关键词','建设途径','立项级别','下载地址','上线时间','用户范围','使用状态','管理员A岗','管理员B岗','所属班组', '责任人','责任部门','开发单位','运行环境','功能简介','备注','技术平台','开发平台','开发语言'];

            foreach ($data as $k => &$v) {
               /* //责任人
                if (!empty($v['tl_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['tl_dutyman']);
                    $v['tl_dutyman'] = $userName['realusername'];
                    $v['tl_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                }*/

                //责任人
                if(!empty($v['tl_dutyman'])){
                    $userName = D('org')->getViewPerson($v['tl_dutyman']);
                   // $v['tl_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                   $v['tl_dutyman'] = $userName['username'];

                    //责任人部门
                    $v['tl_dutydept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
                }else{
                    $v['tl_dutyman'] = '-';
                    $v['tl_dutydept'] = '-';
                }
              //管理员A岗
                if(!empty($v['tl_admina'])){
                    $userName = D('org')->getViewPerson($v['tl_admina']);
                   // $v['tl_admina'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['tl_admina'] = $userName['username'];
                }else{
                    $v['tl_admina'] = '-';
                }
                //管理员B岗
                if(!empty($v['tl_adminb'])){
                    $userName = D('org')->getViewPerson($v['tl_adminb']);
                    //$v['tl_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                  $v['tl_adminb'] = $userName['username'];
                }else {
                    $v['tl_adminb'] = '-';

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
                //翻译字典
//                $v['app_status'] = !empty($v['app_status'])?$this ->getDicById($v['app_status'],'dic_name'):'-';//使用状态
//                $v['app_secret'] = !empty($v['app_secret'])?$this ->getDicById($v['app_secret'],'dic_name'):'-';//密级

                //责任人
                if(!empty($v['tl_dutyman'])){
                    $userName = D('org')->getViewPerson($v['tl_dutyman']);
//                    $v['app_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['tl_dutyman'] = $userName['realusername'];

                    //责任人部门
                    $v['tl_dutydept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
                }else{
                    $v['tl_dutyman'] = '-';
                    $v['tl_dutydept'] = '-';
                }
                //管理员A岗
                if(!empty($v['tl_admina'])){
                    $userName = D('org')->getViewPerson($v['tl_admina']);
//                    $v['app_admin'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['tl_admina'] = $userName['realusername'];
                }else{
                    $v['tl_admina'] = '-';
                }
                //管理员B岗
                if(!empty($v['tl_adminb'])){
                    $userName = D('org')->getViewPerson($v['tl_adminb']);
//                    $v['app_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['tl_adminb'] = $userName['realusername'];
                }else{
                    $v['tl_adminb'] = '-';
                }

                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['tl_atpid'])->count();
                $data[$k]['tlCount'] = $rlxCount;
                $rlxCount = M('checkup')->where("appid = '%s' and atpstatus is null",$v['tl_atpid'])->count();
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
        $arr = ['使用状态(工具软件)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $zhuangTai = $arrDic['使用状态(工具软件)'];
        $zhuangTaiArray = array_column($zhuangTai,'dic_name');

        //字段
        $fields = ['tl_name','tl_shortname','tl_version','tl_keyword','tl_purchase','tl_projectlevel','tl_url','tl_onlinetime','tl_userrange','tl_status','tl_admina','tl_adminb','tl_group','tl_dutyman','tl_dutydept','tl_develop','tl_environment','tl_function','tl_remark','tl_plats','tl_platd','tl_language'];
        //'工具名称','简称','版本','搜索关键词','建设途径','立项级别','下载地址','上线时间','用户范围','使用状态','管理员A岗','管理员B岗','所属班组','责任人','责任部门','开发单位','运行环境','功能简介','备注','技术平台','开发平台','开发语言
        $model = M('tools');
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
                    case 'tl_status': //使用状态
                        $deptNameField ='tl_status';
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
                    case 'tl_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'tl_dutyman';
                        $userInfo = D('org')->getUserNames($v);
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'tl_admina': //管理员A岗
                        $fieldName = '管理员A岗';
                        $userNameField = 'tl_admina';
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
                    case 'tl_adminb': //管理员B岗
                        $fieldName = '管理员B岗';
                        $userNameField = 'tl_adminb';
                        $userInfo = D('org')->getUserNames($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;

                    case 'tl_onlinetime': //上线时间
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
                $arr['tl_atpcreatedatetime'] = $time;
                $arr['tl_atpcreateuser'] = $loginUserId;
                $arr['tl_atpid'] = makeGuid();
                 $initTables[] = $arr;
            }
        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    //获取部门id
                    $value['tl_dutydept'] = M('it_person')->where("domainusername = '%s'",$value['tl_dutyman'])->getfield('orgid');
                    //获取部门中文名称
                    $dutydeptname= D('org')->getDepartId($value['tl_dutydept']);
                    $value['tl_dutydeptname'] =$this->removeStr($dutydeptname['fullname']);
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