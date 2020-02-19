<?php
namespace Demo\Model;
use Think\Model;
class WorkModel extends BaseModel {
    function getGDData($queryparam,$type = 0){
        $map        = [];
        if($type == 0){
            if ($queryparam['offset'] == ''){
                $offset = 0;
            }else{
                $offset = $queryparam['offset'];
            }
            $limit = $queryparam['limit'];
        }
        if ("" != $queryparam['gdh']){
            $searchcontent = trim($queryparam['gdh']);
            $map[0]['rw_workid'] = ['like',"%".$searchcontent."%"];
        }
        if ("" != $queryparam['isstatus']){
            $searchcontent = trim($queryparam['isstatus']);
            $map[0]['rw_status'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['dutyman']){
            $searchcontent = trim($queryparam['dutyman']);
            $map[0]['rw_account'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['begintime']){
            $searchcontent = trim($queryparam['begintime']);
            $map[0][0]['rw_receipttime'] = ['gt',$searchcontent];
        }
        if ("" != $queryparam['endtime']){
            $searchcontent = trim($queryparam['endtime']);
            $map[0][1]['rw_receipttime'] = ['lt',$searchcontent];
        }
        if ('rw_receipttime' != $queryparam['sort']) {
            $order      = array($queryparam['sort']=>$queryparam['sortOrder']);
            if($type == 0){
                $Result = M('work')->where($map)->order($order)->limit($limit,$offset)->select();
            }else{
                $Result = M('work')->where($map)->order($order)->select();
            }
        } else {
            if($type == 0){
                $Result = M('work')->where($map)->order("rw_receipttime desc nulls last")->limit($limit,$offset)->select();
            }else{
                $Result = M('work')->where($map)->order("rw_receipttime desc nulls last")->select();
            }
        }
//        echo M('work')->_sql();die;
        $Count  = M('work')->field('count(*) c')->where($map)->select();
        foreach($Result as $key=> &$value){
            if(!empty($value['rw_account'])){
                $Result[$key]['dealperson'] = getRealusername($value['rw_atpcreateuser']);
            }
            switch($value['rw_status']){
                case "0":
                    $Result[$key]['rw_status'] ="未处理";
                    break;
                case "1":
                    $Result[$key]['rw_status'] ="处理中";
                    break;
                case "2":
                    $Result[$key]['rw_status'] ="已完成";
                    break;
            }
            switch($value['rw_type']){
                case "ZX":
                    $Result[$key]['rw_type'] ="单步";
                    $Result[$key]['rw_resource'] = '45888';
                    break;
                case "YW":
                    $Result[$key]['rw_type'] ="业务";
                    $Result[$key]['rw_resource'] = '45888';
                    break;
                case "BZ":
                    $Result[$key]['rw_type'] ="现场保障";
                    $Result[$key]['rw_resource'] = '45888';
                    break;
                default:
                    $Result[$key]['rw_type'] ="";
                    $Result[$key]['rw_resource'] = '表单系统';
                    break;
            }
        }
        return [$Result,$Count];
    }
}