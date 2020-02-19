<?php
namespace Universal\Controller;
use Think\Controller;
class OfficeController extends Controller {

    /**
     * 在线打开office工具
     * 调用方式：R('Universal/Office/index',[__ROOT__.'/Public/tmplete/a.xlsx', true]);
     * @param $filePath -文件路径
     * @param bool|true $isSave -是否需要保存
     * @return bool
     */
    public function index($filePath, $isSave = true){
        if(empty($filePath)) return false;

        $ext = strtolower(substr($filePath, strrpos($filePath, '.') +1));
        $filename = str_replace('.'.$ext, '', substr($filePath, strrpos($filePath, '/')+1));

        $this->assign('filepath', $filePath);
        $this->assign('filename', $filename);
        $this->assign('ext', $ext);
        $this->assign('issave', $isSave);
        $this->display('Universal@Office/index');
    }

    /**
     * 在线保存文件
     */
    public function saveFileOnline(){
        $oldFileName = base64_decode(trim(I('post.oldfilename')));
        $oldFilePath = base64_decode(trim(I('post.oldfilePath')));

        $rootPath = ltrim( __ROOT__, '/');
        $oldFileName = '.' . str_replace($rootPath, '', $oldFileName);
        $oldFilePath = '.' . str_replace($rootPath, '', $oldFilePath);

        if(empty($oldFileName)) exit(makeStandResult(-1, '缺失参数'));
        if(empty($oldFilePath)) exit(makeStandResult(-1, '缺失参数'));
        if(empty($_FILES['fileinfo'])) exit(makeStandResult(-1, '缺失文件'));

        if($_FILES['fileinfo']['error'] !== 0) exit(makeStandResult(-1, '文件上传遇到错误'));
        //IE 8 上传中文文件时名字有误，暂时不判断后缀
//        $ext = strtolower(substr($_FILES['fileinfo']['name'], strrpos($_FILES['fileinfo']['name'], '.') +1));
//        $exts = ['docx', 'doc', 'xls', 'xlsx', 'vsd'];
//        if(!in_array($ext, $exts)) exit(makeStandResult(-1, '不支持的文件后缀'));

        $oldFilePath = changeCoding($oldFilePath, 'UTF-8', 'GBK');
        $res1 =unlink($oldFilePath);
        $res2 =  move_uploaded_file($_FILES['fileinfo']['tmp_name'], $oldFilePath);

        if($res1 && $res2 ){
            addLog('', '文件编辑日志', '编辑文件'.$oldFileName, '成功');
            exit(makeStandResult(1, '保存成功'));
        }else{
            addLog('', '文件编辑日志', '编辑文件'.$oldFileName, '失败');
            exit(makeStandResult(-1, '保存失败'));
        }
    }
}
