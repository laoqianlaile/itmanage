<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Admin\Model;
use Think\Model;
class FileModel extends Model{
    Protected $autoCheckFields = false;

    /**
     * 对文件内容加密
     * @param $filePath
     * @return array
     */
    public function encryptFileContent($filePath){
        $result = [
            'status' =>  0,
            'message' => ''
        ];
        if(!file_exists($filePath)) {
            $result['message'] = '加密失败：文件不存在';
            return $result;
        }
        //初始化文件内容
        $contents = file_get_contents($filePath) ;

        //以可写方式打开文件
        $handle = fopen ($filePath , 'w+');
        if(!$handle) {
            $result['message'] = '加密失败：无法打开文件';
            return $result;
        }

        //获取加密方法，进行加密
        $func = (string) C('FILECONTENT_ENCRYPT_FUNC');
        $contents = $func($contents);

        $res = fwrite($handle, $contents);
        fclose ($handle);
        if($res) {
            $result['status'] = 1;
            $result['message'] = '加密成功：已成功加密';

            return $result;
        }else{
            $result['message'] = '加密失败：文件写入失败';
            return $result;
        }
    }

}