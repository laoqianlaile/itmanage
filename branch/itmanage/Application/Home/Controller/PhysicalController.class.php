<?php
namespace Home\Controller;

use Think\Controller;

class PhysicalController extends BaseController
{
//应用系统管理
    public function index()
    {

        addLog("it_application", "用户访问日志",  "访问物理机-虚拟机对应关系页面", "成功");
        $this->display();
    }

    /**
     * 应用系统添加或修改
     */
    public function add()
    {
        $id = trim(I('get.pv_atpid'));
        if (!empty($id)) {
            $model = M('physical_virtual');
            $data = $model
                ->where("pv_atpid='%s'", $id)
                ->find();

            $this->assign('data', $data);
        }

        addLog('it_application','用户访问日志', "访问物理机-虚拟机对应关系添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['pv_atpid']);
        //验证物理机ip
        if (!empty($data['pv_pip'])) {
            if ($this->checkAddress($data['pv_pip'], 'IP') === false) exit(makeStandResult(-1, '物理机ip地址有误'));
        }

         //验证虚拟机ip
        if (!empty($data['pv_vip'])) {
            if ($this->checkAddress($data['pv_vip'], 'IP') === false) exit(makeStandResult(-1, '虚拟机ip地址有误'));
        }
        // 这里根据实际需求,进行字段的过滤
        $model = M('physical_virtual');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        if (empty($id)) {
            $data['pv_atpid'] = makeGuid();
            $data = $model->create($data);
            $data['pv_atpcreatetime'] = $time;
            $data['pv_atpcreateuser'] = $user;


            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('physical_virtual', '对象添加日志', '添加主键为'.$data['pv_atpid'], '失败',$data['pv_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('physical_virtual',  '对象添加日志',  '添加主键为'.$data['pv_atpid'], '成功',$data['pv_atpid']);
                //添加服务器关联关系
//                $this -> changeRelationSev($data['app_atpid'],$data['app_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);

            $data['pv_atpmodifytime'] = $time;
            $data['pv_atpmodifyuser'] = $user;


            $res = $model->where("pv_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('videlcontroller', '对象修改日志','修改主键为'.$id, '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
                addLog('videlcontroller', '对象修改日志',  '修改主键为'.$id, '成功',$id);
                //添加服务器关联关系
//                $this -> changeRelationSev($data['app_atpid'],$data['app_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '修改成功'));
            }
        }
    }

    /**
     * 获取应用系统数据
     */
    public function getData($isExport = false)
    {
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'pv_pcol,pv_pip,pv_vip,pv_vname';
        } else {
            $filedStr = '*';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['pv_atpstatus'] = ['exp', 'IS NULL'];


        $pv_pcol = strtolower(trim($queryParam['pv_pcol']));
        if (!empty($pv_pcol)) $where['lower(pv_pcol)'] = ['like', "%$pv_pcol%"];

        $pv_pip = strtolower(trim($queryParam['pv_pip']));
        if (!empty($pv_pip)) $where['lower(pv_pip)'] = ['like', "%$pv_pip%"];

        $pv_vip = strtolower(trim($queryParam['pv_vip']));
        if (!empty($pv_vip)) $where['lower(pv_vip)'] = ['like', "%$pv_vip%"];

        $pv_vname = strtolower(trim($queryParam['pv_vname']));
        if (!empty($pv_vname)) $where['lower(pv_vname)'] = ['like', "%$pv_vname%"];



        $model = M('physical_virtual');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");


        if ($isExport) {
            $data = $obj->select();

            $header = ['集群', '物理机IP','虚拟机IP','虚拟机名称','虚拟机内置服务'];
            foreach ($data as $k => &$v) {

                $list = M('it_relationx i')->field('rw_detail,rlx_useage')
                    ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
                    ->join('it_sevv v on i.rlx_zyid = v.sevv_atpid','left')
                    ->where("sevv_ip = '%s' and rlx_atpstatus is null and rw_atpstatus is null",trim($v['pv_vip']))
                    ->select();
                $detail = '';
                foreach($list as $val){
                    $detail.=$val['rw_detail'].'-'.$val['rlx_useage'].',';
                }
                $data[$k]['detail'] = $detail;


            }
            if ($count <= 0) {
                exit(makeStandResult(-1, '没有要导出的数据'));
            } else if ($count > 1000) {
                csvExport($header, $data, true);
            } else {
                excelExport($header, $data, true);
            }
        } else {
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();

            foreach ($data as $k => &$v) {
                $list = M('it_relationx i')->field('rw_detail,rlx_useage,rlx_zyid')
                    ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
                    ->join('it_sevv v on i.rlx_zyid = v.sevv_atpid','left')
                    ->where("sevv_ip = '%s' and rlx_atpstatus is null and rw_atpstatus is null",trim($v['pv_vip']))
                    ->select();
                $detail = '';
                if(!empty($list)){
                    foreach($list as $val){
                        $detail.=$val['rw_detail'].'-'.$val['rlx_useage'].',';
                    }
                    if(mb_strlen($detail) > 20){
                        $details = mb_substr($detail,0,20).'...';
                    }else{
                        $details = $detail;
                    }
                }else{
                    $details = $detail;
                }

                $data[$k]['details'] = $details;
                $data[$k]['detail'] = $detail;


            }

            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }


    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.pv_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('physical_virtual');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['pv_atpmodifytime'] = $time;
                $data['pv_atpmodifyuser'] = $user;
                $data['pv_atpstatus'] = 'DEL';
                $res = $model->where("pv_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('physical_virtual', '对象删除日志', "删除主键为".$id, '成功',$id);
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('physical_virtual', '对象删除日志', "删除主键为".$ids, '失败',$id);
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    public function submitimpTmn()
    {
        if (IS_POST) {
            M()->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
            set_time_limit(0);
            $upload = new \Think\Upload();
            $upload->maxSize = 3145728;
            $upload->exts = array('xls', 'xlsx');
            $upload->rootPath = './Public/uploads/';
            $upload->savePath = '';
            $info = $upload->upload();
            if (!$info) {
                exit(makeStandResult(1, json_encode([[$upload->error]])));
            }
            $filename = './Public/uploads/' . $info["updataexcel2007"]["savepath"] . $info["updataexcel2007"]["savename"];
            vendor("PHPExcel.PHPExcel");
            $objPhpExcel = \PHPExcel_IOFactory::load($filename);

            $excelsheet = $objPhpExcel->getActiveSheet()->toArray(null, true, true, true, true);
            $column = Array("序号","集群", "物理机IP", "虚拟机IP", "虚拟机名称");
            $thead = array_values($excelsheet[1]);
            $diff = array_diff($thead, $column);
            if (!empty($diff)) {
                exit(makeStandResult(2, json_encode([["请按照模板填写导入数据，保持列头一致。"]])));
            }
            $cc = count($excelsheet);
            if (count($excelsheet) < 2) exit(makeStandResult(2, json_encode([["表中没有数据，请重新上传！"]])));

            $data = [];
            $k=0;
            for ($i = 2; $i <= $cc; $i++) {
                $data[$k]['pv_atpid'] = makeGuid();
                $data[$k]['pv_pcol'] = trim($excelsheet[$i]['B']);
                $data[$k]['pv_pip'] = trim($excelsheet[$i]['C']);
                $data[$k]['pv_vip'] = trim($excelsheet[$i]['D']);
                $data[$k]['pv_vname'] = trim($excelsheet[$i]['E']);
                $k++;
            }

            $model = M('physical_virtual');
            $model->startTrans();
            try{
                foreach($data as $val){
                    $val['pv_atpcreatetime'] = date('Y-m-d H:i:s');
                    $val['pv_atpcreateuser'] = session('user_id');
                    $model->add($val);
                }
                $model->commit();
                exit(makeStandResult(0, '导入成功'));
            } catch (\Exception $e) {
                $model->rollback();
                exit(makeStandResult(3, '导入失败！'));
            }

        }
    }


}