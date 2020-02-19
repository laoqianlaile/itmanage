<?php
namespace Home\Controller;
use Think\Controller;
class OrgController extends Controller {

    //使用人
    public function assignuser(){
        $data = D('org')->assignuser();
        echo $data?$data:false;
    }

    //使用人
    public function assignuserId(){
        $data = D('org')->assignuserId();
        echo $data?$data:false;
    }
    //部门
    public function assigndept(){
        $data = D('org')->assigndept();
        echo $data?$data:false;
    }

    //弹出框视图
    public function DeparList()
    {
        $this->display();
    }
    //故障管理页面中，系统名称
    public function assignXtName()
    {
        $data = D('org')->assignXtName();
        echo $data?$data:false;
    }

    /**
     * 获取部门树
     */
    public function getOrgTree(){
        $orgName = trim(I('post.org_name'));
        $orgType = trim(I('post.org_type'));
        if(empty($orgType)) exit(makeStandResult(-1, '请选择部门类型'));
        $where = [];
        if(!empty($orgName)) $where['org_name'] = ['like', "%$orgName%"];
        if(!empty($orgType)) $where['org_type'] = ['eq', $orgType];

        $model = D('org');
        $data = $model->getOrgList(false, $where);

        //如果有搜索，查出结果后反向递归
        if(!empty($orgName)){
            $orgModel = M('view_org');
            $result = [];
            foreach ($data as $key => $value) {
                $sql = "select org_id id,org_name, org_pid pid,org_fullname from view_org where org_type ='$orgType' and org_isavaliable = '启用' start with (  org_name like '%$orgName%'  ) connect by prior org_pid=org_id  order by org_fullnum desc";
                $res = array_reverse($orgModel->query($sql));
                $result = array_merge($res, $result);
            }
            $data = uniqueArr($result, true);
        }
        $initData = [];
        if(empty($initData)) $initData = [];
        foreach($data as &$value){
            $value['name'] = $value['org_name'];
            $value['open'] = 'true';
            $value['icon'] = __ROOT__.'/Public/vendor/zTree_v3/css/zTreeStyle/img/diy/10.png';
            $initData[] = $value;
        }
        echo json_encode($data);
    }
}