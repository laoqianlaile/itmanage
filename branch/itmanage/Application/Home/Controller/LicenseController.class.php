<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/19
 * Time: 15:02
 */

namespace Home\Controller;

use Think\Controller;

class LicenseController extends BaseController
{
    //许可证书资源管理模块
    public function index()
    {
        addLog("", "用户访问日志", "访问许可证书页面", "成功");
        $arr = ['所在班组','是否'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('shifou', $arrDic['是否']);
        $this->display();
    }

    //1	许可证书资源管理模块添加 修改页面

    public function add()
    {
        $id = trim(I('get.lc_atpid'));
        $Objtype =trim(I('get.type'));
        if (!empty($id)) {
            $model = M('license');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
            $data = $model
                ->field('lc_atpid,lc_name,lc_develop,lc_version,lc_author,lc_server,lc_os,lc_deploymode,
                lc_dependinfo,lc_port,lc_tools,lc_purchase,lc_num,lc_starttime,lc_endtime,lc_userrange,
                lc_status,lc_admina,lc_adminb,lc_group,lc_dutyman,lc_dutydept,lc_onlinetime,lc_ischeck,lc_business,
                lc_technician,lc_auto,lc_keyword,lc_function,lc_remark')
                ->where("lc_atpid='%s'", $id)
                ->find();
            //责任人
            $dutuserId = $data['lc_dutyman'];
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
            $userId = $data['lc_admina'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminA['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminA['username'] = $userName['username'];

            }else{
                $adminA = [];
            }
            $this->assign('adminA', $adminA);

            //管理员B岗
            $userId = $data['lc_adminb'];
            if(!empty($userId)){
                $userName = D('org')->getViewPerson($userId);
                $adminB['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $adminB['username'] = $userName['username'];

            }else{
                $adminB = [];
            }
            $this->assign('adminB', $adminB);

            $this->assign('data', $data);

        }
        $arr = ['使用状态(许可)','许可授权方式','许可依赖信息','许可服务器要求','是否','所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('Objtype', $Objtype);
        $this->assign('zhangTai', $arrDic['使用状态(许可)']);
        $this->assign('shouQuan', $arrDic['许可授权方式']);
        $this->assign('yiLai', $arrDic['许可依赖信息']);
        $this->assign('yaoQiu', $arrDic['许可服务器要求']);
        $this->assign('isCheck', $arrDic['是否']);
        $this->assign('group', $arrDic['所在班组']);

        addLog('', '用户访问日志', "访问许可证书管理添加、编辑页面", '成功');
        $this->display();

    }

    public function addData()
    {
        $data = I('post.');
        $id = trim($data['lc_atpid']);

        $model = M('license');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        $data['lc_dutydept'] = D('org')->getDeptId($data['lc_dutyman']);

        //获取部门中文名称
        $dutydeptname= D('org')->getDepartId($data['lc_dutydept']);
        $data['lc_dutydeptname'] =$this->removeStr($dutydeptname['fullname']);

        if (empty($id)) {
            $data['lc_atpid'] = makeGuid();
            $data['lc_atpcreatedatetime'] = $time;
            $data['lc_atpcreateuser'] = $user;

            $list = session('list');
            $content = LogContent($data,$list);

            $res = $model->add($data);
            if (empty($res)) {
                addLog('license', '对象添加日志','', '失败', '失败');
                exit(makeStandResult(-1, '添加失败'));
            } else {
                addLog('license', '对象添加日志', $content . '失败', '失败');
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);

            $data['lc_atpmodifydatetime'] = $time;
            $data['lc_atpmodifyuser'] = $user;

            $res = $model->where("lc_atpid='%s'",$id)->save($data);


            if (empty($res)) {
                addLog('license', '对象修改日志', '修改主键为'.$id. '失败', '失败');
                exit(makeStandResult(-1, '修改失败'));
            } else {
                addLog('license', '对象修改日志',  $content . '成功', '成功');
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }

    //删除数据
    public function delData()
    {
        $ids = trim(I('post.lc_atpid'));


        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('license');

        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['lc_atpmodifydatetime'] = $time;
                $data['lc_atpmodifyuser'] = $user;
                $data['lc_atpstatus'] = 'DEL';
                $res = $model->where("lc_atpid='%s'", $id)->save($data);
                $list['rlx_atpstatus'] = 'DEL';
                M('it_relationx')->where("rlx_zyid = '%s'", $id)->save($list);
                if ($res) {
                    addLog('license', '对象删除日志',  "删除主键为".$id."成功", '成功');
                    D('relation')->delRelation($id, 'license');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('license', '对象删除日志',  "删除主键为".$id."失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }
    //查询数据
    public function getData($isExport = false)
    {

        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'lc_name,lc_develop,lc_version,lc_author,lc_server,lc_os,lc_deploymode,lc_dependinfo,lc_port,lc_tools,lc_purchase,lc_num,lc_starttime,lc_endtime,lc_userrange,lc_status,lc_admina,lc_adminb,lc_group,lc_dutyman,lc_dutydept,lc_onlinetime,lc_ischeck,lc_business,lc_technician,lc_auto,lc_keyword,lc_function,lc_remark';
        } else {
            $filedStr = 'lc_atpid,lc_name,lc_develop,lc_version,lc_author,lc_server,lc_os,lc_deploymode,lc_dependinfo,lc_port,lc_tools,lc_purchase,lc_num,lc_starttime,lc_endtime,lc_userrange,lc_status,lc_admina,lc_adminb,lc_group,lc_dutyman,lc_dutydept,lc_onlinetime,lc_ischeck,lc_business,lc_technician,lc_auto,lc_keyword,lc_function,lc_remark';
            $queryParam = I('put.');

        }

        //模糊查询
        $where['lc_atpstatus'] = ['exp', 'IS NULL'];
        $lc_name = trim($queryParam['lc_name']);
        if (!empty($lc_name)) $where['lc_name'] = ['like', "%$lc_name%"];

        $lc_ischeck = trim($queryParam['lc_ischeck']);
        if (!empty($lc_ischeck)) $where['lc_ischeck'] = ['like', "%$lc_ischeck%"];


        $lc_develop = trim($queryParam['lc_develop']);
        if (!empty($lc_develop)) $where['lc_develop'] = ['like', "%$lc_develop%"];

        $lc_purchase = trim($queryParam['lc_purchase']);
        if (!empty($lc_purchase)) $where['lc_purchase'] = ['like', "%$lc_purchase%"];

        $lc_admina= trim($queryParam['lc_admina']);
        if (!empty($lc_admina)) $where['lc_admina'] = ['like', "%$lc_admina%"];

        $lc_adminb = trim($queryParam['lc_adminb']);
        if (!empty($lc_adminb)) $where['lc_adminb'] = ['like', "%$lc_adminb%"];

        $lc_group = trim($queryParam['lc_group']);
        if (!empty($lc_group)) $where['lc_group'] = ['like', "%$lc_group%"];

        $lc_dutyman= trim($queryParam['lc_dutyman']);
        if (!empty($lc_dutyman)) $where['lc_dutyman'] = ['like', "%$lc_dutyman%"];

        $lc_auto = trim($queryParam['lc_auto']);
        if (!empty($lc_auto)) $where['lc_auto'] = ['like', "%$lc_auto%"];

        $lc_keyword = trim($queryParam['lc_keyword']);
        if (!empty($lc_keyword)) $where['lc_keyword'] = ['like', "%lc_keyword%"];

        $lc_sx = trim($queryParam['lc_sx']);
        if (!empty($lc_sx)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '许可证书'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');
            if($lc_sx == '1'){
                $where['lc_atpid'] = ['in', $rlx_zyids];
            }else{
                $where['lc_atpid'] = ['not in', $rlx_zyids];
            }
        }

        $lcDutydept = trim($queryParam['lc_dutydept']);
        if (!empty($lcDutydept)) {
            $sql = "select id from it_depart start with id= '$lcDutydept' connect by prior id =pid";
            $ids = M('it_depart')->query($sql);
            $ids =removeArrKey($ids,'id');
            $where['lc_dutydept'] = ['in', $ids];
        }
        //查询数据库
        $model = M('license');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");//转换时间
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");
        if ($isExport) {
            $data = $obj->select();
            $header =['许可名称','厂商','版本','授权方式','许可服务器要求','操作系统要求','许可依赖信息','部署方式','端口', '管理工具及版本','采购途径','许可数量','有效期开始时间','有效期结束时间','用户范围','使用状态','管理员A岗', '管理员B岗','所属班组', '许可责任人','责任部门','上线时间','是否巡查','厂商商务联系人及联系方式', ' 厂商技术联系人及联系方式','是否开机自启动','搜索关键词','功能简介','备注'];
            foreach ($data as $k => &$v) {

                //责任人
                if (!empty($v['lc_dutyman'])) {
                    $userName = D('org')->getViewPerson($v['lc_dutyman']);
                    $v['lc_dutyman'] =  $userName['username'];
                    $v['lc_dutydept'] = $this->removeStr($userName['orgfullname']); //去掉字符串
                }
                //管理员A岗
                if(!empty($v['lc_admina'])){
                    $userName = D('org')->getViewPerson($v['lc_admina']);
                   // $v['lc_admina'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['lc_admina'] = $userName['username'];
                }else{
                    $v['lc_admina'] = '-';
                }
                //管理员B岗
                if(!empty($v['lc_adminb'])){
                    $userName = D('org')->getViewPerson($v['lc_adminb']);
                    //$v['lc_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['lc_adminb'] = $userName['username'];
                }else{
                    $v['lc_adminb'] = '-';
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

                //许可责任人
                if(!empty($v['lc_dutyman'])){
                    $userName = D('org')->getViewPerson($v['lc_dutyman']);
//                    $v['app_dutyman'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['lc_dutyman'] = $userName['realusername'];

                    //责任人部门
                    $v['lc_dutydept'] = $this -> removeStr($userName['orgfullname']);//去掉字符串
                }else{
                    $v['lc_dutyman'] = '-';
                    $v['lc_dutydept'] = '-';
                }
                //管理员A岗
                if(!empty($v['lc_admina'])){
                    $userName = D('org')->getViewPerson($v['lc_admina']);
//                    $v['app_admin'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['lc_admina'] = $userName['realusername'];
                }else{
                    $v['lc_admina'] = '-';
                }
                //管理员B岗
                if(!empty($v['lc_adminb'])){
                    $userName = D('org')->getViewPerson($v['lc_adminb']);
//                    $v['app_adminb'] = $userName['realusername'] . '(' . $userName['username'] . ')';
                    $v['lc_adminb'] = $userName['realusername'];
                }else{
                    $v['lc_adminb'] = '-';
                }
                $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['lc_atpid'])->count();
                $data[$k]['lcCount'] = $rlxCount;
                $rlxCount = M('checkup')->where("appid = '%s' and atpstatus is null",$v['lc_atpid'])->count();
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
        $arr = ['使用状态(许可)','许可授权方式','许可依赖信息','许可服务器要求'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $zhuangTai = $arrDic['使用状态(许可)'];
        $zhuangTaiArray = array_column($zhuangTai,'dic_name');

        $shouQuan = $arrDic['许可授权方式'];
        $shouQuanArray = array_column($shouQuan,'dic_name');

        $yiLai = $arrDic['许可依赖信息'];
        $yiLaiArray = array_column( $yiLai,'dic_name');

        $yaoQiu = $arrDic['许可服务器要求'];
        $yaoQiuArray = array_column($yaoQiu,'dic_name');

        //字段
        $fields = ['lc_name','lc_develop','lc_version','lc_author','lc_server','lc_os','lc_deploymode','lc_dependinfo','lc_port','lc_tools','lc_purchase','lc_num','lc_starttime','lc_endtime','lc_userrange','lc_status','lc_admina','lc_adminb','lc_group','lc_dutyman','lc_dutydept','lc_onlinetime','lc_ischeck','lc_business','lc_technician','lc_auto','lc_keyword','lc_function','lc_remark'
        ];
        //许可名称','厂商','版本','授权方式','许可服务器要求','操作系统要求','许可依赖信息','部署方式','端口''管理工具及版本','采购途径','许可数量','有效期开始时间','有效期结束时间','用户范围','使用状态','管理员A岗','管理员B岗','所属班组', '许可责任人','责任部门','上线时间','是否巡查','厂商商务联系人及联系方式',' 厂商技术联系人及联系方式','是否开机自启动','搜索关键词','功能简介','备注
        $model = M('license');
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
                    case 'lc_status': //使用状态
                        $deptNameField ='lc_status';
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
                    case 'lc_author': //使用状态
                        $deptNameField ='lc_author';
                        $fieldName = '许可授权方式';
                        if(!empty($v)){
                            if(!in_array($v,$shouQuanArray)){
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            }else{
                                $k = array_search($v,$shouQuanArray);
                            }
                            $dicId = $shouQuan[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        }else{
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'lc_dependinfo': //使用状态
                        $deptNameField ='lc_dependinfo';
                        $fieldName = '许可依赖信息';
                        if(!empty($v)){
                            if(!in_array($v,$yiLaiArray)){
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            }else{
                                $k = array_search($v,$yiLaiArray);
                            }
                            $dicId = $yiLai[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        }else{
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'lc_server': //使用状态
                        $deptNameField ='lc_server';
                        $fieldName = '许可服务器要求';
                        if(!empty($v)){
                            if(!in_array($v,$yaoQiuArray)){
                                $error .= "第{$lineNum} 行 {$fieldName} 非字典内容<br>";
                                break;
                            }else{
                                $k = array_search($v,$yaoQiuArray);
                            }
                            $dicId = $yaoQiu[$k]['dic_name'];
                            $arr[$deptNameField] = $dicId;
                        }else{
                            $arr[$deptNameField] = $v;
                        }
                        break;
                    case 'lc_admina': //管理员A岗
                        $fieldName = '管理员A岗';
                        $userNameField = 'lc_admina';
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
                    case 'lc_dutyman': //责任人
                        $fieldName = '责任人';
                        $userNameField = 'lc_dutyman';
                        $userInfo = D('org')->getUserNames($v);
                        if(empty($userInfo)){
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'lc_adminb': //管理员B岗
                        $fieldName = '管理员B岗';
                        $userNameField = 'lc_adminb';
                        $userInfo = D('org')->getUserNames($v);
                        if (empty($userInfo)) {
                            $error .= "第{$lineNum} 行 {$fieldName} 未找到(填写域账号)<br>";
                            break;
                        }
                        $arr[$userNameField] = $userInfo['domainusername'];
                        break;
                    case 'lc_starttime': //上线时间
                        $fieldName = '有效期开始时间';
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
                    case 'lc_endtime': //上线时间
                        $fieldName = '有效期结束时间';
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

                    case 'lc_onlinetime': //上线时间
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
            $arr['lc_atpcreatedatetime'] = $time;
            $arr['lc_atpcreateuser'] = $loginUserId;
            $arr['lc_atpid'] = makeGuid();
            $initTables[] = $arr;
        }

        $model->startTrans();
        try {
            if (empty($error)) {
                $successNum = 0;
                foreach ($initTables as $value) {
                    //获取部门id
                    $value['lc_dutydept'] = M('it_person')->where("domainusername = '%s'",$value['lc_dutyman'])->getfield('orgid');
                    //获取部门中文名称
                    $dutydeptname= D('org')->getDepartId($value['lc_dutydept']);
                    $value['lc_dutydeptname'] =$this->removeStr($dutydeptname['fullname']);
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