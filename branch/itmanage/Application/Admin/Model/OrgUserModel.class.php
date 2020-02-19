<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * makeTime: 10:15
 */
namespace Admin\Model;
use Think\Model;
class OrgUserModel extends Model{
    protected $tableName = 'orguser';

    protected $_auto = Array(
        Array("u_id","makeGuid",1,"function"),
        Array("u_createuser",__NAMESPACE__  . '\OrgUserModel::getUserId',1,"function"),
        Array("u_createtime",__NAMESPACE__  . '\OrgUserModel::makeTime',1,"function"),
        Array("u_lastmodifyuser",__NAMESPACE__  . '\OrgUserModel::getUserId',2,"function"),
        Array("u_lastmodifytime",__NAMESPACE__  . '\OrgUserModel::makeTime',2,"function")
    );

    function getUserId(){
        return session('user_id');
    }

    function makeTime(){
        return date('Y-m-d H:i:s');
    }
}