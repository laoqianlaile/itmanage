<?php
namespace Demo\Model;
use Think\Model;
class RelationModel extends BaseModel{

    // protected $tableName = 'relation';
    const TABLENAME = 'relation';

    protected $_auto = Array(
        Array("rl_atpid","makeGuid",1,"function"),
        Array("rl_atpcreateuser","getUserId",1,"function"),
        Array("rl_atpcreatetime","getDatetime",1,"function"),
        Array("rl_atplastmodifyuser","getUserId",2,"function"),
        Array("rl_atplastmodifytime","getDatetime",2,"function")
    );
}