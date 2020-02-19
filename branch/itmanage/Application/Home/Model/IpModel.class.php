<?php
namespace Home\Model;
use Think\Model;
class IpModel extends Model{
    public function addIpCs($ip,$status){
        $statusArr = C('ipStatusDel');
        $model = M('it_ipbase');
        $time  = date('Y-m-d H:i:s',time());
        $user  = session('user_id');
        $username = M('sysuser')->where("user_id = '%s'",$user)->getField('user_realusername');
        if(!in_array($status,$statusArr)){
            foreach($ip as $key =>$val){
                $res = $model->where("ipb_address = '%s'",$val)->select();
                if($res){
                    $IpbStatus = removeArrKey($res,'ipb_status');
                    if(in_array('2',$IpbStatus)){
                        return  $val;
                    }else{
                        $save['ipb_status'] = '2';
                        $save['ipb_atplastmodifydatetime'] = $time;
                        $save['ipb_atplastmodifyuser'] = $user;
                        $model->where("ipb_address = '%s'",$val)->save($save);
                        addLog('it_ipbase', $username.'修改日志', $val.'状态改为已使用（2）' . '成功', '成功');
                    }
                }else{
                    $data['ipb_atpid'] = makeGuid();
                    $data['ipb_atpcreatedatetime'] = $time;
                    $data['ipb_atpcreateuser'] = $user;
                    $data['ipb_address'] = $val;
                    $data['ipb_addressnum'] = ip2long($val);
                    $data['ipb_status'] = 2;
                    $model->add($data);
                    addLog('it_ipbase', $username.'添加日志','添加IP'.$val . '成功', '成功');
                }
            }
        }
        return 'success';
    }
//    //验证ip是否占用
//    public function checkIp($ip,$status)
//    {
//        $statusArr = C('ipStatusDel');
//        $model = M('it_ipbase');
//        $time  = date('Y-m-d H:i:s',time());
//        $user  = session('user_id');
//        $username = M('sysuser')->where("user_id = '%s'",$user)->getField('user_realusername');
//        if(!in_array($status,$statusArr)){
//            foreach($ip as $key =>$val){
//                $res = $model->where("ipb_address = '%s'",$val)->select();
//                if($res){
//                    $IpbStatus = removeArrKey($res,'ipb_status');
//                    if(in_array('2',$IpbStatus)){
//                        return  $val;
//                    }
//                }else{
//                    $data['ipb_atpid'] = makeGuid();
//                    $data['ipb_atpcreatedatetime'] = $time;
//                    $data['ipb_atpcreateuser'] = $user;
//                    $data['ipb_address'] = $val;
//                    $data['ipb_addressnum'] = ip2long($val);
//                    $model->add($data);
//                    addLog('it_ipbase', $username.'添加日志','添加IP'.$val . '成功', '成功');
//                }
//            }
//        }
//        return 'success';
//    }
//
//    //修改ip状态
//    public function updIp($ip)
//    {
//        $model = M('it_ipbase');
//        $time  = date('Y-m-d H:i:s',time());
//        $user  = session('user_id');
//        $username = M('sysuser')->where("user_id = '%s'",$user)->getField('user_realusername');
//        $save['ipb_status'] = '2';
//        $save['ipb_atplastmodifydatetime'] = $time;
//        $save['ipb_atplastmodifyuser'] = $user;
//        $model->where("ipb_address = '%s'",$ip)->save($save);
//        addLog('it_ipbase', $username.'修改日志', $ip.'状态改为已使用（2）' . '成功', '成功');
//
//    }

    //修改时验证ip
    public function saveIp($ip,$ipUp,$status){
        $statusArr = C('ipStatusDel');
        $model = M('it_ipbase');
        //去掉的ip
        $first = array_diff($ip,$ipUp);
        //新添加的ip
        $second = array_diff($ipUp,$ip);
        //更改之前与之后的所有ip
        $three = array_unique(array_merge($ip,$ipUp));
        //更改后未改变的ip
        $jiao  = array_intersect($ip,$ipUp);

        $time  = date('Y-m-d H:i:s',time());
        $user  = session('user_id');
        $username = M('sysuser')->where("user_id = '%s'",$user)->getField('user_realusername');
        if(!in_array($status,$statusArr)){
            foreach($first as $key =>$val){
                $res = $model->where("ipb_address = '%s'",$val)->select();
                if($res){
                    $IpbStatus = removeArrKey($res,'ipb_status');
                    if(in_array('2',$IpbStatus)){
                        return  $val;
                    }
                }else{
                    $data['ipb_atpid'] = makeGuid();
                    $data['ipb_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                    $data['ipb_atpcreateuser'] = session('user_id');
                    $data['ipb_address'] = $val;
                    $data['ipb_addressnum'] = ip2long($val);
                    $data['ipb_status'] = 2;
                    $model->add($data);
                    addLog('it_ipbase', $username.'添加日志', '添加IP'.$val.'，置为已使用（2）' . '成功', '成功');
                }
            }

            if(!empty($jiao)){
                $where['ipb_address'] = ['in',$jiao];
                $list['ipb_status'] = 2;
                $list['ipb_atplastmodifydatetime'] = $time;
                $list['ipb_atplastmodifyuser'] = $user;
                $model->where($where)->save($list);
            }

            if(!empty($second)){
                foreach($second as $key =>$val){
                    $res = $model->where("ipb_address = '%s'",$val)->select();
                    if($res){
                        $save['ipb_status'] = '';
                        $save['ipb_atplastmodifydatetime'] = $time;
                        $save['ipb_atplastmodifyuser'] = $user;
                        $model->where("ipb_address = '%s'",$val)->save($save);
                        addLog('it_ipbase', $username.'修改日志', $val.'状态改为未使用（）' . '成功', '成功');
                    }else{
                        $data['ipb_atpid'] = makeGuid();
                        $data['ipb_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                        $data['ipb_atpcreateuser'] = session('user_id');
                        $data['ipb_address'] = $val;
                        $data['ipb_addressnum'] = ip2long($val);
                        $data['ipb_status'] = '';
                        $model->add($data);
                        addLog('it_ipbase', $username.'添加日志', '添加IP'.$val.'，置为未使用（）' . '成功', '成功');
                    }
                }
            }

        }else{
            foreach($three as $key =>$val){
                $res = $model->where("ipb_address = '%s'",$val)->select();
                if($res){
                    $save['ipb_status'] = '';
                    $save['ipb_atplastmodifydatetime'] = $time;
                    $save['ipb_atplastmodifyuser'] = $user;
                    $model->where("ipb_address = '%s'",$val)->save($save);
                    addLog('it_ipbase', $username.'修改日志',  $val.'状态改为未使用（）' . '成功', '成功');
                }else{
                    $data['ipb_atpid'] = makeGuid();
                    $data['ipb_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                    $data['ipb_atpcreateuser'] = session('user_id');
                    $data['ipb_address'] = $val;
                    $data['ipb_addressnum'] = ip2long($val);
                    $data['ipb_status'] = '';
                    $model->add($data);
                    addLog('it_ipbase', $username.'添加日志',  '添加IP'.$val.'，置为未使用（）' . '成功', '成功');
                }
            }
        }

        return 'success';
    }

    public function saveIpCs($ip,$ipUp,$status){
        $statusArr = C('ipStatusDel');
        $model = M('it_ipbase');
        //新添加的ip
        $first = array_diff($ip,$ipUp);
        //去掉的ip
        $second = array_diff($ipUp,$ip);
        //更改之前与之后的所有ip
        $three = array_unique(array_merge($ip,$ipUp));
        //更改后未改变的ip
        $jiao  = array_intersect($ip,$ipUp);

        $time  = date('Y-m-d H:i:s',time());
        $user  = session('user_id');
        $username = M('sysuser')->where("user_id = '%s'",$user)->getField('user_realusername');
        if(!in_array($status,$statusArr)){
            foreach($first as $key =>$val){
                $res = $model->where("ipb_address = '%s'",$val)->select();
                if($res){
                    $IpbStatus = removeArrKey($res,'ipb_status');
                    if(in_array('2',$IpbStatus)){
                        return  $val;
                    }else{
                        $save['ipb_status'] = '2';
                        $save['ipb_atplastmodifydatetime'] = $time;
                        $save['ipb_atplastmodifyuser'] = $user;
                        $model->where("ipb_address = '%s'",$val)->save($save);
                        addLog('it_ipbase', $username.'修改日志',  $val.'状态改为已使用（2）' . '成功', '成功');
                    }
                }else{
                    $data['ipb_atpid'] = makeGuid();
                    $data['ipb_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                    $data['ipb_atpcreateuser'] = session('user_id');
                    $data['ipb_address'] = $val;
                    $data['ipb_addressnum'] = ip2long($val);
                    $data['ipb_status'] = 2;
                    $model->add($data);
                    addLog('it_ipbase', $username.'添加日志', '添加IP'.$val.'，置为已使用（2）' . '成功', '成功');
                }
            }

            if(!empty($jiao)){
                $where['ipb_address'] = ['in',$jiao];
                $list['ipb_status'] = 2;
                $list['ipb_atplastmodifydatetime'] = $time;
                $list['ipb_atplastmodifyuser'] = $user;
                $model->where($where)->save($list);
            }

            if(!empty($second)){
                foreach($second as $key =>$val){
                    $res = $model->where("ipb_address = '%s'",$val)->select();
                    if($res){
                        $save['ipb_status'] = '';
                        $save['ipb_atplastmodifydatetime'] = $time;
                        $save['ipb_atplastmodifyuser'] = $user;
                        $model->where("ipb_address = '%s'",$val)->save($save);
//                        addLog('it_ipbase', $username.'修改日志', $val.'状态改为未使用（）' . '成功', '成功');
                    }else{
                        $data['ipb_atpid'] = makeGuid();
                        $data['ipb_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                        $data['ipb_atpcreateuser'] = session('user_id');
                        $data['ipb_address'] = $val;
                        $data['ipb_addressnum'] = ip2long($val);
                        $data['ipb_status'] = '';
                        $model->add($data);
//                        addLog('it_ipbase', $username.'添加日志', '添加IP'.$val.'，置为未使用（）' . '成功', '成功');
                    }
                }
            }

        }else{
            foreach($three as $key =>$val){
                $res = $model->where("ipb_address = '%s'",$val)->select();
                if($res){
                    $save['ipb_status'] = '';
                    $save['ipb_atplastmodifydatetime'] = $time;
                    $save['ipb_atplastmodifyuser'] = $user;
                    $model->where("ipb_address = '%s'",$val)->save($save);
                    addLog('it_ipbase', $username.'修改日志',  $val.'状态改为未使用（）' . '成功', '成功');
                }else{
                    $data['ipb_atpid'] = makeGuid();
                    $data['ipb_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                    $data['ipb_atpcreateuser'] = session('user_id');
                    $data['ipb_address'] = $val;
                    $data['ipb_addressnum'] = ip2long($val);
                    $data['ipb_status'] = '';
                    $model->add($data);
                    addLog('it_ipbase', $username.'添加日志',  '添加IP'.$val.'，置为未使用（）' . '成功', '成功');
                }
            }
        }

        return 'success';
    }

    public function DelIpCs($ip){
       $model = M('it_ipbase');
       $time  = date('Y-m-d H:i:s',time());
       $user  = session('user_id');
       $username = M('sysuser')->where("user_id = '%s'",$user)->getField('user_realusername');
       foreach($ip as $key =>$val) {
           $res = $model->where("ipb_address = '%s'",$val)->select();
           if($res){
               $save['ipb_status'] = '';
               $save['ipb_atplastmodifydatetime'] = $time;
               $save['ipb_atplastmodifyuser'] = $user;
               $model->where("ipb_address = '%s'",$val)->save($save);
               addLog('it_ipbase', $username.'修改日志',  $val.'状态改为未使用（）' . '成功', '成功');
           }else{
               $data['ipb_atpid'] = makeGuid();
               $data['ipb_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
               $data['ipb_atpcreateuser'] = session('user_id');
               $data['ipb_address'] = $val;
               $data['ipb_addressnum'] = ip2long($val);
               $data['ipb_status'] = '';
               $model->add($data);
               addLog('it_ipbase', $username.'添加日志', '添加IP'.$val.'，置为未使用（）' . '成功', '成功');
           }
        }
    }

    /**
     * 根据ipb_address修改ipbase使用状态
     */
    function changeBaseStatusByIp($params)
    {
        $ipb_address = trim($params['ipb_address']);
        $status = empty($params['status']) ? null : '2';
        if ($status == null) {
            $count = M('it_terminal')->where("zd_ipaddress = '" . $params['ipb_address'] . "' and zd_atpstatus is null")->count();
            if ($count) return true;
        }
        if (!$ipb_address) return false;
        $atpids = M('it_ipbase')->where("ipb_address = '$ipb_address'")->field('ipb_atpid')->select();
        $atpids = array_values($atpids);
        $arr['ipb_status'] = $status;
        $arr['ipb_atplastmodifyuser'] = session('user_id');
        $arr['ipb_atplastmodifydatetime'] = date('Y-m-d H:i:s', time());
        $res = M('it_ipbase')->where("ipb_address = '" . $ipb_address . "'")->setField($arr);
        $user  = session('user_id');
        $username = M('sysuser')->where("user_id = '%s'",$user)->getField('user_realusername');
        if ($res) {
            //记录日志
            foreach ($atpids as $key => $val) {
                if ($status == '') {
                    addLog('it_ipbase',$username.'修改日志',  $val.'状态改为未使用（）' . '成功', '成功');
                } else {
                    addLog('it_ipbase', $username.'修改日志',  $val.'状态改为已使用（2）' . '成功', '成功');
                }
            }
            return true;
        } else {
            return false;
        }
    }


    }