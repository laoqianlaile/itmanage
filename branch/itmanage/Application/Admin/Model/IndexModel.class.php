<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class IndexModel extends Model{
    Protected $autoCheckFields = false;

    /**
     * 获取当前用户可见菜单，及菜单id
     * @return array
     */
    public function getMenu(){
        //根据角色id获取用户可见菜单
        $where['ra_roleid'] = ['in', explode(',', session('roleids'))] ;
        $mode = M('roleauth')->where($where)
            ->field('mi_name,mi_url,mi_id,mi_sort,mi_pid')
            ->join("inner join modelinfo on roleauth.ra_miid=modelinfo.mi_id")
            ->group('mi_name,mi_url, mi_id ,mi_sort,mi_pid ')
            ->order('mi_sort asc')
            ->select();
        $init = [];
        $ids = [];
        foreach($mode as $key=>$value){
            $ids[] = $value['mi_id'];
            $id = $value['mi_id'];
            $thisPid = $value['mi_pid'];
            if(empty($thisPid)){
                $init[$key] = $value;
            }else{
                continue;
            }
            foreach($mode as $k => $v){
                $pid = $v['mi_pid'];
                if($id == $pid){
                    $init[$key]['children'][] =  $v;
                }
            }

        }
        return ['menu'=> array_values($init), 'ids'=> $ids];
    }

    /**
     * 记录用户权限
     * @return bool
     */
    public function savePowers(){
        //根据角色id获取用户可见菜单
        $data = D('Index')->getMenu();

        $mode = $data['menu'];
        $authModel = M('modelinfo');

        //根据用户可见菜单获取用户可操作页面
        if(!empty( $data['ids'])){
            $operateViews = $authModel->where(['mi_pid' => ['in', $data['ids']]])->field('distinct(mi_url) as mi_url')->select();
            $operateViews = array_unique(removeArrKey($operateViews, 'mi_url'));
            foreach($operateViews as $key=>$value){
                $operateViews[$key] = strtolower($value);
            }
            cookie('operate_view', $operateViews);  //可操作页面存入cookie
        }

        if (!empty($mode)) {
            session('model', serialize($mode));
        }
        return true;
    }

}