<?php
namespace Demo\Model;
use Think\Model;
class HlwjsjModel extends BaseModel {
    const TABLENAME = 'hlwjsj';
     protected $_auto = Array(
        Array("hlwjsj_atpid","makeGuid",1,"function"),
        Array("hlwjsj_atpcreatedatetime","getDatetime",1,"function"),
        Array("hlwjsj_atpcreateuser","getUserId",1,"function"),
        Array("hlwjsj_atplastmodifydatetime","getDatetime",2,"function"),
        Array("hlwjsj_atplastmodifyuser","getUserId",2,"function"),
    );

    function getData($queryparam,$selecttype = '')
    {
        $Model = M('hlwjsj');
        $where=[];
        $where[0]['hlwjsj_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['hlwjsj_name'])))
        {
            $where[0]['lower(hlwjsj_name)']=['like',"%".strtolower(trim($queryparam['hlwjsj_name']))."%"];
        }
        if(!empty(trim($queryparam['hlwjsj_ip'])))
        {
            $where[0]['hlwjsj_ip']=['like',"%".trim($queryparam['hlwjsj_ip'])."%"];
        }
        if(!empty(trim($queryparam['hlwjsj_mac'])))
        {
            $where[0]['hlwjsj_mac']=['like',"%".trim($queryparam['hlwjsj_mac'])."%"];
        }
        if(!empty(trim($queryparam['hlwjsj_usage'])))
        {
            $where[0]['hlwjsj_usage']=['like',"%".trim($queryparam['hlwjsj_usage'])."%"];
        }
        if(!empty(trim($queryparam['hlwjsj_dept'])))
        {
            $where[0]['hlwjsj_dept']=['like',"%".trim($queryparam['hlwjsj_dept'])."%"];
        }
        if(!empty(trim($queryparam['hlwjsj_didian'])))
        {
            $where[0]['hlwjsj_didian']=['like',"%".trim($queryparam['hlwjsj_didian'])."%"];
        }
        if(!empty(trim($queryparam['hlwjsj_jtxkz'])))
        {
            $where[0]['hlwjsj_jtxkz']=['like',"%".trim($queryparam['hlwjsj_jtxkz'])."%"];
        }
        if($selecttype == ''){
            $Result=$Model->where($where)
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->limit( $queryparam['limit'],$queryparam['offset'])
            ->select();
        }else{
            $Result=$Model->where($where)
            ->order("$queryparam[sort] $queryparam[sortOrder]")
            ->select();
        }
        
        // print_r($Result);die;
        $Count=$Model->where($where)->count();
        return [$Result,$Count];
    }

}