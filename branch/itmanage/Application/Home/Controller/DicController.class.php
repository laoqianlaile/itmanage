<?php
namespace Home\Controller;
use Think\Controller;
class DicController extends Controller {

    //查字典pid
    public function getDicByPid(){
        $data = D('dic')->getDicByPid();
        echo $data?$data:false;
    }
    //查字典name
    public function getDicByName(){
        $data = D('dic')->getDicByName();
        echo $data?$data:false;
    }
    //查楼宇
    public function getDicLouYu()
    {
        $pid = I('post.pid')?I('post.pid'):I('get.pid');
        $data = D('dic')->getDicLouYu($pid);
        echo json_encode(array('results' => $data,'code'=>1));
    }
    //查型号
    public function getDicXingHao()
    {
        $pid = I('post.pid')?I('post.pid'):I('get.pid');
        $data = D('dic')->getDicXingHao($pid);
        echo json_encode(array('results' => $data,'code'=>1));
    }
    //查设备id
    public function getTypeId(){
        $id = I('post.id')?I('post.id'):I('get.id');
        $data = D('dic')->getTypeId($id);
        echo json_encode(array('results' => $data,'code'=>1));

    }
    //查厂家名字
    public function getDicFactoryName(){
        $id = I('post.id')?I('post.id'):I('get.id');
        $data = D('dic')->getDicFactoryName($id);
        echo json_encode(array('results' => $data,'code'=>1));
    }
    //查厂家名字
    public function getDicXingHaoName(){
        $id = I('post.id')?I('post.id'):I('get.id');
        $data = D('dic')->getDicFactoryName($id);
        echo json_encode(array('results' => $data,'code'=>1));
    }
    //查地区名字
    public function getDicAreaName(){
        $id = I('post.id')?I('post.id'):I('get.id');
        $data = D('dic')->getDicAreaName($id);
        echo json_encode(array('results' => $data,'code'=>1));
    }
    //查楼宇名字
    public function getDicLouYuName(){
        $id = I('post.id')?I('post.id'):I('get.id');
        $data = D('dic')->getDicAreaName($id);
        echo json_encode(array('results' => $data,'code'=>1));
    }
    //查厂家
    public function getDicFactory()
    {
        $pid = I('post.pid')?I('post.pid'):I('get.pid');
        $data = D('dic')->getDicFactory($pid);
        echo json_encode(array('results' => $data,'code'=>1));
    }

    //查资产名称
    public function getTypeName(){
        $pid = I('post.id')?I('post.id'):I('get.id');
        $data = D('dic')->getTypeName($pid);
        echo json_encode(array('results' => $data,'code'=>1));
    }

    //查资产id

    public function getTypeIId(){
        $pid = I('post.id')?I('post.id'):I('get.id');
        $data = D('dic')->getTypeIId($pid);
        echo json_encode(array('results' => $data,'code'=>1));
    }

    //资产名称模糊匹配
    public function getTypeNames(){
        $data = D('dic')->getTypeNames();
        echo $data?$data:false;
    }

    //资产ip模糊匹配
    public function getTypeIp(){
        $data = D('dic')->getTypeIp();
        echo $data?$data:false;
    }
}