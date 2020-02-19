<?php
namespace Demo\Model;
use Think\Model;
class PersonModel extends BaseModel
{
    public function getPersonDept($username)
    {
        if (empty($username)) return false;
        $username = trim($username);
        $userinfo = M('person')->where("username = '".$username."'")->field('realusername,orgid')->find();
        if(!empty($userinfo)){
            $deptid       = $userinfo['orgid'];
            $dutydeptname = D('Depart')->getDeptInfoByIds($deptid);
            if(!empty($dutydeptname)){
                $userinfo['deptname']   = $dutydeptname[0]['fullname'];
                $userinfo['officename'] = $dutydeptname[0]['name'];
                $deptInfo = D('Depart')->getDeptInfoByIds($dutydeptname[0]['pid']);
                if(!empty($deptInfo)) $userinfo['departname'] = $deptInfo[0]['name'];
            }
        }
        return $userinfo;
    }

    /**
     *  获取所有人员部门信息（格式：username->deptid）
     */
    public function getAllPersonDeptInfo(){
        $personInfo  = M('person')->field('username,orgid')->select();
        $personDept = [];
        foreach($personInfo as $key=>$val){
            $personDept[$val['username']] = $val['orgid'];
        }
        return $personDept;
    }
}