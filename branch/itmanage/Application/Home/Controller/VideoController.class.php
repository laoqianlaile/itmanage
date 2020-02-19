<?php
namespace Home\Controller;

use Think\Controller;

class VideoController extends BaseController
{
//应用系统管理
    public function index()
    {
        $arr = ['主会场','参会会议室','会议类型','会议形式'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('mainvenue', $arrDic['主会场']);
        $this->assign('branch', $arrDic['参会会议室']);
        $this->assign('type', $arrDic['会议类型']);
        $this->assign('style', $arrDic['会议形式']);

        addLog("it_application", "用户访问日志",  "访问视频会议页面", "成功");
        $this->display();
    }

    /**
     * 应用系统添加或修改
     */
    public function add()
    {
        $id = trim(I('get.vc_atpid'));
        if (!empty($id)) {
            $model = M('videoconference');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");

            $data = $model
                ->where("vc_atpid='%s'", $id)
                ->find();

            $userDept = D('org') -> getDepartId($data['vc_dept']);//去掉字符串
            $userDept = $this->removeStr($userDept['fullname']);
            $this->assign('vc_dept', $userDept);

            //申请人
            $secadminUser = D('org')->getUserNames($data['vc_applyman']);
            $userMan= $secadminUser['realusername'].'('.$secadminUser['domainusername'].')';
            $this->assign('userMan', $userMan);

            //处理人
            $auditUser = D('org')->getUserNames($data['vc_dealperson']);
            $vc_dealperson= $auditUser['realusername'].'('.$auditUser['domainusername'].')';
            $this->assign('vc_dealperson', $vc_dealperson);

            $branch = explode(';',$data['vc_branchvenue']);
            $this->assign('branchs', $branch);

            $this->assign('data', $data);
        }
        $arr = ['主会场','参会会议室','会议类型','会议形式'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('mainvenue', $arrDic['主会场']);
        $this->assign('branch', $arrDic['参会会议室']);
        $this->assign('type', $arrDic['会议类型']);
        $this->assign('style', $arrDic['会议形式']);

        addLog('it_application','用户访问日志', "访问视频会议添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['vc_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $model = M('videoconference');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $data['vc_branchvenue'] = implode(';',$data['vc_branchvenue']);
        if (empty($id)) {
            $data['vc_atpid'] = makeGuid();
            $data = $model->create($data);
            $data['vc_atpcreatetime'] = $time;
            $data['vc_atpcreateuser'] = $user;


            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('videlcontroller', '对象添加日志', '添加主键为'.$data['vc_atpid'], '失败',$data['vc_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('videlcontroller',  '对象添加日志',  '添加主键为'.$data['vc_atpid'], '成功',$data['vc_atpid']);
                //添加服务器关联关系
//                $this -> changeRelationSev($data['app_atpid'],$data['app_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);

            $data['vc_atpmodifytime'] = $time;
            $data['vc_atpmodifyuser'] = $user;


            $res = $model->where("vc_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('videlcontroller', '对象修改日志','修改主键为'.$id, '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
                addLog('videlcontroller', '对象修改日志',  '修改主键为'.$id, '成功',$id);
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
            $filedStr = 'vc_date,vc_time,vc_mainvenue,vc_name,vc_style,vc_type,vc_branchvenue,vc_applyman,vc_applytel,vc_dealperson,vc_dept';
        } else {
            $filedStr = 'vc_date,vc_time,vc_mainvenue,vc_name,vc_style,vc_type,vc_branchvenue,vc_applyman,vc_applytel,vc_dealperson,vc_atpid,vc_dept';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['vc_atpstatus'] = ['exp', 'IS NULL'];
        $mainvenue = trim($queryParam['mainvenue']);
        if (!empty($mainvenue)) $where['vc_mainvenue'] = ['like', "%$mainvenue%"];

        $vc_name = strtolower(trim($queryParam['vc_name']));
        if (!empty($vc_name)) $where['lower(vc_name)'] = ['like', "%$vc_name%"];

        $vc_type = trim($queryParam['vc_type']);
        if (!empty($vc_type)) $where['vc_type'] = ['like', "%$vc_type%"];

        $vc_applyman = trim($queryParam['vc_applyman']);
        if (!empty($vc_applyman)) $where['vc_applyman'] = ['like', "%$vc_applyman%"];


        $model = M('videoconference');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");


        if ($isExport) {
            $data = $obj->select();

            $header = ['日期', '时间','主会场','会议议题','会议形式', '会议类型','参会会议室', '申请人', '电话','处理人', '部门', '处室'];
            foreach ($data as $k => &$v) {


                $userDept = D('org') -> getDepartId($v['vc_dept']);//去掉字符串
                $useroffice = $userDept['name'];
                $userDept = $this->removeStr($userDept['fullname']);
                $dept = explode('-',$userDept);
                $count = count($dept)-1;
                $data[$k]['vc_dept'] = $dept[$count-1].'-'.$dept[$count];
                $data[$k]['office'] = $useroffice;

                //申请人
                $secadminUser = D('org')->getViewPerson($v['vc_applyman']);
                $data[$k]['vc_applyman'] = $secadminUser['realusername'];

                //处理人
                $auditUser = D('org')->getViewPerson($v['vc_dealperson']);
                $data[$k]['vc_dealperson'] = $auditUser['realusername'];

                $shu = ['日','一','二','三','四','五','六'];
                $date = explode('-',$v['vc_date']);
                $data[$k]['vc_date'] = $date[0].'月'.$date[1].'日'.'星期'.$shu[$date[2]];


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

                $userDept = D('org') -> getDepartId($v['vc_dept']);//去掉字符串
                $useroffice = $userDept['name'];
                $userDept = $this->removeStr($userDept['fullname']);
                $dept = explode('-',$userDept);
                $count = count($dept)-1;
                $data[$k]['office'] = $useroffice;
                $data[$k]['dept'] = $dept[$count-1].'-'.$dept[$count];

                //申请人
                $secadminUser = D('org')->getViewPerson($v['vc_applyman']);
                $data[$k]['vc_applyman'] = $secadminUser['realusername'];

                //处理人
                $auditUser = D('org')->getViewPerson($v['vc_dealperson']);
                $data[$k]['vc_dealperson'] = $auditUser['realusername'];

                $shu = ['日','一','二','三','四','五','六'];
                $date = explode('-',$v['vc_date']);
                $data[$k]['vc_date'] = $date[0].'月'.$date[1].'日'.'星期'.$shu[$date[2]];

            }

            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }


    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.vc_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('videoconference');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['vc_atpmodifytime'] = $time;
                $data['vc_atpmodifyuser'] = $user;
                $data['vc_atpstatus'] = 'DEL';
                $res = $model->where("vc_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('videoconference', '对象删除日志', "删除主键为".$id, '成功',$id);
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('videoconference', '对象删除日志', "删除主键为".$ids, '失败',$id);
            exit(makeStandResult(-1, '删除失败'));
        }
    }


}