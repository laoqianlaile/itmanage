<?php
namespace Admin\Controller;
use Think\Controller;
class DicTypeController extends BaseController {

    /**
     * 字典管理
     */
    public function index(){
        addLog('','用户访问日志','访问字典类型管理页面','成功');
        $this->display();
    }

    /**
     * 获取字典列表
     */
    public function getData(){
        $queryParam = I('put.');

        $where = [];
        $dicName = trim($queryParam['dic_name']);
        if(!empty($dicName)) $where['type_name'] = ['like', "%$dicName%"];
        $model = M('dic_type');
        $data = $model->field('dic_type_id,type_name,dic_type_createtime,dic_type_createuser,dic_type_lastmodifytime,dic_type_lastmodifyuser,user_realusername')
                    ->where($where)
                    ->join("left join sysuser on dic_type.dic_type_createuser = sysuser.user_id")
                    ->order("$queryParam[sort] $queryParam[sortOrder]")
                    ->limit($queryParam['offset'], $queryParam['limit'])
                    ->select();
        $count = $model->where($where)->count();

        foreach($data as &$value){
            $value['dic_type_createtime'] = date('Y-m-d H:i:s',$value['dic_type_createtime']);
            if(!empty($value['dic_type_lastmodifytime'])) $value['dic_type_lastmodifytime'] = date('Y-m-d H:i:s',$value['dic_type_lastmodifytime']);
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 字典添加或修改
     */
    public function add(){
        $id = trim(I('get.id'));
        if(!empty($id)){
            $model = M('dic_type');
            $data = $model->field('dic_type_id,type_name')->where("dic_type_id='%s'", $id)->find();
            $this->assign('data', $data);
        }
       addLog('','用户访问日志','访问字典类型添加、编辑页面','成功');
        $this->display();
    }

    /**
     * 字典类型添加修改
     */
    public function addDicType(){
        $id = trim(I('post.id'));
        $data['type_name'] = trim(I('post.type_name'));
        if(empty($data['type_name']))  exit(makeStandResult(-1,'请输入字典名称'));
        $model = M('dic_type');
        //为空则添加
        if(empty($id)){
            $data['dic_type_createtime'] = time();
            $data['dic_type_id'] = makeGuid();
            $data['dic_type_createuser'] = session('user_id');
            $res = $model->add($data);
            if(empty($res)){
               addLog('dic_type', '对象修改日志', '添加字典类型=>'.$data['type_name']. '，失败','失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
               addLog('dic_type', '对象修改日志',  '添加字典类型=>'.$data['type_name']. '，成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['dic_type_lastmodifytime'] = time();
            $data['dic_type_lastmodifyuser'] = session('user_id');
            $typeName = $model->where("dic_type_id='%s'", $id)->field('type_name')->find();
            $res = $model->where("dic_type_id='%s'", $id)->save($data);
            if(empty($res)){
               addLog('dic_type', '对象修改日志','修改字典类型=>'.$typeName['type_name']. '，失败','失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
               addLog('dic_type', '对象修改日志','修改字典类型=>'.$typeName['type_name']. '，成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 删除字典
     */
    public function delDicType(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $idStr = "'".implode("','", $id)."'";
        $model = M('dic_type');
        $names = $model-> where("dic_type_id in ($idStr)")->field('type_name')->select();
        $names = implode(',', removeArrKey($names, 'type_name'));
        $typenames=$model->field('type_name')->where("dic_type in($idStr)")->join('dic on dic.dic_type=dic_type.dic_type_id')->select();
        if(!empty($typenames))
        {
            $typenames =array_unique(removeArrKey($typenames, 'type_name'));
            $typenames = implode(',', $typenames);
           addLog('dic_type', '对象修改日志', '删除字典类型=>'.$names. '，失败','失败');
            exit(makeStandResult(-1,$typenames.'字典类型存在字典不可删除'));
        }
        $res = $model -> where("dic_type_id in ($idStr)")->delete();
        if(empty($res)){
           addLog('dic_type', '对象修改日志',  '删除字典类型=>'.$names. '，失败','失败');
            exit(makeStandResult(-1,'删除失败'));
        }else{
           addLog('dic_type', '对象修改日志', '删除字典类型=>'.$names. '，成功','成功');
            exit(makeStandResult(1,'删除成功'));
        }
    }

}