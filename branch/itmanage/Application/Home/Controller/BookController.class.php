<?php
namespace Home\Controller;
use Think\Controller;
class BookController extends BaseController {
    public function index(){
        addLog("","用户访问日志","","访问管理员手册维护页面","成功");
        $arrDic = D('Admin/Dictionary')->getDicValueByName('所在班组');
        $this->assign('dic',$arrDic);
        $this->display();
    }

    /**
     * 获取数据
     */
    public function getData(){
        $queryParam = I('put.');
        $where = '';
        $roles = session('roleids');
        $account = session('user_account');
        $roles = explode(',',$roles);
        //TF702A909E1D0446F917178EB 室领导，T886B426D95924386A24DA736 管理员
        $allrange = ['TF702A909E1D0446F917178EB','T886B426D95924386A24DA736'];
        //T886B426D95924386A24DA736 组长
        $grouprange = ['T83F3FD07E66A4BCC8E9D5686'];
        $appname = strtolower(trim($queryParam['appname']));
        $user = trim($queryParam['user']);
        $group = trim($queryParam['group']);
        if(!empty($appname)) {
            $where["lower(amd_appname)"] = ['like', "%$appname%"];
        }
        if(!empty($group)) {
            $where['amd_appgroup'] = ['EQ', "$group"];
        }
        if(!empty($user)) {
            $user = M('sysuser')->where("user_id = '%s'",$user)->getfield('user_name');
            $where[0]['amd_admin1'] = ['EQ', "$user"];
            $where[0]['amd_admin2'] = ['EQ', "$user"];
            $where[0]['_logic'] = 'OR';
        }
//        if(!empty(array_intersect($roles,$allrange))){
//
//        }else if(!empty(array_intersect($roles,$grouprange))){
//            $yserGroup = M('sysuser')->where("user_name = '%s'",$account)->getField('user_group');
//            $where['amd_appgroup'] = ['EQ', "$yserGroup"];
//        }else{
//            $where[0]['amd_admin1'] = ['EQ', "$account"];
//            $where[0]['amd_admin2'] = ['EQ', "$account"];
//            $where[0]['_logic'] = 'OR';
//        }
        $model = M('v_appmanagedoc t');
        $data = $model->field("t.*,(case when amd_admin1 ='".$account."' then 1 
 when amd_admin2 ='".$account."' then 2
  else 3 end) sort1")->where($where)->limit($queryParam['offset'], $queryParam['limit'])
//            ->order('sort1,amd_admin1')
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->select();
        $count = $model->where($where)->count();

     // $itemModel = M('it_appmanagedoc_item');
      // foreach($data as $key =>$value){
      //    $countGl = $itemModel->query("select count(*) as count from it_appmanagedoc_item where amdi_amdid = '".$value['amd_atpid']."' and amdi_itemtype = '管理员'");
      //    $countYh = $itemModel->query("select count(*) as count from it_appmanagedoc_item where amdi_amdid = '".$value['amd_atpid']."' and amdi_itemtype = '用户'");
      //     $data[$key]['countgl'] = $countGl[0]['count'];
      //     $data[$key]['countyh'] = $countYh[0]['count'];
      //  }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    public function word(){
        $id = I('get.id');
        $model = M('it_appmanagedoc');
        $path = $model ->where("amd_atpid = '%s'",$id)->getField('amd_docpath');
        $public  = './Public/upload/words/模板文件.docx';
        $pathx = iconv('UTF-8','GBK',$path);
        $public = iconv('UTF-8','GBK',$public);
        $isExist = file_exists("./Public".$pathx);
        $paths = './Public'.$pathx;
        if($isExist){
            R('Universal/Office/index', [__ROOT__ . '/Public'.$path, true]);
        }else{
            if(copy($public,$paths)){
                R('Universal/Office/index', [__ROOT__ . '/Public'.$path, true]);
            }else{
                echo '拷贝失败,请稍后重试！！！';
            }
        }
    }

    public function knowledge(){
        $id = trim(I('get.id'));
        $version = trim(I('get.version'));
        $arrDic = D('Admin/Dictionary')->getDicValueByName(['所在班组','知识项应用对象']);
        $model = M('it_appmanagedoc');
        $list = $model->field('amd_appname,amd_appgroup,amd_atpid')->where("amd_atpid = '%s'",$id)->find();
        $this->assign('amd_atpid',$list['amd_atpid']);
        $this->assign('amd_appname',$list['amd_appname']);
        $this->assign('amd_appgroup',$list['amd_appgroup']);
        $this->assign('dic',$arrDic['所在班组']);
        $this->assign('object',$arrDic['知识项应用对象']);
        $this->assign('version',$version);
        $this->assign('amdid',$id);
        $this->display();
    }

    public function getDataItem(){
//        $queryParam = I('put.');
        $queryParam = json_decode(file_get_contents("php://input"), true);
//        print_r($queryParam);die;
        $where = '';
        $appname = trim($queryParam['appname']);
        $version = trim($queryParam['version']);
        $group = trim($queryParam['group']);
        $code = trim($queryParam['code']);
//        $amdid = trim($queryParam['amdid']);
//        if(!empty($amdid)){
//            $where .="  and amd_atpid = '$amdid'";
//        }
        if(!empty($appname)){
            $where .="  and amdi_amdid = '$appname'";
        }
        if(!empty($version)){
            $where .="  and amdi_itemtype = '$version'";
        }
        if(!empty($group)){
            $where .="  and amd_appgroup = '$group'";
        }
        if(!empty($code)){
            $where .="  and (amdi_itemname like '%$code%' or amdi_itemcode like '%$code%')";
        }
        $coon = C('OCI');
        $sql    = "select * from it_appmanagedoc_item i inner join it_appmanagedoc d on d.amd_atpid = i.amdi_amdid where 1=1 ".$where;
        $sql_select = $sql. " order by ".$queryParam['sort'].'  '.$queryParam['sortOrder'];
        $sql_select = buildSqlPage($sql_select, $queryParam['offset'], $queryParam['limit']);
        $oci_rs = oci_parse($coon,$sql_select);
        oci_execute($oci_rs,OCI_DEFAULT);
        $data                             = [];
        $key = 0;
        while($row = oci_fetch_array($oci_rs,OCI_ASSOC)){
            $data[$key]['amdi_atpid'] = $row['AMDI_ATPID'];
            $data[$key]['amd_appname'] = $row['AMD_APPNAME'];
            $data[$key]['amdi_amdid'] = $row['AMDI_AMDID'];
            $data[$key]['amdi_itemname'] = $row['AMDI_ITEMNAME'];
            $data[$key]['amdi_itemtype'] = $row['AMDI_ITEMTYPE'];
            $data[$key]['amdi_itemmodel'] = $row['AMDI_ITEMMODEL'];
            $data[$key]['amdi_itemkeywords'] = $row['AMDI_ITEMKEYWORDS'];
            $data[$key]['amdi_itemtext'] = $row['AMDI_ITEMTEXT'];
            $data[$key]['amdi_itemtextlink'] = $row['AMDI_ITEMTEXTLINK'];
            $data[$key]['amdi_itemtextcontent'] = $row['AMDI_ITEMTEXTCONTENT'];
            $data[$key]['amdi_istop'] = $row['AMDI_ISTOP'];
            $data[$key]['amdi_itemcode'] = $row['AMDI_ITEMCODE'];
            $amdi_itemcontent = $row['AMDI_ITEMCONTENT'];

            $amdi_itemcontent = $amdi_itemcontent->load();
            $data[$key]['amdi_itemcontent'] = htmlspecialchars_decode($amdi_itemcontent);
            $key++;

        }
        $count = $count = count(M('it_appmanagedoc_item')->query($sql));

        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    public function add(){
        $arrDic = D('Admin/Dictionary')->getDicValueByName(['知识项应用对象','是否']);
        $appname = I('get.appname');
        $appname = M('it_appmanagedoc')->where("amd_atpid = '%s'",$appname)->getField('amd_appname');
        $version = I('get.version');
        $amdid = I('get.amdid');
        $id =   I(('get.id'));
        if(!empty($id)){
            $coon = C('OCI');
            $sql    = "select * from it_appmanagedoc_item where amdi_atpid = '$id'";
            $oci_rs = oci_parse($coon,$sql);
            oci_execute($oci_rs,OCI_DEFAULT);
            while($row = oci_fetch_array($oci_rs,OCI_ASSOC)){
                $matterInfo['amdi_atpid'] = $row['AMDI_ATPID'];
                $matterInfo['amdi_amdid'] = $row['AMDI_AMDID'];
                $matterInfo['amdi_itemname'] = $row['AMDI_ITEMNAME'];
                $matterInfo['amdi_itemtype'] = $row['AMDI_ITEMTYPE'];
                $matterInfo['amdi_itemmodel'] = $row['AMDI_ITEMMODEL'];
                $matterInfo['amdi_itemkeywords'] = $row['AMDI_ITEMKEYWORDS'];
                $matterInfo['amdi_itemtext'] = $row['AMDI_ITEMTEXT'];
                $matterInfo['amdi_itemtextlink'] = $row['AMDI_ITEMTEXTLINK'];
                $matterInfo['amdi_itemtextcontent'] = $row['AMDI_ITEMTEXTCONTENT'];
                $matterInfo['amdi_istop'] = $row['AMDI_ISTOP'];
                $matterInfo['amdi_itemcode'] = $row['AMDI_ITEMCODE'];
                $amdi_itemcontent = $row['AMDI_ITEMCONTENT'];
                $amdi_itemcontent = $amdi_itemcontent->load();
                $matterInfo['amdi_itemcontent'] = htmlspecialchars_decode($amdi_itemcontent);


            }
            $this->assign('data',$matterInfo);
        }
        $this->assign('appname',$appname);
        $this->assign('version',$version);
        $this->assign('amdid',$amdid);
        $this->assign('ying',$arrDic['知识项应用对象']);
        $this->assign('is',$arrDic['是否']);
        $this->display();
    }

    /**
     * Z知识项内容提交保存
     */
    public function Mattersubmit(){
        $amdi_itemmodel   = I('post.amdi_itemmodel');
        $amdi_itemname    = I('post.amdi_itemname');
        $amdi_itemtype    = I('post.amdi_itemtype');
        $amdi_itemkeywords    = I('post.amdi_itemkeywords');
        $amdi_istop    = I('post.amdi_istop');
        $amdi_itemtext    = I('post.amdi_itemtext');
        $amdi_itemtextlink    = I('post.amdi_itemtextlink');
        $amdi_itemtextcontent    = I('post.amdi_itemtextcontent');
        $amdi_itemcontent    = I('post.amdi_itemcontent');
        $amdi_amdid    = I('post.amdi_amdid');
        $amdi_atpid    = I('post.amdi_atpid');
        //$coon = C('OCI');
        $coon = C('OCI');
        $username = session('domainusername');
        $now      = date('Y-m-d H:i:s');
        $res = "select * from it_appmanagedoc_item order by amdi_itemcode desc";
        $res = M('it_appmanagedoc_item')->query($res);
        $ress=mb_substr($res[0]['amdi_itemcode'],1,6,'utf-8');
        $num=$ress+1;
        $val=sprintf("%06d",$num);
        $amdi_itemcode='Q'.$val;
        if(empty($amdi_atpid)){
            $amdi_atpid = makeGuid();
            $sql = "insert into it_appmanagedoc_item (amdi_atpid,amdi_atpcreatedatetime,amdi_atpcreateuser,amdi_itemmodel,amdi_itemname,amdi_itemtype,amdi_itemkeywords,amdi_itemtext,amdi_itemtextlink,amdi_itemtextcontent,amdi_amdid,amdi_istop,amdi_itemcode,amdi_itemcontent) values ('$amdi_atpid','".$now."','$username','$amdi_itemmodel','$amdi_itemname','$amdi_itemtype','$amdi_itemkeywords','$amdi_itemtext','$amdi_itemtextlink','$amdi_itemtextcontent','$amdi_amdid','$amdi_istop','$amdi_itemcode', EMPTY_CLOB()) RETURNING amdi_itemcontent INTO :myclob";

            $stmt = oci_parse($coon, $sql);
            $clob = oci_new_descriptor($coon, OCI_D_LOB);
            oci_bind_by_name($stmt, ":myclob", $clob, -1, OCI_B_CLOB);
            oci_execute($stmt, OCI_DEFAULT);
            $res = $clob->save($amdi_itemcontent);
            oci_commit($coon);
            if($res){
                exit(makeStandResult(0, '添加成功！'));
            }else{
                exit(makeStandResult(1, '添加失败！'));
            }
        }else{
            $sql  = "update it_appmanagedoc_item set amdi_itemcontent =  EMPTY_CLOB(),amdi_atplastmodifydatetime='".$now."',amdi_atplastmodifyuser='$username',amdi_itemmodel='$amdi_itemmodel',amdi_itemname='$amdi_itemname',amdi_itemtype='$amdi_itemtype',amdi_itemkeywords='$amdi_itemkeywords',amdi_itemtext='$amdi_itemtext',amdi_itemtextlink='$amdi_itemtextlink',amdi_itemtextcontent='$amdi_itemtextcontent',amdi_amdid='$amdi_amdid',amdi_istop='$amdi_istop'  where amdi_atpid = '$amdi_atpid' RETURNING amdi_itemcontent INTO :myclob ";
            $stmt = oci_parse($coon, $sql);
            $clob = oci_new_descriptor($coon, OCI_D_LOB);
            oci_bind_by_name($stmt, ":myclob", $clob, -1, OCI_B_CLOB);
            oci_execute($stmt, OCI_DEFAULT);
            $res = $clob->save($amdi_itemcontent);
            oci_commit($coon);
            if($res){
                exit(makeStandResult(0, '修改成功！'));
            }else{
                exit(makeStandResult(1, '修改失败！'));
            }
        }
    }

    /**
     * 删除数据
     */
    public function delData()
    {
        $id = trim(I('post.amdi_atpid'));
        if (empty($id)) exit(makeStandResult(-1, '参数缺少'));
        $where = [];
        $id = explode(',', $id);
        $where['amdi_atpid'] = ['in', $id];
        $model = M('it_appmanagedoc_item');

        $res = $model->where($where)->delete();
        if ($res) {
            exit(makeStandResult(1, '删除成功'));
        } else {
            exit(makeStandResult(1, '删除成功'));
        }
    }



    /**
     * 连接小Q\数据库
     */
    public function addQ(){

        //获取小Q数据库全部知识项id
        $Model = M('tsc_solution','','q');
        $data  = $Model->getField('id',true);

        $ids = array_values($data);

       // print_r($ids);die;

        $res = M('it_appmanagedoc_item','','appmanagedocclob')
            ->join('it_appmanagedoc ON it_appmanagedoc_item.amdi_amdid =it_appmanagedoc.amd_atpid ')
            ->select();

        foreach($res  as $k => $v ){
            $arr = [];
            $id =  $v['amdi_itemcode'];
            //当知识项为管理员属性时solution_level字段为1，当知识项为普通用户属性时solution_level字段为0；
            if($v['amdi_itemtype'] === '用户'){
                $user = 0;
            }
            if($v['amdi_itemtype'] === '管理员'){
                $user = 1;
            }
            //当知识项amdi_istop为“是”则写入1，amdi_istop为“否”则写入0；
            if($v['amdi_istop'] === '是'){
                $amdi_istop = 1;
            }

            if($v['amdi_istop'] === '否'){
                $amdi_istop = 0;
            }
            $arr['owner'] = $v['amd_admin1'].';'.$v['amd_admin1'];
            //print_r( $arr['owner'] );die;
            $arr['is_important']= $amdi_istop;
            $arr['solution_level'] = $user;
            $arr['system_name'] = $v['amd_appname'];
            $arr['tags'] = $v['amd_appname'];
            $arr['solution_detail_url'] = $v['amdi_itemcode']; //编号
            $arr['solution_name'] = ($v['amdi_itemname']); //知识项名字
            $arr['search_content'] = $v['amdi_itemkeywords'];//关键字
            $arr['solution_answer_url_text'] = $v['amdi_itemtext'];  //链接文字
            $arr['solution_answer_url'] = $v['amdi_itemtextlink']; //链接文字地址
            $arr['solution_description']  = $v['amdi_itemtextcontent']; //文字解决方案
            $amdi_itemcontent= $v['amdi_itemcontent'];
            $amdi_itemcontent = str_replace('/itmanage/','http://10.78.72.240:8081/itmanage/',$amdi_itemcontent);
            $arr['solution_text']= $amdi_itemcontent;
            $arr['is_search']= 1;
            $arr['is_private']= 0;


            //判断小Q数据库是否存在运维系统数据
            if(in_array($id,$ids)){
                $arrs = $Model->create($arr);

                $res = $Model->where("id = '%s'", $id)->save($arrs);
            }else{
                $arr['id'] =  $v['amdi_itemcode'];
                $res = $Model->add($arr);

            }
        }

       if($res) {
                exit(makeStandResult(0, '添加成功'));
            } else {
                exit(makeStandResult(1, '添加失败'));
            }
    }
}