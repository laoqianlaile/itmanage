<?php
namespace Home\Model;
use Think\Model;
class OrgModel extends Model {

    //获取人员信息 通过账号
    function getUserName($user)
    {
        $data = M('it_person p')
            ->where("p.username='%s'", $user)
            ->find();
        return $data;
    }

    function getDepart($depart){
        $iFull = explode('-',$depart);
        $countiFull = count($iFull);
        unset($iFull[$countiFull-1]);
        if($iFull[$countiFull-2]== '五院本级'){
            unset($iFull[$countiFull-2]);
        }
        $depart  = implode('-',$iFull);
        return $depart;
    }

    //获取人员信息 通过账号
    function getUserNames($user)
    {
        $data = M('it_person p')
            ->where("p.domainusername='%s'", $user)
            ->find();
        return $data;
    }
    function getDepartId($orgid)
    {
        $data = M('it_depart p')
            ->where("p.id='%s'", $orgid)
            ->find();
        return $data;
    }

    /*故障管理页面中，需要选择系统名称
     *
     */
    public function assignXtName(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.app_name id,p.app_name text from  it_application p  where
                    p.app_name like '%".$q."%' and p.app_atpstatus is null";
        $result=$Model->query($sql_select);
        return json_encode(array('q' =>$q, 'results' => $result));
    }

    /*故障管理页面中，需要选择系统名称
     *
     */
    public function assignuser(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.domainusername id,p.realusername||'('||p.domainusername||')'||'-'||d.name text from  it_person p,it_depart d  where
                    (p.username like '%".$q."%' or p.realusername like '%".$q."%') and p.orgid = d.id";
        $result=$Model->query($sql_select);
        return json_encode(array('q' =>$q, 'results' => $result));
    }

    /*使用人
     *
     */
    public function assignuserid(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.id id,p.realusername||'('||p.username||')'||'-'||d.name text from  it_person p,it_depart d  where
                    (p.username like '%".$q."%' or p.realusername like '%".$q."%') and p.orgid = d.id";
        $result=$Model->query($sql_select);
        return json_encode(array('q' =>$q, 'results' => $result));
    }
    //部门
    public function assigndept(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  id,fullname text from
                    it_depart  where
                    fullname like '%".$q."%'";
        $result=$Model->query($sql_select);
        foreach($result as $key =>$val){
            $iFull = explode('-',$val['text']);
            $countiFull = count($iFull);
            unset($iFull[$countiFull-1]);
            if($iFull[$countiFull-2]== '五院本级'){
                unset($iFull[$countiFull-2]);
            }
            $result[$key]['text'] = implode('-',$iFull);
        }
        return json_encode(array('q' =>$q, 'results' => $result));
    }

    /*视图人员
     * @userName 账号
     */
    public function getViewPerson($userName){
        $Model = M('view_person');
        $data = $Model->where("username='%s'",$userName)
            ->find();
        return $data;
    }

    public function getDeptId($username){
        $orgId = M('it_person')->where("domainusername = '%s'",$username)->getfield('orgid');
        return $orgId;
    }

    public function getDeptList($username){
        $org = M('it_person p')->field('t.id,t.fullname,p.realusername')->join('it_depart t on p.orgid = t.id')->where("p.domainusername = '%s'",$username)->find();
        $org['fullname'] = $this->removeStr($org['fullname']);
        return $org;
    }

    //去掉
    public function removeStr($str){
        if(mb_strstr($str,'综合管理层') !== false){
            //去掉 -五院本级-中国航天科技集团公司第五研究院
            $name = mb_substr($str,0,-27);
        } else if(mb_strstr($str,'五院本级') !== false){
            //去掉 -五院本级-中国航天科技集团公司第五研究院
            $name = mb_substr($str,0,-21);
        }else{
            //去掉 -中国航天科技集团公司第五研究院
            if(mb_strstr($str,'中国航天科技集团公司第五研究院') !== false){
                $name = mb_substr($str,0,-16);
            }else{
                return $str;
            }
        }
        return implode('-',array_reverse(explode('-',$name)));
    }



    public function getDeptName($orgId){
        $orgName = M('it_depart')->where("id = '%s'",$orgId)->getfield('fullname');
        $iFull = explode('-',$orgName);
        $countiFull = count($iFull);
        unset($iFull[$countiFull-1]);
        if($iFull[$countiFull-2]== '五院本级'){
            unset($iFull[$countiFull-2]);
        }
        $iFull = implode('-',$iFull);
        return $iFull;
    }



    /**
     * 获取层级部门列表
     * @param bool|true $isLevel
     * @return array
     */
    public function  getOrgList($isLevel = true, $where = []){
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')
            ->field('org_id id,org_name, org_pid pid,org_fullname')
            ->where($where)
            ->order('org_fullnum asc')
            ->select();
        if($isLevel === true){
            $org = getLevelData($org, null);
            foreach($org as &$value){
                $value['org_name'] = str_repeat( '&nbsp;&nbsp;&nbsp',$value['level']).$value['org_name'];
            }
        }
        return $org;
    }

    public function getOrgViewList($isLevel = true, $where = []){
        $where['role_name'] = ['eq', '部领导'];
        $org = M('v_userrole')->field('user_id id,user_name,user_realusername')
            ->where($where)
            ->order('user_sort asc')
            ->select();
        if($isLevel === true){
            $org = getLevelData($org, null);
            foreach($org as &$value){
                $value['user_realusername'] = $value['user_realusername'];
            }
        }
        return $org;
    }

    /**
     * 获取归属部门
     * @param bool|true $isLevel
     * @return array
     */
    public function getAscriptionDept($isLevel = true){
        $where['org_type'] = ['in', ['内部部门', '内部领域']];
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')->field('org_id id,org_name, org_pid pid,org_fullname')
            ->where($where)
            ->order('org_fullnum asc')
            ->select();
        if($isLevel === true){
            $org = getLevelData($org, '0');
            foreach($org as &$value){
                $value['org_name'] = str_repeat( '&nbsp;&nbsp;&nbsp',$value['level']).$value['org_name'];
            }
        }
        return $org;
    }

    public function getOrgId($orgName){
        if(empty($orgName)) return false;
        $where['org_name'] = ['like', "%$orgName%"];
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')->field('org_id id')
            ->where($where)
            ->find();
        if(!empty($org)){
            return $org['id'];
        }else{
            return false;
        }
    }

    /**
     * 根据orgid获取信息
     * @param $orgId
     * @param string $field
     * @return bool
     */
    public function getOrgInfo($orgId, $field = '*'){
        if(empty($orgId)) return false;
        $where['org_id'] = ['eq', $orgId];
        $where['org_isavaliable'] = ['eq', '启用'];
        $org = M('view_org')->field($field)
            ->where($where)
            ->find();
        if(!empty($org)){
            return $org;
        }else{
            return false;
        }
    }

}