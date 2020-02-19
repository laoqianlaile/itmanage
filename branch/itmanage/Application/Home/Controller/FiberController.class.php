<?php
namespace Home\Controller;
use Think\Controller;
class FiberController extends BaseController {

    public function index(){
        $arr = ['光纤类型'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('type', $arrDic['光纤类型']);
        addLog("","用户访问日志","","访问Fiber页面","成功");
        $this->display();
    }    

    /**
    * Fiber添加或修改
    */
    public function add(){
        $id = trim(I('get.fb_atpid'));
        if(!empty($id)){
            $model = M('fiber');
            $data = $model->field('fb_type,fb_end,fb_sumnum,fb_start,fb_atpid,fb_status,fb_remark')->where("fb_atpid='%s'", $id)->find();
            session('list',$data);
            $this->assign('data', $data);
        }
        $arr = ['光纤类型'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('type', $arrDic['光纤类型']);
        addLog('','用户访问日志','',"访问Fiber添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['fb_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $isnum = is_numeric($data['fb_sumnum']);
        if($isnum != '1'){
            exit(makeStandResult(-1,'总芯数必须为数字!'));
        }
        $model = M('fiber');
        $models = M('subfiber');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty($id)){
            $data['fb_atpid'] = makeGuid();
            $data['fb_createtime'] = date('Y-m-d H:i:s',time());
            $data = $model->create($data);
            $res = $model->add($data);
            for($i=0;$i<$data['fb_sumnum'];$i++){
                $dataz['sfb_atpid'] = makeGuid();
                $dataz['sfb_num'] = $i+1;
                $dataz['sfb_pid'] = $data['fb_atpid'];
                $models->add($dataz);
            }

            if(empty($res)){
                // 修改日志
                addLog('fiber', '对象添加日志', 'add', '添加主键为' . $data['fb_atpid'],  '失败',$data['fb_atpid']);
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('fiber', '对象添加日志', 'add',  '添加主键为'. $data['fb_atpid'], '成功',$data['fb_atpid']);
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);
            $res = $model->where("fb_atpid='%s'", $id)->save($data);
            if($list['fb_sumnum'] < $data['fb_sumnum']){
                $num = $data['fb_sumnum'] - $list['fb_sumnum'];
                $count = $models->where("sfb_pid = '%s'",$id)->count();
                for($i=0;$i<$num;$i++){
                    $dataz['sfb_atpid'] = makeGuid();
                    $dataz['sfb_num'] = $count+$i+1;
                    $dataz['sfb_pid'] = $data['fb_atpid'];
                    $models->add($dataz);
                }
            }
            if(empty($res)){
                // 修改日志
                addLog('fiber', '对象修改日志','修改主键为'.$id, '失败',$id);
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                if(!empty($content)){
                    addLog('fiber', '对象修改日志',  $content , '成功',$id);
                }
//                addLog('fiber', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取Fiber数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'fb_type,fb_start,fb_end,fb_sumnum,fb_atpid,fb_usenum,fb_remainnum,fb_status,fb_remark';
        }else{
            $filedStr = 'fb_type,fb_start,fb_end,fb_sumnum,fb_usenum,fb_remainnum, fb_atpid,fb_status,fb_remark';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $where['fb_atpstatus'] = ['exp','is null'];
        $fbType = trim($queryParam['fb_type']);
        if(!empty($fbType)) $where['fb_type'] = ['like', "%$fbType%"];
        
        $fbStart = trim($queryParam['fb_start']);
        if(!empty($fbStart)) $where['fb_start'] = ['like', "%$fbStart%"];
        
        $fbEnd = trim($queryParam['fb_end']);
        if(!empty($fbEnd)) $where['fb_end'] = ['like', "%$fbEnd%"];
        
        $fb_status = trim($queryParam['fb_status']);
        if(!empty($fb_status)) $where['fb_status'] = ['like', "%$fb_status%"];

        
        $model = M('fiber');
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            foreach($data as $key =>$val){
                $useCount  = M('subfiber')->field('sfb_atpid')->where("sfb_pid = '%s' and sfb_detail is not null and sfb_atpstatus is null",$val['fb_atpid'])->count();
                $data[$key]['fb_usenum'] = $useCount;
                $data[$key]['fb_remainnum'] = $val['fb_sumnum']-$useCount;
                unset($data[$key]['fb_atpid']);
            }
            $header = ['光纤类型','起始点','终点','总芯数','已用芯数','未使用芯数','状态','备注'];
            if($count <= 0){
              exit(makeStandResult(-1, '没有要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach($data as $key =>$val){
                $useCount  = M('subfiber')->field('sfb_atpid')->where("sfb_pid = '%s' and (sfb_detail is not null and sfb_detail != '空')  and sfb_atpstatus is null",$val['fb_atpid'])->count();
                $data[$key]['fb_usenum'] = $useCount;
                $data[$key]['fb_remainnum'] = $val['fb_sumnum']-$useCount;
            }
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    

    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.fb_atpid'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        $wheres = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['fb_atpid'] = ['in', $id];
            $wheres['sfb_pid'] = ['in', $id];
        }else{
            $where['fb_atpid'] = ['eq', $id];
            $wheres['sfb_pid'] = ['eq', $id];
        }

        $model = M('fiber');
        $models = M('subfiber');
        // 获取旧数据记录日志
        $data['fb_atpstatus'] = 'DEL';
        $datas['sfb_atpstatus'] = 'DEL';
        $model->where($where)->save($data);
        $res = $models->where($wheres)->save($datas);
        if($res){
            // 修改日志
            addLog('fiber', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('fiber', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }

    public function SaveData(){
        $data = I('post.');
        $model = M('fiber');
        $res = $model->where("fb_atpid = '%s'",$data['fb_atpid'])->save($data);
        if($res){
            echo 'success';die;
        }else{
            echo 'error';die;
        }
    }
}