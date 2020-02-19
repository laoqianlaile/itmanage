<?php
namespace Universal\Controller;
use Think\Controller;
class TableCopyController extends Controller {
    /**
     * 统一html表格数据复制粘贴入口
     */
    public function tableCopy(){
        $head = explode(',', trim(I('get.head')));
        $method = trim(I('get.method'));
        $remark = trim(I('get.remark'));
        $extraParam = trim(I('get.extraparam'));

        $this->assign('head', $head);
        $this->assign('method', $method);
        $this->assign('remark', $remark);
        $this->assign('extraParam', $extraParam);
        $this->display();
    }

}