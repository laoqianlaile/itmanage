<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {

    /**
     * 后台主页
     */
    public function index(){
        $noReopen = intval(I('get.noReopen'));
        $fromsign = I('get.fromsign','0');
        $this->assign('fromsign',$fromsign);
//         if ($noReopen != 1) {
//             $url = U('Admin/Index/index', '', '', true) . '?noReopen=1';
//             if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
//                 exit("<script>
//                     var fulls = 'left=0,screenX=0,top=0,screenY=0,scrollbars=1';
//                     if(window.screen){
//                         var ah = screen.availHeight;
//                         var aw = screen.availWidth;
//                         fulls += ',height='+ah;
//                         fulls += ',width='+aw;
//                         fulls += ',innerWidth='+aw;
//                         fulls += ',resizable';
//                     }else{
//                         fulls += ',resizable';
//                     }
//                     window.opener = null;
//                     newWin = window.open('{$url}', 'riskwin', fulls);
//                     win = window.open('riskwin','_self');
//                     win.focus();
//                     window.close();
//                 </script>");
//             }
//         }
        $clear = intval(I('get.clearLoginInfo'));  //是否清除登录信息
        if ($clear == 1) {
            session(null);
            cookie(null);
        }

        $config = D('Config')->getConfig();
        $this->assign('config', $config);

        $userId = session('user_id');
        if (!empty($userId)) {
            $model = session('model');
            $mode = unserialize($model);
            $this->assign('data', $mode);
        }

        $this->display('Index/indexMenu');
    }
}