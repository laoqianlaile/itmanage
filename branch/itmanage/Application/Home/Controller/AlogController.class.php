<?php
namespace Home\Controller;
use Think\Controller;
class AlogController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","","访问违规操作审计页面","成功");
        $this->display();
    }
    public function Aproblem(){
        addLog("","用户访问日志","","访问违规操作审计页面","成功");
        $this->display();
    }

    /**
    * 违规操作审计添加或修改
    */
    public function add(){
        $id = trim(I('get.al_id'));
        if(!empty($id)){
            $model = M('warnlog_3all1_alog');
            $data = $model->field('al_id,al_isok,al_event,al_ip,al_sectionname,al_eventtime,al_isnotify,al_createtime,al_contact,al_msg,al_alertsource,al_alertid,al_hostname')->where("al_id='%s'", $id)->find();
            $this->assign('data', $data);
        }
        addLog('','用户访问日志','',"访问违规操作审计添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['al_id']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('warnlog_3all1_alog');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty($id)){
            $data['al_id'] = makeGuid();
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('warnlog_3all1_alog', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('warnlog_3all1_alog', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data = $model->create($data);
            $res = $model->where("al_id='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('warnlog_3all1_alog', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('warnlog_3all1_alog', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取违规操作审计数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'al_ip,al_hostname,al_contact,al_alertsource,al_sectionname,al_msg,al_isnotify,al_event,al_eventtime';
        }else{
            $filedStr = 'al_ip,al_hostname,al_contact,al_alertsource,al_sectionname,al_msg,al_isnotify,al_event,al_eventtime, al_id';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $alIsok = isset($_GET['al_isok'])?$_GET['al_isok']:$_POST['al_isok'];
        $where['al_isok'] = ['eq',$alIsok];
        $alIp = trim($queryParam['al_ip']);
        if(!empty($alIp)) $where['al_ip'] = ['like', "%$alIp%"];
        
        $alHostname = trim($queryParam['al_hostname']);
        if(!empty($alHostname)) $where['al_hostname'] = ['like', "%$alHostname%"];
        
        $alEvent = trim($queryParam['al_event']);
        if(!empty($alEvent)) $where['al_event'] = ['like', "%$alEvent%"];
        
        $model = M('warnlog_3all1_alog');
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();
            $header = ['账号','名称','contact','报警来源','单位名','问题类型','是否已通知过审计员','事件名称','事件时间'];
            foreach ($data as $key => &$value) {
                $value['al_isnotify'] = $value['al_isnotify'] == 1 ? '已通知' : '未通知';
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
            foreach ($data as $key => &$value) {
                $value['al_isnotify'] = $value['al_isnotify'] == 1 ? '已通知' : '未通知';
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    
    /**
     * 设置
     */
    public function updataInScan()
    {
        $id = trim(I('post.al_id'));
        if (empty($id)) exit(makeStandResult(-1, '参数缺少'));
        $where = [];
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
            $where['al_id'] = ['in', $id];
        } else {
            $where['al_id'] = ['eq', $id];
        }
        //1为正常，0为异常
        $flag = trim(I('post.flag'));
        $data['al_isok'] = $flag;

        $model = M('warnlog_3all1_alog');
        $res = $model->where($where)->save($data);
        if ($res) {
            // 修改日志
            addLog('warnlog_3all1_alog', '设置扫描日志', 'delete', "设置扫描xxx 成功", '成功');
            exit(makeStandResult(1, '设置成功'));
        } else {
            // 修改日志
            addLog('warnlog_3all1_alog', '设置扫描日志', 'delete', "设置扫描xxx 失败", '失败');
            exit(makeStandResult(1, '设置成功'));
        }
    }
    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.al_id'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['al_id'] = ['in', $id];
        }else{
            $where['al_id'] = ['eq', $id];
        }

        $model = M('warnlog_3all1_alog');
        // 获取旧数据记录日志
        //$oldData = $model->where($where)->field('td_name')->select();
        //$names = implode(',', removeArrKey($oldData, 'td_name'));
        $res = $model->where($where)->delete();
        if($res){
            // 修改日志
            addLog('warnlog_3all1_alog', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('warnlog_3all1_alog', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }
    /**
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $powers = D('Admin/RefinePower')->getViewPowers('AlogRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }
}