<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/2
 * Time: 10:05
 */
namespace Demo\Controller;
use Think\Controller;
class BaoXiuController extends Controller {
    public function index(){
        $nameid=I('get.username');
        // print_r($nameid);die;
        // $nameid="mapei";
        $where[0]['t_taskid']=['like','IT-BX-%'];
        $where[0]['t_atpstatus']=['exp','is null'];
        $where[0] = [ "t_bxnameid='".$nameid."' or t_atpcreateuser='".$nameid."'"];
        $zhuangtai=M('task')->field('t_bxstatus')
            ->group('t_bxstatus')
            ->where($where)
            ->select();
        $this->assign('zhuangtai',$zhuangtai);
        $this->assign("nameid",$nameid);
        $this->display();
    }

    public function add(){
        $username=I('get.username');
        $data= M('person')->where("username='%s'",$username)->find();
        $dept= M('depart')->where("id='%s'",$data['orgid'])->find();
        $data['departname']=$dept['fullname'];
//        $data['name']="马培(mapei)";
//        $data['t_name']="马培";
//        $data['t_bxnameid']="mapei";
//        $data['t_depart']="总体部";
        $pos= strripos ( $data['departname'] ,  "-中国航天科技集团公司第五研究院" );
        if ( $pos  ===  false ) {
        } else {
            $data['departname']=substr_replace($data['departname'],'',$pos);
        }
        if(!empty($dept)){
            $data['fullname']=$data['realusername'].'('.$data['username'].')'.'--'.$data['departname'];
        }
        $this->assign("data",$data);
        $this->display();
    }

    public function detail(){
        $id = I('get.id');
        $data=M('task')->where("t_atpid='%s'",$id)->find();
        $dept= M('depart')->where("id='%s'",$data['t_depart'])->find();
        $data['departname']=$dept['name'];
        $detail=M('taskdetail')->where("tl_taskid='%s'",$data['t_taskid'])->order('tl_solvetime asc')->select();
        foreach($detail as $info)
        {
            if(empty($infos))
            {
                $infos=$info['tl_solvetime'].'   '. $info['tl_process'];
            }
            else
            {
                if(strpos($info['tl_process'],'退回')===false&&strpos($info['tl_process'],'受理')!==false) {
                    $shouli=$info['tl_solvetime'].'   '. $info['tl_process'];
                }
                if(strpos($info['tl_process'],'处理完毕')!==false)
                {
                    $wanbi=$info['tl_solvetime'].'   '. $info['tl_process'];
                }
            }
        }
        if(!empty($shouli))
        {
            $infos=$infos.'
'.$shouli;
        }
        if(!empty($wanbi))
        {
            $infos=$infos.'
'.$wanbi;
        }
        $filepath = explode(';',$data['t_bxfilepath']);
        $filename=explode(';',$data['t_bxfilename']);
        $adminfilepath=explode(';',$data['t_bxfilepath_admin']);
        $adminfilename=explode(';',$data['t_bxfilename_admin']);
        $file = [];
        $adminfile = [];
        foreach($filepath as $key=>$val){
            if(empty($val))
            {
                continue;
            }
//            $filename=explode('/',$val);
//            $file[$key]['filename']=$filename[1];
            $file[$key]['filepath']=$val;
        }
        foreach($filename as $key=>$val){
            if(empty($val))
            {
                continue;
            }
            $file[$key]['filename']=$val;
        }
        foreach($adminfilepath as $key=>$val){
            if(empty($val))
            {
                continue;
            }
//            $adminfilename=explode('/',$val);
//            $adminfile[$key]['filename']=$adminfilename[1];
            $adminfile[$key]['filepath']=$val;
        }
        foreach($adminfilename as $key=>$val){
            if(empty($val))
            {
                continue;
            }
            $adminfile[$key]['filename']=$val;
        }
        $this->assign("data",$data);
        $this->assign("file",$file);
        $this->assign("infos",$infos);
        $this->assign("adminfile",$adminfile);
        $this->display();
    }
    public function edit(){

        $this->display();
    }
    public function getbxid()
    {
        $Model = M('task');

        $idlist = array();
        $tmpdate = date("Ymd", time());
        $arrayid = $Model->where("t_taskid like '%IT-BX-$tmpdate%'")->field('t_taskid')->select();
        if ($arrayid == null ||count($arrayid)==0) {
            $workid = 'IT-BX-'. $tmpdate . '-001';
        } else {
            foreach ($arrayid as $key => $value) {
                array_push($idlist,$value['t_taskid']);
            }
            rsort($idlist);
            $tmp = explode("-", $idlist[0]);
            $num = sprintf("%03d",($tmp[3] +1));
            $workid = 'IT-BX-'. $tmpdate . '-' . $num;
        }
        return $workid;
    }

    public function submitData(){
        $tempdata = I('post.');
        $upload = new \Think\Upload();
        $upload->maxSize = 10485760;
        $upload->exts = array('xls','xlsx','jpg','png','rar','txt','doc','docx','bmp');
        $bxid= $this->getbxid();
        $upload->rootPath = './Public/uploads/';
        if(!file_exists( './Public/uploads/'.$bxid)) {
            mkdir('./Public/uploads/'.$bxid);
        }
        $upload->savePath = '';
        $upload->subName =$bxid;

        $info = $upload->upload();
        $data['t_atpid']=makeGuid();
        $data['t_taskid']=$bxid;
        $data['t_rwid']=$bxid;
        $person= M('person')->where("username='%s'",$tempdata['t_user'])->find();


        $data['t_name']=$person['realusername'];
        $data['t_bxnameid']=$person['username'];
        $data['t_depart']=$person['orgid'];
        $data['t_description']=$tempdata['t_description'];
        $data['t_phone']=$tempdata['t_phone'];
        $data['t_bxstatus']='等待受理';
        $data['t_rwtype']='BX';
        $data['t_atplastmodifyuser']='报修系统';
        $data['t_atpcreateuser']=$tempdata['t_bxnameid'];
        $data['t_atpcreatedatetime'] = date('Y-m-d H:i:s', time());
        $data['t_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
        for ($i = 0; $i < count($tempdata['bxsort']); $i++) {

            $data['t_bxfilepath']=$data['t_bxfilepath']. $info[$i]['savepath']. $info[$i]['savename'].';';
            $data['t_bxfilename']=$data['t_bxfilename']. $info[$i]['name'].';';
        }

        $tldata['tl_atpid']=makeGuid();
        $tldata['tl_taskid']=$bxid;
        $tldata['tl_solvetime']=date('Y-m-d H:i:s', time());
        $tldata['tl_process']="您的在线报修单正在等待信息中心受理";
        M()->startTrans();
        try
        {
            M('task')->add($data);
            M('taskdetail')->add($tldata);
            M()->commit();
            exit(makeStandResult(0, json_encode([["提交成功"]])));
        }
        catch(Exception $e){
            M()->rollback();
            exit(makeStandResult(1, json_encode([["提交失败"]])));
        }
    }


    public function addData(){

    }
    public function getData(){
        $queryParam = json_decode(file_get_contents( "php://input"), true);
        $starttime = trim($queryParam['starttime']);
        $endtime = trim($queryParam['endtime']);
        $taskid = trim($queryParam['taskid']);
        $bxstatus = trim($queryParam['bxstatus']);
        $description = trim($queryParam['description']);
        $name=trim($queryParam['id']);
//        $where[0]['t_bxnameid'] = ['eq', $name];
        $where[0] = [ "t_bxnameid='".$name."' or t_atpcreateuser='".$name."'"];
        $where[0]['t_atpstatus']=['exp','is null'];
        $model=M('task');
        if (!empty($starttime))
        {
            $where[0]['t_atpcreatedatetime'] = ['egt', $starttime];
        }
        if (!empty($endtime))
        {
            $where[0]['t_atpcreatedatetime'] = ['lt', $endtime];
        }
        if (!empty($taskid))
        {
            $where[0]['t_taskid'] = ['like', "%$taskid%"];
        }
        if (!empty($description))
        {
            $where[0]['t_description'] = ['like', "%$description%"];
        }
        if (!empty($bxstatus))
        {
            $where[0]['t_bxstatus'] = ['like', "%$bxstatus%"];
        }
        $data=$model
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->limit($queryParam['limit'],$queryParam['offset'])
            ->select();
//        print_r($model->_sql());die;
        $count = $model->where($where)->count();


        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    public function deletetask(){
        $atpid = I('post.id');
        $taskid = I('post.taskid');
        $status = I('post.status');
        $username = I('post.username');

        $task['t_atpstatus']='del';

        $detail['tl_atpstatus']='del';

        M()->startTrans();
        try{
            $user=M('person')->where("username='%s'",$username)->find();
            M('task')->where("t_taskid='%s'",$taskid)->save($task);
            M('taskdetail')->where("tl_taskid='%s'",$taskid)->save($detail);
            $this->recordLog('del','bxd','删除了报修单; '.$taskid.'当前状态为'.$status,'it_task',$taskid,'',$user['domainusername'],$user['realusername']);
            M()->commit();
            echo 'ok';
        }catch(Exception $e){
            echo $e;
            M()->rollback();
        }
    }
    public function recordLog($type, $module, $content,$table = '',$atpid = '',$beizhu='',$username,$realname){
        $optime = date('Y-m-d H:i:s',time());
        $data = array(
            'l_atpid'      => makeGuid(),
            'l_optime'     => $optime,
            'l_ipaddress'  => get_client_ip(),
            'l_optype'     => $type,
            'l_opuserid'   => $username,
            'l_opusername' => $realname,
            'l_modulename' => $module,
            'l_detail'     => $content,
            'l_tablename'  => $table,
            'l_mainid'     => $atpid,
            'l_beizhu'     => $beizhu
        );
        $res = M('log')->add($data);
        return $res;
    }
    public function assignuser(){
        $q = $_POST['data']['q'];
        $Model = M();
        $sql_select="select  p.username id,p.realusername||'('||p.username||')--'||d.fullname text from  it_person p,it_depart d  where
(p.username like '%".$q."%' or p.realusername like '%".$q."%') and p.orgid = d.id";
        $result=$Model->query($sql_select);
        foreach($result as &$val)
        {
            $pos= strripos ( $val['text'] ,  "-中国航天科技集团公司第五研究院" );
            if ( $pos  ===  false ) {
            } else {
                $val['text']=substr_replace($val['text'],'',$pos);
            }
        }
        echo json_encode(array('q' =>$q, 'results' => $result));
    }
    public function download()
    {
        $fileid=I('get.v');

        $filename=I('get.name');
        if(!file_exists( './Public/uploads/'.$fileid)) {
            echo "<script>alert('文件不存在');history.go(-1);</script>";
        }
        else
        {
            $contents=file_get_contents( './Public/uploads/'.$fileid);
            //告诉浏览器这是一个文件流格式的文件
            Header("Content-type:application/octet-stream");
            //请求范围的度量单位
            Header("Accept-Ranges:bytes");
            //Content-Length是指定包含于请求或响应中数据的字节长度
            Header("Accept-Length:".filesize( './Public/uploads/'.$fileid));
            //用来告诉浏览器，文件时可以当做附件被下载
            $filename= changeCoding($filename,'utf-8','gbk');
            Header("Content-Disposition:attachment;filename=".$filename);
            echo $contents;

        }
    }
}