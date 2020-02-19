<?php
namespace Admin\Controller;
use Think\Controller;
class DictionaryController extends BaseController {

    /**
     * 字典管理
     */
    public function index(){
        addLog('','用户访问日志','访问字典管理','成功');
        $dicType = D('Dictionary')->getDicType();
        $this->assign('dictionaryType', $dicType);
        $this->display();
    }

    /*
     * 楼宇管理字典
     * */
    public function louYu(){
        addLog('','用户访问日志','访问厂家管理字典','成功');

        $dic = D('dictionary')->getDicValueByName(['地区']);
        $this->assign('idQu', $dic['地区']);
        $this->display();
    }

    public function louYuAdd(){
        $id = trim(I('get.id'));
        $choseDicType = trim(I('get.dic_type'));
        if(!empty($id)){
            $model = M('dic_louyu');
            $data = $model
                ->where("dic_id='%s'", $id)
                ->find();
            $this->assign('data', $data);
        }
//        addLog('','用户访问日志','字典添加\修改页面','成功');
        $dic = D('dictionary')->getDicValueByName(['地区']);
        $this->assign('diQu', $dic['地区']);
        $this->assign('choseDicType', $choseDicType);
        $this->display();
    }

    /**
     * 厂家管理字典添加修改
     */
    public function addDictionaryLouYu(){
        $id = trim(I('post.id'));
        $data['dic_name'] = trim(I('post.dic_name'));
        $data['dic_pid'] = trim(I('post.pid'));
        $data['dic_value'] = trim(I('post.dic_value'));
        $data['dic_order'] = trim(I('post.dic_order'));
        if(empty($data['dic_name'])) exit(makeStandResult(-1,'请输入字典名称'));
        if(empty($data['dic_pid'])) exit(makeStandResult(-1,'请输入地区'));
        if(!is_numeric($data['dic_order'])) exit(makeStandResult(-1,'排序号须为数字'));
        $model = M('dic_louyu');
        // $dic = D('dictionary')->getDicValueByName(['楼宇']);
        $data['dic_type'] = 'louyu';

        //为空则添加
        if(empty($id)){
            $data['dic_id'] = makeGuid();
            $data['dic_createtime'] = time();
            $data['dic_createuser'] = session('user_id');
            $res = $model->add($data);

            if(empty($res)){
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['dic_lastmodifytime'] = time();
            $data['dic_lastmodifyuser'] = session('user_id');
            $res = $model->where("dic_id='%s'", $id)->save($data);
            if(empty($res)){
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    public function changJia(){
        $dic = D('dictionary')->getDicValueByName('资产类型');
        $this->assign('dicZiChan', $dic);
        $this->display();
    }

    public function getDataChangJia(){
        $queryParam = I('put.');
        $where['dic_status'] = ['eq', 0];
        $dicName = trim($queryParam['dic_name']);
        if(!empty($dicName)) $where['dic_name'] = array('like' ,"%$dicName%");
        $dicType = trim($queryParam['search_type']);
        if(!empty($dicType)) $where['dic_type'] = array('eq' ,"$dicType");

        $model = M('dic_factory f');
        $data = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        foreach($data as $key =>$val){
            $data[$key]['dic_type'] = M('dic')->where("dic_id = '%s'",$val['dic_type'])->getField('dic_name');
        }
        $count = $model->where($where)->count();
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    public function changJiaAdd(){
        $id = trim(I('get.id'));
        $choseDicType = trim(I('get.dic_type'));
        if(!empty($id)){
            $model = M('dic_factory');
            $data = $model
                ->where("dic_id='%s'", $id)
                ->find();
            $this->assign('data', $data);
        }
//        addLog('','用户访问日志','字典添加\修改页面','成功');
        $dic = D('dictionary')->getDicValueByName('资产类型');
        $this->assign('dicZiChan', $dic);
        $this->assign('choseDicType', $choseDicType);
        $this->display();
    }

    public function addDictionaryChangJia(){
        $id = trim(I('post.id'));
        $data['dic_name'] = trim(I('post.dic_name'));
        $data['dic_type'] = trim(I('post.dic_type'));
        $data['dic_value'] = trim(I('post.dic_value'));
        $data['dic_order'] = trim(I('post.dic_order'));
        if(empty($data['dic_name'])) exit(makeStandResult(-1,'请输入字典名称'));
        if(empty($data['dic_type'])) exit(makeStandResult(-1,'请输入资产类型'));
        if(!is_numeric($data['dic_order'])) exit(makeStandResult(-1,'排序号须为数字'));
        $model = M('dic_factory');
        //为空则添加
        if(empty($id)){
            $data['dic_id'] = makeGuid();
            $data['dic_createtime'] = date('Y-m-d H:i:s',time());
            $data['dic_createuser'] = session('user_id');
            $res = $model->add($data);

            if(empty($res)){
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['dic_lastmodifytime'] =  date('Y-m-d H:i:s',time());
            $data['dic_lastmodifyuser'] = session('user_id');
            $res = $model->where("dic_id='%s'", $id)->save($data);
            if(empty($res)){
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 删除字典
     */
    public function delDictionaryChangjia(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('dic_factory');
        $names = $model ->field('dic_name')-> where(['dic_id'=> ['in', $id]])->select();
        $names = removeArrKey($names, 'dic_name');
        $res = $model -> where(['dic_id'=> ['in', $id]])->delete();
        if(empty($res)){
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')成功', '成功');
            exit(makeStandResult(-1,'删除失败'));
        }else{
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')失败','失败');
            exit(makeStandResult(1,'删除成功'));
        }
    }

    /**
     * 获取厂家字典列表
     */
    public function getDataLouYu(){
        $queryParam = I('put.');

        $where['dic_status'] = ['eq', 0];
        $dicName = trim($queryParam['dic_name']);
        if(!empty($dicName)) $where['dic_name'] = array('like' ,"%$dicName%");
        $dicType = trim($queryParam['search_type']);
        if(!empty($dicType)) $where['dic_pid'] = array('eq' ,"$dicType");

        $model = M('dic_louyu');
        $data = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->where($where)->count();

        foreach($data as $k => $value){
            $data[$k]['dic_createtime'] = date('Y-m-d H:i:s',$value['dic_createtime']);
            $data[$k]['diqu'] = !empty($value['dic_pid'])?$this ->getDicById($value['dic_pid'],'dic_name'):'-';//楼宇
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }
    /**
     * 删除字典
     */
    public function delDictionaryLouYu(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('dic_xinghao');
        $names = $model ->field('dic_name')-> where(['dic_id'=> ['in', $id]])->select();
        $names = removeArrKey($names, 'dic_name');
        $res = $model -> where(['dic_id'=> ['in', $id]])->delete();
        if(empty($res)){
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')成功', '成功');
            exit(makeStandResult(-1,'删除失败'));
        }else{
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')失败','失败');
            exit(makeStandResult(1,'删除成功'));
        }
    }


    /*
    * 型号管理字典
    * */
    public function xingHao(){
        addLog('','用户访问日志','访问厂家管理字典','成功');
//        $dicType = D('Dictionary')->getDicType();
        $dic = D('dictionary')->getDicValueByName('资产类型');
        $this->assign('dicZiChan', $dic);
//        $this->assign('dicChangJia', $dic['厂家']);
        $this->display();
    }

    public function xingHaoAdd(){
        $id = trim(I('get.id'));
        $choseDicType = trim(I('get.dic_type'));
        if(!empty($id)){
            $model = M('dic_xinghao');
            $data = $model
                ->where("dic_id='%s'", $id)
                ->find();
            $fact =M('dic_factory')->field('dic_name,dic_id,dic_type')->where("dic_id = '%s'",$data['dic_type'])->find();
            $factory = $fact['dic_name'];
            $ziChan = M('dic')->where("dic_id = '%s'",$fact['dic_type'])->getField('dic_id');
            $this->assign('factory', $factory);
            $this->assign('ziChan', $ziChan);
            $this->assign('data', $data);
        }else{
            $dicId = trim(I('get.dic_id'));
            $dicType = trim(I('get.zType'));
            $this->assign('factory',$dicId);
            $this->assign('ziChan',$dicType);
        }
        $dic = D('dictionary')->getDicValueByName(['资产类型']);
        $this->assign('dicZiChan', $dic['资产类型']);
        $this->assign('choseDicType', $choseDicType);
        $this->display();
    }

    /**
     * 型号管理字典添加修改
     */
    public function addDictionaryXingHao(){
        $id = trim(I('post.id'));
        $data['dic_name'] = trim(I('post.dic_name'));
        $data['dic_pid'] = trim(I('post.pid'));
        $data['dic_pid2'] = trim(I('post.pid2'));
        $data['dic_order'] = trim(I('post.dic_order'));
        $data['dic_value'] = $data['dic_name'];
        if(empty($data['dic_name']))  exit(makeStandResult(-1,'请输入字典名称'));
        if(empty($data['dic_pid2']))  exit(makeStandResult(-1,'请输入资产类型'));
        if(empty($data['dic_pid']))  exit(makeStandResult(-1,'请输入厂家'));
        if(!is_numeric($data['dic_order']))  exit(makeStandResult(-1,'排序号须为数字'));
        $model = M('dic_xinghao');

        $data['dic_type'] =$data['dic_pid'];//型号id

        //为空则添加
        if(empty($id)){
            $data['dic_id'] = makeGuid();
            $data['dic_createtime'] = time();
            $data['dic_createuser'] = session('user_id');
            $res = $model->add($data);

            if(empty($res)){
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['dic_lastmodifytime'] = time();
            $data['dic_lastmodifyuser'] = session('user_id');
            $res = $model->where("dic_id='%s'", $id)->save($data);
            if(empty($res)){
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 获取型号字典列表
     */
    public function getDataXingHao(){
        $queryParam = I('put.');

        $where['dic_status'] = ['eq', 0];
        $dicName = trim($queryParam['dic_name']);
        if(!empty($dicName)) $where['dic_name'] = array('like' ,"%$dicName%");
        $factory = trim($queryParam['factory']);
        if(!empty($factory)) $where['dic_type'] = array('eq' ,$factory);
//        var_dump($where);die;
        $model = M('dic_xinghao x');
        $data = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->where($where)->count();
        foreach($data as $k => $value){
            $data[$k]['dic_createtime'] = date('Y-m-d H:i:s',$value['dic_createtime']);
            $data[$k]['type'] = !empty($value['dic_pid2'])?$this ->getDicById($value['dic_pid2'],'dic_name'):'-';//资产
            $data[$k]['factory'] = !empty($value['dic_pid'])?$this ->getDicById($value['dic_pid'],'dic_name'):'-';//厂家
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    public function getDicFactory(){
        $pid = I('post.pid')?I('post.pid'):I('get.pid');
        $model = M('dic_factory');
        $data = $model->where("dic_type = '%s'",$pid)->select();
        echo json_encode(array('results' => $data,'code'=>1));
    }





    /**
     * 删除字典
     */
    public function delDictionaryXingHao(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('dic_xinghao');
        $names = $model ->field('dic_name')-> where(['dic_id'=> ['in', $id]])->select();
        $names = removeArrKey($names, 'dic_name');
        $res = $model -> where(['dic_id'=> ['in', $id]])->delete();
        if(empty($res)){
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')成功', '成功');
            exit(makeStandResult(-1,'删除失败'));
        }else{
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')失败','失败');
            exit(makeStandResult(1,'删除成功'));
        }
    }

    /**
     * 获取字典列表
     */
    public function getData(){
        $queryParam = I('put.');

        $where['dic_status'] = ['eq', 0];
        // $where['dic_type_is_hide'] = ['eq', 0];
        $dicName = trim($queryParam['dic_name']);
        if(!empty($dicName)) $where['dic_name'] = array('like' ,"%$dicName%");
        $dicTye = trim($queryParam['search_type']);
        if(!empty($dicTye)) $where['dic_type'] = array('eq' ,"$dicTye");

        $model = M('dic');
        $data = $model->field('dic_id,dic_value,dic_name,dic_createtime,dic_createuser,dic_order,type_name as dic_type')
                    ->join('dic_type on dic.dic_type=dic_type.dic_type_id')
                    ->where($where)
                    ->order("$queryParam[sort] $queryParam[sortOrder] ")
                    ->limit($queryParam['offset'], $queryParam['limit'])
                    ->select();
        $count = $model->where($where)->count();

        foreach($data as &$value){
            $value['dic_createtime'] = date('Y-m-d H:i:s',$value['dic_createtime']);
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 字典添加或修改
     */
    public function add(){
        $id = trim(I('get.id'));
        $choseDicType = trim(I('get.dic_type'));
        if(!empty($id)){
            $model = M('dic');
            $data = $model->field('dic_id,dic_value,dic_name,dic_pid,dic_type,dic_order')->where("dic_id='%s'", $id)->find();
            $this->assign('data', $data);
        }
        addLog('','用户访问日志','字典添加\修改页面','成功');
        $dicType = D('Dictionary')->getDicType();
        $this->assign('dictionaryType', $dicType);
        $this->assign('choseDicType', $choseDicType);
        $this->display();
    }

    /**
     * 字典添加修改
     */
    public function addDictionary(){
        $id = trim(I('post.id'));
        $data['dic_name'] = trim(I('post.dic_name'));
        $data['dic_type'] = trim(I('post.dic_type'));
        $data['dic_value'] = $data['dic_name'];
        // $data['dic_value'] = trim(I('post.dic_value'));
        $data['dic_order'] = trim(I('post.dic_order'));
        if(empty($data['dic_name']))  exit(makeStandResult(-1,'请输入字典名称'));
        if(empty($data['dic_type']))  exit(makeStandResult(-1,'请输入字典类型'));
        if(!is_numeric($data['dic_order']))  exit(makeStandResult(-1,'排序号须为数字'));
        $model = M('dic');
        //为空则添加
        if(empty($id)){
            $data['dic_createtime'] = time();
            $data['dic_id'] = makeGuid();
            $data['dic_createuser'] = session('user_id');
            $res = $model->add($data);

            if(empty($res)){
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('dic', '对象添加日志', '添加字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['dic_lastmodifytime'] = time();
            $data['dic_lastmodifyuser'] = session('user_id');
            $res = $model->where("dic_id='%s'", $id)->save($data);
            if(empty($res)){
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('dic', '对象修改日志', '修改字典=>'.$data['dic_name']. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 删除字典
     */
    public function delDictionary(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $model = M('dic');
        $names = $model ->field('dic_name')-> where(['dic_id'=> ['in', $id]])->select();
        $names = removeArrKey($names, 'dic_name');
        $res = $model -> where(['dic_id'=> ['in', $id]])->delete();
        if(empty($res)){
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')成功', '成功');
            exit(makeStandResult(-1,'删除失败'));
        }else{
            addLog('dic', '对象修改日志', '删除字典=>('.$names. ')失败','失败');
            exit(makeStandResult(1,'删除成功'));
        }
    }
}