<?php
namespace Admin\Controller;
use Think\Controller;
class UserSafeController extends BaseController {

    /**
     * 安全管理
     */
    public function index(){
        addLog('','用户访问日志','访问安全管理','成功');
        $this->display();
    }

    /**
     * 获取用户列表
     */
    public function getData($isThinkPhp = false){
        if($isThinkPhp){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }

        $where = [];
        $realName = trim($queryParam['real_name']);
        $userName = trim($queryParam['user_name']);
        $userEnable = trim($queryParam['user_enable']);
        $where['user_isdelete']=['neq',1];

        if(!empty($realName)) $where['user_realusername'] = array('like' ,"%$realName%");
        if(!empty($userName))  $where['user_name'][] = array('like' ,"%$userName%");
        if(!empty($userEnable)) $where['user_enable'] = array('eq' ,$userEnable);

        $model = M('sysuser');
        $count = $model->where($where) ->join("left join view_org o on o.org_id=sysuser.user_orgid")->count();
        $obj = $model->where($where)->order("$queryParam[sort] $queryParam[sortOrder]") ->join("left join view_org o on o.org_id=sysuser.user_orgid");
        $staffSecret = D('Dictionary')->getDicValueByName('人员密级');
        $staffSecret = array_column($staffSecret, 'dic_name', 'val');

        if($isThinkPhp){
            $data = $obj->field('user_realusername,user_name,org_name,user_secretlevel,user_enable')->select();
            foreach($data as $key=>$value){
                $data[$key]['user_secretlevel'] = $staffSecret[$value['user_secretlevel']];
            }

            $header = array('姓名','账号', '所在单位/部门','密级','是否冻结');
            addLog('','对象修改日志','导出用户状态列表','成功');
            if($count <= 0){
                exit(makeStandResult(-1, '没有需要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            $data = $obj->field('user_id,user_realusername,user_enable,user_name,user_issystem,user_secretlevel,user_secretlevelcode,org_name')
                ->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach($data as &$value){
                $tmp = md5($value['user_id'].$value['user_secretlevel']);
                $value['user_secretlevel'] = $staffSecret[$value['user_secretlevel']];
                if($tmp == $value['user_secretlevelcode']){
                    $value['secsign'] = 0;
                }else{
                    $value['secsign'] = 1;
                }
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }

    }

    /**
     * 编辑模块
     */
    public function editSysUser(){
        $ids = I('post.ids');
        $status=I('post.status');
        if(empty($ids)||empty($status)) exit(makeStandResult(-1,'参数缺少'));

        $id = explode(',', $ids);
        if(in_array(session('user_id'),$id))
            exit(makeStandResult(-1,'不能操作自己的账号'));
        $idStr = "'".implode("','", $id)."'";
        $model = M('sysuser');
        if($status==='freeze')
        {
            $operation='冻结:';
            $data['user_enable']='冻结';
        }
        if($status==='unfreeze')
        {
            $operation='解冻:';
            $data['user_enable']='启用';
            $data['user_passworderrornum']=0;
        }
        if($status==='reset')
        {
            $operation='重置密码:';
            $data['user_password']= md5(C('PWD_SALT').md5('Guanli'.date('Y')));
            $data['user_passworderrornum']=0;
            $data['user_firstuse']='是';
        }
        $data['user_lastmodifytime'] = time();
        $data['user_lastmodifyuser'] = session('user_id');
        $res = $model -> where("user_id in ($idStr)")->save($data);
        $user_names=removeArrKey($model->field('user_name')->where("user_id in ($idStr)")->select(),'user_name');
        $user_names=implode(',', $user_names);
        if(empty($res)){
            addLog('sysuser','三员操作日志',$operation.'账号('.$user_names.')','失败');
            exit(makeStandResult(-1,'操作失败'));
        }else{
            addLog('sysuser','三员操作日志',$operation.'账号('.$user_names.')','成功');
            exit(makeStandResult(1,'操作成功'));
        }
    }
}