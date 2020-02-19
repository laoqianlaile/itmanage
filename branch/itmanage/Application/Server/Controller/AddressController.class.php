<?php
namespace Server\Controller;
use Think\Controller;
class AddressController extends Controller\WebServiceController{
    /**
     * @param $data
     */
    public function macData($mac){}
    public function ipAddressdata($miji,$username,$area,$louyu){}
    public function ipdata($ip,$is){}
}

class AddressImplementController extends Controller{

    public function macData($mac){
        $mac = strtolower($mac);
        $model = M('it_terminal');
        $res = $model->where("lower(zd_macaddress) = '%s' and zd_atpstatus is null",$mac)->getField('zd_atpid');
        if(!empty($res)){
            return 1;
        }else{
            return 0;
        }
    }

    public function ipAddressdata($miji,$username,$area,$louyu){
        $model = M('it_ipaddress');
        $baseModel = M('it_ipbase');
        $depart = D('Home/org')->getDeptId($username);
        $area = $this->getDicId($area);
        $louyu = $this->getDicId($louyu);
        $id = $model->where("ip_secret_level = '".$miji."',ip_depart = '".$depart."',ip_area like '".'%'.$louyu.'%'.",ip_parea = '".$area."'");
        $ipBase = $baseModel->where("ipb_ipid = '%s' and ipb_status is null",$id)->order('ipb_addressnum asc')->getfield('ipb_address');
        if(!empty($ipBase)){
            $data['ipb_status'] = 1;
            $baseModel->where("ipb_address = '%s'",$ipBase)->save($data);
            return $ipBase;
        }else{
            return null;
        }
    }

    public function ipdata($ip,$is){
        $model = M('it_ipbase');
        if($is == 'ÊÇ'){
            $data['ipb_status'] = '2';
            $model->where("ipb_address = '%s'",$ip)->save($data);
        }else{
            $data['ipb_status'] = '';
            $model->where("ipb_address = '%s'",$ip)->save($data);
        }
    }

    public function getDicId($name){
        $model = M('dic');
        $id = $model->where("dic_name = '%s'",$name)->getField('dic_id');
        return $id;
    }


}
