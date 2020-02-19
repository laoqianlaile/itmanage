<?php
namespace Home\Controller;

use Think\Controller;

class OplogController extends BaseController
{
    public function index(){
        $id = trim(I('get.id'));
        $this->assign('id',$id);
        $this->display();
    }

    //获取任务数据
    public function getData(){
        $queryParam = I('put.');
        $zyId = trim($queryParam['zyId']);
        if(!empty($zyId)) $where['opl_mainid'] = array('eq' ,"$zyId");

        $model = M('oplog');
        $data = $model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        foreach($data as $key =>$val){
            $data[$key]['opl_time'] = date('Y-m-d H:i:s',$val['opl_time']);
        }
        $count = $model->where($where)->count();
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }
}