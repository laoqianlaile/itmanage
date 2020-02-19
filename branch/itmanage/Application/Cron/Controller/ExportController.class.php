<?php
namespace Cron\Controller;
use Think\Controller;
class ExportController extends Controller {

    public function index(){
        ini_set('memory_limit','512M');
        set_time_limit(0);
        ini_set('memory_limit','256M');
//        print_r($queryparam);die;
        $Model  = M('it_terminal');
        $sql_select="
                select * from it_terminal du";
        $sql_select = $this->buildSql($sql_select,"du.zd_atpstatus is null");
        $sql_select = $sql_select . " order by du.zd_atpid  asc  ";
        // echo $sql_select;die;
        $Result = $Model->query($sql_select);

        foreach($Result as $key=> $value){
            $duty = D('Home/org')->getUserNames($value['zd_dutyman']);
            $Result[$key]['zd_dutyman'] = $duty['realusername'].'('.$duty['domainusername'].')';
            $use = D('Home/org')->getUserNames($value['zd_useman']);
            $Result[$key]['zd_useman'] = $use['realusername'].'('.$use['domainusername'].')';
            $Result[$key]['zd_dutydeptname'] = D('Home/org')->getDeptName($value['zd_dutydeptid']);
            $Result[$key]['zd_usedeptname'] = D('Home/org')->getDeptName($value['zd_usedeptid']);
        }

        // $header = array('序号','设备类型', '设备编码', '出厂编号', 'IP地址', 'MAC地址', '设备名称', '设备状态', '厂家', '型号', '密级','部标编码','使用人', '使用人账号','使用人部门','责任人','责任人账号','责任部门','地区', '楼宇', '房间号', '采购日期', '维保日期', '到保日期', '启用日期','管理方式','仪设台账', '保密台账','备注','硬盘序号','OS安装时间','是否隔离','是否安装干扰器','交换机IP端口信息','交换机IP地址');
        //		默认网关	主机型号	显示器型号						操作系统及版本		配置视频干扰器

        $header = array('设备编码', '设备名称','设备类型', '厂家', '型号', '密级', '出厂编号','部标编码','状态', 'IP地址','子网掩码', 'MAC地址','默认网关','显示器型号','操作系统及版本','配置视频干扰器','使用人','责任人','责任部门','地区', '楼宇', '房间号', '采购日期', '维保日期', '到保日期', '启用日期','管理方式','仪设台账', '保密台账','备注','硬盘序列号','操作系统安装日期','是否安装隔离插座','序列号','交换机IP端口信息','交换机IP地址');
        foreach($header as &$val){
            $val = iconv('utf-8','gbk',$val);
        }
        $filename = date('Ymd').time().rand(0,1000).'.csv';
        $filePath = 'Public/export/'.date('Y-m-d');
        $filePaths = 'export/'.date('Y-m-d');
        if(!is_dir($filePath)) mkdir($filePath, 0777, true);

        $fp = fopen($filePath.'/'.$filename,'w');
        fputcsv($fp, $header);

//        $Result = changeCoding($Result);

        foreach($Result as $key=>$value){
            $data = [];
            $data[] = $value['zd_devicecode']; //设备编码
            $data[] = $value['zd_name']; //设备名称
            $data[] = $value['zd_type']; //设备类型
            $data[] = $value['zd_factoryname']; //厂家
            $data[] = $value['zd_modelnumber']; //型号
            $data[] = $value['zd_secretlevel']; //密级
            $data[] = $value['zd_seqno']; //出厂编号
            $data[] = $value['zd_anecode']; //部标编码
            // $data[] = $value['zd_devicecode']; //设备编码
            $data[] = $value['zd_status']; //设备状态
            $data[] = $value['zd_ipaddress']; //IP地址
            $data[] = $value['zd_mask']; //子网掩码
            $data[] = $value['zd_macaddress']; //MAC地址
            $data[] = $value['zd_gateway']; //默认网关
            $data[] = $value['zd_display']; //显示器型号
            $data[] = $value['zd_os']; //操作系统及版本
            $data[] = $value['zd_isinstalljammer']; //配置视频干扰器
            $data[] = $value['zd_useman'] ; //使用人
            $data[] = $value['zd_dutyman'] ; //责任人
            $data[] = $value['zd_dutydeptname']; //责任部门
            $data[] = $value['zd_area']; //地区
            $data[] = $value['zd_belongfloor']; //楼宇
            $data[] = $value['zd_roomno']; //房间号
            $data[] = $value['zd_purchasetime']; //采购日期
            $data[] = $value['zd_maintainbegintime']; //维保日期
            $data[] = $value['zd_maintainendtime']; //到保日期
            $data[] = $value['zd_startusetime']; //启用日期
            $data[] = $value['zd_managetype']; //管理方式
            $data[] = $value['zd_devicebook']; //仪设台账
            $data[] = $value['zd_privacybook']; //保密台账
            $data[] = $value['zd_memo']; //备注
            $data[] = $value['zd_harddiskseq']; //硬盘序号
            $data[] = $value['zd_osinstalltime']; //OS安装时间
            $data[] = $value['zd_isisolate']; //是否隔离
            $data[] = $value['zd_atpid']; //序列号

            // $data[] = $value['zd_useman']."\t"; //使用人账号
            // $data[] = $value['zd_usedeptname']."\t"; //使用人部门
            // $data[] = $value['zd_dutyman']."\t"; //责任人账号

            // $data[] = $value['zd_isinstalljammer']."\t"; //是否安装干扰器
            $data[] = $value['zd_swport']."\t"; //交换机IP端口信息
            $data[] = $value['zd_swip']."\t"; //交换机IP地址
            foreach($data as &$v){
                $v = iconv('utf-8','gbk',$v);
            }
            fputcsv($fp, $data);
        }

        $fileFullPath = $filePath.'/'.$filename;
        $fileFullPaths = $filePaths.'/'.$filename;
        $modelList = M('it_backuplist');
        $list = [];
        $list['bl_atpid'] = makeGuid();
        $list['bl_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
        $list['bl_atpcreateruser'] = session('user_id');
        $list['bl_name'] = $filename;
        $list['bl_type'] = '终端资产';
        $list['bl_url'] = $fileFullPaths;
        $modelList->add($list);
        $relativePath = $_SERVER['SCRIPT_NAME'];
        $pathData = explode('index.php', $relativePath);
        $fileRootPath = $pathData[0];
        header('location:'.$fileRootPath.$fileFullPath);
    }

    function buildSql($sql,$condition)
    {
        if($this->containString($sql," where "))
        {
            return $sql." and ".$condition;
        }
        else
        {
            return $sql." where ".$condition;
        }
    }

    function containString($input,$splite)
    {
        $tmparray = explode($splite, $input);
        if (count($tmparray) > 1) {
            return true;
        } else {
            return false;
        }
    }

}