<?php
namespace Cron\Controller;
use Think\Controller;
use Think\Exception;
//监测资源的所有IP哪些重复
class IpController extends Controller{

    public function index(){
        $model = M('it_sev');
        $modelv = M('it_sevv');
        $modelDb = M('it_ipbase');
        $status = C('ipStatusDel');
        $time  = date('Y-m-d H:i:s',time());
        $user  = session('user_id');

        //sev数据状态为在线的所有ip
        $where['sev_atpstatus'] = ['exp','is null'];
        $where[0][0]['sev_status'] = ['not in',$status];
        $where[0][1]['sev_status'] = ['exp','is null'];
        $where[0]['_logic'] = 'or';
        $sevListOn = $model->where($where)->select();
        $sevListUP = $this->ipSev($sevListOn);

        //sevv数据状态为在线的所有ip
        $whered['sevv_atpstatus'] = ['exp','is null'];
        $whered[0][0]['sevv_status'] = ['not in',$status];
        $whered[0][1]['sevv_status'] = ['exp','is null'];
        $whered[0]['_logic'] = 'or';
        $sevvListOnd = $modelv->where($whered)->select();
        $sevvListUPd = $this->ipSevv($sevvListOnd);

        $arrUp = array_filter(array_merge($sevListUP,$sevvListUPd));
        $ips = array_count_values($arrUp);
        $listUp = array();
        foreach($ips as $key =>$val){
            if($val > 1){
                //重复的IP
                $listUp[] = $key;
            }
        }
        //不重复的IP
        // $diffArr = array_diff($arrUp,$listUp);
        $listNum = count($listUp);
        set_time_limit(0);
        $saveNum = 0;
        $addNum = 0;
        foreach($arrUp as $key =>$val){
            $res = $modelDb->where("ipb_address = '%s'",$val)->getField('ipb_atpid');
            if($res){
                $save['ipb_status'] = '2';
                $save['ipb_atplastmodifydatetime'] = $time;
                $save['ipb_atplastmodifyuser'] = $user;
                $modelDb->where("ipb_address = '%s'",$val)->save($save);
                $saveNum++;
            }else{
                $data['ipb_atpid'] = makeGuid();
                $data['ipb_atpcreatedatetime'] = $time;
                $data['ipb_atpcreateuser'] = $user;
                $data['ipb_address'] = $val;
                $data['ipb_addressnum'] = ip2long($val);
                $data['ipb_status'] = 2;
                $modelDb->add($data);
                $addNum++;
            }
        }
        echo '本次监测更新IP'.$saveNum.'条，添加IP'.$addNum.'条,重复',$listNum,'条';
        echo '<br/>';
        print_r($listUp);die;

    }

    public function ipbase(){
        $model = M('it_sev');
        $modelv = M('it_sevv');
        $modelDb = M('it_ipbase');
        $status = C('ipStatusDel');
        $time  = date('Y-m-d H:i:s',time());
        $user  = session('user_id');

        //sev数据状态为在线的所有ip
        $where['sev_atpstatus'] = ['exp','is null'];
        $where[0][0]['sev_status'] = ['not in',$status];
        $where[0][1]['sev_status'] = ['exp','is null'];
        $where[0]['_logic'] = 'or';
        $sevListOn = $model->where($where)->select();
        $sevListUP = $this->ipSev($sevListOn);

        //sevv数据状态为在线的所有ip
        $whered['sevv_atpstatus'] = ['exp','is null'];
        $whered[0][0]['sevv_status'] = ['not in',$status];
        $whered[0][1]['sevv_status'] = ['exp','is null'];
        $whered[0]['_logic'] = 'or';
        $sevvListOnd = $modelv->where($whered)->select();
        $sevvListUPd = $this->ipSevv($sevvListOnd);

        $arrUp = array_filter(array_merge($sevListUP,$sevvListUPd));

        $ipbase = $modelDb->field('ipb_address')->where('ipb_status = 2')->select();
        $ips = removeArrKey($ipbase,'ipb_address');
        $diffIPs = array_diff($ips,$arrUp);
        $count = count($diffIPs);
        set_time_limit(0);
        foreach($diffIPs as $key =>$val){
            $save['ipb_status'] = '';
            $save['ipb_atplastmodifydatetime'] = $time;
            $save['ipb_atplastmodifyuser'] = $user;
            $modelDb->where("ipb_address = '%s'",$val)->save($save);
        }
        print_r($diffIPs);
        echo '本次监测更新IP'.$count.'条';

    }

    //获取sec表的所有IP
    public function ipSev($sevList){
        $ip = removeArrKey($sevList,'sev_ip');
        $subip = removeArrKey($sevList,'sev_subip');
        $iloip = removeArrKey($sevList,'sev_iloip');
        $ips = array_merge($ip,$iloip);
        foreach($subip as $val){
            $subipSon  = str_replace(',',';',$val);
            $subipSon = explode(';',$subipSon);
            $ips = array_merge($ips,$subipSon);
        }
        $ips =array_filter($ips);
        return $ips;
    }

    //获取sevv表的所有IP
    public function ipSevv($sevList){
        $ips = removeArrKey($sevList,'sevv_ip');
        $subip = removeArrKey($sevList,'sevv_subip');
        foreach($subip as $val){
            $subipSon  = str_replace(',',';',$val);
            $subipSon = explode(';',$subipSon);
            $ips = array_merge($ips,$subipSon);
        }
        $ips =array_filter($ips);
        return $ips;
    }

}