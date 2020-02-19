<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller{

    public function __construct(){
        parent::__construct();
        $userId = session('user_id');

        $loginModel = D('login');
        if (empty($userId)) $loginModel->loginOut();

        $loginModel->checkLoginIfExpire(false); //检测登录是否过期
    }

    /**
     * 该方法用于简化方法实现逻辑：如需要导入，方法后面加上withexport，无权限页面加上withnopower（不区分大小写）
     * 后续扩展在switch 中增加case分支即可
     * 具体逻辑仍需自己在同一个方法中实现
     * @param $method
     */
    public function _empty($method){
        $method = strtolower($method);
        $withIndex = strpos($method, 'with');
        if($withIndex === false){
            if (file_exists_case($this->view->parseTemplate())) {
                exit($this->display());
            }else{
                E($method . '页面未找到', 404);
            }
        }

        $split = explode('with', $method);
        switch($split[1]){
            case 'export': //导出
            case 'nopower': //无权限页面
                $this->$split[0](true);
                break;
            default:
                E($method . '页面未找到', 404);
        }
    }

    //根据字典id获取字典
    public function getDicById($id,$field = ''){
        if(empty($id)) return false;
        $model = M('dic');
        $data = $model
            ->where("dic_id = '%s' ", $id )
            ->find();
        if(!empty($field)){
            return $data[$field];
        }else{
            return $data;
        }
    }

    //查字典pid
    public function getDicByPid($pid){
        $data = M('dic')
            ->field('dic_id,dic_name,dic_value,dic_type')
            ->where("dic_status=0 and dic_pid='%s'", $pid)
            ->order('dic_order')
            ->select();
        return $data;
    }
    //查字典name
    public function getDicByName($name,$pid =''){
        if(!empty($pid)){
            $where['dic_pid'] = ['eq',$pid];
        }
        $where['dic_name'] = ['like',$name];
        $where['dic_status'] = ['eq','0'];
        $data = M('dic')
            ->field('dic_id,dic_name,dic_value,dic_pid,dic_type')
            ->where($where)
            ->order('dic_order')
            ->select();
        return $data;
    }
    //查字典type
    public function getDicByType($type){
        $data = M('dic')
            ->field('dic_id,dic_name,dic_value,dic_pid,dic_type')
            ->where("dic_status=0 and dic_type='%s'", $type)
            ->order('dic_order')
            ->select();
        return $data;
    }
}