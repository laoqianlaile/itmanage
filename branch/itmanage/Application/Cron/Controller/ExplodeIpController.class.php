<?php
namespace Cron\Controller;
use Think\Controller;
use Think\Exception;
//拆分子IP、子mac、防火墙源地址、防火墙目的地址等，读取多个字符的字段内容，将相应信息写入subelement表中
class ExplodeIpController extends Controller{

        public function index(){
            $modelSev = M('it_sev');
            $modelSevv = M('it_sevv');
            $modelSub = M('subelement');
            $sevList = $modelSev->field('sev_subip,sev_submac,sev_type,sev_atpid')->where("sev_atpstatus is null and (sev_subip is not null or sev_submac is not null)")->select();
            $sevvList = $modelSevv->field('sevv_subip,sevv_submac,sevv_type,sevv_atpid')->where("sevv_atpstatus is null and (sevv_subip is not null or sevv_submac is not null)")->select();
            set_time_limit(0);
            foreach($sevList as $key =>$val){
                //若子IP不为空，拆分子IP将相应信息写入subelement表中
                $modelSub->where("sub_pid = '%s'",$val['sev_atpid'])->delete();
                if(!empty($val['sev_subip'])){
                    $subips  = str_replace(';',',',$val['sev_subip']);
                    $subips = explode(',',$subips);
                    foreach($subips as $v){
                        $data['sub_content'] = $v;
                        $data['sub_type'] = $val['sev_type'];
                        $data['sub_pid']  = $val['sev_atpid'];
                        $data['sub_num'] = ip2long($v);
                        $data['sub_field'] = 'sev_subip';
                        $modelSub->add($data);
                    }
                }
                //若子mac不为空，拆分子mac将相应信息写入subelement表中
                if(!empty($val['sev_submac'])){
                    $submac  = str_replace(';',',',$val['sev_submac']);
                    $submac = explode(',',$submac);
                    foreach($submac as $mac){
                        $list['sub_content'] = $mac;
                        $list['sub_type'] = $val['sev_type'];
                        $list['sub_pid']  = $val['sev_atpid'];
                        $list['sub_field'] = 'sev_submac';
                        $modelSub->add($list);
                    }
                }
            }

            foreach($sevvList as $k =>$value){
                $modelSub->where("sub_pid = '%s'",$value['sevv_atpid'])->delete();
                //若子IP不为空，拆分子IP将相应信息写入subelement表中
                if(!empty($value['sevv_subip'])){
                    $subipd  = str_replace(';',',',$value['sevv_subip']);
                    $subipd = explode(',',$subipd);
                    foreach($subipd as $ip){
                        $arr['sub_content'] = $ip;
                        $arr['sub_type'] = $value['sevv_type'];
                        $arr['sub_pid']  = $value['sevv_atpid'];
                        $arr['sub_num'] = ip2long($ip);
                        $arr['sub_field'] = 'sevv_subip';
                        $modelSub->add($arr);
                    }
                }
                //若子mac不为空，拆分子mac将相应信息写入subelement表中
                if(!empty($value['sevv_submac'])){
                    $submacd  = str_replace(';',',',$value['sevv_submac']);
                    $submacd = explode(',',$submacd);
                    foreach($submacd as $macd){
                        $res['sub_content'] = $macd;
                        $res['sub_type'] = $value['sevv_type'];
                        $res['sub_pid']  = $value['sevv_atpid'];
                        $res['sub_field'] = 'sevv_submac';
                        $modelSub->add($res);
                    }
                }
            }

            echo '完成';

        }

    public function fwcl(){
        $model = M('fwcl');
        $modelSub = M('subelement');
        $list = $model->field('cl_atpid,cl_sourceip,cl_objectip,cl_port')->where("cl_atpstatus is null")->select();
        foreach($list as $key =>$val){
//            $sourceip  = str_replace(';',',',$val['cl_sourceip']);
//            $sourceip  = str_replace('、',',',$sourceip);
//            $sourceip  = str_replace('；',',',$sourceip);
//            $sourceip  = str_replace('，',',',$sourceip);
//            $sourceip = explode(',',$sourceip);
////            $arr[] = $sourceip;
//            foreach($sourceip as $v){
//                if(!empty($v)){
//                    $ip = explode('.',$v);
//                    $count = count($ip);
////                    if($v == '10.74.53.1-510.74.53.65-76'){
////                        print_r($ip[0]);
////                        dump(is_numeric($ip[0]));die;
////                    }
//                    if(is_numeric($ip[0])){
//                        if($count != 4){
//                            $save['cl_biao'] = 'update';
//                            $model->where("cl_atpid = '%s'",$val['cl_atpid'])->save($save);
//                        }
//                    }
//                }else{
//                    $save['cl_biao'] = 'update';
//                    $model->where("cl_atpid = '%s'",$val['cl_atpid'])->save($save);
//                }
//            }
            $objectip  = str_replace(';',',',$val['cl_objectip']);
            $objectip  = str_replace('、',',',$objectip);
            $objectip  = str_replace('；',',',$objectip);
            $objectip  = str_replace('，',',',$objectip);
            $objectip  = str_replace(' ',',',$objectip);
            $objectip = explode(',',$objectip);
//            $arr[] = $sourceip;
            foreach($objectip as $v){
                if(!empty($v)){
                    $ip = explode('.',$v);
                    if(is_numeric($ip[0])) {
                        if (count($ip) != 4) {
                            $save['cl_biao'] = 'update';
                            $model->where("cl_atpid = '%s'", $val['cl_atpid'])->save($save);
                        }
                    }
                }else{
                    $save['cl_biao'] = 'update';
                    $model->where("cl_atpid = '%s'",$val['cl_atpid'])->save($save);
                }
            }
//            $port  = str_replace(';',',',$val['cl_port']);
//            $port  = str_replace('、',',',$port);
//            $port  = str_replace('；',',',$port);
//            $port  = str_replace('，',',',$port);
//            $port  = str_replace(' ',',',$port);
//            $port = explode(',',$port);
////            $arr[] = $sourceip;
//            foreach($port as $v){
//                if(!empty($v)){
//                    $res['sub_content'] = $v;
//                    $res['sub_type'] = '防火墙策略';
//                    $res['sub_pid']  = $val['cl_atpid'];
//                    $res['sub_field'] = 'cl_port';
//                    $modelSub->add($res);
//                }
//            }
        }
    }
}
