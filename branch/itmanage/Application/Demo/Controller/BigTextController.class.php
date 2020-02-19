<?php
namespace Demo\Controller;
use Think\Controller;
class BigTextController extends BaseController{

    public function index(){
        $this->display();
    }

    /**
     *  获取数据
     */
    public function getData(){
        //获取搜索参数
        $queryParam = I('put.');

        //初始化数据表对象
        $model = M('bigtext');

        //初始化搜索条件
        $where = [];

        //拼接搜索条件
        $title = trim($queryParam['search_title']);
        if(!empty($title)) $where['bt_title'] = ['like', "%$title%"];

        //获取数据行数
        $count = $model->where($where)->count();

        //获取数据
        $data = $model->where($where)
            ->field("bt_id, bt_title, bt_createtime, user_realusername || '(' || user_name || ')' as createuser ")
            ->join("left join sysuser on bt_createuser = sysuser.user_id")
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();

        foreach($data as &$value){
            $value['bt_createtime'] = date('Y-m-d H:i:s', $value['bt_createtime']);
        }

        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 提交处理
     */
    public function saveData(){
        //接受post参数
        $data = I('post.');

        // 验证非空
        if(empty($data['bt_title'])) exit(makeStandResult(-1, '请填写标题'));
        if(empty($data['bt_content'])) exit(makeStandResult(-1, '请填写文本内容'));

        //获取主键
        $id = trim(I('post.bt_id'));

        //初始化表连接
        $model = D('BigText');

        $res = $model->saveData($data, $id);
        if(!empty($res)){
            exit(makeStandResult(1, '保存成功'));
        }else{
            exit(makeStandResult(-1, '保存失败'));
        }
    }

    /**
     * 添加编辑页面
     */
    public function edit(){
        //接收主键id
        $btId = trim(I('get.bt_id'));

        if(!empty($btId)){
            //初始化表连接
            $model = D('BigText');

            //查找主键对应信息
            $data = $model->getDataById($btId);

            $this->assign('data', $data);
            $this->assign('bt_id', $btId);
        }

        $this->display();
    }

    /**
     * 展示页面
     */
    public function view(){
        //接收主键
        $btId = trim(I('get.bt_id'));
        if(empty($btId)) exit('缺失参数');

        //初始化表连接
        $model = D('BigText');

        //查找主键对应信息
        $data = $model->getDataById($btId);

        $data['bt_content'] = htmlspecialchars_decode($data['bt_content']);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 问题反馈删除处理
     */
    public function deleteData(){
        //接收主键
        $btId = trim(I('post.bt_id'));
        if(empty($btId)) exit(makeStandResult(-1, '缺失主键'));

        $res = D('BigText')->deleteData($btId);
        if(!empty($res)){
            exit(makeStandResult(1, '删除成功'));
        }else{
            exit(makeStandResult(-1, '删除失败'));
        }
    }
}