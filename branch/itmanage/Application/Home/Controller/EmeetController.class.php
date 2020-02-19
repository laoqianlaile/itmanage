<?php
namespace Home\Controller;

use Think\Controller;

class EmeetController extends BaseController
{
//应用系统管理
    public function index()
    {
        $arr = ['会议室名称'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('room', $arrDic['会议室名称']);

        addLog("it_application", "用户访问日志",  "访问电子会议室页面", "成功");
        $this->display();
    }

    /**
     * 应用系统添加或修改
     */
    public function add()
    {
        $id = trim(I('get.emr_atpid'));
        if (!empty($id)) {
            $model = M('emeetingroom');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");

            $data = $model
                ->where("emr_atpid='%s'", $id)
                ->find();

            $userDept = D('org') -> getDepartId($data['emr_dept']);//去掉字符串
            $userDept = $this->removeStr($userDept['fullname']);
            $this->assign('emr_dept', $userDept);

            //申请人
            $secadminUser = D('org')->getUserNames($data['emr_applyman']);
            $userMan= $secadminUser['realusername'].'('.$secadminUser['domainusername'].')';
            $this->assign('userMan', $userMan);

            //处理人
            $auditUser = D('org')->getUserNames($data['emr_dealperson']);
            $emr_dealperson= $auditUser['realusername'].'('.$auditUser['domainusername'].')';
            $this->assign('emr_dealperson', $emr_dealperson);

            $branch = explode(';',$data['emr_usedemand']);
            $this->assign('use', $branch);

            $room = explode(';',$data['emr_roomname']);
            $this->assign('room', $room);

            $this->assign('data', $data);
        }
        $arr = ['使用需求','会议室名称'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('usedemand', $arrDic['使用需求']);
        $this->assign('roomname', $arrDic['会议室名称']);
        addLog('it_application','用户访问日志', "访问电子会议室添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['emr_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $model = M('emeetingroom');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $data['emr_usedemand'] = implode(';',$data['emr_usedemand']);
        $data['emr_roomname'] = implode(';',$data['emr_roomname']);
        if (empty($id)) {
            $data['emr_atpid'] = makeGuid();
            $data = $model->create($data);
            $data['emr_atpcreatetime'] = $time;
            $data['emr_atpcreateuser'] = $user;


            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('emeetingroom', '对象添加日志', '添加主键为'.$data['emr_atpid'], '失败',$data['emr_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('emeetingroom',  '对象添加日志',  '添加主键为'.$data['emr_atpid'], '成功',$data['emr_atpid']);
                //添加服务器关联关系
//                $this -> changeRelationSev($data['app_atpid'],$data['app_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);

            $data['emr_atpmodifydatetime'] = $time;
            $data['emr_atpmodifyuser'] = $user;


            $res = $model->where("emr_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('emeetingroom', '对象修改日志','修改主键为'.$id, '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
               addLog('emeetingroom', '对象修改日志','修改主键为'.$id, '成功',$id);
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
            $filedStr = 'emr_date,emr_time,emr_applyman,emr_applytel,emr_name,emr_usedemand,emr_roomname,emr_dealperson,emr_bz,emr_dept';
        } else {
            $filedStr = 'emr_date,emr_time,emr_applyman,emr_applytel,emr_name,emr_usedemand,emr_roomname,emr_dealperson,emr_bz,emr_dept,emr_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['emr_atpstatus'] = ['exp', 'IS NULL'];
        $applyman = trim($queryParam['emr_applyman']);
        if (!empty($applyman)) $where['emr_applyman'] = ['like', "%$applyman%"];

        $emr_name = strtolower(trim($queryParam['emr_name']));
        if (!empty($emr_name)) $where['lower(emr_name)'] = ['like', "%$emr_name%"];

        $roomname = trim($queryParam['roomname']);
        if (!empty($roomname)) $where['emr_roomname'] = ['like', "%$roomname%"];

        $model = M('emeetingroom');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");


        if ($isExport) {
            $data = $obj->select();

            $header = ['日期', '时间','申请人','电话','会议主题', '使用需求','会议室名称', '处理人', '备注', '部门', '处室'];
            foreach ($data as $k => &$v) {


                $userDept = D('org') -> getDepartId($v['emr_dept']);//去掉字符串
                $useroffice = $userDept['name'];
                $userDept = $this->removeStr($userDept['fullname']);
                $dept = explode('-',$userDept);
                $count = count($dept)-1;
                $data[$k]['emr_dept'] = $dept[$count-1].'-'.$dept[$count];
                $data[$k]['office'] = $useroffice;

                //安全员
                $secadminUser = D('org')->getViewPerson($v['emr_applyman']);
                $data[$k]['emr_applyman'] = $secadminUser['realusername'];

                //审计员
                $auditUser = D('org')->getViewPerson($v['emr_dealperson']);
                $data[$k]['emr_dealperson'] = $auditUser['realusername'];

                $shu = ['日','一','二','三','四','五','六'];
                $date = explode('-',$v['emr_date']);
                $data[$k]['emr_date'] = $date[0].'月'.$date[1].'日'.'星期'.$shu[$date[2]];


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

                $userDept = D('org') -> getDepartId($v['emr_dept']);//去掉字符串
                $useroffice = $userDept['name'];
                $userDept = $this->removeStr($userDept['fullname']);
                $dept = explode('-',$userDept);
                $count = count($dept)-1;
                $data[$k]['office'] = $useroffice;
                $data[$k]['dept'] = $dept[$count-1].'-'.$dept[$count];

                //申请人
                $secadminUser = D('org')->getViewPerson($v['emr_applyman']);
                $data[$k]['emr_applyman'] = $secadminUser['realusername'];

                //处理人
                $auditUser = D('org')->getViewPerson($v['emr_dealperson']);
                $data[$k]['emr_dealperson'] = $auditUser['realusername'];

                $shu = ['日','一','二','三','四','五','六'];
                $date = explode('-',$v['emr_date']);
                $data[$k]['emr_date'] = $date[0].'月'.$date[1].'日'.'星期'.$shu[$date[2]];

            }

            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }


    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.emr_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('emeetingroom');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['emr_atpmodifytime'] = $time;
                $data['emr_atpmodifyuser'] = $user;
                $data['emr_atpstatus'] = 'DEL';
                $res = $model->where("emr_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('emeetingroom', '对象删除日志', "删除主键为".$id, '成功',$id);
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('emeetingroom', '对象删除日志', "删除主键为".$ids, '失败',$id);
            exit(makeStandResult(-1, '删除失败'));
        }
    }


}