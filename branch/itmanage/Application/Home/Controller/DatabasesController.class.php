<?php
namespace Home\Controller;
use Think\Controller;
class DatabasesController extends BaseController{
    /** 服务器平台展示 */
    public function index(){
        addLog("db_plat","用户访问日志","访问数据库管理界面页面", "成功");

        $arr = ['所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('group', $arrDic['所在班组']);

        $this->display();
    }
    /** 获取数据库获取数据 */
    public function getData(){
        $queryParam = I('put.');
        $where['db_atpstatus'] = ['exp', 'is null'];
        $db_ischeck = $queryParam['db_ischeck'];
        if(!empty($db_ischeck)) $where['db_ischeck'] = array('like' ,"%$db_ischeck%");

        $db_group = $queryParam['db_group'];
        if(!empty($db_group)) $where['db_group'] = array('like' ,"%$db_group%");

        $db_ip = $queryParam['db_ip'];
        if(!empty($db_ip)) $where['db_ip'] = array('like' ,"%$db_ip%");
        $db_admin=$queryParam['db_admin'];
        if(!empty($db_admin)) {
            $where[0]['db_admin'] = array('eq' ,"$db_admin");
            $where[0]['db_adminb'] = array('eq' ,"$db_admin");
            $where[0]['_logic'] = 'OR';
        }

        $field="db_atpid,db_ip,db_dns,db_adminname,db_type,db_admin,db_adminb,db_adminnameb,db_remark,db_ischeck,db_group";
        $model=M('db_plat');

        $dbPlatInfo=$model
            ->where($where)
            ->field($field)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();

        $count = $model->where($where)->count();

        foreach($dbPlatInfo as  $k => $v){
            $rlxCount = M('checkup')->where("appid = '%s' and atpstatus is null",$v['db_atpid'])->count();
            $dbPlatInfo[$k]['xjCount'] = $rlxCount;
        }

       // print_r($dbPlatInfo);die;

        exit(json_encode(array('total' => $count, 'rows' => $dbPlatInfo)));

    }
    /** 数据库平台添加展示 */
    public function add(){
        addLog("db_plat", "用户访问日志", "访问数据库平台编辑管理界面页面", "成功");

        $id=trim(I('get.db_atpid'));


        if(!empty($id)){
            $field="db_atpid,db_ip,db_adminname,db_type,db_admin,db_adminb,db_adminnameb,db_remark,db_ischeck,db_group";
            $where=[
                'db_atpid'=>$id
            ];
            $model=M('db_plat');
            $data=$model->field($field)->where($where)->find();
            session('list',$data);

            $arr=['数据库类型','所在班组','是否'];
            $arrDic = D('Dic')->getDicValueByName($arr);
            $this->assign('ischeck', $arrDic['是否']);
            $this->assign('group', $arrDic['所在班组']);
            $this->assign('dic_data', $arrDic['数据库类型']);
            $this->assign('data',$data);

            $this->display();
        }else{
            $arr=['数据库类型','所在班组','是否'];
            $arrDic = D('Dic')->getDicValueByName($arr);
            $this->assign('dic_data', $arrDic['数据库类型']);
            $this->assign('ischeck', $arrDic['是否']);
            $this->assign('group', $arrDic['所在班组']);
            $this->display();
        }

    }
    /** 数据库平台添加执行 */
    public function addData(){
        $id=trim(I('post.db_atpid'));
        $date=date("Y-m-d H:i:s",time());

        $dbPlat=M('db_plat');
        $data=I('post.');
        $userModel=M('it_person');
        $admin_name=$userModel->field('realusername')->where("domainusername = '%s'",$data['db_admin'])->getField('realusername');
        $admin_nameb=$userModel->field('realusername')->where("domainusername = '%s'",$data['db_adminb'])->getField('realusername');
        $user = session('user_id');
        $data['db_adminname'] = $admin_name;
        $data['db_adminnameb'] = $admin_nameb;
        if(!empty($id)){
            $where=[
                'db_atpid'=>$id
            ];
            $res = $dbPlat->where("db_ip = '%s' and db_atpid != '".$id."' and db_atpstatus is null",$data['db_ip'])->find();
            if($res){
                exit(makeStandResult(-1, 'ip地址不可重复！'));
            }

            $data = $dbPlat->create($data);
            $list = session('list');
            $content = LogContent($data,$list);

            $data['db_atplastmodifydatetime'] = $data;
            $data['db_atplastmodifyuser'] = $user;
            $res=$dbPlat->where($where)->save($data);
            if($res){
                if(!empty($content)) {
                    addLog('db_plat', '对象修改日志', $content . '成功', '成功', $id);
                }
                exit(makeStandResult(1, '修改成功'));
            }else{
                addLog('db_plat', '对象修改日志', '修改主键为'.$id . '失败', '失败',$id);
                exit(makeStandResult(0, '修改失败'));
            }
        }else{
            $res = $dbPlat->where("db_ip = '%s' and db_atpstatus is null",$data['db_ip'])->find();
            if($res){
                exit(makeStandResult(-1, 'ip地址不可重复！'));
            }
            $db_atpid=makeGuid();
            $data['db_atpid']=$db_atpid;
            $data['db_atpcreatedatetime']=$date;
            $data['db_atpcreatedateuser']=$user;
            $res=$dbPlat->add($data);
            if($res){
                addLog('db_plat', '对象添加日志', '添加主键为'.$data['db_atpid'] . '成功', '成功',$data['db_atpid']);

                exit(makeStandResult(1, '添加成功'));
            }else{
                addLog('db_plat', '对象添加日志', '添加主键为'.$data['db_atpid'] . '失败', '失败',$data['db_atpid']);

                exit(makeStandResult(0, '添加失败'));
            }
        }
    }
    /** 数据库平台删除 */
    public function delData(){
        $id=trim(I('post.db_atpid'));
        $ids = explode(',',$id);
        $model=M('db_plat');
        $data=[
            'db_atpstatus'=>'DEL',
        ];
        $where['db_atpid'] = ['in',$ids];
        $res=$model->where($where)->save($data);
        if($res){
            exit(makeStandResult(1, '删除成功'));
        }else{
            exit(makeStandResult(0, '删除失败'));
        }
    }
    /** 数据库实例添加和修改展示 */
    public function instanceAdd(){
        addLog("", "用户访问日志",  "访问数据库实例添加管理界面页面", "成功");

        $in_atpid=trim(I('get.in_atpid'));
        $model=M('db_plat');
        $inModel=M('db_instance');
        $field="db_atpid,db_type||'-'||db_ip text";
        $infield='in_dbid,in_name,in_atpid,in_useage,in_bz,in_dns';
        $dbWhere['db_atpstatus']=['exp', 'is null'];
        if(!empty($in_atpid)){
            $where['in_atpstatus']=[ 'exp','is null'];
            $where['in_atpid']=[ 'eq',$in_atpid];
            $data=$inModel->field($infield)->where($where)->find();
            session('list',$data);
            $platInfo=$model->field($field)->where($dbWhere)->select();

            $this->assign('platInfo',$platInfo);
            $this->assign('data',$data);
            $this->display();
        }else{
            $platInfo=$model->field($field)->where($dbWhere)->select();
            $this->assign('platInfo',$platInfo);
            $this->display();
        }
    }
    /** 数据库实例展示页面 */
    public function instanceIndex(){
        $model=M('db_plat');
        $where['db_atpstatus'] = ['exp', 'is null'];
//        $filed="db_atpid,db_type||'-'||db_ip||'-'||db_adminname in_dbid";
        $filed="db_atpid,db_type||'-'||db_ip in_dbid";
        $InDbInfo=$model->field($filed)->where($where)->select();

        $this->assign('inDbData',$InDbInfo);
        $this->display();

    }
    /** 获取数据库实例数据 */
    public function inGetData(){
        $queryParam = I('put.');
        $where['in_atpstatus'] = ['exp', 'is null'];
        $where['db_atpstatus'] = ['exp', 'is null'];
        $in_name = strtolower(trim($queryParam['in_name']));
        if(!empty($in_name)) $where['lower(in_name)'] = array('like' ,"%$in_name%");
        $db_atpid=$queryParam['db_atpid'];
        if(!empty($db_atpid)) $where['in_dbid'] = array('eq' ,"$db_atpid");

        $model=M('db_instance');

        $count=$model
            ->join("db_plat on db_atpid = in_dbid","inner")
            ->where($where)->count();
        $InDbInfo=$model
            ->join("db_plat on db_atpid = in_dbid","inner")
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();

        foreach($InDbInfo as $key => $val){
            $InDbInfo[$key]['text'] = $val['db_type'].'-'.$val['db_ip'];
        }
        exit(json_encode(array('total' => $count, 'rows' => $InDbInfo)));

    }
    /** 数据库实例添加和修改执行 */
    public function addInData(){
        $id=trim(I('post.in_atpid'));
        $model=M('db_instance');
        $date=date("Y-m-d H:i:s",time());
        $user = session('user_id');
        $data=I('post.');
        if(!empty($id)){
            $where=[
                'in_atpid'=>$id
            ];
            $res = $model->where("in_dbid = '".$data['in_dbid']."' and in_name = '".$data['in_name']."' and in_atpid != '".$id."' and in_atpstatus is null")->find();
            if($res){
                exit(makeStandResult(-1, '同意平台下，域名不可重复！'));
            }

            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);

            $data['in_atplastmodifydatetime'] = $date;
            $data['in_atplastmodifyuser'] = $user;
            $res=$model->where($where)->save($data);
            if($res){
                if(!empty($content)) {
                    addLog('db_instance', '对象修改日志', $content, '成功', $id);
                }
                exit(makeStandResult(1, '修改成功'));
            }else{
                addLog('db_instance', '对象修改日志',  '修改主键为'.$id ,'失败',$id);

                exit(makeStandResult(0, '修改失败'));
            }


        }else{
            $res = $model->where("in_dbid = '".$data['in_dbid']."' and in_name = '".$data['in_name']."' and in_atpstatus is null")->find();
            if($res){
                exit(makeStandResult(-1, '同意平台下，域名不可重复！'));
            }
            $data['in_atpid']=makeGuid();
            $data['in_atpcreatedatetime']=$date;
            $data['in_atpcreatedateuser']=$user;

            $res=$model->add($data);

            if($res){
                addLog('db_instance', '对象添加日志',  '添加主键为'.$data['in_atpid'] . '成功', '成功',$data['in_atpid']);

                exit(makeStandResult(1, '添加成功'));
            }else{
                addLog('db_instance', '对象添加日志', '添加主键为'.$data['in_atpid'] . '失败', '失败',$data['in_atpid']);

                exit(makeStandResult(0, '添加失败'));
            }
        }
    }
    /** 数据库实例删除 */
    public function delInData(){
        $id=I('post.in_atpid');
        $ids = explode(',',$id);
        $model=M('db_instance');
        $where['in_atpid'] = ['in',$ids];
        $data=[
            'in_atpstatus'=> 'DEL'
        ];
        $res=$model->where($where)->save($data);
        if($res){
            exit(makeStandResult(1, '删除成功'));
        }else{
            exit(makeStandResult(0, '删除失败'));
        }


    }
    /** 数据库表空间展示 */
    public function tablesIndex(){
        $model=M('db_plat');
        $where['db_atpstatus'] = ['exp', 'is null'];
        $field="db_atpid,db_ip||'-'||db_type text";
        $dbInTsData=$model->field($field)->where($where)->select();
        $InTsData=M('db_instance')->field('in_atpid,in_name')->where("in_atpstatus is null")->select();
        $arr = ['所在班组','使用状态(数据库用户)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('status', $arrDic['使用状态(数据库用户)']);
        $this->assign('db_info',$dbInTsData);
        $this->assign('db_ins',$InTsData);
        $this->display();
    }
    /** 数据库表空间添加修改展示 */
    public function tablesAdd(){
        addLog("", "用户访问日志", "访问数据库实例添加管理界面页面", "成功");

        $ts_atpid=trim(I('get.ts_atpid'));
        $Objtype = trim(I('get.type'));
        $model=M('db_tablespace');
        $inModel=M('db_instance');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $field='ts_inid,ts_username,ts_bz,ts_atpid,ts_tablespace,ts_status,ts_dutyman,ts_group,ts_createtime,ts_deletetime,ts_stoptime';
        $dbfield="in_atpid,db_ip||'-'||db_type||'-'||in_name text";
        $db_in_where['in_atpstatus']= ['exp','is null'];
        $db_in_where['db_atpstatus']= ['exp','is null'];
        $db_in_Info=$inModel
            ->join("db_plat on db_atpid = in_dbid","inner")
            ->field($dbfield)
            ->where($db_in_where)
            ->select();
        $arr = ['所在班组','使用状态(数据库用户)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('group', $arrDic['所在班组']);
        $this->assign('status', $arrDic['使用状态(数据库用户)']);


        if(!empty($ts_atpid)){
            $where['ts_atpid'] = ['eq',$ts_atpid];
            $where['ts_atpstatus'] = ['exp','is null'];
            $tsInfo=$model->field($field)->where($where)->find();
            session('list',$tsInfo);

            $userId = $tsInfo['ts_dutyman'];
            if (!empty($userId)) {
                $userName = D('org')->getViewPerson($userId);
                $userMan['name'] = $userName['realusername'] . '(' . $userName['username'] . ')--' . $userName['orgname'];
                $userMan['username'] = $userName['username'];
            }

            $this->assign('dutyman', $userMan);

            $this->assign('Objtype',$Objtype);
            $this->assign('data',$tsInfo);
            $this->assign('dbInInfo',$db_in_Info);
            $this->display();

        }else{
            $this->assign('dbInInfo',$db_in_Info);
            $this->display();
        }
    }
    /** 数据库表空间添加修改执行 */
    public function tablesAddData(){
        $id=trim(I('post.ts_atpid'));
        $model=M('db_tablespace');
        $data=I('post.');
        $user=session('user_id');
        $date=date("Y-m-d H:i:s",time());
        $data['ts_dutydept'] = D('org')->getDeptId($data['ts_dutyman']);
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        if(!empty($id)){
            $where=[
                'ts_atpid'=>$id
            ];
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);

            $data['ts_atplastmodifydatetime'] = $date;
            $data['ts_atplastmodifyuser'] = $user;
            $res=$model->where($where)->save($data);
            if($res){
                if(!empty($content)) {
                    addLog('db_tablespace', '对象修改日志', $content, '成功', $id);
                }
                exit(makeStandResult(1, '修改成功'));
            }else{
                addLog('db_tablespace', '对象修改日志','修改主键为'.$id . '失败','失败',$id);

                exit(makeStandResult(0, '修改失败'));
            }

        }else{

            $in_atpid=makeGuid();
            $data['ts_atpid']=$in_atpid;
            $data['ts_atpcreatedatetime']=$date;
            $data['ts_atpcreatedateuser']=$user;
            $res=$model->add($data);

            if($res){
                addLog('db_instance', '对象添加日志',  '添加主键为'.$in_atpid .'成功', '成功',$in_atpid);

                exit(makeStandResult(1, '添加成功'));
            }else{
                addLog('db_instance', '对象添加日志',  '添加主键为'.$in_atpid . '失败', '失败',$in_atpid);

                exit(makeStandResult(0, '添加失败'));
            }
        }

    }
    /** 数据库表空间获取数据 */
    public function tablesGetData(){
        $queryParam = I('put.');
        $where['ts_atpstatus'] = ['exp', 'is null'];
        $db_atpid = $queryParam['db_atpid'];
        if(!empty($db_atpid)) $where['db_atpid'] = array('like' ,"%$db_atpid%");
        $ts_username=strtolower(trim($queryParam['ts_username']));
        if(!empty($ts_username)) $where['lower(ts_username)'] = array('like' ,"%$ts_username%");
        $ts_tablespace=strtolower(trim($queryParam['ts_tablespace']));
        if(!empty($ts_tablespace)) $where['lower(ts_tablespace)'] = array('like' ,"%$ts_tablespace%");
        $ts_status=trim($queryParam['ts_status']);
        if(!empty($ts_status)) $where['ts_status'] = array('like' ,"%$ts_status%");
        $ts_dutyman=trim($queryParam['ts_dutyman']);
        if(!empty($ts_dutyman)) $where['ts_dutyman'] = array('like' ,"%$ts_dutyman%");
        $ts_group=trim($queryParam['ts_group']);
        if(!empty($ts_group)) $where['ts_group'] = array('like' ,"%$ts_group%");
        $in_atpid=$queryParam['in_atpid'];
        if(!empty($in_atpid)) $where['in_atpid'] = array('like' ,"%$in_atpid%");


        $db_sx = trim($queryParam['db_sx']);
        if (!empty($db_sx)){
            $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '数据库用户'")->select();
            $rlx_zyids = array_column($limit, 'rlx_zyid');
            if($db_sx == '1'){
                $where['ts_atpid'] = ['in', $rlx_zyids];
            }else{
                $where['ts_atpid'] = ['not in', $rlx_zyids];
            }
        }

        $model=M('db_tablespace');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $field="db_atpid,db_ip||'-'||db_type text,in_atpid,in_name,ts_atpid,ts_username,ts_bz,ts_tablespace,in_dns,ts_status,ts_dutyman,ts_group,ts_createtime,ts_deletetime,ts_stoptime";
        $dbInTsData=$model
            ->join("db_instance on in_atpid = ts_inid","inner")
            ->join("db_plat on db_atpid = in_dbid","inner")
            ->field($field)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count=$model
            ->join("db_instance on in_atpid = ts_inid","inner")
            ->join("db_plat on db_atpid = in_dbid","inner")
            ->field($field)
            ->where($where)
            ->count();

        foreach($dbInTsData as $k=>$v){
            $rlxCount = M('it_relationx')->where("rlx_zyid = '%s' and rlx_atpstatus is null",$v['ts_atpid'])->count();
            $dbInTsData[$k]['appCount'] = $rlxCount;
            //责任人
            if (!empty($v['ts_dutyman'])) {
                $userName = D('org')->getViewPerson($v['ts_dutyman']);
                $dbInTsData[$k]['ts_dutyman'] = $userName['realusername'];
            }
        }

        exit(json_encode(array('total' => $count, 'rows' => $dbInTsData)));
    }
    /** 数据库表空间删除数据 */
    public function tablesDelData(){
        $id = I('post.ts_atpid');
        $ids = explode(',',$id);
        $model=M('db_tablespace');
        $where['ts_atpid'] = ['in',$ids];
        $data=[
            'ts_atpstatus'=> 'DEL'
        ];
        $res=$model->where($where)->save($data);
        $list['rlx_atpstatus']  ='DEL';
        $whered['rlx_zyid'] = ['in',$ids];
        M('it_relationx')->where($whered)->save($list);
        if($res){
            exit(makeStandResult(1, '删除成功'));
        }else{
            exit(makeStandResult(0, '删除失败'));
        }
    }

    /**
     * 当前角色可操作页面集成
     */
    public function frame()
    {
        $powers = D('Admin/RefinePower')->getViewPowers('DbRoleViewConfig');
        $this->assign('powers', $powers);
        $this->display('Universal@Public/roleViewFrame');
    }

    public function geTShiLi(){
        $id = I('post.pid');
        $data = M('db_instance')->where("in_dbid = '%s' and in_atpstatus is null",$id)->select();
        echo json_encode(array('results' => $data,'code'=>1));
    }
}