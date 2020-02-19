<?php
namespace Demo\Model;
use Think\Model;
class UsbkeyModel extends Model {
    public function getInfoByUsbCode($ucode){
        if(empty($ucode)) return false;
        $usbkey = M('usbkey')->where("u_code = '".$ucode."' and u_atpstatus is null")->find();
        return $usbkey;
    }
}