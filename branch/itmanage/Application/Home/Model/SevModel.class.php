<?php
namespace Home\Model;
use Think\Model;
class SevModel extends Model {

    //ip查重
    public function sevIpChecking($ip)
    {
        $model = M('it_sev');
        $data = $model
        // ->field('')
        ->where("sev_ip='%s' and sev_atpstatus is null",$ip)
        ->select();
        $res = empty($data)?false:ture;
        return $res;
    }
    //ip查重
    public function sevvIpChecking($ip)
    {
        $model = M('it_sevv');
        $data = $model
        // ->field('')
        ->where("sevv_ip='%s' and sevv_atpstatus is null",$ip)
        ->select();
        $res = empty($data)?false:ture;
        return $res;
    }
    public function stoIpChecking($ip)
    {
        $model = M('storage');
        $data = $model
            ->where("sto_ip='%s' and sto_atpstatus is null",$ip)
            ->select();
        $res = empty($data)?false:ture;
        return $res;
    }

}