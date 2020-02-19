<?php
namespace Demo\Controller;
use Think\Controller;
use Think\Crypt;

class IndexController extends Controller{
    public function index(){
        $this->display();
    }

    public function check(){
        header("Content-Type:text/html; charset=utf-8");
        if (IS_POST) {
            $projCode = 'Default';
            $projIndex = "";
            session(null);
            $userinfo = I('post.');
            $crypt    = new Crypt();
            if($userinfo['type'] == "1"){
                $client = new \SoapClient('http://10.78.72.87/CSC/CSCService.asmx?wsdl',array('trace' => true, 'exceptions' => true));
                $client->soap_defencoding = 'UTF-8';
                $client->decode_utf8 = false;
                $client->xml_encoding = 'UTF-8';
                $loginresult = $client->LoginByDomainJson(array('applicationid' => '73564f0fec014669b7078388dbeb2d7c', 'domainusername' => $userinfo['username'], 'userip' => 'test'));
                if($loginresult->LoginByDomainJsonResult=='PasswordOutOfDate'||$loginresult->LoginByDomainJsonResult=='Success') {
                    session('access', 1);
                    session('user_name', $userinfo['username']);
                    session('user_password', $userinfo['password']);
                    $userjson = json_decode($loginresult->userjson, true);
                    session('user_id', $userjson['id']);
                    session('domainusername', $userjson['domainusername']);
                    session("realusername", $userjson['realusername']);
                    session('username', explode('\\', $userjson['domainusername'])[1]);
                    session('userorg',  $userjson['org']);

                    $authsjson = json_decode($loginresult->authsjson, true);
                    $modules   = $crypt->encrypt(json_encode($authsjson[0]['modules']),C('CRYPT_KEY'));
                    session('modules', $modules);
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
                    $this->ajaxReturn('0');
                }else if($loginresult->LoginByDomainJsonResult=='UserIsLocked'){
                    $this->ajaxReturn('-1');
                }else {
                    $this->ajaxReturn('-2');
                }
            }else{

                $client = new \SoapClient('http://10.78.72.87/CSC/CSCService.asmx?wsdl',array('trace' => true, 'exceptions' => true));
                $client->soap_defencoding = 'UTF-8';
                $client->decode_utf8 = false;
                $client->xml_encoding = 'UTF-8';
                $loginresult = $client->LoginJson(array('applicationid' => '73564f0fec014669b7078388dbeb2d7c', 'domainusername' => $userinfo['username'],'password' => md5($userinfo['password']), 'userip' => 'test'));
                if($loginresult->LoginJsonResult=='PasswordOutOfDate'||$loginresult->LoginJsonResult=='Success') {
                    if(I('param.remember','') == 1){
                        cookie('remember', 1, 3600 * 24 * 30);
                        $username = trim(I('param.username'));
                        $password = trim(I('param.password'));
                        $password = $crypt->encrypt($password,C('CRYPT_KEY'));
                        cookie('remember_username',$username , 3600 * 24 * 30);
                        cookie('remember_password',$password , 3600 * 24 * 30);
                    } else {
                        cookie('remember', null);
                        cookie('remember_username', null);
                        cookie('remember_password', null);
                    }
                    session('access', 1);
                    session('user_name', $userinfo['username']);
                    session('user_password', $userinfo['password']);
                    $userjson = json_decode($loginresult->userjson, true);
                    session('user_id', $userjson['id']);
                    session('domainusername', $userjson['domainusername']);
                    session("realusername", $userjson['realusername']);
                    session('username', explode('\\', $userjson['domainusername'])[1]);
                    session('userorg',  $userjson['org']);

                    $authsjson = json_decode($loginresult->authsjson, true);
                    $modules   = $crypt->encrypt(json_encode($authsjson[0]['modules']),C('CRYPT_KEY'));
                    session('modules', $modules);
                    foreach($authsjson as $key=>$authsjsonval){
                        foreach($authsjsonval as $val){
                            if($val['code']==$projCode){
                                $projIndex = $key;
                            }
                        }
                    }
                    session('roles',json_encode($authsjson[$projIndex]['roles']));
//                    $this->leftMenuForSession();
                    $this->ajaxReturn('0');
                }else if($loginresult->LoginJsonResult=='UserIsLocked'){
                    $this->ajaxReturn('-1');
                }else {
                    $this->ajaxReturn('-2');
                }
            }
        } else {
            $this->display("loginPage");
        }
    }

    public function loginPage(){
//        session(null);
        $username = cookie('remember_username');
        $password = cookie('remember_password');
        $crypt    = new Crypt();
        $password = $crypt->decrypt($password,C('CRYPT_KEY'));
        $remember = cookie('remember');
        if($username && $password){
            $this->assign('username', $username);
            $this->assign('password', $password);
            $this->assign('remember', $remember);
        }
        $this->display();
    }
}