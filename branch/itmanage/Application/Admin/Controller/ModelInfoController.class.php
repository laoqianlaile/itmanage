<?php
namespace Admin\Controller;
use Think\Controller;
class ModelInfoController extends BaseController {

    /**
     * 模块管理
     */
    public function index(){
        $model = M('modelinfo');
        $modelList = $model->field('mi_id,mi_name,mi_pid')->where("mi_type='网站菜单'")->select();
        $this->assign('modellist', $modelList);
        addLog('','用户访问日志','访问模块管理','成功');
        $this->display();
    }

    /**
     * 获取模块列表
     */
    public function getData(){
        $queryParam = I('put.');
        $where = [];
        $miName = trim($queryParam['mi_name']);
        $pid=trim($queryParam['mi_pid']);
        if(!empty($miName))
        {
            $where['a.mi_name'] = array('like' ,"%$miName%");
        }
        if(!empty($pid))
        {
            $where['a.mi_pid'] = array('eq' ,$pid);
        }
        $where['a.mi_issystem'] = array('neq' ,'是');
        $model = M('modelinfo a');
        $data = $model->field('a.mi_id mi_id,a.mi_sort mi_sort,a.mi_type mi_type,a.mi_name mi_name,a.mi_url mi_url,a.mi_createtime mi_createtime,a.mi_createuser mi_createuser,a.mi_lastmodifytime mi_lastmodifytime,b.mi_name pname')
                    ->join('modelinfo b on a.mi_pid= b.mi_id','left')
                    ->where($where)
                    ->order("$queryParam[sort] $queryParam[sortOrder]")
                    ->limit($queryParam['offset'], $queryParam['limit'])
                    ->select();
        $count = $model->where($where)->count();

        foreach($data as &$value){
            if($value['mi_createtime']!=null)
            $value['mi_createtime'] = date('Y-m-d H:i:s',$value['mi_createtime']);
            if($value['mi_lastmodifytime']!=null)
            $value['mi_lastmodifytime'] = date('Y-m-d H:i:s',$value['mi_lastmodifytime']);
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }
    public function export(){
        $queryParam = I('get.');

        $where = [];
        $miName = trim($queryParam['mi_name']);
        $pid=trim($queryParam['mi_pid']);
        if(!empty($miName))
        {
            $where['a.mi_name'] = array('like' ,"%$miName%");
        }
        if(!empty($pid))
        {
            $where['a.mi_pid'] = array('eq' ,$pid);
        }
        $where['a.mi_isdefault']=['neq','是'];
        $model = M('modelinfo a');
        $data = $model->field('a.mi_sort mi_sort,a.mi_name mi_name,b.mi_name pname,a.mi_type mi_type,a.mi_url mi_url,a.mi_createtime mi_createtime,a.mi_lastmodifytime mi_lastmodifytime')
            ->join('modelinfo b on a.mi_pid= b.mi_id','left')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->select();
        $count = $model->where($where)->count();

        foreach($data as &$value){
            if($value['mi_createtime']!=null)
            $value['mi_createtime'] = date('Y-m-d H:i:s',$value['mi_createtime']);
            if($value['mi_lastmodifytime']!=null)
            $value['mi_lastmodifytime'] = date('Y-m-d H:i:s',$value['mi_lastmodifytime']);
        }
        addLog('','对象修改日志','导出模块列表','成功');
        $header = array('排序号','模块名称','父级模块','模块类型','访问路径','创建时间','上次修改时间');
        if( $count > 1000){
            csvExport($header, $data, true);
        }else{
            excelExport($header, $data, true);
        }
    }
    /**
     * 模块添加或修改
     */
    public function add(){
        $id = trim(I('get.id'));
        $add=trim(I('get.add'));
        $model = M('modelinfo');
        $modelList = $model->field('mi_id,mi_name,mi_pid')->where(" mi_type='网站菜单'")->select();
        if(!empty($id)){
            //判断是添加还是修改
            if(!empty($add))
            {
                $data = $model->field('mi_pid')->where("mi_id='%s'", $id)->find();
                $modelList = $model->field('mi_id,mi_name,mi_pid')->where("mi_id!='%s' and  mi_type='网站菜单'")->select();
                $this->assign('data', $data);
            }else
            {
                $data = $model->field('mi_id,mi_sort,mi_name,mi_type,mi_url,mi_pid')->where("mi_id='%s'", $id)->find();
                $modelList = $model->field('mi_id,mi_name,mi_pid')->where("mi_id!='%s' and  mi_type='网站菜单'", $id)->select();
                $this->assign('data', $data);
            }
        }
        addLog('','用户访问日志','访问模块添加','成功');

        $this->assign('modellist', $modelList);
        $this->display();
    }

    /**
     * 模块添加修改
     */
    public function addModelInfo(){
        $id = trim(I('post.id'));
        $data['mi_name'] = trim(I('post.mi_name'));
        $data['mi_sort']=trim(I('post.mi_sort'));
        $data['mi_type']=trim(I('post.mi_type'));
        $data['mi_url']=trim(I('post.mi_url'));
        $data['mi_pid']=trim(I('post.mi_pid'));
        if(empty($data['mi_name']))  exit(makeStandResult(-1,'请输入模块名称'));
        $model = M('modelinfo');
        //为空则添加
        if(empty($id)){
            $tem= $model->where("mi_name='%s'",$data['mi_name'])->find();
            if(!empty($tem)) {
                addLog('modelinfo','三员操作日志','新增模块'.$data['mi_name'],'失败');
                exit(makeStandResult(-1,'模块已存在'));
            }
            $tem= $model->where("mi_sort='%s'",$data['mi_sort'])->find();
            if(!empty($tem))
            {
               $t= $model->where("mi_sort>=%s",$data['mi_sort'])->select();
                foreach($t as &$val)
                {
                    $val['mi_sort']=$val['mi_sort']+1;
                    $model->save($val);
                }
            }
            $data['mi_createtime'] = time();
            $data['mi_id'] = makeGuid();
            $data['mi_createuser'] = session('user_id');
            $res = $model->add($data);
            if(empty($res)){
                addLog('modelinfo','三员操作日志','新增模块'.$data['mi_name'],'失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('modelinfo','三员操作日志','新增模块'.$data['mi_name'],'成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['mi_lastmodifytime'] = time();
            $data['mi_lastmodifyuser'] = session('user_id');
            $tem= $model->where("mi_name='%s'",$data['mi_name'])->find();
            if($tem['mi_id']!=$id&&!empty($tem)) {
                addLog('modelinfo','三员操作日志','修改模块'.$data['mi_name'],'失败');
                exit(makeStandResult(-1,'模块已存在'));
            }
            $tem= $model->where("mi_sort = %s and mi_id !='$id'",$data['mi_sort'])->find();
            if(!empty($tem))
            {
                $t= $model->where("mi_sort>=%s",$data['mi_sort'])->select();
                foreach($t as &$val)
                {
                    $val['mi_sort']=$val['mi_sort']+1;
                    $model->save($val);
                }
            }
            $res = $model->where("mi_id='%s'", $id)->save($data);
            if(empty($res)){
                addLog('modelinfo','三员操作日志','修改模块'.$data['mi_name'],'失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('modelinfo','三员操作日志','修改模块'.$data['mi_name'],'成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 删除模块
     */
    public function delModelInfo(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $idStr = "'".implode("','", $id)."'";
        $model = M('modelinfo');
        $roleauth=M('roleauth');
        foreach($id as $key=>$val)
        {
            $tem= $model->where("mi_id='%s'",$val)->find();
            if(!empty($tem))
            {
//                addLog('modelinfo','三员操作日志','delete','删除模块'.$tem['mi_name'],'成功');
            }
        }
        $roleauth->where("ra_miid in ($idStr)")->delete();
        $res = $model -> where("mi_id in ($idStr)")->delete();
        if(empty($res)){
            addLog('modelinfo','三员操作日志','删除模块','失败');
            exit(makeStandResult(-1,'删除失败'));
        }else{
            addLog('modelinfo','三员操作日志','删除模块','成功');
            exit(makeStandResult(1,'删除成功'));
        }
    }

}