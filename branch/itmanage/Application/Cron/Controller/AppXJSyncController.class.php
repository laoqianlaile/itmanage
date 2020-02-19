<?php
namespace Cron\Controller;
use Think\Controller;
class AppXJSyncController extends Controller {

    public function index()
    {
        header("Content-type:text/html;charset=utf-8");
        echo "<br><b>同步应用系统信息</b><br>";
        $this->syncApp();
    }
    public function syncApp()
    {
        echo "<br><b>同步应用系统信息</b><br>";
        $srcdata = M('主表','','ExcelServer_CONFIG')->field("系统名称 APPNAME,系统管理员 ADMINNAME1,系统管理员2 ADMINNAME2,所属班组 APPGROUP,ExcelServerRCID SID")->select();
        //读取原巡检系统的应用系统列表
        $srcarr = [];
        foreach ($srcdata as $value) {
            $srcarr[$value['sid']] = $value;
        }
        //读取新巡检系统的应用系统列表
        $desModel = M('it_appmanagedoc');
        $desdata = $desModel->select();
        $desarr = [];
        foreach ($desdata as $value) {
            $desarr[$value['amd_sid']] = $value;
        }
        //读取主数据人员
        $userdata = M('cdc_pv_501itcxj_user','','Publicdb_CONFIG')->select();
        $userarr = [];
        foreach ($userdata as $value) {
            $userarr[$value['realusername']] = $value;
        }
        $userId = session('user_id');
        $username = M('sysuser')->where("user_id = '%s'",$userId)->getField('user_realusername');
        try{
            //刪除重复项
            foreach ($desarr as $key => $value) {
                if(!isset($srcarr[$value['sid']]))
                {
                    $desModel->where("amd_sid = '%s'",$value['sid'])->delete();
                }
            }
            //新增/更新项
            foreach ($srcarr as $key => $value) {
                $data = [];
                $data['amd_appname'] = $value['appname'];
                $data['amd_adminname1'] = str_replace('1','',$value['adminname1']);
                if(isset($userarr[$data['amd_adminname1']]))
                {
                    $data['amd_admin1'] = $userarr[$data['amd_adminname1']]['domainusername'];
                }
                $data['amd_adminname2'] = str_replace('1','',$value['adminname2']);
                if(isset($userarr[$data['amd_adminname2']]))
                {
                    $data['amd_admin2'] = $userarr[$data['amd_adminname2']]['domainusername'];
                }
                $data['amd_appgroup'] = $value['appgroup'];
                $data['amd_sortindex'] = $value['row_number'];
                $data['amd_docpath'] = '/upload/word/'.$data['amd_appname'].'.'.'docx';
                if(!isset($desarr[$value['sid']]))
                {
                    $data['amd_atpid'] = makeGuid();
                    $data['amd_sid'] = $value['sid'];
                    $data['amd_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                    $data['amd_atpcreateuser'] = $username;
                    $desModel->add($data);
                }
                else
                {
                    $data['amd_atplastmodifydatetime'] = date('Y-m-d H:i:s',time());
                    $data['amd_atplastmodifyuser'] = $username;
                    $desModel->where("amd_sid = '%s'",$value['sid'])->save($data);
                }
            }
        }catch (\Exception $e) {
            echo error_log();
        }
        echo "<br><b>同步应用系统信息</b><br>";
    }



}