<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class LoginModel extends Model{
    protected $autoCheckFields = false;

    /**
     * CA登陆
     * @param $username
     * @param $onlyCheckPwd  -是否只校验密码正确与否，不验证是否需要修改密码
     * @return array
     */
    public function caLogin($username, $onlyCheckPwd = true){
        //获取角色信息
        $data =  M('sysuser')->field('user_id,user_realusername,user_password,user_name,user_secretlevel,user_orgid,user_enable,user_issystem,user_secretlevelcode,user_firstuse,user_passworderrornum,user_frozentime,user_lastmodifytime,user_passworderrortime')
            ->where("user_name='%s' and user_isdelete ='0' ", $username)
            ->find();

        if (empty($data)) {
            addLog('', '用户登录日志', '账号('.$username.')登录', '失败', $username);
            return makeStandResult(-3, '账号不存在', 2);
        }
        return $this->passwordRight($data, $onlyCheckPwd);
    }

    /**
     * 普通登录
     * @param $username
     * @param $password
     * @return array
     */
    public function login($username, $password){
        //获取角色信息
        $data =  M('sysuser')->field('user_id,user_realusername,user_password,user_name,user_secretlevel,user_orgid,user_enable,user_issystem,user_secretlevelcode,user_firstuse,user_passworderrornum,user_frozentime,user_lastmodifytime,user_passworderrortime')
            ->where("user_name='%s'  and user_isdelete ='0' ", $username)
            ->find();
        if (empty($data)) {
            addLog('', '用户登录日志', '账号('.$username.')登录', '失败', $username);
            return makeStandResult(-3, '账号不存在', 2);
        }

        if ($data['user_password'] == md5(C('PWD_SALT').$password)) {
            return $this->passwordRight($data);
        }else{
            return $this->passwordWrong($data);
        }
    }

    /**
     * 密码正确的情况
     * @param $data
     * @param $onlyCheckPwd  -只校验密码正确与否，不验证是否需要修改密码
     * @return array
     */
    private function passwordRight($data, $onlyCheckPwd = false){
        $model = M('sysuser');
        $username = $data['user_name'];
        $userId = $data['user_id'];
        $config = D('Admin/Config')->getConfig();

        $isunclassified =$config['SEC_CHANGEPWD'];
        if($isunclassified==1) {
            if (!empty($data['user_lastmodifytime'])) {
                if (time() - $data['user_lastmodifytime'] >= 7 * 24 * 3600) $isedit = true;
            }
        }
        //查询用户角色
        $roleids= M('userauth')->field('ua_roleid')->where("ua_userid='%s'",$userId)->select();

        $rolearray=removeArrKey($roleids,'ua_roleid');

        if($data['user_issystem']=='否'){
            $rolearray[] = C('COMMONUSERID');
            $rolearray = array_unique($rolearray);
        }
        //密码正确
        if ($data['user_enable'] === '冻结' || empty($data['user_enable'])) {
            if($roleids[0]['ua_roleid'] == C('SAFEMANAGERID')){ //安全管理员30分钟自动解冻账号
                $frozenTime = intval($data['user_frozentime']);
                $FROZEN_SAFEMANAGER_TIME = $config['SEC_LOCKTIME'];
                $unfrozenTime =  $frozenTime + $FROZEN_SAFEMANAGER_TIME;

                if( time() > $unfrozenTime){
                    $arr['user_enable'] = '启用';
                    $arr['user_frozentime'] = 0;
                    $model->where("user_id='%s'", $userId)->save($arr);
                }else{
                    addLog('', '用户登录日志','账号('.$username. ')登录,账户被冻结或不可用', '失败', $data['user_realusername'].'('.$data['user_name'].')');
                    return makeStandResult(-4, '该账户将在'.date('Y-m-d H:i:s',$unfrozenTime).'解冻', 2);
                }
            }else{
                addLog('', '用户登录日志', '账号('.$username. ')登录,账户被冻结或不可用', '失败', $data['user_realusername'].'('.$data['user_name'].')');
                return makeStandResult(-4, '账号被冻结或不可用请联系安全管理员', 2);
            }
        }
        session('user_account', $data['user_name']);
        session('user_name', $data['user_name']);
        session('domainusername', $data['user_name']);
        if($onlyCheckPwd == false){
            if(empty($data['user_firstuse']) || $data['user_firstuse'] != '否'){
                $FIRSTLOGINUPDATEPWD = $config['SEC_FIRSTLOGINUPDATEPWD'];
                if($FIRSTLOGINUPDATEPWD){
                    return makeStandResult(2, '首次登录请修改密码', 2);
                }
            }
            if(!empty($data['user_firstuse']) && $isunclassified!="是") if ( $isedit) return makeStandResult(3, '请修改密码', 2);
        }

//        $tmp = md5($userId.$data['user_secretlevel']);
//        if($tmp != $data['user_secretlevelcode']) {
//            addLog('', '用户登录日志', '账号('.$username. ')登录,密级被篡改', '失败');
//            return makeStandResult(-4, '密级被篡改,不可登录请联系安全管理员', 2);
//        }
        session('user_id', $userId);
        $now = time();
        session('operatetime',$now);
        session('realusername', $data['user_realusername']);
        session('user_secretlevel', $data['user_secretlevel']);
        session('roleids',implode(',',$rolearray));
        D('Index')->savePowers();

        $num['user_passworderrornum'] = 0;
        $model->where("user_id='%s'",$userId)->save($num);
        addLog('', '用户登录日志', '账号('.$username. ')登录', '成功');
        return makeStandResult(1000, '登录成功', 2);
    }

    /**
     * 密码错误的情况
     * @param $data
     * @return array
     */
    private function passwordWrong($data){
        $username = $data['user_name'];
        $userId = $data['user_id'];
        //密码错误
        addLog('', '用户登录日志', '账号('.$username. ')登录,密码错误', '失败', $data['user_realusername'].'('.$data['user_name'].')');

        $data['user_passworderrortime'] = time();
        if (empty($data['user_passworderrornum'])) $data['user_passworderrornum'] = 0;
        $userModel = M('sysuser');
        $data['user_passworderrornum']++;
        if ($data['user_passworderrornum'] >= 5) {
            if($data['user_passworderrornum'] == 5){
                $data['user_enable'] = '冻结';
                $data['user_frozentime'] = time();
                $userModel->where("user_id='%s'", $userId)->save($data);
            }
            addLog('', '用户登录日志', '账号('.$username. ')登录,密码错误超过五次，账号被冻结', '失败', $data['user_realusername'].'('.$data['user_name'].')');
            return makeStandResult(-5, '密码错误超过5次,账号被冻结,请联系安全管理员', 2);
        } else {
            $userModel->where("user_id='%s'", $userId)->save($data);
            return makeStandResult(-5, '密码错误,还可以输入'.(5 - $data['user_passworderrornum']).'次' , 2);
        }
    }

    /**
     * 检测登录是否过期
     * @param bool|false $outIfJump  确认过期后是否执行跳转
     * @param bool|false $isUpdateOperateTime  是否更新当前操作时间
     * @return bool
     */
    public function checkLoginIfExpire($outIfJump = false, $isUpdateOperateTime = true){
        //获取系统配置
        $isExpire = false;
        $config = D('Admin/Config')->getConfig();
        $isCheckLoginStatus = $config['SEC_LOGINTIMEOUTCHECK'];
        if($isCheckLoginStatus){
            $lastOperateTime = session('operatetime');
            $loginOutTime = $config['SEC_LOGINTIMEOUTTIME'];
            if ( (time() - $lastOperateTime) > $loginOutTime){
                $isExpire = true;
            }
        }
        if($isUpdateOperateTime)  session('operatetime', time());
        if($outIfJump && $isExpire) $this->loginOut();
        return $isExpire;
    }

    /**
     * 退出登录
     */
    public function loginOut(){
        session(null);
        cookie(null);
        if (IS_GET) {
            echo "<script>top.location.href='".U('Admin/Index/index')."';</script>";
        } else {
            if (IS_POST || IS_AJAX) {
                exit(makeStandResult(-1001, '请先登录'));
            }
        }
    }

    public function check($user){
        header("Content-Type:text/html; charset=utf-8");
            $projCode = 'Default';
            $projIndex = "";
                $client = new \SoapClient('http://10.78.72.87/CSC/CSCService.asmx?wsdl',array('trace' => true, 'exceptions' => true));
                $client->soap_defencoding = 'UTF-8';
                $client->decode_utf8 = false;
                $client->xml_encoding = 'UTF-8';
                $loginresult = $client->LoginByDomainJson(array('applicationid' => '73564f0fec014669b7078388dbeb2d7c', 'domainusername' => $user, 'userip' => 'test'));
                if($loginresult->LoginByDomainJsonResult=='PasswordOutOfDate'||$loginresult->LoginByDomainJsonResult=='Success') {
                    session('access', 1);
//                    session('user_name', $user);
                    $userjson = json_decode($loginresult->userjson, true);
//                    session('user_id', $userjson['id']);
//                    session('domainusername', $userjson['domainusername']);
//                    session("realusername", $userjson['realusername']);
                    session('username', explode('\\', $userjson['domainusername'])[1]);
                    session('userorg',  $userjson['org']);

                    $authsjson = json_decode($loginresult->authsjson, true);
                    foreach($authsjson as $key=>$authsjsonval){
                        foreach($authsjsonval as $val){
                            if($val['code']==$projCode){
                                $projIndex = $key;
                            }
                        }
                    }
//                    session('modules',json_encode($authsjson[$projIndex]['roles'][0]['modules']));
                    session('roles',json_encode($authsjson[$projIndex]['roles']));
//                    $this->leftMenuForSession();
//                    $this->ajaxReturn('0');
                    return 0;
                }else if($loginresult->LoginByDomainJsonResult=='UserIsLocked'){
//                    $this->ajaxReturn('-1');
                    return -1;
                }else {
//                    $this->ajaxReturn('-2');
                    return -2;
                }
    }
}