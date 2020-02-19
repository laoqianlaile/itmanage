<?php
namespace Admin\Controller;

use Think\Controller;

class LogManageController extends BaseController
{
    /**
     * 日志查询
     */
    public function index()
    {
        $roleids = session('roleids');
        if(stripos($roleids, ',') !== false) {
            $roleId = explode(',', $roleids);
        }else {
            $roleId = [$roleids];
        }
        if (in_array('1',$roleId) || in_array('2',$roleId)||in_array('3',$roleId)) {
            $this->assign('roleids', $roleids);
            $this->assign('isuseful',true);
        } else {
            $this->assign('isuseful',false);
            addLog('', '用户访问日志', '访问日志查询', '失败');
        }
        addLog('', '用户访问日志', '访问日志查询', '成功');
        $this->display();
    }

    /**
     * 获取日志
     */
    public function getData()
    {
        $queryParam = I('put.');
        $realName = trim($queryParam['real_name']);
        if(stripos($queryParam['roleids'], ',') !== false) {
            $roleId = explode(',', $queryParam['roleids']);
        }else {
            $roleId = [$queryParam['roleids']];
        }
        $count=0;
        if (!empty($realName))
        {
            $where['opl_user'][$count] = ['like', "%$realName%"];
            $count=1;
            $conditon='用户-'.$realName.';';
        }

        $logtype = trim($queryParam['logtype']);
        if (!empty($logtype))
        {
            $where['opl_logtype'][] = ['like', "%$logtype%"];
            $conditon=$conditon.'日志类型-'.$logtype.';';
        }

        $fristcontent = trim($queryParam['firstcontent']);
        if (!empty($fristcontent))
        {
            $where['opl_firstcontent'] = ['like', "%$fristcontent%"];
            $conditon=$conditon.'操作内容-'.$fristcontent.';';
        }

        $result = trim($queryParam['result']);
        if (!empty($result))
        {
            $where['opl_result'] = ['like', "%$result%"];
            $conditon=$conditon.'操作结果-'.$result.';';
        }

        $endtime = strtotime(trim($queryParam['endtime']));
        if (!empty($endtime))
        {
            $endtime+=24*3600;
            $where['opl_time'][] = ['lt', $endtime];
            $conditon=$conditon.'创建时间不大于-'.date('Y-m-d H:i:s', $endtime).';';
        }

        $starttime = strtotime(trim($queryParam['starttime']));
        if (!empty($starttime))
        {
            $where['opl_time'][] = ['egt', $starttime];
            $conditon=$conditon.'创建时间不小于-'.date('Y-m-d H:i:s', $starttime).';';
        }
        $ip = trim($queryParam['ip']);
        if (!empty($ip))
        {
            $where['opl_ip'] = ['like', "%$ip%"];
            $conditon=$conditon.'操作结果-'.$ip.';';
        }
        $model = M('oplog');
        $where['opl_user'][$count]=[];
        if ($queryParam['isshow']) {
            if ($queryParam['index'] == 0) {
                if (in_array('3',$roleId)) {
                    //普通用户日志
                    $where['opl_user'][$count] = [['notlike', '%(sysadmin)'], ['notlike', '%(sysadmin2)'], ['notlike', '%(secadmin)'], ['notlike', '%(secadmin2)'], ['notlike', '%(auditadmin)'], ['notlike', '%(auditadmin2)'],['neq','CA同步数据']];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_id,opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time,opl_object,opl_logcode')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->limit($queryParam['offset'], $queryParam['limit'])
                        ->select();
                    $count = $model
                        ->where($where)
                        ->count();
                } else if (in_array('2',$roleId)) {
                    //系统管理员日志
                    $where['opl_user'][$count] = [['like', '%(sysadmin)'], ['like', '%(sysadmin2)'],'or'];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_id,opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time,opl_object,opl_logcode')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->limit($queryParam['offset'], $queryParam['limit'])
                        ->select();
//                    print_r($model->_sql());die;
                    $count = $model
                        ->where($where)
                        ->count();
                }
            } else {
                if (in_array('3',$roleId)) {
                    //审计管理员日志
                    $where['opl_user'][$count] = [['like', '%(auditadmin)'], ['like', '%(auditadmin2)'],'or'];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_id,opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time,opl_object,opl_logcode')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->limit($queryParam['offset'], $queryParam['limit'])
                        ->select();
                    $count = $model
                        ->where($where)
                        ->count();
                } else if (in_array('2',$roleId)) {
                    //安全管理员日志
                    $where['opl_user'][$count] = [['like', '%(secadmin)'], ['like', '%(secadmin2)'],'or'];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_id,opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time,opl_object,opl_logcode')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->limit($queryParam['offset'], $queryParam['limit'])
                        ->select();
                    $count = $model
                        ->where($where)
                        ->count();
                }
            }
        } else {
            $where['opl_user'][$count] = [['eq','CA同步数据']];
            //$where['opl_logtype'][] = ['eq', '系统运行日志'];
            $data = $model->field('opl_id,opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time,opl_object,opl_logcode')
                ->where($where)
                ->order("$queryParam[sort] $queryParam[sortOrder]")
                ->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            $count = $model
                ->where($where)
                ->count();
        }
        foreach ($data as &$value) {
            $tmp = md5($value['opl_id'].$value['opl_user'].$value['opl_ip'].$value['opl_time'].$value['opl_logtype'].$value['opl_object'].$value['opl_firstcontent'].$value['opl_result']);
            if($value['opl_time']!=null)
            $value['opl_time'] = date('Y-m-d H:i:s', $value['opl_time']);
            if($tmp == $value['opl_logcode']){
                $value['secsign'] = 0;
            }else{
//                print_r($value);
//                echo $tmp."<br/>";
//                echo $value['opl_logcode'];die;
                $value['secsign'] = 1;
            }
        }
        echo json_encode(array('total' => $count, 'rows' => $data));
    }

    /**
     * 导出数据
     */
    public function export()
    {
        $queryParam = I('get.');
        $realName = trim($queryParam['real_name']);
        if(stripos($queryParam['roleids'], ',') !== false) {
            $roleId = explode(',', $queryParam['roleids']);
        }else {
            $roleId = [$queryParam['roleids']];
        }
        $count=0;
        if (!empty($realName))
        {
            $where['opl_user'][$count] = ['like', "%$realName%"];
            $count=1;
            $conditon='用户-'.$realName.';';
        }

        $logtype = trim($queryParam['logtype']);
        if (!empty($logtype))
        {
            $where['opl_logtype'][] = ['like', "%$logtype%"];
            $conditon=$conditon.'日志类型-'.$logtype.';';
        }

        $fristcontent = trim($queryParam['firstcontent']);
        if (!empty($fristcontent))
        {
            $where['opl_firstcontent'] = ['like', "%$fristcontent%"];
            $conditon=$conditon.'操作内容-'.$fristcontent.';';
        }

        $result = trim($queryParam['result']);
        if (!empty($result))
        {
            $where['opl_result'] = ['like', "%$result%"];
            $conditon=$conditon.'操作结果-'.$result.';';
        }

        $endtime = strtotime(trim($queryParam['endtime']));
        if (!empty($endtime))
        {
            $endtime+=24*3600;
            $where['opl_time'][] = ['lt', $endtime];
            $conditon=$conditon.'创建时间不大于-'.date('Y-m-d H:i:s', $endtime).';';
        }

        $starttime = strtotime(trim($queryParam['starttime']));
        if (!empty($starttime))
        {
            $where['opl_time'][] = ['egt', $starttime];
            $conditon=$conditon.'创建时间不小于-'.date('Y-m-d H:i:s', $starttime).';';
        }
        $ip = trim($queryParam['ip']);
        if (!empty($ip))
        {
            $where['opl_ip'] = ['like', "%$ip%"];
            $conditon=$conditon.'操作结果-'.$ip.';';
        }

        $model = M('oplog');
        $where['opl_user'][$count]=[];
        if ($queryParam['isshow']) {
            if ($queryParam['index'] == 0) {
                if (in_array('3',$roleId)) {
                    //普通用户日志
                    addLog('','对象修改日志','导出普通用户日志','成功');
                    $where['opl_user'][$count] = [['notlike', '%(sysadmin)'], ['notlike', '%(sysadmin2)'], ['notlike', '%(secadmin)'], ['notlike', '%(secadmin2)'], ['notlike', '%(auditadmin)'], ['notlike', '%(auditadmin2)'],['neq','CA同步数据']];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->select();
                    $count = $model
                        ->where($where)
                        ->count();
                } else if (in_array('2',$roleId)) {
                    //系统管理员日志
                    addLog('','对象修改日志','导出系统管理员日志','成功');
                    $where['opl_user'][$count] = [['like', '%(sysadmin)'], ['like', '%(sysadmin2)'],'or'];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->select();
                    $count = $model
                        ->where($where)
                        ->count();
                }
            } else {
                if (in_array('3',$roleId)) {
                    //审计管理员日志
                    addLog('','对象修改日志','导出审计管理员日志','成功');
                    $where['opl_user'][$count] = [['like', '%(auditadmin)'], ['like', '%(auditadmin2)'],'or'];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->select();
                    $count = $model
                        ->where($where)
                        ->count();
                } else if (in_array('2',$roleId)) {
                    //安全管理员日志
                    addLog('','对象修改日志','导出安全管理员日志','成功');
                    $where['opl_user'][$count] = [['like', '%(secadmin)'], ['like', '%(secadmin2)'],'or'];
                    $where['opl_logtype'][] = ['neq', '系统运行日志'];
                    $data = $model->field('opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time')
                        ->where($where)
                        ->order("$queryParam[sort] $queryParam[sortOrder]")
                        ->select();
                    $count = $model
                        ->where($where)
                        ->count();
                }
            }
        } else {
            addLog('','对象修改日志','导出系统运行日志','成功');
            $where['opl_user'][$count] = [['eq','CA同步数据']];
            //$where['opl_logtype'][] = ['eq', '系统运行日志'];
            $data = $model->field('opl_user,opl_ip,opl_logtype,opl_firstcontent,opl_result,opl_time')
                ->where($where)
                ->order("$queryParam[sort] $queryParam[sortOrder]")
                ->select();
            $count = $model
                ->where($where)
                ->count();
        }

        foreach ($data as &$value) {
            if($value['opl_time']!=null)
            $value['opl_time'] = date('Y-m-d H:i:s', $value['opl_time']);
        }
        $header = array('用户','用户IP','日志类型','操作内容','操作结果','创建时间');
        if( $count > 1000){
            csvExport($header, $data, true);
        }else{
            excelExport($header, $data, true);
        }
    }
}