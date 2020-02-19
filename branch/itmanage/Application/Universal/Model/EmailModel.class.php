<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/05
 * Time: 10:15
 */
namespace Universal\Model;
use Think\Exception;
use Think\Model;
class EmailModel extends Model{
    protected $autoCheckFields = false;

    /**
     * 发送邮件
     * @param $serviceId
     * @param $receiver - 接收人，如果是多个接收人，请传入一维数组 收件人格式：jiwei@hq.cast
     * @param $content - 邮件内容
     * @param string $title - 邮件标题
     * @return bool  返回结果===true为发送成功，否则返回报错原因
     */
    public function sendEmail($serviceId, $receiver, $content, $title = ''){
        $server = getWebServiceObj('http://www.501msgcenter.cast/MsgRevService/MsgService.asmx?wsdl');
        if(is_array($receiver)) $receiver = implode(';', $receiver);

        try{
            $param = [
                'serviceID'=> $serviceId,
                'from' => C('EMAIL_FROM'),
                'to' => $receiver,
                'title' => $title,
                'content' => $content
            ];
            $res = $server->AddMsg($param);
            if(empty($res->AddMsgResult)){
                return true;
            }else{
                return $res->AddMsgResult;
            }
        }catch(Exception $e){
            return false;
        }
    }

}