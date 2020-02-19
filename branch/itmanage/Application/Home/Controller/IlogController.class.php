<?php
namespace Home\Controller;
use Think\Controller;
class IlogController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","访问违规外联审计页面","成功");
        $this->display();
    }
    public function Iproblem(){
        addLog("","用户访问日志","访问违规外联审计页面","成功");
        $this->display();
    }

    /**
    * 违规外联审计添加或修改
    */
    public function add(){
        $id = trim(I('get.il_id'));
        if(!empty($id)){
            $model = M('warnlog_3all1_ilog');
            $data = $model->field('il_msg,il_event,il_mac,il_sectionname,il_contact,il_alertid,il_netip,il_eventtime,il_alertsource,il_createtime,il_ip,il_id,il_isok,il_isnotify,il_hostname,il_hid')->where("il_id='%s'", $id)->find();
            $this->assign('data', $data);
        }
        addLog('','用户访问日志','',"访问违规外联审计添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['il_id']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('warnlog_3all1_ilog');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty($id)){
            $data['il_id'] = makeGuid();
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('warnlog_3all1_ilog', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('warnlog_3all1_ilog', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data = $model->create($data);
            $res = $model->where("il_id='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('warnlog_3all1_ilog', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('warnlog_3all1_ilog', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取违规外联审计数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'il_ip,il_mac,il_hostname,il_contact,il_alertsource,il_sectionname,il_msg,il_isnotify,il_event,il_eventtime,il_hid,il_netip';
        }else{
            $filedStr = 'il_ip,il_mac,il_hostname,il_contact,il_alertsource,il_sectionname,il_msg,il_isnotify,il_event,il_eventtime,il_hid,il_netip, il_id';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $ilIsok = isset($_GET['il_isok'])?$_GET['il_isok']:$_POST['il_isok'];
        $where['il_isok'] = ['eq',$ilIsok];
        $ilIp = trim($queryParam['il_ip']);
        if(!empty($ilIp)) $where['il_ip'] = ['like', "%$ilIp%"];
        
        $ilHostname = trim($queryParam['il_hostname']);
        if(!empty($ilHostname)) $where['il_hostname'] = ['like', "%$ilHostname%"];
        
        $ilEvent = trim($queryParam['il_event']);
        if(!empty($ilEvent)) $where['il_event'] = ['like', "%$ilEvent%"];
        
        $ilMac = trim($queryParam['il_mac']);
        if(!empty($ilMac)) $where['il_mac'] = ['like', "%$ilMac%"];
        
        $model = M('warnlog_3all1_ilog');
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();
            $header = ['ip地址','mac地址','名称','contact','报警来源','单位名','问题类型','是否已通知过审计员','事件名称','事件时间','hid','netip'];
            foreach ($data as $key => &$value) {
                $value['il_isnotify'] = $value['il_isnotify'] == 1 ? '已通知' : '未通知';
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
                $value['il_isnotify'] = $value['il_isnotify'] == 1 ? '已通知' : '未通知';
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    
    /**
     * 设置
     */
    public function updataInScan()
    {
        $id = trim(I('post.il_id'));
        if (empty($id)) exit(makeStandResult(-1, '参数缺少'));
        $where = [];
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
            $where['il_id'] = ['in', $id];
        } else {
            $where['il_id'] = ['eq', $id];
        }
        //1为正常，0为异常
        $flag = trim(I('post.flag'));
        $data['il_isok'] = $flag;

        $model = M('warnlog_3all1_ilog');
        $res = $model->where($where)->save($data);
        if ($res) {
            // 修改日志
            addLog('warnlog_3all1_ilog', '设置扫描日志', 'delete', "设置扫描xxx 成功", '成功');
            exit(makeStandResult(1, '设置成功'));
        } else {
            // 修改日志
            addLog('warnlog_3all1_ilog', '设置扫描日志', 'delete', "设置扫描xxx 失败", '失败');
            exit(makeStandResult(1, '设置成功'));
        }
    }
    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.il_id'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['il_id'] = ['in', $id];
        }else{
            $where['il_id'] = ['eq', $id];
        }

        $model = M('warnlog_3all1_ilog');
        // 获取旧数据记录日志
        //$oldData = $model->where($where)->field('td_name')->select();
        //$names = implode(',', removeArrKey($oldData, 'td_name'));
        $res = $model->where($where)->delete();
        if($res){
            // 修改日志
            addLog('warnlog_3all1_ilog', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('warnlog_3all1_ilog', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }
    /**
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $powers = D('Admin/RefinePower')->getViewPowers('IlogRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }
}