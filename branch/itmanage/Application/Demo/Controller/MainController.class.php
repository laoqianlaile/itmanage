<?php
/**
 * Created by PhpStorm.
 * User: wangshuo3
 * Date: 2017/3/28
 * Time: 8:44
 */
namespace Demo\Controller;
use Think\Controller;
class MainController extends BaseController {

    public function index(){
        $menu     = getMenu();
        $this->assign('menus', $menu);
        $this->display("mainPage");
    }

    public function mainPage(){

    }

    public function changepwd(){
        $domainusername = I('session.domainusername', '');
        $username = I('session.username', '');
        $this->assign('domainusername', $domainusername);
        $this->assign('username', $username);
        $this->display();
    }

    public function changepassword(){
        header("Content-Type:text/html; charset=utf-8");
        if (IS_POST) {
            $userinfo = I('post.');
            $client = new \SoapClient('http://10.78.72.87/CSC/CSCService.asmx?wsdl',array('trace' => true, 'exceptions' => true));
            //增加属性设置
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = false;
            $client->xml_encoding = 'UTF-8';
            $changepwdresult = $client->ChangeUserPassword(array('applicationid' => '73564f0fec014669b7078388dbeb2d7c', 'domainusername' => $userinfo['domainusername'], 'oldPassword' => md5($userinfo['oldpwd']), 'newPassword' => md5($userinfo['newpwd']),'userip' => 'test'));
            if($changepwdresult->ChangeUserPasswordResult=='Success') {
                $this->ajaxReturn('0');
            }  else if($changepwdresult->ChangeUserPasswordResult=='PasswordWrong'){
                $this->ajaxReturn('-1');
            } else {
                $this->ajaxReturn('-2');
            }
        } else {
            $this->ajaxReturn('-3');
        }
    }
    public function exitsystem(){
        session(null);
        $this->ajaxReturn('0');
    }

}