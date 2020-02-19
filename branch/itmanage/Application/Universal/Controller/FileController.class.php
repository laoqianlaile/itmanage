<?php
namespace Universal\Controller;
use Think\Controller;
class FileController extends Controller {

    /**
     * 下载文件--接受的路径必须为用 FILE_KEY 配置项加密后的字符串，以防止暴露真实文件地址
     */
    public function download(){
        //文件路径
        $path = trim(I('get.path'));
        if(empty($path)) E('非法访问', 404);

        //解密文件路径的key
        $fileKey = C('FILE_KEY');

        //解密文件路径
        $path = deciphering($path, $fileKey);

        //初始化数据表
        $model = M('fileinfo');

        //获取文件名称
        $fileName = $model->where("f_path = '%s'", $path)->getField('f_name');

        if(strpos('Public', $path) === false) $path = 'Public/'.$path;
        if(file_exists($path)){
            downloadFile($path);
            if(empty($fileName)){
                addLog('', '文件下载日志', '下载文件'.$fileName, '成功');
            }else{
                addLog('', '文件下载日志', '未找到文件名称，文件路径为'.$path, '成功');
            }
            exit;
        }else{
            E('文件不存在', 404);
            exit;
        }
    }
}