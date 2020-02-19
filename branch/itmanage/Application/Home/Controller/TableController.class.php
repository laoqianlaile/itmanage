<?php
namespace Home\Controller;
use Think\Controller;
class TableController extends BaseController {
    /**
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $this->display();
    }

}
