<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller
{

    //展示登陆页面
    public function login()
    {
        $fromsign = I('get.fromsign', '0');
        session(null);
        cookie(null);
        $loginWay = C('ADMIN_LOGIN_WAY');

        $this->assign('fromsign', $fromsign);
        switch ($loginWay) {
            case 1:
                $this->display();//数据库登录
                break;
            case 2:
                $this->display('Login/safeCenter');//安全中心登录
                break;
            default :
                exit('登录方式配置错误！');
                break;
        }
    }

    // CA登陆
    public function dologinForCA(){
        $username = trim(I('post.username'));
        if (empty($username)) exit(makeStandResult(-2, '请输入账号'));
        
        $res = D('Login')->caLogin($username);
        exit(json_encode($res));
    }

    //普通账号密码登陆
    public function dologin(){
        $username = trim(I('post.username'));
        $password = trim(I('post.password'));
        if (empty($username)) exit(makeStandResult(-2, '请输入账号'));
        if (empty($password)) exit(makeStandResult(-2, '请输入密码'));
        $res = D('Login')->login($username, $password);
        exit(json_encode($res));
    }

    /**
     * 安全中心登录
     */
    public function safeCenterLogin(){
        session(null);
        $username = trim(I('post.username'));
        $password = trim(I('post.password'));
        $type = intval(I('post.type'));
        if (empty($username)) exit(makeStandResult(-2, '请输入账号'));

        //ca登录
        if($type == 1){
            $res = D('Login')->safeCenterLoginNoPwd($username);
        }else if($type == 2){
            //安全中心登录
            if (empty($password)) exit(makeStandResult(-2, '请输入密码'));
            $res = D('Login')->safeCenterLogin($username, $password);
        }else{
            exit(makeStandResult(-1, '参数有误'));
        }

        exit(json_encode($res));
    }

    /**
     * 系统集成处理
     */
    public function integration(){
        $user = session('user_account');
        $jumpView = trim(I('post.currentView'));
        $res = D('Login')->check($user, true);
        if($res== 0){
            exit(makeStandResult($res, $jumpView));
        }else{
            exit(json_encode($res));
        }
    }

    //检测登陆
    public function checkLogin(){
        $userId = session('user_id');
        if (!empty($userId)) {
            exit(makeStandResult(1, '已登录'));
        } else {
            exit(makeStandResult(-1, '未登录'));
        }
    }

    //检测登陆是否过期
    public function checkLoginExpire(){
        $res = D('Login')->checkLoginIfExpire(true, false);
        if($res){
            exit(makeStandResult(-1, '登录已超时'));
        }else{
            exit(makeStandResult(1, '登录状态正常'));
        }
    }

    //检测密码复杂度开关
    public  function  updatePassword(){
        $pwdCheck = D('Config')->getConfig('SEC_PWDCHECK');
        $this->assign('isunclassified', $pwdCheck);
        $this->display();
    }

    //修改密码
    public function updatePsd()
    {
        $old =  md5(C('PWD_SALT').trim(I('post.old')));
        $new = trim(I('post.new1'));
        $user_name = session('user_account');
        $model = M('sysuser');
        $data = $model->where("user_name='$user_name' and user_password='$old'")->find();
        if (empty($data)) {
            exit(makeStandResult(-1, '原密码错误'));
        }
        //查询用户角色
        $roleids=M('userauth')->field('ua_roleid')->where("ua_userid='%s'",$data['user_id'])->select();
        $rolearray=removeArrKey($roleids,'ua_roleid');
        if($data['user_issystem']=='否'){
            $rolearray[] = C('COMMONUSERID');
            $rolearray = array_unique($rolearray);
        }

        $data['user_password'] = md5(C('PWD_SALT').$new);
        $data['user_lastmodifytime'] = time();
        $data['user_lastmodifyuser'] = $data['user_id'];
        if (empty($data['user_firstuse'])||$data['user_firstuse']==='是') {
            $data['user_firstuse'] = '否';
            addLog('', '用户登录日志', '账号('.$user_name. ')首次登录', '成功');
        }else
        {
            addLog('', '用户登录日志', '账号('.$user_name. ')登录', '成功');
        }
        $data['user_passworderrornum'] = 0;
        session('user_id',$data['user_id']);
        $now = time();
        session('operatetime',$now);
        session('user_account', $data['user_name']);
        session('user_name', $data['user_name']);
        session('domainusername', $data['user_name']);
        session('realusername', $data['user_realusername']);
        session('user_secretlevel', $data['user_secretlevel']);

        session('roleids',implode(',',$rolearray));
        $res = $model->where("user_id='%s'", $data['user_id'])->save($data);
        if (empty($res)) {
            addLog('sysuser', '用户登录日志',  '账号('.$user_name. ')修改密码', '失败');
            exit(makeStandResult(-1, '修改失败'));
        } else {
            addLog('sysuser', '用户登录日志',  '账号('.$user_name. ')修改密码', '成功');
            session('user_id', $data['user_id']);

            exit(makeStandResult(1, '修改成功'));
        }
    }

    //验证码生成
    public function verify()
    {
        $config = array(
            'fontSize' => 30,    // 验证码字体大小
            'length' => 3,     // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
    
}