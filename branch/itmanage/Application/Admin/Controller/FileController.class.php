<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

class FileController extends BaseController {

    /**
     * 文件管理
     */
    public function index($isThinkPHP = false){
        $objId = trim(I('get.objId'));
        $objType = trim(I('get.objType'));
        addLog('','用户访问日志','访问文件上传管理页面','成功');

        $secrets = D('Dictionary')->getPowerSecretLevel();
        $this->assign('secrets', $secrets);
        $this->assign('objId', $objId);
        $this->assign('objType', $objType);

        if($isThinkPHP === true) $this->assign('nopower', 1);
        $this->display('File/index');
    }

    /**
     * 获取字典列表
     */
    public function getData(){
        $queryParam = I('put.');

        $objId = trim($queryParam['objId']);
        $objType = trim($queryParam['objType']);
        $name = strtolower(trim($queryParam['search_name']));

        if(empty($objId) || empty($objType)) exit(makeStandResult(-1, '缺失参数'));

        $where['fr_objid'] = array('eq' ,$objId);
        $where['fr_objtype'] = array('eq' ,$objType);
        if(!empty($name))  $where["lower(f_name)"] = ['like', "%$name%"];

        $timeBegin = trim($queryParam['time_begin']);
        $timeEnd = trim($queryParam['time_end']);
        if(!empty($timeBegin)) {
            $where[0]['_logic'] = 'and';
            $where[0][0]["f_uploadtime"] = [ 'EGT', $timeBegin.' 00:00:00'];
        }
        if(!empty($timeEnd)) {
            $where[0]['_logic'] = 'and';
            $where[0][1]["f_uploadtime"] = [ 'ELT', $timeEnd.' 23:59:59'];
        }

        //只能看到小于等于自己密级的文件
        $where['f_secret'] = ['elt', session('user_secretlevel')];

        $model = M('filerelation t');
        $data = $model->field('f_id,f_name,f_secret,f_uploadtime,f_remark,f_path')
                    ->join('fileinfo t1 on t.fr_fileid=t1.f_id')
                    ->where($where)
                    ->order("$queryParam[sort] $queryParam[sortOrder] ")
                    ->limit($queryParam['offset'], $queryParam['limit'])
                    ->select();
        $staffSecret = D('Dictionary')->getDicValueByName('文件密级');
        $staffSecret = array_column($staffSecret, 'dic_name', 'val');

        foreach($data as &$value){
            $value['f_path'] =  encryptFilePath($value['f_path']);
            $value['f_secret'] =  $staffSecret[$value['f_secret']];
        }

        $count = $model->where($where)->join('fileinfo t1 on t.fr_fileid=t1.f_id')
                ->where($where)
                ->count();
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 文件信息修改页面
     */
    public function edit(){
        $id = trim(I('get.f_id'));
        if(empty($id)) exit( '缺失参数');

        $model = M('fileinfo');
        $data = $model->field('f_name')->where("f_id='%s'", $id)->find();
        $this->assign('data', $data);

        addLog('','用户访问日志','文件信息修改页面','成功');

        $this->assign('id', $id);
        $this->display();
    }


    public function editFile(){
        $id = trim(I('post.f_id'));
        $name = trim(I('post.f_name'));
        if(empty($id)) exit( '缺失参数');
        if(empty($name)) exit( '缺失参数');

        $model = M('fileinfo');
        $oldName = $model->where("f_id = '%s'", $id)->getField('f_name');
        $res = $model->where("f_id = '%s'", $id)->setField('f_name', $name);
        if($res){
            addLog('fileinfo', '对象修改日志', "将文件{$oldName}修改为{$name}成功", '成功');
            exit(makeStandResult(1, '修改成功'));
        }else{
            addLog('fileinfo', '对象修改日志', "将文件{$oldName}修改为{$name}失败", '失败');
            exit(makeStandResult(-1, '修改失败'));
        }
    }

    /**
     * 删除文件及关联信息
     */
    public function delFile(){
        $id = I('post.id');
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));

        $model = M('fileinfo');
        $data = $model->where("f_id = '%s'", $id)->field('f_name,f_path')->find();
        $model->startTrans();
        try{
            $model->where("f_id = '%s'", $id)->delete();
            M('filerelation')->where("fr_fileid = '%s'", $id )->delete();

            $model->commit();
            clearFile('Public/'.$data['f_path']);
            addLog('fileinfo', '对象删除日志',"删除文件:{$data['f_name']} 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }catch (Exception $e){
            $model->rollback();
            addLog('fileinfo', '对象删除日志',"删除文件:{$data['f_name']} 成功", '成功');
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    /**
     * 文件上传
     */
    public function fileUpload(){
        $objId = trim(I('post.objId'));
        $objType = trim(I('post.objType'));
        $secret = trim(I('post.secret'));

        if(empty($_FILES)) exit(makeStandResult(-1, '未检测到文件，可能是您的文件过大,允许上传的文件最大为20M！'));
        if(empty($objId) || empty($objType)) exit(makeStandResult(-1 , '缺失参数'));
        if(empty($secret)) exit(makeStandResult(-1 , '请选择密级'));

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     5242880 ;// 设置附件上传大小
//        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg','txt','doc','docx','xls','xlsx','pptx','vsd', 'pdf', 'csv');// 设置附件上传类型
        $upload->rootPath  =      './Public/'; // 设置附件上传目录
        $upload->savePath  =      'upload/material/'; // 设置附件上传目录
        $upload->subName  =  '';
        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['file']);
        if(!$info) {
            //上传错误提示错误信息
            exit(makeStandResult(-1, $upload->getError()));
        }else{
            //上传成功 判断是否需要加密
            if(C('FILECONTENT_ENCRYPT')){
                $tmpFilePath = './Public/'.$info['savepath'].$info['savename'];
                $res = D('File')->encryptFileContent($tmpFilePath);
                if(empty($res['status'])) exit(makeStandResult(-1, $res['message']));
            }

            $model = M('fileinfo');
            $filePath =  $info['savepath'].$info['savename'];
            $time = date('Y-m-d H:i:s');
            $arr = [
                'f_id' => str_replace('.'.$info['ext'], '', $info['savename']), //主键与文件名一致
                'f_createtime' => $time,
                'f_createuser' => session('user_id'),
                'f_name'=> $info['name'],
                'f_secret' => $secret,
                'f_uploadtime' => $time,
                'f_path' => $filePath
            ];
            $arr = $model ->create($arr);
            $model->startTrans();
            try{
                $model->add($arr);
                $relation = [
                    'fr_id' => makeGuid(),
                    'fr_createtime' => $time,
                    'fr_createuser' => $arr['f_createuser'],
                    'fr_objtype' => $objType,
                    'fr_objid' => $objId,
                    'fr_fileid' => $arr['f_id']
                ];
                M('filerelation')->add($relation);
                $model->commit();
                addLog('fileinfo', '对象添加日志', "objid:{$objId},objtype:{$objType},上传文件:{$arr['f_name']}成功", '成功');
                exit(makeStandResult(1, '上传成功'));
            }catch (Exception $e){
                $model->rollback();
                addLog('fileinfo', '对象添加日志', "objid:{$objId},objtype:{$objType},上传文件:{$arr['f_name']}失败", '失败');
                exit(makeStandResult(-1, '上传失败'));
            }
        }
    }
}