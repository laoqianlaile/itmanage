<?php
namespace Demo\Controller;
use Think\Controller;
class SevController extends BaseController {    

    public function index(){
        addLog("","用户访问日志","","访问服务器资产管理界面页面","成功");
        $this->display();
    }    

    /**
    * 服务器资产管理界面添加或修改
    */
    public function add(){
        $id = trim(I('get.sev_atpid'));
        if(!empty($id)){
        $model = M('sev', '', 'DB_NEW');
            $data = $model->field('sev_atpid,sev_ip,sev_powernum,sev_kvmnum,sev_directstorage,sev_disksn,sev_mask,sev_devicecode,sev_hostip,sev_mac,sev_net,sev_wwnno,sev_ilopass,sev_purchasetime,sev_supportdrift,sev_dutyman,sev_disknum,sev_atpstatus,sev_startusetime,sev_gateway,sev_modelnumber,sev_hbanum,sev_type,sev_useman,sev_kvmsw,sev_fsip,sev_subip,sev_powerport,sev_cpunum,sev_belongfloor,sev_submac,sev_atpmodifyuser,sev_roomno,sev_osinstalltime,sev_sn,sev_factory,sev_status,sev_cabloc,sev_usedept,sev_app,sev_assetusedept,sev_os,sev_anecode,sev_area,sev_iloip,sev_dutydept,sev_atpcreatetime,sev_assetsource,sev_ilomac,sev_remark,sev_name,sev_secretlevel,sev_assetdutydept,sev_memory,sev_swinterface,sev_atpcreateuser,sev_cab,sev_atpmodifytime')->where("sev_atpid='%s'", $id)->find();
            $this->assign('data', $data);
        }
        addLog('','用户访问日志','',"访问服务器资产管理界面添加、编辑页面",'成功');
        $this->display();
    }    

    /**
     * 数据添加、修改
     */
    public function addData(){
        $data = I('post.');
        $id = trim($data['sev_atpid']);
        // 这里根据实际需求,进行字段的过滤
        $model = M('sev', '', 'DB_NEW');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty($id)){
            $data['sev_atpid'] = makeGuid();
            $data = $model->create($data);
            $res = $model->add($data);

            if(empty($res)){
                // 修改日志
                addLog('it_sev', '对象添加日志', 'add', '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('it_sev', '对象添加日志', 'add', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            $data = $model->create($data);
            $res = $model->where("sev_atpid='%s'", $id)->save($data);
            if(empty($res)){
                // 修改日志
                addLog('it_sev', '对象修改日志', 'update', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('it_sev', '对象修改日志', 'update', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }    

    /**
     * 获取服务器资产管理界面数据
     */
    public function getData($isExport = false){
        if($isExport){
            $queryParam = I('post.');
            $filedStr = 'sev_name,sev_ip,sev_mac,sev_type,sev_belongfloor,sev_roomno,sev_secretlevel,sev_dutyman,sev_useman';
        }else{
            $filedStr = 'sev_name,sev_ip,sev_mac,sev_type,sev_belongfloor,sev_roomno,sev_secretlevel,sev_dutyman,sev_useman, sev_atpid';
            $queryParam = I('put.');
        }
        //过滤方法这里统一为trim，请根据实际需求更改
        $where = [];
        $sevSecretlevel = trim($queryParam['sev_secretlevel']);
        if(!empty($sevSecretlevel)) $where['sev_secretlevel'] = ['like', "%$sevSecretlevel%"];
        
        $sevBelongfloor = trim($queryParam['sev_belongfloor']);
        if(!empty($sevBelongfloor)) $where['sev_belongfloor'] = ['like', "%$sevBelongfloor%"];
        
        $sevRoomno = trim($queryParam['sev_roomno']);
        if(!empty($sevRoomno)) $where['sev_roomno'] = ['like', "%$sevRoomno%"];
        
        $sevDutyman = trim($queryParam['sev_dutyman']);
        if(!empty($sevDutyman)) $where['sev_dutyman'] = ['like', "%$sevDutyman%"];
        
        $sevUseman = trim($queryParam['sev_useman']);
        if(!empty($sevUseman)) $where['sev_useman'] = ['like', "%$sevUseman%"];
        
        $sevName = trim($queryParam['sev_name']);
        if(!empty($sevName)) $where['sev_name'] = ['like', "%$sevName%"];
        
        $sevType = trim($queryParam['sev_type']);
        if(!empty($sevType)) $where['sev_type'] = ['like', "%$sevType%"];
        
        $sevIp = trim($queryParam['sev_ip']);
        if(!empty($sevIp)) $where['sev_ip'] = ['like', "%$sevIp%"];
        
        $sevMac = trim($queryParam['sev_mac']);
        if(!empty($sevMac)) $where['sev_mac'] = ['like', "%$sevMac%"];
        
        $model = M('sev', '', 'DB_NEW');
        $count = $model->where($where)->count();
        $obj = $model->field($filedStr)
            ->where($where);
//            ->order("$queryParam[sort] $queryParam[sortOrder]");

        if($isExport){
            $data = $obj->select();

            $header = ['服务器名称','IP地址','MAC地址','设备类型','楼宇','房间号','密级','责任人','使用人'];
            if($count <= 0){
              exit(makeStandResult(-1, '没有要导出的数据'));
            } else if( $count > 1000){
                csvExport($header, $data, true);
            }else{
                excelExport($header, $data, true);
            }
        }else{
            $data = $obj->limit($queryParam['offset'], $queryParam['limit'])
                ->select();
            exit(json_encode(array( 'total' => $count,'rows' => $data)));
        }
    }    

    /**
     * 删除数据
     */
    public function delData(){
        $id = trim(I('post.sev_atpid'));
        if(empty($id)) exit(makeStandResult(-1,'参数缺少'));
        $where = [];
        if(strpos($id, ',') !== false){
            $id = explode(',', $id);
            $where['sev_atpid'] = ['in', $id];
        }else{
            $where['sev_atpid'] = ['eq', $id];
        }
        $model = M('sev', '', 'DB_NEW');
        $model->where($where)->delete();
        // 获取旧数据记录日志
        //$oldData = $model->where($where)->field('td_name')->select();
        //$names = implode(',', removeArrKey($oldData, 'td_name'));
        $res = $model->where($where)->delete();
        if($res){
            // 修改日志
            addLog('it_sev', '对象删除日志', 'delete', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('it_sev', '对象删除日志', 'delete', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }
}