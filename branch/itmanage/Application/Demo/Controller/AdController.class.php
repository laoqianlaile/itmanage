<?php
namespace Demo\Controller;
use Think\Controller;
class AdController extends BaseController {

    public function admanage(){
        $this->display();
    }

    public function computermanage(){
        $this->display();
    }

    /**
     * AD账户创建
     */
    public function createAdAccount(){
        $this->display();
    }

    /**
     * 获取组织结构
     */
    public function getAllOU(){
        $cacheData = S('deptTree');
        if(!empty($cacheData)) exit(json_encode($cacheData));

        //缓存消失重新获取数据并缓存
        $client = new \SoapClient('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'UTF-8';
        $result = $client->getAllOU();

        $model = D('Ad');
        if($result->GetAllOUResult == 'success'){
            $res = json_decode($result->lstResult, true);
            $result = $model->getTreeArray($res);
            exit(json_encode($result));
        }else{
            exit($this->makeStandResult(-1, '获取数据失败'));
        }
    }

    /**
     * 获取表中数据
     */
    public function getDataByDeptId(){
        set_time_limit(0);
        $queryParam = json_decode(file_get_contents( "php://input"), true);
        $deptId = trim($queryParam['choosemenu']);

        $limit = $queryParam['limit'];
        $offset = empty($queryParam['offset']) ? 0:$queryParam['offset'];

        $cacheData =  json_decode(S(md5($deptId)));
        if(!empty($cacheData)){
            $list = array_slice($cacheData, $offset, $limit);
            $result = array(
                'total' => count($cacheData),
                'rows' => $list
            );
            exit(json_encode($result));
        }

        //缓存消失时
        $client = new \SoapClient('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'UTF-8';
        $result = $client->GetPersonsInfoByOU(array('strOUDistinguishedName' => $deptId, 'IncludeChildUser' => false));
        $list = json_decode($result->GetPersonsInfoByOUResult, true);
        if(count($list) >= 30){
            S(md5($deptId), json_encode($list), 3600 * 2);
        }
        $data = array_slice($list, $offset, $limit);
        if(count($data) <= 0) $data = [];
        $result = array(
            'total' => count($list),
            'rows' => $data
        );
        exit(json_encode($result));

    }

    /**
     * Ad计算机管理
     */
    public function AdComputerManage(){
        $this->display();
    }

    /**
     * Ad计算机管理-数据
     */
    public function AdComputerList(){
        ini_set("max_execution_time", '0');
        $queryParam = json_decode(file_get_contents( "php://input"), true);
        $userLoginName = trim($queryParam['userLoginName']);
        $computerName = trim($queryParam['computerName']);
        if(empty($userLoginName) && empty($computerName)) exit($this->makeStandResult(-1, '请输入搜索条件'));
        $client = new \SoapClient('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'UTF-8';
        $result = [];


        $data = $client->GetComputerlist(array('LogonName' =>$userLoginName ,'ComputerName'=>$computerName));
        if($data->GetComputerlistResult == 'success'){
            $data = json_decode($data->lstResult, true);
            foreach($data as $key => $val){
                if(empty($val['CN'])) unset($data[$key]);
                if(trim($val['CN']) == trim($result[0]['Description'])){
                    unset($data[$key]);
                    continue;
                }

                $data[$key]['Description'] = $val['CN'];
                $data[$key]['DisplayName'] = $val['UserDisplayName'];
            }
            $result = array_merge($result, $data);
        }

        $total = count($result);
        $result = array(
            'total' => $total,
            'rows' => $result
        );

        exit(json_encode($result));
    }

    /**
     * 计算机删除
     */
    public function deleteComputer(){
        $id = trim(I('post.id'));
        if(empty($id)) $this->makeStandResult(-1, '条件非法');
        $client = new \SoapClient('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'UTF-8';
        $res = $client->DeleteComputer(array('ComputerDistinguishedName' => $id));
        if($res->DeleteComputerResult == 'success') {
            $this->recordLog('delete', 'AD', '删除'.$id.'成功');
            exit($this->makeStandResult(1, '删除成功'));
        }else{
            $this->recordLog('delete', 'AD', '删除'.$id.'失败');
            exit($this->makeStandResult(-1, '删除失败'));
        }
    }

    /**
     * Ad计算机管理
     */
    public function AdAccountSetData(){
        $queryParam = json_decode(file_get_contents( "php://input"), true);
        $searchContent = trim($queryParam['searchContent']);

        //缓存消失重新获取数据并缓存
        $client = new \SoapClient('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'UTF-8';
        $result  = $client->GeAccountList(array('LogonName' => $searchContent));
//        print_r($result);die;
        if($result->GeAccountListResult == 'success'){
            $data = json_decode($result->lstResult, true);
            $res = array(
                'rows' => $data,
                'total' => count($data)
            );
            exit(json_encode($res));
        }else{
            exit($this->makeStandResult(-1, '获取数据失败'));
        }
    }

    /**
     * 创建账户页面
     */
    public function tempCreate(){
        $CreateToOU    = I('get.deptId');
        $mode          = I('get.mode');
        if(empty($CreateToOU) || ($mode != 0 && $mode != 1)){
            echo "<script>alert('参数缺失，请重试！');location.href='createAdAccount';</script>";
            return false;
        }
        $CreateToOUtmp = explode(',',$CreateToOU);
        $CreateToOUstr = 'hq.cast';
        for($i = count($CreateToOUtmp)-1;$i--;$i >= 0){
            $tmp = explode('=',$CreateToOUtmp[$i]);
            if($tmp[0] == 'OU'){
                $CreateToOUstr .= '/'.$tmp[1];
            }
        }
        $modelInfo = D('Dictionary')->getLogintoModel();
        $this->assign('modelInfo',$modelInfo);
        $this->assign('CreateToOU',$CreateToOU);
        $this->assign('CreateToOUstr',$CreateToOUstr);
        $this->assign('mode',$mode);
        $this->display();
    }

    /**
     * 创建账户
     *
     * CreateToOU（必须参数）：建立在哪个OU下
     * UserInfo（必须参数）：建立用户信息，包含用户所有属性，包括//
     * SAMAccountName、DisplayName、UserWorkstations、sn、givenname、description、company、department、postalcode、postofficebox、streetaddress、L、password
     * NORMALACCOUNT：账号登录后是否需要更改密码；
     * ACCOUNTDISABLE：账号是否置为禁用状态
     */
    function addADUser()
    {
        $userData   = I('post.');
        $CreateToOU = $userData['CreateToOU'];
        if(empty($CreateToOU)){
            echo "<script>alert('参数缺失，请重试！');location.href='createAdAccount';</script>";
            return false;
        }
        $UserInfo                   = [];
        $UserInfo['SAMAccountName'] = $userData['username'];
        $UserInfo['Password']       = $userData['newpwd'];
        if(empty($UserInfo['SAMAccountName']) || empty($UserInfo['Password'])){
            echo $this->makeStandResult(1, '参数缺失，请重试！');
//            echo "<script>alert('参数缺失，请重试！');location.href='tempCreate';</script>";
            return false;
        }
        $UserInfo['UserPrincipalName'] = $UserInfo['SAMAccountName'];
        $UserInfo['DisplayName']       = $userData['truename'];
        $UserInfo['UserWorkstations']  = implode(',',$userData['select_computer']);
        $UserInfo['SN']                = $userData['lastname'];
        $UserInfo['GivenName']         = $userData['firstname'];
        $UserInfo['Description']       = $userData['desc'];
        $UserInfo['Company']           = $userData['companyname'];
        $UserInfo['Department']        = $userData['deptname'];
        $UserInfo['PostalCode']        = $userData['postcode'];
        $UserInfo['PostOfficeBox']     = $userData['email'];
        $UserInfo['StreetAddress']     = $userData['road'];
        $UserInfo['L']                 = $userData['city'];
        $content = '';
        foreach($UserInfo as $key=>$val){
            if(!empty($val) && ($key!='Password')) $content .= $key.':'.$val.';';
        }
        $UserInfo                      = json_encode($UserInfo);
        $NORMALACCOUNT    = ($userData['changepass'] == 'true')?true:false;   //账号登录后是否需要更改密码；
        $ACCOUNTDISABLE   = ($userData['accountdeny'] == 'true')?true:false; //账号是否置为禁用状态

        $client  = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $res     = $client->AddADUser(array('CreateToOU' => $CreateToOU, 'UserInfo' => $UserInfo, 'NORMALACCOUNT' => $NORMALACCOUNT , 'ACCOUNTDISABLE' => $ACCOUNTDISABLE));
        $result  = $res->AddADUserResult;
        $symbol  = $res->symbol;
        $this->recordLog("add",'AD',$content);
        if($result == 'success'){
            echo $this->makeStandResult(0, $symbol);
            return true;
        }else{
            echo $this->makeStandResult(1, $symbol);
            return true;
        }
    }

    /**
     * Ad账户批量添加
     */
    public function batchAdd(){
        header("content-type:text/html;charset=utf-8");
        $dept = I('post.dept');
        if(empty($dept)) {
            echo "<script>alert('请先选择部门');history.go(-1)</script>";
            return;
        }
        if(empty($_FILES) || empty($_FILES['file']['name'])) {
            echo "<script>alert('没有文件被上传');history.go(-1)</script>";
            return;
        }
        if($_FILES['file']['error'] == 1)  {
            echo "<script>alert('文件大小超过2M');history.go(-1)</script>";
            return;
        }
        $fileType = explode('.', $_FILES['file']['name']);
        if($fileType[1] != 'csv'){
            echo "<script>alert('目前仅支持csv格式的文件');history.go(-1)</script>";
            return;
        }

        $path = $_FILES['file']['tmp_name'];

        $file = fopen($path,'r');
        while($data = fgetcsv($file)){
            $fileData[] = $data;
        }

        $fileData = changeCoding($fileData, 'gbk', 'utf-8');
        $ruleHeader = array(0=>'用户登录名称',1=> '登录到',2=> '账户选项', 3=>'姓',4=> '名',5=> '显示名称',6=> '描述', 7=>'邮政信箱',8=> '邮政编码',9=> '部门',10=>'公司',11=>'密码');
        $fileHeader = $fileData[0];

        $diff = array_diff($fileHeader,$ruleHeader);
        if(!empty($diff)) {
            echo "<script>alert('文件表头不符合要求');history.go(-1)</script>";
            return;
        }
        unset($fileData[0]);
        $result = [];
        foreach($fileData as $key=>$value){
            if(empty($value[0])){
                echo "<script>alert('‘用户登录名称’该列有内容为空');history.go(-1)</script>";
                return;
            }
            if(empty($value[1])){
                echo "<script>alert('‘登录到’该列有内容为空');history.go(-1)</script>";
                return;
            }
            if(empty($value[2])){
                echo "<script>alert('‘账户选项’该列有内容为空');history.go(-1)</script>";
                return;
            }elseif(!in_array($value[2], array('账户已禁用/交互式登录必须使用智能卡','账户已禁用','交互式登录必须使用智能卡'))){
                echo "<script>alert('‘账户选项’必须是’账户已禁用/交互式登录必须使用智能卡‘的一项或两项');history.go(-1)</script>";
            }
            if(empty($value[3])){
                echo "<script>alert('‘姓’该列有内容为空');history.go(-1)</script>";
                return;
            }
            if(empty($value[4])){
                echo "<script>alert('‘名’该列有内容为空');history.go(-1)</script>";
                return;
            }
            if(empty($value[5])){
                echo "<script>alert('‘显示名称’该列有内容为空');history.go(-1)</script>";
                return;
            }
            if(empty($value[11])){
                echo "<script>alert('‘密码’该列有内容为空');history.go(-1)</script>";
                return;
            }else if(strlen($value[11]) < 10){
                echo "<script>alert('‘密码’的长度必需大于或等于10');history.go(-1)</script>";
                return;
            }else if(!preg_match('/[A-Z]+/', $value[11])){
                echo "<script>alert('‘密码’必需有大写字母组成！');history.go(-1)</script>";
                return;
            }else if(!preg_match('/[a-z]+/', $value[11])){
                echo "<script>alert('‘密码’必需有小写字母组成！');history.go(-1)</script>";
                return;
            }else if(!preg_match('/[0-9]+/', $value[11])){
                echo "<script>alert('‘密码’必需有大写字母、小写字母和数字共同组成！');history.go(-1)</script>";
                return;
            }
            $result[] = array(
                'SAMAccountName' => $value[0],
                'userWorkStations' => $value[1],
                'option' => $value[2],
                'sn' => $value[3],
                'givenName' => $value[4],
                'displayName' => $value[5],
                'description' => $value[6],
                'postOfficeBox' => $value[7],
                'postalCode' => $value[8],
                'department' => $value[9],
                'company' => $value[10],
                'password' => $value[11]
            );
        }

        $transferData = json_encode($result);
        $client = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $res = $client->BatchAddADUser(array('CreateToOU' => $dept, 'UserInfo' => $transferData));
        $successNum = (int)$res->successNum;
        $failNum = (int)$res->failNum;
        $this->recordLog('add','AD', '对'.$dept.'批量增加'.$res->symbol );

        $remindText = "处理完毕！成功 $successNum 条，失败 $failNum 条";
        echo "<script>alert(".$remindText.");history.go(-1)</script>";
        return;
    }

    /**
     * 根据内容搜索ou
     */
    public function checkOuByContent(){
        $searchContent = trim(I('post.searchContent'));
        if(empty($searchContent)) exit($this->makeStandResult(-1 ,'缺少参数'));
        $clicnt = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $data = $clicnt->GetOUByName(array('OUName' => $searchContent));
        if($data->GetOUByNameResult == 'success'){
            $result = $data->lstResult;
        }else{
            $result = json_encode([]);
        }
        exit($result);
    }

    /**
   * AD账户密码设置页面
   */
    function AdChangePwd(){
        $SAMAccountName    = I('get.id');
        if(empty($SAMAccountName)){
            echo "<script>alert('参数缺失，请重试！');history.go(-1);;</script>";
            return false;
        }
        $client    = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $userInfo  = $client->GetAccountProperty(array('strSAMAccountName' => $SAMAccountName));
        $res       = $userInfo->GetAccountPropertyResult;
        if($res == 'error'){
            echo "<script>alert('参数有误，请重试！');history.go(-1);;</script>";
            return false;
        }
        $UsbStatus = $client->GetAccountUsbStatus(array('strSAMAccountName' => $SAMAccountName));
        $result    = $UsbStatus->symbol;
//        $result    = $UsbStatus->GetAccountUsbStatusResult;
        if(!empty($result)){
            echo "<script>alert('参数有误，请重试！');history.go(-1);;</script>";
            return false;
        }
        $userInfo       = $userInfo->lstResult;
        $userInfo       = json_decode($userInfo);
        $SAMAccountName = $userInfo->SAMAccountName;
        $DisplayName    = $userInfo->DisplayName;
        $Company        = $userInfo->Company;
        $Department     = $userInfo->Department;
        $Description    = $userInfo->Description;
//        print_r($UsbStatus);die;
        $UsbStatus      = empty($UsbStatus->GetAccountUsbStatusResult)?false:true;
        $this->assign('SAMAccountName',$SAMAccountName);
        $this->assign('DisplayName',$DisplayName);
        $this->assign('Company',$Company);
        $this->assign('Department',$Department);
        $this->assign('Description',$Description);
        $this->assign('UsbStatus',$UsbStatus);
        $this->display();
    }

    /**
    * AD账户密码设置
    */
    public function SetAccountPass(){
        $SAMAccountName  = I('post.strSAMAccountName');
        $Description     = strtoupper(I('post.Description'));
        $Descriptions    = strtolower($Description);
        $DisKeylogin     = I('post.DisKeylogin');
        $newpwd          = I('post.newpwd');
        $duetime         = I('post.duetime');
        $reason          = I('post.reason');
        $levels          = I('post.levels');
        $reason          = $levels.":".$reason;
        if(empty($SAMAccountName)){
            echo "<script>alert('参数缺失，请重试！');location.href='createAdAccount';</script>";
            return false;
        }
        $client = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $DisKeylogin = ($DisKeylogin == 'true')?true:false;
        if($DisKeylogin){
            $res    = $client->SetAccountPass(array('strSAMAccountName' => $SAMAccountName, 'DisKeylogin' => false, 'UserPasswd' => ''));
            $symbol = $res->symbol;
            $res    = $res->SetAccountPassResult;
            if($res == 'success'){
                //记录日志
                $this->recordLog("update",'AD',$symbol."；强制USBKEY登录");
                if(empty($Description)){
                    echo $this->makeStandResult(0, '');
                    return true;
                }else{
                    //更新USBKEY表信息
                    $keyInfo = M('usbkey')->where("u_code = '".$Description."' or u_code = '".$Descriptions."'")->find();
                    if(empty($keyInfo)){
                        echo $this->makeStandResult(0, '');
                        return true;
                    }else{
                        $arr['u_isforce']    = 0;
                        $arr['u_expiredate'] = '';
                        $arr['u_reason']     = '';
                        $res = M('usbkey')->where("u_atpid = '".$keyInfo['u_atpid']."'")->save($arr);
                        //记录日志
                        $this->recordLog("update",'usbkey',"强制USBKEY登录，强制状态：强制；",'usbkey',$keyInfo['u_atpid']);
                        if($res){
                            echo $this->makeStandResult(0, '');
                            return true;
                        }else{
                            echo $this->makeStandResult(1, '修改USBKEY信息失败，请重试！');
                            return true;
                        }
                    }
                }
            }
        }else{
            $res    = $client->SetAccountPass(array('strSAMAccountName' => $SAMAccountName, 'DisKeylogin' => true, 'UserPasswd' => $newpwd));
            $symbol = $res->symbol;
            $res    = $res->SetAccountPassResult;
            if($res == 'success'){
                //记录日志
                if(empty($Description)){
                    echo $this->makeStandResult(0, '');
                    return true;
                }else{
                    //更新USBKEY表信息
                    $keyInfo = M('usbkey')->where("u_code = '".$Description."' or u_code = '".$Descriptions."'")->find();
                    if(empty($keyInfo)){
                        echo $this->makeStandResult(0, '');
                        return true;
                    }else{
                        $arr['u_isforce']    = 1;
                        $arr['u_expiredate'] = $duetime;
                        $arr['u_reason']     = $reason;
                        $res = M('usbkey')->where("u_atpid = '".$keyInfo['u_atpid']."'")->save($arr);
                        //记录日志
                        $this->recordLog("update",'usbkey',"强制USBKEY登录，强制状态：不强制；到期时间：".$duetime."；理由：".$reason."；",'usbkey',$keyInfo['u_atpid']);
                        if($res){
                            $this->recordLog("update",'AD',$symbol."；不强制USBKEY登录：".$reason."；到期时间：".$duetime,'usbkey',$keyInfo['u_atpid']);
                            echo $this->makeStandResult(0, '');
                            return true;
                        }else{
                            $this->recordLog("update",'AD',$symbol."；不强制USBKEY登录：更改失败");
                            echo $this->makeStandResult(1, '修改USBKEY信息失败，请重试！');
                            return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * AD账户属性设置页面
     */
    function AdAccountattri(){
        $SAMAccountName    = I('get.id');
        if(empty($SAMAccountName)){
            echo "<script>alert('参数缺失，请重试！');location.href='createAdAccount';</script>";
            return false;
        }
        $client   = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $userInfo = $client->GetAccountProperty(array('strSAMAccountName' => $SAMAccountName));
        $res      = $userInfo->GetAccountPropertyResult;
        if($res == 'error'){
            echo "<script>alert('登录名有误，请重试！');history.go(-1);</script>";
            return false;
        }
//        print_r($userInfo);die;
        $AccountDisable   = $userInfo->AccountDisable;
        $userInfo         = $userInfo->lstResult;
        $userInfo         = json_decode($userInfo);
        $SAMAccountName   = $userInfo->SAMAccountName;
        $DisplayName      = $userInfo->DisplayName;
        $Description      = $userInfo->Description;
        $Company          = $userInfo->Company;
        $Department       = $userInfo->Department;
        $PostalCode       = $userInfo->PostalCode;
        $PostOfficeBox    = $userInfo->PostOfficeBox;
        $L                = $userInfo->L;
        $StreetAddress    = $userInfo->StreetAddress;
        $UserWorkstations = $userInfo->UserWorkstations;
        $UserWorkstations = json_encode($UserWorkstations);
        $AccountLockout   = $userInfo->AccountLockout;
        $modelInfo = D('Dictionary')->getLogintoModel();
        $this->assign('modelInfo',$modelInfo);
        $this->assign('SAMAccountName',$SAMAccountName);
        $this->assign('DisplayName',$DisplayName);
        $this->assign('Description',$Description);
        $this->assign('Company',$Company);
        $this->assign('Department',$Department);
        $this->assign('PostOfficeBox',$PostOfficeBox);
        $this->assign('PostalCode',$PostalCode);
        $this->assign('L',$L);
        $this->assign('StreetAddress',$StreetAddress);
        $this->assign('UserWorkstations',$UserWorkstations);
        $this->assign('AccountLockout',$AccountLockout);
        $this->assign('AccountDisable',$AccountDisable);
        $this->display();
    }

    /**
     * 获取部门下用户
     */
    public function getUsersByDept(){
        $dept = trim(I('post.dept'));
        $isContainChild = I('post.isContainChild');
        if($isContainChild == 'true'){
            $isContainChild = true;
        }else{
            $isContainChild = false;
        }
        $client = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $data = $client->GetPersonsInfoByOU(array('strOUDistinguishedName' => $dept, 'IncludeChildUser' => $isContainChild));
        $result = $data->GetPersonsInfoByOUResult;
        exit($result);
    }

    /**
     * 批量修改、删除数据操作
     */
    public function batchUpdateData(){
        $request = I('post.');
        $client = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');

        $data['updateUser'] = $request['needUpdateUser'];
        $data['add'] = $request['add'];
        $data['delete'] = $request['del'];
        $isContainAllLoginUser = $request['isContainAllLoginUser'] ;
        $isContainAllLoginUser = $isContainAllLoginUser == 'true'? true:false;
        $res = $client->BatchModifyAccount(array('updateUser' => json_encode($data), 'allWorkstations' => $isContainAllLoginUser));

        $mark = $res->BatchModifyAccountResult;
        //记录日志
        $logContent = '对以下用户：'.implode(',',$data['updateUser']);
        if(!empty($data['add'])){
            $logAddContent = $logContent . '批量增加以下登录到：' .$data['add'];
            if($mark == 'success'){
                $logAddContent .= '成功';
            }else{
                $logAddContent .= '失败';
            }
            $this->recordLog('add', 'AD', $logAddContent);
        }
        if(!empty($data['delete'])){
            $logDelContent = $logContent . '批量删除以下登录到：'. $data['delete'];
            if($mark == 'success'){
                $logDelContent .= '成功';
            }else{
                $logDelContent .= '失败';
            }
            $this->recordLog('delete', 'AD', $logDelContent);
        }

        if($mark == 'success'){
            exit($this->makeStandResult(1, '操作成功'));
        }else{
            exit($this->makeStandResult(-1, '操作失败'));
        }
    }

    /*
    * AD账户属性设置
    */
    public function SetAccountAttr(){
        $strSAMAccountName = I('post.strSAMAccountName');
        if(empty($strSAMAccountName)){
            echo "<script>alert('参数缺失，请重试！');location.href='createAdAccount';</script>";
            return false;
        }
        $strDisplayName      = I('post.DisplayName');
        $strDescription      = I('post.Description');
        $strCompany          = I('post.Company');
        $strDepartment       = I('post.Department');
        $strPostalCode       = I('post.PostalCode');
        $strPostOfficeBox    = I('post.PostOfficeBox');
        $strL                = I('post.L');
        $strStreetAddress    = I('post.strStreetAddress');
        $strUserWorkStations = I('post.select_computer');
        $strUserWorkStations = implode(',',$strUserWorkStations);
        $AccountLockout      = I('post.AccountLockout');
        $AccountDisable      = I('post.AccountDisable');
        $AccountLockout      = ($AccountLockout == 'true')?true:false;
        $AccountDisable      = ($AccountDisable == 'true')?true:false;
        $client = $this->getWebServiceObj('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        //获取旧信息
        $userInfo   = S(md5($strSAMAccountName));
        if(empty($userInfo)){
            $userInfo = $client->GetAccountProperty(array('strSAMAccountName' => $strSAMAccountName));
        }

        $AccountDisables = $userInfo->AccountDisable;
        $userInfo        = json_decode($userInfo->lstResult,true);
        $oldUerInfo      = [];
        $newUerInfo      = [];
        $oldUerInfo['Description']      = $strDescription;
        $oldUerInfo['AccountDisable']   = ($AccountDisable == true)?true:'';
        $oldUerInfo['AccountLockout']   = ($AccountLockout == true)?true:'';
        $oldUerInfo['UserWorkStations'] = $strUserWorkStations;
        $oldUerInfo['Company']          = $strCompany;
        $oldUerInfo['Department']       = $strDepartment;
        $oldUerInfo['L']                = $strL;
        $oldUerInfo['PostOfficeBox']    = $strPostOfficeBox;
        $oldUerInfo['PostalCode']       = $strPostalCode;
        $oldUerInfo['StreetAddress']    = $strStreetAddress;

        $newUerInfo['Description']      = $userInfo['Description'];
        $newUerInfo['AccountDisable']   = $AccountDisables;
        $newUerInfo['AccountLockout']   = $userInfo['AccountLockout'];
        $newUerInfo['UserWorkStations'] = $userInfo['UserWorkstations'];
        $newUerInfo['Company']          = $userInfo['Company'];
        $newUerInfo['Department']       = $userInfo['Department'];
        $newUerInfo['L']                = $userInfo['L'];
        $newUerInfo['PostOfficeBox']    = $userInfo['PostOfficeBox'];
        $newUerInfo['PostalCode']       = $userInfo['PostalCode'];
        $newUerInfo['StreetAddress']    = $userInfo['StreetAddress'];
        $res    = $client->SetAccountProperty(['strSAMAccountName'=>$strSAMAccountName, 'strDisplayName'=>$strDisplayName,'strDescription'=>$strDescription, 'strCompany'=>$strCompany, 'strDepartment'=>$strDepartment, 'strPostalCode'=>$strPostalCode, 'strPostOfficeBox'=>$strPostOfficeBox, 'strL'=>$strL, 'strStreetAddress'=>$strStreetAddress, 'strUserWorkStations'=>$strUserWorkStations ,'AccountDisable'=>$AccountDisable, 'AccountLock'=>$AccountLockout]);
        //print_r($res);die;
        $symbol = $res->symbol;
        $res    = $res->SetAccountPropertyResult;
        //记录日志
        $diff    = array_diff($oldUerInfo,$newUerInfo);
        $content = '';
        foreach($diff as $key=>$val){
            if($newUerInfo[$key] == ''){
                $content .= $key."：".$val."-0；";
            }else{
                $content .= $key."：".$val."-".$newUerInfo[$key]."；";
            }
        }
        $this->recordLog('update','AD',$symbol.$content);
        if($res == 'success'){
            echo $this->makeStandResult(0, '');
            return true;
        }else{
            echo $this->makeStandResult(1, '修改用户属性信息失败，请稍后再试！');
            return true;
        }
    }

    /**
     * AD账户属性设置页面
     */
    function AdAccountattris(){
        $this->display();
    }

    /**
     * Ad计算机管理
     */
    public function AdAccountSetDatas(){
        $queryParam = I('post.');
        $searchContent = trim($queryParam['SAMAccountName']);

        //缓存消失重新获取数据并缓存
        $client = new \SoapClient('http://10.78.72.240:8080/ADWebService/ADWebService.asmx?wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        $client->xml_encoding = 'UTF-8';
        $result = $client->GetAccountProperty(array('strSAMAccountName' => $searchContent));
        if($result->GetAccountPropertyResult == 'success'){
            $tmp = md5($searchContent);
            S($tmp,$result,3600);
            exit($this->makeStandResult(0, '0'));
        }else{
            exit($this->makeStandResult(1, '登录名输入有误，请重新填写！'));
        }
    }
}