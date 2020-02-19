<?php
namespace Admin\Controller;
use Think\Controller;
class TrunkController extends BaseController {
    public function login(){        
        $userId = session('user_id');

        $loginModel = D('login');
        if (empty($userId)) $loginModel->loginOut();

        $loginModel->checkLoginIfExpire(false); //����¼�Ƿ����
    }

    //AD�˻�����
    public function createAdAccount(){        
        $config = C('publicUrl');
        $url = $config.'/Home/Ad/createAdAccount';
        $this->Url($url);
    }
    //AD���������
    public function AdComputerManage(){
        $config = C('publicUrl');
        $url=$config.'/Home/Ad/AdComputerManage';
        $this->Url($url);
    }
    //AD�˻���������
    public function AdAccountBatchAdd(){
        $config = C('publicUrl');
        $url = $config.'/Home/Ad/AdAccountBatchAdd';
        $this->Url($url);
    }
    //AD�˻������޸�
    public function AdAccountBatchupdate(){
        $config = C('publicUrl');
        $url = $config.'/Home/Ad/AdAccountBatchupdate';
        $this->Url($url);
    }
    //AD�˻�����
    public function AdAccountSet(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Ad/AdAccountSet';
        $this->Url($url);
    }

    //�ն��ʲ�����
    public function Terminal(){
        $config = C('publicUrl');
        $url = $config.'/Home/Terminal';
        $this->Url($url);
    }

    //������̨��ά��
    public function netDeviceindex(){
        $config = C('publicUrl');
        $url = $config.'/Home/Netdevice/netDeviceindex';
        $this->Url($url);
    }

    //���޵�����
    public function BaoXiuGD(){
        $config = C('publicUrl');
        $url = $config.'/Home/BaoXiuGD';
        $this->Url($url);
    }

    //��ά��������
    public function Tasksolution(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Tasksolution';
        $this->Url($url);
    }

    //��ά�����
    public function Joborder(){
        $config = C('publicUrl');
        $url = $config.'/Home/Joborder';
        $this->Url($url);
    }

    //��ά�������
    public function assigntask(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Joborder/assigntask.html';
        $this->Url($url);
    }

    //ͨ��վ�ɹ�������
    public function txzjob(){
        $config = C('publicUrl');
        $url = $config.'/Home/Joborder/txzjob';
        $this->Url($url);
    }

    //�������Զ�����
    public function Configure(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Switchboard/Configure';
        $this->Url($url);
    }

    //������ģ��༭
    public function modelEdit(){
        $config = C('publicUrl');
        $url = $config.'/Home/Switchboard/modelEdit';
        $this->Url($url);
    }

    //USBȨ�޷������
    public function usbassign(){         $config = C('publicUrl');
        $url = $config.'/Home/Usbkey/usbassign';
        $this->Url($url);
    }

    //USB���ݹ���
    public function Usbkey(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Usbkey/index';
        $this->Url($url);
    }

    //USB��־��ѯ
    public function usblog(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Usbkey/usblog';
        $this->Url($url);
    }

    //USB�������
    public function usbtask(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Usbkey/usbtask';
        $this->Url($url);
    }

    //����ѯ
    public function Biaodan(){
        $config = C('publicUrl');
        $url = $config.'/Home/Biaodan';
        $this->Url($url);
    }

    //����ģ��
    public function Gongdan(){  
        $config = C('publicUrl');
        $url = $config.'/Home/Gongdan';
        $this->Url($url);
    }

    //ȫ��������
    public function Cycle(){  
        $config = C('publicUrl');
        $url = $config.'/Home/Life/Cycle.html';
        $this->Url($url);
    }

    //IP��ַ
    public function ipAddress(){  
        $config = C('publicUrl');
        $url = $config.'/Home/IP/index.html';
        $this->Url($url);
    }

    //IP��ַ��ѯ
    public function ipSearch(){         $config = C('publicUrl');
        $url = $config.'/Home/IP/Search.html';
        $this->Url($url);
    }

    //ͨ���ֵ�
    public function Dictionarymng(){ 
        $config = C('publicUrl');
        $url = $config.'/Admin/Dictionarymng';
        $this->Url($url);
    }

    //¥�����
    public function building(){ 
        $config = C('publicUrl');
        $url = $config.'/Admin/Dictionarymng/building';
        $this->Url($url);
    }

    //���ҹ���
    public function factory(){ 
        $config = C('publicUrl');
        $url = $config.'/Admin/Dictionarymng/factory';
        $this->Url($url);
    }

    //�ͺŹ���
    public function modelnumber(){ 
        $config = C('publicUrl');
        $url = $config.'/Admin/Dictionarymng/modelnumber';
        $this->Url($url);
    }

    //ģ�����
    public function moban(){ 
        $config = C('publicUrl');
        $url = $config.'/Admin/Dictionarymng/moban';
        $this->Url($url);
    }

    //�������������ݱ�
    public function switchesDiff(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Diff/switchesDiff';
        $this->Url($url);
    }

    //��ز������ݱ�
    public function AdDiff(){  
        $config = C('publicUrl');
        $url = $config.'/Home/Diff/AdDiff';
        $this->Url($url);
    }

    //��-��ά�ʲ�����
    public function bdywDiff(){
        $config = C('publicUrl');
        $url = $config.'/Home/Diff/bdywDiff';
        $this->Url($url);
    }

    //������ɨ��-��ά�ʲ�����
    public function ywscanDiff(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Diff/ywscanDiff';
        $this->Url($url);
    }

    //����׼��-��ά�ʲ�����
    public function zrywDiff(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Diff/zrywDiff';
        $this->Url($url);
    }

    //�����˲��Ŵ��Ҳ����
    public function dutyDeptDiff(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Diff/dutyDeptDiff';
        $this->Url($url);
    }

    //ʹ���˲��Ŵ��Ҳ����
    public function userDeptDiff(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Diff/userDeptDiff';
        $this->Url($url);
    }

    //���ü�����ʲ�����
    public function TerminalHy(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/TerminalHy';
        $this->Url($url);
    }

    //���ü������־��ѯ
    public function TmnhyLog(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Life/TmnhyLog';
        $this->Url($url);
    }

    //���ε�¼��ѯ
    public function Roam(){ 
        $config = C('publicUrl');
        $url = $config.'/Home/Roam';
        $this->Url($url);
    }


    public function Url($url){         $config = C('publicUrl');
        echo"<script>
                    location.href='../login/systemIntegration?currentView='+'$url'
                 </script>";
    }



}
