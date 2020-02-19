<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/10
 * Time: 10:15
 */
namespace Universal\Model;
use Think\Model;
class FormModel extends Model{
    protected $autoCheckFields = false;

    /**
     * 生成总体部表单系统创建表单链接
     * @param $uuid - 流程id  表单开发人员提供
     * @param $userId - 创建人域账号
     * @return bool|mixed|string
     */
    public function getZongtiCreateFormUrl($uuid, $userId = ''){
        if(empty($uuid)) return false;
        if(empty($userId)) $userId = session('user_account');
        $url = sprintf(C('ZONGTIBUFORM_CREATE_ADDRESS') , $userId, $uuid);
        return $url;
    }

    /**
     * 生成总体部表单系统编辑、查看表单链接
     * @param $bindId - 表单bindid
     * @param $taskId - 为当前流程id时表单可编辑，不是当前流程id时表单只可查看，表单开发人员提供
     * @param string $createUser - 创建人域账号
     * @return bool|mixed|string
     */
    public function getZongtiReadFormUrl($bindId, $taskId, $createUser = ''){
        if(empty($bindId)) return false;
        if(empty($taskId)) return false;
        if(empty($createUser)) $createUser = session('user_account');

        $url = sprintf(C('ZONGTIBUFORM_READ_ADDRESS') , $createUser, $bindId, $taskId);
        return $url;
    }
    
    /**
     * 生成院表单系统创建表单链接
     * @param string $userId - 用户名（不包含域
     * @param $billType - 表单Billtype
     * @param string $dataId - 此字段为空时为空白表单；此字段为表单ID（FormID）时为已有表单
     * @return bool|string
     */
    public function getYuanCreateFormUrl($userId = '', $billType, $dataId = ''){
        if(empty($billType)) return false;
        if(empty($userId)) $userId = str_replace('hq\\', '', session('user_account'));

        $url = sprintf(C('YUANFORM_CREATE_ADDRESS') , $userId, $billType, $dataId);
        return $url;
    }

    /**
     * 生成院表单系统编辑、查看表单链接, 具体参数找表单开发人员沟通
     * @param string $userId - 域用户名（不包含域名）
     * @param $billType - 表单Billtype表单Billtype
     * @param string $dataId - 此字段为空时为空白表单；此字段为表单ID（FormID）时为已有表单
     * @return bool|string
     */
    public function getYuanReadFormUrl($userId = '', $billType, $dataId = ''){
        if(empty($billType)) return false;
        if(empty($userId)) $userId = str_replace('hq\\', '', session('user_account'));

        $url = sprintf(C('YUANFORM_READ_ADDRESS'), $userId, $billType, $dataId);
        return $url;
    }
}