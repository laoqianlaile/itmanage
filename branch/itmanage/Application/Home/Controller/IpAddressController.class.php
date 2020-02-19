<?php
namespace Home\Controller;
use Think\Controller;
class IpAddressController extends BaseController
{
    public function index()
    {
        $this->display();
    }

    public function address()
    {
        $arr = ['密级', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $this->display();
    }

    public function indexNopower()
    {
        $status = I('get.status');
        $id = I('get.id');
        $this->assign('status',$status);
        $this->assign('id',$id);
        $this->display();
    }

    /**
     * 获取数据
     */
    public function getData()
    {
        $queryParam = I('put.');
        $start = trim($queryParam['IpStart']);
        $end = trim($queryParam['IpEnd']);
        $status = trim($queryParam['status']);
        $where = [];
        if (!empty($start)) {
            $startNum = $this->IPformat($start);
            $where[0][0]['ipb_addressnum'] = ['egt', $startNum];
        }
        if (!empty($end)) {
            $endNum = $this->IPformat($end);
            $where[0][1]['ipb_addressnum'] = ['elt', $endNum];
        }

        if (!empty($status)){
            if($status == '3'){
                $where[1][0]['ipb_status'] = ['exp', 'is null'];
                $where[1][1]['ipb_status'] = ['eq', '0'];
                $where[1]['_logic'] =  'or';
            }else{
                $where['ipb_status'] = ['eq', $status];
            }
        }

        $model = M('it_ipbase');
        $data = $model
            ->where($where)->limit($queryParam['offset'], $queryParam['limit'])
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->select();
        $count = $model->where($where)->count();

        echo json_encode(array('total' => $count, 'rows' => $data));
    }

    public function remark(){
        $id = I('post.id');
        $remark = I('post.remark');
        $model = M('it_ipbase');
        $list['ipb_text'] = $remark;
        $res =$model->where("ipb_atpid = '%s'",$id)->save($list);
        if($res){
            echo 'success';die;
        }else{
            echo 'error';die;
        }
    }

    public function indexView(){
        $ip = I('get.ip');
        $this->assign('ip',$ip);
        $this->display();
    }

    public function getViewData(){
        $queryParam = I('put.');
        $ipaddress = trim($queryParam['ipaddress']);
        $where = [];
        if (!empty($ipaddress)) {
            $where[0]['ip'] = ['like',"%$ipaddress%"];
            $where[0]['subip'] = ['like',"%$ipaddress%"];
            $where[0]['iloip'] = ['like',"%$ipaddress%"];
            $where[0]['_logic'] = 'or';
        }

        $model = M('v_ipall');
        $data = $model
            ->where($where)->limit($queryParam['offset'], $queryParam['limit'])
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->select();

        $count = $model->where($where)->count();

        echo json_encode(array('total' => $count, 'rows' => $data));
    }

    public function status(){
        $id = I('get.id');
        $data = M('it_ipbase')->where("ipb_atpid = '%s'",$id)->find();
        $this->assign('data',$data);
        $this->display();
    }

    public function addStatusData(){
        $data = I('post.');
        $res = M('it_ipbase')->where("ipb_atpid = '%s'",$data['ipb_atpid'])->save($data);
        if($res){
            exit(makeStandResult(1, '修改成功'));
        }else{
            exit(makeStandResult(-1, '修改失败'));
        }
    }

    public function add(){
        //字典
        $arr = ['密级', '地区'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('miJi', $arrDic['密级']);
        $this->assign('diQu', $arrDic['地区']);
        $id = I('get.id');
        if(!empty($id)){
            $data = M('it_ipaddress')->where("ip_atpid = '%s'",$id)->find();
            $depart = explode(',',$data['ip_depart']);
            $where['id'] = ['in',$depart];
            $depart  = M('it_depart')->where($where)->select();
            foreach($depart as $key =>$val){
                $depart[$key]['fullname'] = D('org')->getDepart($val['fullname']);
            }
            $area = explode(',',$data['ip_area']);
            $wheres['dic_id'] = ['in',$area];
            $belong = M('dic_louyu')->where($wheres)->select();
            $this->assign('belong',$belong);
            $this->assign('area',$area);
            $this->assign('depart',$depart);
            $this->assign('data',$data);
        }
        $this->display();
    }

    public function addData(){
        $data = I('post.');
        if(!empty($data['ip_start'])){
            if ($this->checkAddress($data['ip_start'], 'IP') === false) exit(makeStandResult(-1, '起始ip地址有误'));
        }else{
            exit(makeStandResult(-1, '起始ip地址不能为空'));
        }
        if(!empty($data['ip_end'])){
            if ($this->checkAddress($data['ip_end'], 'IP') === false) exit(makeStandResult(-1, '结束ip地址有误'));
        }else{
            exit(makeStandResult(-1, '结束ip地址不能为空'));
        }
        $data['ip_area'] = implode(',',$data['ip_area']);
        $data['ip_depart'] = implode(',',$data['ip_depart']);
        $data['ip_startnum'] = ip2long($data['ip_start']);
        $data['ip_endnum'] = ip2long($data['ip_end']);
        $ip_start = $data['ip_start'];
        $ip_end = $data['ip_end'];
        $user = session('username');
        $time = date('Y-m-d H:i:s',time());
        if(empty($data['ip_atpid'])){
            $data['ip_atpid']     = makeGuid();
            $data['ip_atpcreateuser']     = $user;
            $data['ip_atpcreatedatetime'] = $time;
            M('it_ipaddress')->add($data);
            $tmp0        = explode('.',$ip_start);
            $tmp1        = explode('.',$ip_end);
             if($tmp0[2] == $tmp1[2]){
                 //ipbase表添加数据
                 try {
                     M('it_ipbase')->startTrans();
                     for ($i = $tmp0[3]; $i <= $tmp1[3]; $i++) {
                         $address = $tmp0[0] . '.' . $tmp0[1] . '.' . $tmp0[2] . '.' . $i;
                         $res = M('it_ipbase')->where("ipb_address = '%s'", $address)->getField('ipb_atpid');
                         if ($res) {
                             $database['ipb_addressnum'] = ip2long($address);
                             $database['ipb_ipid'] = $data['ip_atpid'];
                             $database['ipb_atplastmodifyuser'] = $user;
                             $database['ipb_atplastmodifydatetime'] = $time;
                             M('it_ipbase')->where("ipb_atpid = '%s'", $res)->save($database);
                         } else {
                             $base['ipb_address'] = $address;
                             $base['ipb_addressnum'] = ip2long($address);
                             $base['ipb_ipid'] = $data['ip_atpid'];
                             $base['ipb_atpid'] = makeGuid();
                             $base['ipb_atpcreateuser'] = $user;
                             $base['ipb_atpcreatedatetime'] = $time;
                             M('it_ipbase')->add($base);
                         }
                     }
                     M('it_ipbase')->commit();
                     exit(makeStandResult(1, '添加成功'));
                 } catch(\Exception $e)
                 {
                     echo $e;
                     M('it_ipbase')->rollback();
                     exit(makeStandResult(-1, '添加失败'));
                 }

             }else{
                 try{
                     M('it_ipbase')->startTrans();
                     for($i=$tmp0[2];$i<=$tmp1[2];$i++){
                         if($i == $tmp0[2]){
                             for($j=$tmp0[3];$j<=255;$j++){
                                 $address = $tmp0[0].'.'.$tmp0[1].'.'.$i.'.'.$j;
                                 $res= M('it_ipbase')->where("ipb_address = '%s'",$address)->getField('ipb_atpid');
                                 if($res){
                                     $base['ipb_ipid'] = $data['ip_atpid'];
                                     $base['ipb_atplastmodifyuser']     = $user;
                                     $base['ipb_atplastmodifydatetime'] = $time;
                                     M('it_ipbase')->where("ipb_atpid = '%s'",$res)->save($base);
                                 }else{
                                     $database['ipb_ipid'] = $data['ip_atpid'];
                                     $database['ipb_address'] = $address;
                                     $database['ipb_addressnum'] = ip2long($address);
                                     $database['ipb_atpid']     = makeGuid();
                                     $database['ipb_atpcreateuser']     = $user;
                                     $database['ipb_atpcreatedatetime'] = $time;
                                     M('it_ipbase')->add($database);
                                 }
                             }
                         }else if($i == $tmp1[2]){
                             for($k=0;$k<=$tmp1[3];$k++){
                                 $addressd = $tmp0[0].'.'.$tmp0[1].'.'.$i.'.'.$k;
                                 $res= M('it_ipbase')->where("ipb_address = '%s'",$addressd)->getField('ipb_atpid');
                                 if($res){
                                     $base1['ipb_ipid'] = $data['ip_atpid'];
                                     $base1['ipb_atplastmodifyuser']     = $user;
                                     $base1['ipb_atplastmodifydatetime'] = $time;
                                     M('it_ipbase')->where("ipb_atpid = '%s'",$res)->save($base1);
                                 }else{
                                     $database1['ipb_ipid'] = $data['ip_atpid'];
                                     $database1['ipb_address'] = $addressd;
                                     $database1['ipb_addressnum'] = ip2long($addressd);
                                     $database1['ipb_atpid']     = makeGuid();
                                     $database1['ipb_atpcreateuser']     = $user;
                                     $database1['ipb_atpcreatedatetime'] = $time;
                                     M('it_ipbase')->add($database1);
                                 }
                             }
                         }else{
                             for($l=0;$l<=255;$l++){
                                 $addressd1 = $tmp0[0].'.'.$tmp0[1].'.'.$i.'.'.$l;
                                 $res= M('it_ipbase')->where("ipb_address = '%s'",$addressd1)->getField('ipb_atpid');
                                 if($res){
                                     $base2['ipb_ipid'] = $data['ip_atpid'];
                                     $base2['ipb_atplastmodifyuser']     = $user;
                                     $base2['ipb_atplastmodifydatetime'] = $time;
                                     M('it_ipbase')->where("ipb_atpid = '%s'",$res)->save($base2);
                                 }else{
                                     $database2['ipb_ipid'] = $data['ip_atpid'];
                                     $database2['ipb_address'] = $addressd1;
                                     $database2['ipb_addressnum'] = ip2long($addressd1);
                                     $database2['ipb_atpid']     = makeGuid();
                                     $database2['ipb_atpcreateuser']     = $user;
                                     $database2['ipb_atpcreatedatetime'] = $time;
                                     M('it_ipbase')->add($database2);
                                 }
                             }
                         }
                     }
                     M('it_ipbase')->commit();
                     exit(makeStandResult(1, '添加成功'));
                 }
                 catch(\Exception $e)
                 {
                     echo $e;
                     M('it_ipbase')->rollback();
                     exit(makeStandResult(-1, '添加失败'));
                 }
             }
        }else{
            $data['ip_atplastmodifyuser']     = $user;
            $data['ip_atplastmodifydatetime'] = $time;
            $res = M('it_ipaddress')->where("ip_atpid = '%s'",$data['ip_atpid'])->save($data);
            if($res){
                exit(makeStandResult(1, '修改成功'));
            }else{
                exit(makeStandResult(-1, '修改失败'));
            }
        }


    }



    /**
     * 获取数据
     */
    public function getNopowerData()
    {
        $queryParam = I('put.');
        $status = trim($queryParam['status']);
        $id = trim($queryParam['id']);
        $list = M('it_ipaddress')->field('ip_startnum,ip_endnum')->where("ip_atpid = '%s'",$id)->find();

        $where = [];
        if ($status == '已使用') {
            $where['ipb_status'] = ['eq', '2'];
        }else{
            $where[0][0]['ipb_status'] = ['neq','2'];
            $where[0][1]['ipb_status'] = ['exp','is null'];
            $where[0]['_logic'] = 'or';
        }
        if(!empty($id)){
            $where[1][0]['ipb_addressnum'] = ['egt',$list['ip_startnum']];
            $where[1][1]['ipb_addressnum'] = ['elt',$list['ip_endnum']];
            $where[1]['_logic'] = 'AND';
        }
        $model = M('it_ipbase');
        $data = $model
            ->where($where)->limit($queryParam['offset'], $queryParam['limit'])
            ->order("$queryParam[sort] $queryParam[sortOrder]")
            ->select();
        $count = $model->where($where)->count();

        echo json_encode(array('total' => $count, 'rows' => $data));
    }



    public function getAddressData($isExport = false){
        if ($isExport) {
            $queryParam = I('post.');
            $filedStr = 'ip_start,ip_end,ip_vlan_no,ip_secret_level,ip_area,ip_parea,ip_depart,ip_purpose,ip_mask,ip_gateway';
         } else {
            $filedStr = 'ip_start,ip_end,ip_vlan_no,ip_secret_level,ip_area,ip_parea,ip_depart,ip_purpose,ip_mask,ip_gateway,ip_atpid,ip_startnum,ip_endnum';
            $queryParam = I('put.');
        }
        $start = trim($queryParam['IpStart']);
        $miji = trim($queryParam['miji']);
        $vlan = trim($queryParam['vlan']);
        $ip_depart = trim($queryParam['ip_depart']);
        $ip_parea = trim($queryParam['ip_parea']);
        $ip_area = trim($queryParam['ip_area']);
        $where = [];
        $model = M('it_ipaddress');
        $modelBase = M('it_ipbase');
        $where['ip_isdel'] = ['exp','is null'];
        if (!empty($start)) $where['ip_start'] = ['like', "%$start%"];
        if (!empty($miji)) $where['ip_secret_level'] = ['eq', $miji];
        if (!empty($vlan)) $where['ip_vlan_no'] = ['eq', $vlan];
        if (!empty($ip_depart)) $where['ip_depart'] = ['like', "%$ip_depart%"];
        if (!empty($ip_parea)) $where['ip_parea'] = ['like', "%$ip_parea%"];
        if (!empty($ip_area)) $where['ip_area'] = ['like', "%$ip_area%"];
        $count = $model->where($where)->count();
        $obj = $model
            ->field($filedStr)
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();
            $header = ['起始IP', '结束IP', 'Vlan号', '密级', '部门', '用途','子网掩码','网关','地区-楼宇'];
            foreach($data as $key =>$val){
                $area = explode(',',$val['ip_area']);
                $wheres['dic_id'] = ['in',$area];
                $belong = M('dic_louyu')->where($wheres)->select();
                $area  = removeArrKey($belong,'dic_name');
                $parea = M('dic')->where("dic_id = '%s'",$val['ip_parea'])->getField('dic_name');
                if(!empty($area)){
                    foreach($area as $v){
                        $areaName[] = $parea.'-'.$v;
                    }
                    $data[$key]['areaName'] = implode(',',$areaName);
                }else{
                    $data[$key]['areaName'] =$parea;
                }
                $data[$key]['ip_area'] = implode(',',$areaName);
                $departIds = explode(',',$val['ip_depart']);
                $whered['id'] = ['in',$departIds];
                $depart = M('it_depart')->where($whered)->select();
                $depart = removeArrKey($depart,'fullname');
                foreach($depart as $v){
                    $departs[] = D('org')->getDepart($v);
                }
                $data[$key]['ip_depart'] = implode(',',$departs);
                unset($data[$key]['ip_area']);
                unset($data[$key]['ip_parea']);
            }
            if ($count <= 0) {
                exit(makeStandResult(-1, '没有要导出的数据'));
            } else if ($count > 1000) {
                csvExport($header, $data, true);
            } else {
                excelExport($header, $data, true);
            }
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            foreach($data as $key =>$val){
                $area = explode(',',$val['ip_area']);
                $wheres['dic_id'] = ['in',$area];
                $belong = M('dic_louyu')->where($wheres)->select();
                $area  = removeArrKey($belong,'dic_name');

                $parea = M('dic')->where("dic_id = '%s'",$val['ip_parea'])->getField('dic_name');
                if(!empty($area)){
                    foreach($area as $v){
                        $areaName[] = $parea.'-'.$v;
                    }
                    $data[$key]['areaName'] = implode(',',$areaName);
                }else{
                    $data[$key]['areaName'] =$parea;
                }
                $departIds = explode(',',$val['ip_depart']);
                $whered['id'] = ['in',$departIds];
                $depart = M('it_depart')->where($whered)->select();
                $depart = removeArrKey($depart,'fullname');
                foreach($depart as $v){
                    $departs[] = D('org')->getDepart($v);
                }
                $data[$key]['ip_depart'] = implode(',',$departs);
                $data[$key]['num']  = $modelBase->field('ipb_atpid') ->where("ipb_addressnum >= '".$val['ip_startnum']."' and ipb_addressnum <= '".$val['ip_endnum']."'")->count();
                $data[$key]['numUp']  = $modelBase->field('ipb_atpid') ->where("ipb_addressnum >= '".$val['ip_startnum']."' and ipb_addressnum <=  '".$val['ip_endnum']."' and ipb_status = 2")->count();
                $data[$key]['numDown']  =  $modelBase->field('ipb_atpid') ->where("ipb_addressnum >= '".$val['ip_startnum']."' and  ipb_addressnum <= '".$val['ip_endnum']."' and (ipb_status != 2 or ipb_status is null)")->count();
            }
        }
        echo json_encode(array('total' => $count, 'rows' => $data));
    }

    public function delData(){
        $ids = trim(I('post.ip_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));

        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');
        $arr = explode(',', $ids);
        $model = M('it_ipaddress');
        $model->execute("alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['ip_atplastmodifydatetime'] = $time;
                $data['ip_atplastmodifyuser'] = $user;
                $data['ip_isdel'] = 'DEL';
                $res = $model->where("ip_atpid='%s'", $id)->save($data);
                if ($res) {
                    // 修改日志
                    addLog('it_ipaddress', '对象删除日志', 'delete', "删除xxx 成功", '成功');
                    D('relation')->delRelation($id, 'it_sev');
                }
            }
            $model->commit();
            exit(makeStandResult(1, '删除成功'));
        } catch (\Exception $e) {
            $model->rollback();
            addLog('it_ipaddress', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(-1, '删除失败'));
        }
    }

    /**
     * ip地址格式化为数值
     */
    function IPformat($ip)
    {
        if (empty($ip)) return false;
        $ip = trim($ip);
        $data = explode('_', $ip);
        $res = $data[0] * 256 * 256 * 256 + $data[1] * 256 * 256 + $data[2] * 256 + $data[3];
        return $res;

    }

}