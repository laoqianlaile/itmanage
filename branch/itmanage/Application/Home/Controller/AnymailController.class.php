<?php
namespace Home\Controller;
use Think\Controller;
class AnymailController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","","访问邮件降密发送审计页面","成功");
        $this->display();
    }
    public function problem(){
        addLog("","用户访问日志","","访问邮件降密发送审计页面","成功");
        $this->display();
    }
    
    /**
    * 邮件降密发送审计添加或修改
    */
    public function add(){
        $id = trim(I('get.am_id'));
        if(!empty($id)){
            $model = M('warnlog_anymail');
            $data = $model->field('am_msg,am_isnotify,am_createtime,am_id,am_attachlist,am_isok,am_mlogtimestr,am_subj,am_mfrom,am_mailmj,am_mto,am_size')->where("am_id='%s'", $id)->find();
            $this->assign('data', $data);
        }
        addLog('','用户访问日志','',"访问邮件降密发送审计添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['am_id']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('warnlog_anymail');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty($id)){
            $data['am_id'] = makeGuid();
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('warnlog_anymail', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('warnlog_anymail', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data = $model->create($data);
            $res = $model->where("am_id='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('warnlog_anymail', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('warnlog_anymail', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取邮件降密发送审计数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'am_mfrom,am_mto,am_mlogtimestr,am_subj,am_attachlist,am_mailmj,am_msg';
        }else{
            $filedStr = 'am_mfrom,am_mto,am_mlogtimestr,am_subj,am_attachlist,am_mailmj,am_msg, am_id';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $amIsok = isset($_GET['am_isok'])?$_GET['am_isok']:$_POST['am_isok'];
        $where['am_isok'] = ['eq',$amIsok];
        $amMfrom = trim($queryParam['am_mfrom']);
        if(!empty($amMfrom)) $where['am_mfrom'] = ['like', "%$amMfrom%"];
        
        $amMlogtimestr = trim($queryParam['am_mlogtimestr']);
        if(!empty($amMlogtimestr)) $where['am_mlogtimestr'] = ['EGT', $amMlogtimestr];
        
        $amMlogtimestrEnd = trim($queryParam['am_mlogtimestr_end']);
        if(!empty($amMlogtimestrEnd)) $where['am_mlogtimestr'] = ['ELT', $amMlogtimestrEnd];
        
        $amSubj = trim($queryParam['am_subj']);
        if(!empty($amSubj)) $where['am_subj'] = ['like', "%$amSubj%"];
        
        $amAttachlist = trim($queryParam['am_attachlist']);
        if(!empty($amAttachlist)) $where['am_attachlist'] = ['like', "%$amAttachlist%"];
        
        $amMailmj = trim($queryParam['am_mailmj']);
        if(!empty($amMailmj)) $where['am_mailmj'] = ['like', "%$amMailmj%"];
        $model = M('warnlog_anymail');
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
            // ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['发件人','收件人','发送时间','邮件标题','附件列表','密级','问题类型'];
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
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    
    /**
     * 设置
     */
    public function updataInScan()
    {
        $id = trim(I('post.am_id'));
        if (empty($id)) exit(makeStandResult(-1, '参数缺少'));
        $where = [];
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
            $where['am_id'] = ['in', $id];
        } else {
            $where['am_id'] = ['eq', $id];
        }
        //1为正常，0为异常
        $flag = trim(I('post.flag'));
        $data['am_isok'] = $flag;

        $model = M('warnlog_anymail');
        $res = $model->where($where)->save($data);
        if ($res) {
            // 修改日志
            addLog('warnlog_anymail', '设置扫描日志', 'delete', "设置扫描xxx 成功", '成功');
            exit(makeStandResult(1, '设置成功'));
        } else {
            // 修改日志
            addLog('warnlog_anymail', '设置扫描日志', 'delete', "设置扫描xxx 失败", '失败');
            exit(makeStandResult(1, '设置成功'));
        }
    }
    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.am_id'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['am_id'] = ['in', $id];
        }else{
            $where['am_id'] = ['eq', $id];
        }

        $model = M('warnlog_anymail');
        // 获取旧数据记录日志
        //$oldData = $model->where($where)->field('td_name')->select();
        //$names = implode(',', removeArrKey($oldData, 'td_name'));
        $res = $model->where($where)->delete();
        if($res){
            // 修改日志
            addLog('warnlog_anymail', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('warnlog_anymail', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }
    /**
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $powers = D('Admin/RefinePower')->getViewPowers('AnymailRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }
}