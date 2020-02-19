<?php
namespace Home\Controller;
use Think\Controller;
class SubfiberController extends BaseController {    

    public function index(){
        $id = I('get.id');
        $type = I('get.type');
        $this->assign('type',$type);
        $this->assign('id',$id);
        $arr = ['使用状态(芯光纤)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('state', $arrDic['使用状态(芯光纤)']);
        addLog("","用户访问日志","","访问Subfiber页面","成功");
        $this->display();
    }    

    /**
    * Subfiber添加或修改
    */
    public function add(){
        $id = trim(I('get.sfb_atpid'));
        $pid = trim(I('get.pid'));
        if(!empty($id)){
            $model = M('subfiber');
            $data = $model->field('sfb_detail,sfb_pid,sfb_usedept,sfb_person,sfb_tel,sfb_atpid,sfb_num')->where("sfb_atpid='%s'", $id)->find();
            session('list',$data);
            $this->assign('data', $data);
        }
        $this->assign('pid',$pid);
        $arr = ['使用状态(芯光纤)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('state', $arrDic['使用状态(芯光纤)']);
        addLog('','用户访问日志','',"访问Subfiber添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['sfb_atpid']);
        // 这里根据实际需求,进行字段的过滤

        $model = M('subfiber');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty($id)){
            $data['sfb_atpid'] = makeGuid();
            $data['sfb_createtime'] = date('Y-m-d H:i:s',time());
            $data = $model->create($data);
            $count = $model->field('sfb_atpid')->where("sfb_pid = '%s' and sfb_atpstatus is null",$data['sfb_pid'])->count();
            $fcount = M('fiber')->where("fb_atpid = '%s'",$data['sfb_pid'])->getField('fb_sumnum');
            if($count >= $fcount){
                exit(makeStandResult(-1,'芯光纤数量超过规定总数量，请先删除数据再进行添加'));
            }
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('subfiber', '对象添加日志', 'add', '添加主键为'.$data['sfb_atpid'],  '失败',$data['sfb_atpid']);
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('subfiber', '对象添加日志', 'add','添加主键为'.$data['sfb_atpid'],'成功',$data['sfb_atpid']);
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data = $model->create($data);
            $list = session('list');
            $content = LogContent($data,$list);
            $res = $model->where("sfb_atpid='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('subfiber', '对象修改日志', 'update', '修改主键为'.$id, '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                if(!empty($content)){
                    addLog('subfiber', '对象修改日志',  $content , '成功',$id);
                }
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取Subfiber数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'to_number(sfb_num) sfb_num,sfb_usedept,sfb_person,sfb_tel,sfb_detail';
        }else{
            $filedStr = 'to_number(sfb_num) sfb_num,sfb_usedept,sfb_person,sfb_tel,sfb_detail,sfb_pid, sfb_atpid';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $where['sfb_atpstatus'] = ['exp','is null'];
        
        $sfbState = trim($queryParam['type']);
        if(!empty($sfbState)){
            $where[0][1]['sfb_detail'] = ['exp', "is not null"];
            $where[0][2]['sfb_detail'] = ['neq', "空"];
            $where[0]['_logic'] = 'and';
        }

        $pid = trim($queryParam['pid']);
        if(!empty($pid)) $where['sfb_pid'] = ['like', "%$pid%"];
        
        $model = M('subfiber');
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
        if($queryParam['sort'] == 'sfb_num'){
            $obj =  $obj->order("$queryParam[sort]");
        }else{
            $obj =  $obj->order("$queryParam[sort] $queryParam[sortOrder]");
        }
        if($isExport){
            $data = $obj->select();
            $header = ['芯号','使用单位','使用状态','联系人','联系电话','使用详情'];
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

            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    

    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.sfb_atpid'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['sfb_atpid'] = ['in', $id];
        }else{
            $where['sfb_atpid'] = ['eq', $id];
        }

        $model = M('subfiber');
        $data['sfb_atpstatus'] = 'DEL';

        $res = $model->where($where)->save($data);
        if($res){
            // 修改日志
            addLog('subfiber', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('subfiber', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }

    public function SaveData(){
        $data = I('post.');
        $model = M('subfiber');
        $res = $model->where("sfb_atpid = '%s'",$data['sfb_atpid'])->save($data);
        if($res){
            echo 'success';die;
        }else{
            echo 'error';die;
        }
    }
}