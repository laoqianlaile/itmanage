<?php
namespace Admin\Controller;
use Think\Controller;
class PublicController extends BaseController {

    /**
     * 公共报错页面
     */
    public function error(){
        $this->display();
    }
}