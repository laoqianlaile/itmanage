<?php
namespace Home\Controller;

use Think\Controller;

class ZtableController extends BaseController
{
    public function index(){
        addLog("", "用户访问日志",  "访问综合报表查询页面", "成功");
        $this->display();
    }
}