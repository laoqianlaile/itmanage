<?php
namespace Home\Controller;

use Think\Controller;

class FaultController extends BaseController
{
//应用系统管理
    public function index()
    {

        addLog("it_application", "用户访问日志",  "访问故障管理页面", "成功");
        $this->display();
    }

    /**
     * 应用系统添加或修改
     */
    public function add()
    {
        $id = trim(I('get.fe_atpid'));
        if (!empty($id)) {
            $model = M('faultevent');
            $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");

            $data = $model
                ->where("fe_atpid='%s'", $id)
                ->find();

            //处理人
            $auditUser = D('org')->getUserNames($data['fe_dealperson']);
            $emr_dealperson= $auditUser['realusername'].'('.$auditUser['domainusername'].')';
            $this->assign('fe_dealperson', $emr_dealperson);

            $this->assign('data', $data);
        }
        $arr = ['工作来源','类别'];
        $arrDic = D('Dic')->getDicValueByName($arr);

        $this->assign('source', $arrDic['工作来源']);
        $this->assign('type', $arrDic['类别']);
        addLog('it_application','用户访问日志', "访问故障管理添加、编辑页面", '成功');
        $this->display();
    }

    /**
     * 数据添加、修改
     */
    public function addData()
    {
        $data = I('post.');
        $id = trim($data['fe_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $model = M('faultevent');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        unset($data['fe_file']);
        if(!empty($_FILES)){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     1048576000 ;// 设置附件上传大小
//        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg','txt','doc','docx','xls','xlsx','pptx','vsd', 'pdf', 'csv');// 设置附件上传类型
            $upload->rootPath  =      './Public/'; // 设置附件上传目录
            $upload->savePath  =      'upload/fujian/'; // 设置附件上传目录
            $upload->subName  =  '';
            // 上传单个文件
            $info   =   $upload->uploadOne($_FILES['fe_file']);
            if(!$info) {    //上传错误提示错误信息
                exit(makeStandResult(-1, $upload->getError()));
            }else{  //上传成功 获取上传文件信息
                $filePath =  $info['savepath'].$info['savename'];
            }
            $data['fe_file'] = $filePath;
        }
        if (empty($id)) {
            $data['fe_atpid'] = makeGuid();
            $data = $model->create($data);
            $data['fe_atpcreatetime'] = $time;
            $data['fe_atpmodifytime'] = $time;
            $data['fe_atpcreateuser'] = $user;


            $res = $model->add($data);

            if (empty($res)) {
                // 修改日志
                addLog('faultevent', '对象添加日志', '添加主键为'.$data['fe_atpid'], '失败',$data['fe_atpid']);
                exit(makeStandResult(-1, '添加失败'));
            } else {
                // 修改日志
                addLog('faultevent',  '对象添加日志',  '添加主键为'.$data['fe_atpid'], '成功',$data['fe_atpid']);
                //添加服务器关联关系
//                $this -> changeRelationSev($data['app_atpid'],$data['app_name'],'','应用系统','it_application',$appHostId);
                exit(makeStandResult(1, '添加成功'));
            }
        } else {
            $data = $model->create($data);

            $data['fe_atpmodifytime'] = $time;
            $data['fe_atpmodifyuser'] = $user;


            $res = $model->where("fe_atpid='%s'", $id)->save($data);
            if (empty($res)) {
                // 修改日志
                addLog('faultevent', '对象修改日志','修改主键为'.$id, '失败',$id);
                exit(makeStandResult(-1, '修改失败'));
            } else {
                // 修改日志
               addLog('faultevent', '对象修改日志',  '修改主键为'.$id, '成功',$id);
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
            $filedStr = 'fe_date,fe_dealdate,fe_dealdatelength,fe_description,fe_effectapp,fe_effectrange,fe_outside,fe_dealperson,fe_bz';
        } else {
//            $filedStr = 'fe_startdate,fe_enddate,fe_detail,fe_source,fe_type,fe_file,fe_atpmodifytime,fe_bdid,fe_dealperson,fe_bz,fe_atpid,fe_atpmodifytime';
            $filedStr = '*';
            $queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        $where['fe_atpstatus'] = ['exp', 'IS NULL'];
        $fe_outside = trim($queryParam['fe_outside']);
        if (!empty($fe_outside)) $where['fe_outside'] = ['like', "%$fe_outside%"];

        $fe_effectapp = trim($queryParam['fe_effectapp']);
        if (!empty($fe_effectapp)) $where['fe_effectapp'] = ['like', "%$fe_effectapp%"];

        $fe_description= trim($queryParam['fe_description']);
        if (!empty($fe_description)) $where['fe_description'] = ['like', "%$fe_description%"];

        $fe_dealperson = trim($queryParam['fe_dealperson']);
        if (!empty($fe_dealperson)) $where['fe_dealperson'] = ['like', "%$fe_dealperson%"];

        $model = M('faultevent');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD'");
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");


        if ($isExport) {
            $data = $obj->select();

            $header = ['故障发生时间', '故障解决时间','解决时长','故障描述','影响系统名称', '影响范围','外协方', '处理人', '备注'];
            foreach ($data as $k => &$v) {

                //审计员
                $auditUser = D('org')->getViewPerson($v['fe_dealperson']);
                $data[$k]['fe_dealperson'] = $auditUser['realusername'];

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

                //处理人
                $auditUser = D('org')->getViewPerson($v['fe_dealperson']);
                $data[$k]['fe_dealperson'] = $auditUser['realusername'];

            }
            exit(json_encode(array('total' => $count, 'rows' => $data)));
        }
    }


    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.fe_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('faultevent');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['fe_atpmodifytime'] = $time;
                $data['fe_atpmodifyuser'] = $user;
                $data['fe_atpstatus'] = 'DEL';
                $res = $model->where("fe_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('specialmatter', '对象删除日志', "删除主键为".$id, '成功',$id);
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('specialmatter', '对象删除日志', "删除主键为".$ids, '失败',$id);
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    /**
     * 下载文件
     * @param $filePath -eg:Public/download/excel/2019-02-20/201902201550652158949.xlsx
     */
    function downloadFile(){
        $filePath = I('get.filepath');
        $file = explode('/',$filePath);
        $filename = $file[2];
        $filenames = iconv('UTF-8','gbk',$filePath);
        $filePath = 'Public/'.$filenames;
        if (file_exists ($filePath)) {
            header ( 'Content-Description: File Transfer' );
            header ( 'Content-Type: application/octet-stream' );
            header ( 'Content-Disposition: attachment; filename=' . $filename);
            header ( 'Content-Transfer-Encoding: binary' );
            header ( 'Expires: 0' );
            header ( 'Cache-Control: must-revalidate' );
            header ( 'Pragma: public' );
            header ( 'Content-Length: '. filesize ($filePath ));
            ob_clean ();
            flush ();

            //判断是否需要解密
            if(C('FILECONTENT_ENCRYPT')){
                //读取文件内容
                $contents = file_get_contents($filePath);

                //获取解密方法，进行解密
                $func = (string) C('FILECONTENT_DECIPHERING_FUNC');

                echo $func($contents);
            }else{
                readfile($filePath);
            }
            exit;
        }else{
            E('文件不存在', 404);
            exit;
        }
    }


}