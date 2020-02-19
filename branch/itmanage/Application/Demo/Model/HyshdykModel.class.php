<?php
namespace Demo\Model;
use Think\Model;
class HyshdykModel extends BaseModel {
    const TABLENAME = 'hyshdyk';
     protected $_auto = Array(
        Array("hyshdyk_atpid","makeGuid",1,"function"),
        Array("hyshdyk_atpcreatedatetime","getDatetime",1,"function"),
        Array("hyshdyk_atpcreateuser","getUserId",1,"function"),
        Array("hyshdyk_atplastmodifydatetime","getDatetime",2,"function"),
        Array("hyshdyk_atplastmodifyuser","getUserId",2,"function"),
    );

    function getData($queryparam,$selecttype = '')
    {
        $Model = M('hyshdyk');
        $where=[];
        $where[0]['hyshdyk_atpstatus']=['exp','is null'];
        if(!empty(trim($queryparam['hyshdyk_name'])))
        {
            $where[0]['lower(hyshdyk_name)']=['like',"%".strtolower(trim($queryparam['hyshdyk_name']))."%"];
        }
        if(!empty(trim($queryparam['hyshdyk_secret'])))
        {
            $where[0]['hyshdyk_secret']=['like',"%".trim($queryparam['hyshdyk_secret'])."%"];
        }
        if(!empty(trim($queryparam['hyshdyk_dept'])))
        {
            $where[0]['hyshdyk_dept']=['like',"%".trim($queryparam['hyshdyk_dept'])."%"];
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