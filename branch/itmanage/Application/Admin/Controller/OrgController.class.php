<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class OrgController extends BaseController {

    /**
     * 获取后台部门列表
     */

    public function getOrgLists(){
        $model = M('view_org');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $search = I('post.');
        $search = trim($search['data']['q']);
        $data = $model->field("org_id id,org_type || '-' ||org_name text")
            ->where("org_name like '%s' and org_isavaliable = '启用'",[ "%$search%"])
            ->select();
        echo json_encode(array('q' => $search, 'results' => $data));
    }

    /**
     * 用户管理
     */
    public function orgAuth(){
        addLog('','用户访问日志','访问部门授权管理','成功');
        $this->display();
    }
    /**
     * 用户管理
     */
    public function index(){
        addLog('','用户访问日志','访问部门管理','成功');
        $this->display();
    }

    /**
     * 获取部门列表
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }

        $chooseMenu = trim($queryParam['choosemenu']);

        $whereStr = " s.org_isavaliable = '启用'";
        $orgName = trim($queryParam['org_name']);
        $orgFullName = trim($queryParam['org_fullname']);
        $orgType = trim($queryParam['org_type']);
        if(!empty($orgName)) $whereStr .= " and s.org_name like '%$orgName%' ";
        if(!empty($orgFullName)) $whereStr .= " and s.org_fullname like '%$orgFullName%' ";
        if(!empty($orgType)) $whereStr .= " and s.org_type = '$orgType' ";
        if(!empty($chooseMenu)) $whereStr .=" and   s.org_id in (select org_id from view_org start with org_id= '$chooseMenu' connect by prior org_id =org_pid)";
        $model = M('view_org s');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($whereStr)->count();

        if($isExport){
            $data = $model->field('s.org_name,s.org_fullname,p.org_name org_pname,s.org_createtime,s.org_lastmodifytime,s.org_fullnum')
                ->where($whereStr)
                ->join('view_org p on s.org_pid = p.org_id','left')
                ->order("$queryParam[sort] $queryParam[sortOrder]")
                ->select();
            foreach($data as &$value){
                if($value['org_createtime']!=null)
                    $value['org_createtime'] = date('Y-m-d H:i:s',$value['org_createtime']);
                if($value['org_lastmodifytime']!=null)
                    $value['org_lastmodifytime'] = date('Y-m-d H:i:s',$value['org_lastmodifytime']);
            }
            addLog('','导出日志','导出部门列表','成功');
            $header = array('名称','全称','父级部门','创建时间','上次修改时间','排序号');
            if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }

        }else{
            $data = $model->field('s.org_id,s.org_type,s.org_name,s.org_fullname,s.org_pid,s.org_createtime,s.org_lastmodifytime,p.org_name org_pname,s.org_fullnum')
                ->where($whereStr)
                ->join('view_org p on s.org_pid = p.org_id','left')
                ->order('s.org_fullnum asc')
                ->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach($data as &$value){
                if($value['org_createtime']!=null)
                    $value['org_createtime'] = date('Y-m-d H:i:s',$value['org_createtime']);
                if($value['org_lastmodifytime']!=null)
                    $value['org_lastmodifytime'] = date('Y-m-d H:i:s',$value['org_lastmodifytime']);
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }

    }

    /**
     * 获取部门授权列表
     */
    public function getAuthData($isExport = false){
        if($isExport){
            $queryParam = I('get.');
        }else{
            $queryParam = I('put.');
        }

        $chooseMenu = trim($queryParam['choosemenu']);

        $whereStr = "o. org_isavaliable = '启用'";
        $org      = trim($queryParam['search_org']);
        $user     = trim($queryParam['search_name']);
        if(!empty($org)) $whereStr .= " and a.u_orgid = '$org' ";
        if(!empty($user)) $whereStr .= " and ( a.u_userid like '%$user%' or u.user_name like '%$user%') ";
        $model = M('orguser a');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->join('sysuser u on u.user_id = a.u_userid','left')
            ->join('view_org o on o.org_id = a.u_orgid','left')
            ->where($whereStr)
            ->count();

        if($isExport){
            $data = $model->field("o.org_name u_orgname,o.org_fullname u_orgfullname,u.user_realusername || '(' || u.user_name || ')' u_username,a.u_createtime")
                ->join('sysuser u on u.user_id = a.u_userid','left')
                ->join('view_org o on o.org_id = a.u_orgid','left')
                ->where($whereStr)
                ->order("$queryParam[sort] $queryParam[sortOrder]")
                ->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach($data as &$value){
                if($value['u_createtime']!=null)
                    $value['u_createtime'] = date('Y-m-d H:i:s',$value['u_createtime']);
            }
            addLog('','导出日志','导出部门授权列表','成功');
            $header = array('部门','部门全称','人员','创建时间');
            if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }

        }else{
            $data = $model->field("a.u_createtime,a.u_id,u.user_realusername || '(' || u.user_name || ')' u_username,o.org_name u_orgname,o.org_fullname u_orgfullname ")
                ->join('sysuser u on u.user_id = a.u_userid','left')
                ->join('view_org o on o.org_id = a.u_orgid','left')
                ->where($whereStr)
                ->order("$queryParam[sort] $queryParam[sortOrder]")
                ->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach($data as &$value){
                if($value['u_createtime']!=null)
                    $value['u_createdate'] = date('Y-m-d H:i:s',$value['u_createtime']);
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }

    }

    /**
     * 用户添加或修改
     */
    public function add(){
        $id      = trim(I('get.id'));
        $orgType = trim(I('get.org_type'));
        $where = [];
        if(!empty($id)) $where['org_id'] = ['neq', $id];
        if(!empty($id)){
            $model = M('view_org');
            $data = $model->field('org_id,org_name,org_fullname,org_pid,org_type,org_fullnum')->where("org_id='%s'", $id)->find();
            $this->assign('data', $data);
            $where['org_type'] = ['eq', $data['org_type']];
        }

        if(!empty($orgType) && empty($id)) $where['org_type'] = ['eq', $orgType];
        $orgList= M('view_org')->field('org_id id,org_name, org_pid pid,org_fullname')->where($where)->order('org_fullnum desc')->select();

        addLog('','用户访问日志','访问部门添加修改页面','成功');
        $type = ['内部部门', '外部组织', '内部领域'];
        $deptId = trim(I('get.deptid'));
        $this->assign('deptId', $deptId);
        $this->assign('orgType', $orgType);
        $this->assign('type', $type);
        $this->assign('orglist', $orgList);
        $this->display();
    }

    /**
     * 部门授权添加或修改
     */
    public function authAdd(){
        $id      = trim(I('get.id'));
        $where = [];
        if(!empty($id)) $where['u_id'] = ['neq', $id];
        if(!empty($id)){
            $model = M('orguser a');
            $data = $model->field("a.u_id,a.u_userid,a.u_orgid,u.user_realusername || '(' || u.user_name || ')' u_username,o.org_type org_type")
                ->join('sysuser u on u.user_id = a.u_userid','left')
                ->join('view_org o on o.org_id = a.u_orgid','left')
                ->where("u_id = '".$id."'")
                ->find();
            $this->assign('data', $data);
            $where['org_type'] = ['eq', $data['org_type']];
        }

        $deptname = trim(I('get.deptname',''));
        $deptId = trim(I('get.deptid'));
        if(!empty($deptId)){
            $deptname = explode('-',$deptname)[0];
        }else{
            $deptname = '内部部门';
        }
        if(!empty($data) && empty($id)) {
            $orgType = $data['orgtype'];
        }else{
            $orgType = $deptname;
        }
        $where['org_type'] = ['eq',$orgType];
        $orgList= M('view_org')->field('org_id id,org_name, org_pid pid,org_fullname')->where($where)->order('org_fullnum asc')->select();

        addLog('','用户访问日志','访问部门授权添加修改页面','成功');
        $type = ['内部部门', '外部组织', '内部领域'];
        $this->assign('deptId', $deptId);
        $this->assign('orgType', $orgType);
        $this->assign('type', $type);
        $this->assign('orglist', $orgList);
        $this->display();
    }

    /**
     * 用户添加修改
     */
    public function addOrg(){
        $id = trim(I('post.id'));
        $data['org_name'] = trim(I('post.org_name'));
        $data['org_fullname'] = trim(I('post.org_fullname'));
        $data['org_pid'] = trim(I('post.org_pid'));
        $data['org_fullnum'] = trim(I('post.org_fullnum'));
        if(empty($data['org_pid'])) $data['org_pid'] = '0';
        $orgType = trim(I('post.org_type'));
        switch($orgType){
            case '内部部门':
                $model = M('org');
                break;
            case '外部组织':
                $model = M('org_out');
                break;
            case '内部领域':
                $model = M('org_region');
                break;
            default:
                exit(makeStandResult(-1, '部门类型不存在'));
        }
        //为空则添加
        if(empty($id)){
            $tem= $model->where("org_name='%s'",$data['org_name'])->find();
            if(!empty($tem)) {
                addLog('org','对象添加日志','新增部门'.$data['org_name'],'失败');
                exit(makeStandResult(-1,'部门已存在'));
            }
            $data['org_createtime'] = time();
            $data['org_isavaliable'] = '启用';
            $data['org_id'] = makeGuid();
            $data['org_createuser'] = session('user_id');
            if($data['org_pid'] == '0'){
                $count = $model->where("org_pid = '0'")->count();
                $count++;
                if($count < 10){
                    $data['org_fullnum'] = '0'.$count;
                }else{
                    $data['org_fullnum'] = $count;
                }
            }else{
                $parentOrder = $model->where("org_id = '{$data['org_pid']}'")->field('org_fullnum')->find();
                $count = $model->where("org_pid = '{$data['org_pid']}'")->count();
                $count++;
                if($count < 10){
                    $data['org_fullnum'] = $parentOrder['org_fullnum'].'0'.$count;
                }else{
                    $data['org_fullnum'] = $parentOrder['org_fullnum'].$count;
                }
            }
            $res = $model->add($data);
            if(empty($res)){
                addLog('org','对象添加日志','添加部门'.$data['org_name'],'失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('org','对象添加日志','添加部门'.$data['org_name'],'成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data['org_lastmodifytime'] = time();
            $data['org_lastmodifyuser'] = session('user_id');
            $tem= $model->field('org_id')->where("org_name='%s'",$data['org_name'])->find();
            if($tem['org_id']!=$id&&!empty($tem)) {
                addLog('org','对象修改日志','修改部门'.$data['org_name'],'失败');
                exit(makeStandResult(-1,'部门已存在'));
            }

            $beforeOrgType = M('view_org')->field('org_type')->where("org_id = '%s'", $id)->find();
            if($beforeOrgType['org_type'] != $orgType){
                switch($beforeOrgType['org_type']){
                    case '内部部门':
                        $beforeModel = M('org');
                        break;
                    case '外部组织':
                        $beforeModel = M('org_out');
                        break;
                    case '内部领域':
                        $beforeModel = M('org_region');
                        break;
                    default :
                        exit(makeStandResult(-1, '部门类型有误'));
                }
                $beforeModel->startTrans();
                try{
                    $beforeModel->where("org_id = '%s'", $id)->delete();
                    $data['org_id'] = $id;
                    $model->add($data);
                    $beforeModel->commit();
                    addLog('org','对象修改日志','修改部门'.$data['org_name'],'成功');
                    exit(makeStandResult(1,'修改成功'));
                }catch (Exception $e){
                    $beforeModel->rollback();
                    addLog('org','对象修改日志','修改部门'.$data['org_name'],'失败');
                    exit(makeStandResult(-1,'修改失败'));
                }

            }else{
                $res = $model->where("org_id='%s'", $id)->save($data);
            }

            if(empty($res)){
                addLog('org','对象修改日志','修改部门'.$data['org_name'],'失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('org','对象修改日志','修改部门'.$data['org_name'],'成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 部门授权添加修改
     */
    public function addOrgAuth(){
        $id = trim(I('post.u_id'));
        $data['u_orgid'] = trim(I('post.u_orgid'));
        $data['u_userid'] = trim(I('post.u_userid'));
        $model = D('OrgUser');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        //为空则添加
        if(empty($id)){
            $data = $model->create($data,1);
            $res = $model->add($data);
            if(empty($res)){
                addLog('orguser','对象添加日志','添加部门授权'.$data['u_orgid'],'失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                addLog('orguser','对象添加日志','添加部门授权'.$data['u_orgid'],'成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            // 验证无重复
            $isHave = $model->where($data)->where("u_id !='".$id."'")->count();
            if($isHave != '0') exit(makeStandResult(-2,'添加失败，该条数据已存在'));
            $data = $model->create($data,2);
            $res = $model->where("u_id='%s'", $id)->save($data);

            if(empty($res)){
                addLog('orguser','对象修改日志','修改部门授权'.$id,'失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                addLog('orguser','对象修改日志','修改部门授权'.$id,'成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }

    /**
     * 删除模块
     */
    public function delOrg(){
        $id = I('post.id');
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));

        $model = M('view_org');

        //不能含有子部门
        $children = $model->where("org_pid ='%s'", $id)->find();
        if(!empty($children))  exit(makeStandResult(-1,'要删除的部门下还有子部门，不可删除'));

        //部门下不能有用户
        $users = M('sysuser')->where("user_orgid ='%s'", $id)->find();
        if(!empty($users))  exit(makeStandResult(-1,'要删除的部门下还有用户，不可删除'));

        $org = $model->where("org_id = '%s'",$id)->field('org_type,org_name')->find();

        switch($org['org_type']){
            case '外部组织':
                $orgModel = M('org_out');
                break;
            case '内部部门':
                $orgModel = M('org');
                break;
            case '内部领域':
                $orgModel = M('org_region');
                break;
            default:$orgModel = M('org');
        }

        $res = $orgModel -> where("org_id ='%s'", $id)->delete();
        if(empty($res)){
            addLog('org','对象修改日志','删除部门'.$org['org_name'],'成功');
            exit(makeStandResult(-1,'删除失败'));
        }else{
            addLog('org','对象修改日志','删除部门'.$org['org_name'],'失败');
            exit(makeStandResult(1,'删除成功'));
        }
    }

    /**
     * 删除用户授权
     */
    public function delAuth(){
        $id = I('post.id');
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));

        $model = M('orguser');

        $res = $model -> where("u_id ='%s'", $id)->delete();
        if(empty($res)){
            addLog('orguser','对象删除日志','删除部门授权'.$id,'成功');
            exit(makeStandResult(-1,'删除失败'));
        }else{
            addLog('orguser','对象删除日志','删除部门授权'.$id,'失败');
            exit(makeStandResult(1,'删除成功'));
        }
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

    /**
     * 根据类型获取部门信息
     */
    public function getDeptByOrgType(){
        $Model     = M('view_org');
        $orgType  = I('post.org_type');
        $where['org_isavaliable'] = ['eq', '启用'];
        if(!empty($orgType)) $where['org_type'] = ['eq', $orgType];
        $orgList= $Model->field('org_id id,org_name, org_pid pid,org_fullname')->where($where)->order('org_fullnum asc')->select();
        if($orgType != '内部领域'){
            $orgList = getLevelData($orgList, '0');
            foreach($orgList as &$value){
                $value['org_name'] = str_repeat( '&nbsp;&nbsp;&nbsp',$value['level']).$value['org_name'];
            }
        }
        echo json_encode($orgList);
    }

    /**
     * 选择用户单位
     */
    public function chooseUserOrgData(){
        $queryParam = json_decode(file_get_contents( "php://input"), true);
        $model = M('view_org');

        $orgType = trim($queryParam['org_type']);
        $searchName = trim($queryParam['search_name']);

        //TODO 以后可能会用到
        $isOnlyShow = intval($queryParam['isonlyshow']);
        if($isOnlyShow) $where['org_isonlyshow'] = ['eq', 1];

        $where['org_isavaliable'] = ['eq', '启用'];
        if(!empty($orgType)) $where['org_type'] = ['eq', $orgType];
        if(!empty($searchName)) $where['org_name'] = ['like', "%$searchName%"];

        if($queryParam['limit']){
            $data = $model->where($where)->field('org_name,org_id')->order("org_fullnum asc ")->limit($queryParam['offset'], $queryParam['limit'])->select();
            $count = $model->where($where)->count();
        }else{
            $data = $model->where($where)->field('org_name,org_id')->order("org_fullnum asc ")->select();
            $count = count($data);
        }
        exit(json_encode(array( 'total' => $count,'rows' => $data)));
    }
}