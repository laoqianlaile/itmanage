<?php
namespace Home\Controller;
use Think\Controller;
class UlogController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","","访问U盘违规操作审计页面","成功");
        $this->display();
    }
    public function Uproblem(){
        addLog("","用户访问日志","","访问U盘违规操作审计页面","成功");
        $this->display();
    }

    /**
    * U盘违规操作审计添加或修改
    */
    public function add(){
        $id = trim(I('get.ul_id'));
        if(!empty($id)){
            $model = M('warnlog_3all1_ulog');
            $data = $model->field('ul_id,ul_usectionname,ul_alertid,ul_eventtime,ul_alertsource,ul_ip,ul_serialnumber,ul_type,ul_hostname,ul_unitname,ul_isok,ul_company,ul_event,ul_isnotify,ul_createtime,ul_contact,ul_msg')->where("ul_id='%s'", $id)->find();
            $this->assign('data', $data);
        }
        addLog('','用户访问日志','',"访问U盘违规操作审计添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['ul_id']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('warnlog_3all1_ulog');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty($id)){
            $data['ul_id'] = makeGuid();
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('warnlog_3all1_ulog', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('warnlog_3all1_ulog', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data = $model->create($data);
            $res = $model->where("ul_id='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('warnlog_3all1_ulog', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('warnlog_3all1_ulog', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取U盘违规操作审计数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'ul_ip,ul_hostname,ul_contact,ul_alertsource,ul_usectionname,ul_unitname,ul_msg,ul_event,ul_isnotify,ul_company,ul_type,ul_serialnumber,ul_eventtime';
        }else{
            $filedStr = 'ul_ip,ul_hostname,ul_contact,ul_alertsource,ul_usectionname,ul_unitname,ul_msg,ul_event,ul_isnotify,ul_company,ul_type,ul_serialnumber,ul_eventtime, ul_id';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $ulIsok = isset($_GET['ul_isok'])?$_GET['ul_isok']:$_POST['ul_isok'];
        $where['ul_isok'] = ['eq',$ulIsok];
        $ulIp = trim($queryParam['ul_ip']);
        if(!empty($ulIp)) $where['ul_ip'] = ['like', "%$ulIp%"];
        
        $ulHostname = trim($queryParam['ul_hostname']);
        if(!empty($ulHostname)) $where['ul_hostname'] = ['like', "%$ulHostname%"];
        
        $ulEvent = trim($queryParam['ul_event']);
        if(!empty($ulEvent)) $where['ul_event'] = ['like', "%$ulEvent%"];
        
        $ulSerialnumber = trim($queryParam['ul_serialnumber']);
        if(!empty($ulSerialnumber)) $where['ul_serialnumber'] = ['like', "%$ulSerialnumber%"];
        
        $model = M('warnlog_3all1_ulog');
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();
            $header = ['账号','姓名','报警来源','密级','单位名','单位','问题类型','事件名称','是否已通知过审计员','company','类型','序列号','事件时间'];
            foreach ($data as $key => &$value) {
                $value['ul_isnotify'] = $value['ul_isnotify'] == 1 ? '已通知' : '未通知';
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
                $value['ul_isnotify'] = $value['ul_isnotify'] == 1 ? '已通知' : '未通知';
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }
    /**
     * 设置
     */
    public function updataInScan()
    {
        $id = trim(I('post.ul_id'));
        if (empty($id)) exit(makeStandResult(-1, '参数缺少'));
        $where = [];
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
            $where['ul_id'] = ['in', $id];
        } else {
            $where['ul_id'] = ['eq', $id];
        }
        //1为正常，0为异常
        $flag = trim(I('post.flag'));
        $data['ul_isok'] = $flag;

        $model = M('warnlog_3all1_ulog');
        $res = $model->where($where)->save($data);
        if ($res) {
            // 修改日志
            addLog('warnlog_3all1_ulog', '设置扫描日志', 'delete', "设置扫描xxx 成功", '成功');
            exit(makeStandResult(1, '设置成功'));
        } else {
            // 修改日志
            addLog('warnlog_3all1_ulog', '设置扫描日志', 'delete', "设置扫描xxx 失败", '失败');
            exit(makeStandResult(1, '设置成功'));
        }
    }
    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.ul_id'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['ul_id'] = ['in', $id];
        }else{
            $where['ul_id'] = ['eq', $id];
        }

        $model = M('warnlog_3all1_ulog');
        // 获取旧数据记录日志
        //$oldData = $model->where($where)->field('td_name')->select();
        //$names = implode(',', removeArrKey($oldData, 'td_name'));
        $res = $model->where($where)->delete();
        if($res){
            // 修改日志
            addLog('warnlog_3all1_ulog', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('warnlog_3all1_ulog', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }
    /**
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $powers = D('Admin/RefinePower')->getViewPowers('UlogRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }
}