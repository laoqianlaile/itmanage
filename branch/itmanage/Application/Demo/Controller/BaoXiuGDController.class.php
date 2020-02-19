<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/2
 * Time: 10:05
 */
namespace Demo\Controller;
use Think\Controller;
class BaoXiuGDController extends BaseController {
    public function index(){
        $where[0]['t_taskid']=['like','IT-BX-%'];
        $where[0]['t_atpstatus']=['exp','is null'];
        $yixian=M('ywperson')->where("yw_type='一线'")->select();
        $erxian=M('ywperson')->where("yw_type='二线'")->select();

        $zhuangtai=M('task')->field('t_bxstatus')
            ->group('t_bxstatus')
            ->where($where)
            ->select();
            // var_dump($zhuangtai);die;
        $this->assign('zhuangtai',$zhuangtai);
        $this->assign('yixian',$yixian);
        $this->assign('erxian',$erxian);
        $this->display();
    }

    public function add(){

        $this->display();
    }

    public  function zhuanpai()
    {
        $id=I('get.id');
        $data=M('task')->where("t_atpid='%s'",$id)->find();
        $zhuanpaidata=M('ywperson')->select();
        $detail=M('taskdetail')->where("tl_taskid='%s'",$data['t_taskid'])->order('tl_solvetime asc')->select();
        foreach($detail as $info)
        {
            if(empty($infos))
            {
                $infos=$info['tl_solvetime'].'   '. $info['tl_process'];
            }
            else
            {
                $infos=$infos.'
'.$info['tl_solvetime'].'   '. $info['tl_process'];
            }

        }
        $this->assign("infos",$infos);
        $this->assign('id',$id);
        $this->assign('data',$data);
        $this->assign('zhuanpaidata',$zhuanpaidata);
        $this->display();
    }
    public function tuihui(){
        $id=I('get.id');
        $data=M('task')->where("t_atpid='%s'",$id)->find();
        $detail=M('taskdetail')->where("tl_taskid='%s'",$data['t_taskid'])->order('tl_solvetime asc')->select();
        foreach($detail as $info)
        {
            if(empty($infos))
            {
                $infos=$info['tl_solvetime'].'   '. $info['tl_process'];
            }
            else
            {
                $infos=$infos.'
'.$info['tl_solvetime'].'   '. $info['tl_process'];
            }

        }
        $this->assign("infos",$infos);
        $this->assign('id',$id);
        $this->assign('data',$data);
        $this->display();
    }

    public function detail(){

        $id = I('get.id');
        $data=M('task')->where("t_atpid='%s'",$id)->find();
        $dept= M('depart')->where("id='%s'",$data['t_depart'])->find();
        $detail=M('taskdetail')->where("tl_taskid='%s'",$data['t_taskid'])->order('tl_solvetime asc')->select();
        foreach($detail as $info)
        {
            if(empty($infos))
            {
                $infos=$info['tl_solvetime'].'   '. $info['tl_process'];
            }
            else
            {
                $infos=$infos.'
'.$info['tl_solvetime'].'   '. $info['tl_process'];
            }

        }
        $filepath = explode(';',$data['t_bxfilepath']);
        $filename=explode(';',$data['t_bxfilename']);
        $file = [];
        foreach($filepath as $key=>$val){
            if(empty($val))
            {
                continue;
            }
            $file[$key]['filepath']=$val;
        }
        foreach($filename as $key=>$val){
            if(empty($val))
            {
                continue;
            }
            $file[$key]['filename']=$val;
        }
        $data['departname']=$dept['name'];
        $this->assign("data",$data);
        $this->assign("infos",$infos);
        $this->assign("file",$file);
        $this->display();
    }
    public function edit(){
        $id = I('get.id');
        $data=M('task')->where("t_atpid='%s'",$id)->find();
        $dept= M('depart')->where("id='%s'",$data['t_depart'])->find();
        $filepath = explode(';',$data['t_bxfilepath']);
        $filename=explode(';',$data['t_bxfilename']);
        $file = [];
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
        if($data['t_bxstatus']=='等待受理'||$data['t_bxstatus']=='等待受理（转派）')
        {
            $tldata['tl_atpid']=makeGuid();
            $tldata['tl_taskid']=$data['t_taskid'];
            $tldata['tl_solvetime']=date('Y-m-d H:i:s', time());
            $tldata['tl_process']="您的在线报修单已由".session('realusername').'受理';
            M()->startTrans();
            try
            {
                if($data['t_bxstatus']=='等待受理')
                {
                    $data['t_person']=session('username');
                    $data['t_bxstatus']='已受理';
                }else if($data['t_bxstatus']=='等待受理（转派）'){
                    $data['t_person2']=session('username');
                    $data['t_bxstatus']='已受理（转派）';
                }
                if($data['t_bxcount']>0)
                {
                    $data['t_bxcount']+=1;
                }
                else
                {
                    $data['t_bxcount']=1;
                }
                M('taskdetail')->add($tldata);
                M('task')->where("t_atpid='%s'",$id)->save($data);
                M()->commit();
            }
            catch(Exception $e){
                M()->rollback();
            }
        }
        $detail=M('taskdetail')->where("tl_taskid='%s'",$data['t_taskid'])->order('tl_solvetime asc')->select();
        foreach($detail as $info)
        {
            if(empty($infos))
            {
                $infos=$info['tl_solvetime'].'   '. $info['tl_process'];
            }
            else
            {
                $infos=$infos.'
'.$info['tl_solvetime'].'   '. $info['tl_process'];
            }

        }
        $this->assign("infos",$infos);
        $data['departname']=$dept['name'];
        $this->assign("data",$data);
        $this->assign("file",$file);
        $this->display();
    }
    public function submitData(){

//       print_r(session('realusername'));die;

        $tempdata = I('post.');
        $data=M('task')->where("t_atpid='%s'",$tempdata['t_atpid'])->find();
        $data['t_bxsolution']=$tempdata['t_bxsolution'];
        if($data['t_bxstatus']=='已受理')
        {
            $data['t_person']=session('username');
        }else if($data['t_bxstatus']=='已受理（转派）'){
            $data['t_person2']=session('username');
        }

        $data['t_bxstatus']='处理完毕';


        $data['t_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());

        if($data['t_bxcount']>0)
        {
            $data['t_bxcount']+=1;
        }
        else
        {
            $data['t_bxcount']=1;
        }
        $tldata['tl_atpid']=makeGuid();
        $tldata['tl_taskid']=$data['t_taskid'];
        $tldata['tl_solvetime']=date('Y-m-d H:i:s', time());
        $tldata['tl_process']="您的在线报修单已由".session('realusername').'处理完毕';
        M()->startTrans();
        try
        {
            M('task')->where("t_atpid='%s'",$tempdata['t_atpid'])->save($data);
            M('taskdetail')->add($tldata);
            M()->commit();
            exit(makeStandResult(0, json_encode([["处理成功"]])));
        }
        catch(Exception $e){
            M()->rollback();
            exit(makeStandResult(1, json_encode([["处理失败"]])));
        }

    }


    public function addData(){

    }
    public function getData(){
        $queryParam = json_decode(file_get_contents( "php://input"), true);
        $thisusername=session('username');

        $ywperson= M('ywperson')->where("yw_account='%s'",$thisusername)->find();
        if($ywperson['yw_type']=='一线')
        {

        }
        else
        {
            $where[0]['t_person2']=['eq',$thisusername];
        }
        $starttime = trim($queryParam['starttime']);
        $endtime = trim($queryParam['endtime']);
        $taskid = trim($queryParam['taskid']);
        $bxstatus = trim($queryParam['bxstatus']);
        $realname = trim($queryParam['realname']);
        $depart = trim($queryParam['depart']);
        $yixian = trim($queryParam['yixian']);
        $erxian = trim($queryParam['erxian']);
        $model=M('task');
        $where[0]['t_taskid']=['like','IT-BX-%'];
        $where[0]['t_atpstatus']=['exp','is null'];
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
            $where[0]['t_taskid'] = [['like', "%$taskid%"],['like','IT-BX-%'],'and'];
        }
        if (!empty($bxstatus))
        {
            $where[0]['t_bxstatus'] = ['like', "%$bxstatus%"];
        }
        if (!empty($yixian))
        {
            $where[0]['t_person'] = ['like', "%$yixian%"];
        }
        if (!empty($erxian))
        {
            $where[0]['t_person2'] = ['like', "%$erxian%"];
        }
        if (!empty($realname))
        {
            $where[0]['t_name'] = ['eq', $realname];
        }
        if (!empty($depart))
        {
            $dd= M('depart')->field('id')->where("fullname like '%$depart%'")->select();
            $fullnames='';
            foreach($dd as $val)
            {
                if(empty($fullnames))
                {
                    $fullnames=$val['id'];
                }
                else
                {
                    $fullnames=$fullnames.','.$val['id'];
                }
            }
             $where[0]['t_depart'] = ['in', $fullnames];
        }
        if (!empty($problemtype))
        {
            $where[0]['t_problemtype'] = ['eq', $problemtype];
        }
        $data=$model->field('t_atpid,t_taskid,t_name,name,fullname,t_atpcreatedatetime,t_bxstatus,t_problemtype,t_person,t_person2,a.yw_name person,b.yw_name person2,t_bxcount')
            ->where($where)
            ->join('it_depart on it_depart.id=it_task.t_depart','left')
            ->join('it_ywperson a on a.yw_account=it_task.t_person','left')
            ->join('it_ywperson b on b.yw_account=it_task.t_person2','left')
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->limit($queryParam['limit'],$queryParam['offset'])
            ->select();
//        print_r($data);die;
//        print_r($model->_sql());die;
        foreach($data as &$tempval)
        {
            if($tempval['t_person2']==$thisusername) {
                $tempval['zhuanjiao']=1;
            }
            else {
                $tempval['zhuanjiao']=0;
            }
            if($tempval['t_person']==$thisusername) {
                $tempval['shouli']=1;
            }
            else {
                $tempval['shouli']=0;
            }
        }
//        dump($data);die;
        $count = $model->where($where)->count();


        echo json_encode(array( 'total' => $count,'rows' => $data));
    }


    public  function tuihuisubmit(){
        $tempdata = I('post.');
        $data=M('task')->where("t_atpid='%s'",$tempdata['t_atpid'])->find();
        $data['t_bxstatus']='等待受理';
        $data['t_person2']='';

        $data['t_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());

        if($data['t_bxcount']>0)
        {
            $data['t_bxcount']+=1;
        }
        else
        {
            $data['t_bxcount']=1;
        }
        $tldata['tl_atpid']=makeGuid();
        $tldata['tl_taskid']=$data['t_taskid'];
        $tldata['tl_solvetime']=date('Y-m-d H:i:s', time());
        $tldata['tl_process']='退回理由：'.$tempdata['tuihui'].';  状态变更为等待受理';
        M()->startTrans();
        try
        {
            M('task')->where("t_atpid='%s'",$tempdata['t_atpid'])->save($data);

            M('taskdetail')->add($tldata);

            M()->commit();
            exit(makeStandResult(0, json_encode([["退回成功"]])));
        }
        catch(Exception $e){
            M()->rollback();
            exit(makeStandResult(1, json_encode([["退回失败"]])));
        }
    }

    public  function zhuanpaisubmit(){
        $tempdata = I('post.');
        $data=M('task')->where("t_atpid='%s'",$tempdata['t_atpid'])->find();
        $data['t_bxstatus']='等待受理（转派）';
        $data['t_person2']=$tempdata['zhuanpai'];
        if(empty($tempdata['zhuanpai']))
        {
            exit(makeStandResult(1, json_encode([["请选择转派人"]])));
        }
        $data['t_person']=session('username');
        $data['t_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());

        if($data['t_bxcount']>0)
        {
            $data['t_bxcount']+=1;
        }
        else
        {
            $data['t_bxcount']=1;
        }
        $tldata['tl_atpid']=makeGuid();
        $tldata['tl_taskid']=$data['t_taskid'];
        $tldata['tl_solvetime']=date('Y-m-d H:i:s', time());
        $person= M('ywperson')->where("yw_account='%s'",$data['t_person'])->find();
        $person2= M('ywperson')->where("yw_account='%s'",$data['t_person2'])->find();
        $tldata['tl_process']=$person['yw_name'].'转派给'.$person2['yw_name'];
        M()->startTrans();
        try
        {
            M('task')->where("t_atpid='%s'",$tempdata['t_atpid'])->save($data);

            M('taskdetail')->add($tldata);

            M()->commit();
            exit(makeStandResult(0, json_encode([["转派成功"]])));
        }
        catch(Exception $e){
            M()->rollback();
            exit(makeStandResult(1, json_encode([["转派失败"]])));
        }
    }

    public function deltasks(){
        $ids = I('post.ids');
        if(empty($ids)) exit(makeStandResult(-1,'参数缺少'));
        $id = explode(',', $ids);
        $idStr = "'".implode("','", $id)."'";


        $task['t_atpstatus']='del';

        $detail['tl_atpstatus']='del';

        M()->startTrans();
        try{
            M('task')->where("t_taskid in ($idStr)")->save($task);
            M('taskdetail')->where("tl_taskid in ($idStr)")->save($detail);
            if(count($id)==1)
            {
            $this->recordLog('del','bxd','删除了报修单; ','it_task',$id[0]);
            }
            else{
                $this->recordLog('del','bdx','删除了报修单：'.count($id).'条数据','it_task',$idStr);
            }
            M()->commit();
            exit(makeStandResult(1,'删除成功'));
        }catch(Exception $e){
            M()->rollback();
            exit(makeStandResult(-1,'删除失败'));
        }
    }
}