<?php
namespace Home\Controller;
use Think\Controller;
class ShiXiangController extends BaseController
{
    //事项管理
    public function index(){
        $arr = ['所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('group', $arrDic['所在班组']);
        addLog("", "用户访问日志",  "访问事项管理页面", "成功");
        $this->display();
    }


    public function indexNopower(){
        $arr = ['所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $code = I('get.code');
        $this->assign('code',$code);
        $this->assign('group', $arrDic['所在班组']);
        $this->display();
    }

    public function table(){
        $id = I('get.id');
        $objType = I('get.objType');
        $this->assign('id',$id);
        $this->assign('objType',$objType);
        addLog("", "用户访问日志",  "访问事项查看页面", "成功");
        $this->display();
    }

    //模糊查找事项
    public function getShiXiang(){
        $q = strtolower($_POST['data']['q']);
        $Model = M();
        $sql_select="select  p.sx_atpid id,p.sx_code||'-'||p.sx_name||'-'||r.rw_detail text from  shixiang p left join renwu r on r.rw_sxid = p.sx_atpid where
                    (lower(p.sx_code) like '%".$q."%' or lower(p.sx_name) like '%".$q."%' or lower(r.rw_detail) like '%".$q."%') and p.sx_atpstatus is null and r.rw_atpstatus is null";
        $result=$Model->query($sql_select);

        echo json_encode(array('q' =>$q, 'results' => $result));
    }

    /**
     * 获取事项数据
     */
    public function getData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rw_atpstatus'] = ['exp', 'is null'];
        $biao = trim($queryParam['biao']);
        if(!empty($biao)){
            $account =session('user_account');
            $where['sx_dutymanid'] = array('like' ,"%$account%");
        }
        $code = strtolower(trim($queryParam['sx_code']));
        if(!empty($code)) $where['lower(sx_code)'] = array('like' ,"%$code%");
        $name = strtolower(trim($queryParam['sx_name']));
        if(!empty($name)) $where['lower(sx_name)'] = array('like' ,"%$name%");
        $dutyman = trim($queryParam['dutyman']);
        if(!empty($dutyman)) $where['sx_dutymanid'] = array('eq' ,"$dutyman");
        $demander = trim($queryParam['demander']);
        if(!empty($demander)) $where['sx_demanderid'] = array('eq' ,"$demander");
        $group = trim($queryParam['group']);
        if(!empty($group)) $where['sx_group'] = array('eq' ,"$group");

        $model = M('shixiang x');
        $data = $model
            ->join('renwu r on r.rw_sxid = x.sx_atpid','left')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->where($where)->count();
        $Zy = ['物理服务器','虚拟服务器','防火墙策略','数据库用户','集中存储设备','安全产品'];
        $Xx = ['应用系统','平台'];
        $Gg = ['工具软件'];
        $Xk = ['许可证书'];
        $whered = [];
        $whered['rtype'] = ['in',$Zy];
        $whered['rstatus'] = ['eq','在用'];
        $wherex = [];
        $wherex['rtype'] = ['in',$Xx];
        $wherex['rstatus'] = ['neq','下线'];
        $wheret = [];
        $wheret['rtype'] = ['in',$Gg];
        $wheret['rstatus'] = ['neq','停用'];
        $wherek = [];
        $wherek['rtype'] = ['in',$Xk];
        $wherek[0][1]['rstatus'] = ['eq','在线'];
        $wherek[0][2]['rstatus'] = ['eq','试用'];
        $wherek[0]['_logic'] = 'or';
        foreach($data as $key => $val){
//            $data[$key]['sx_dutymandept'] = D('org')->getDepart($val['sx_dutymandept']);
            $data[$key]['sx_demanddept'] = D('org')->getDepart($val['sx_demanddept']);
            $zCount = M('v_sx_zy_union')->where("sxid = '%s'",$val['sx_atpid'])->count();
            $zyCount = M('v_sx_zy_union')->where("sxid = '%s'",$val['sx_atpid'])->where($whered)->count();
            $xxCount = M('v_sx_zy_union')->where("sxid = '%s'",$val['sx_atpid'])->where($wherex)->count();
            $ggCount = M('v_sx_zy_union')->where("sxid = '%s'",$val['sx_atpid'])->where($wheret)->count();
            $xkCount = M('v_sx_zy_union')->where("sxid = '%s'",$val['sx_atpid'])->where($wherek)->count();
            $Ycount = $zyCount+$xxCount+$ggCount+$xkCount;
            $Ncount = $zCount-$Ycount;
            $data[$key]['zyCount'] = $Ycount;
            $data[$key]['tyCount'] = $Ncount;
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取资源关联事项数据
     */
    public function getTableData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
//        $where['rw_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_zyid'] = array('eq' ,"$atpid");
        $model = M('it_relationx x');

        $data = $model
            ->join('renwu r on r.rw_atpid = x.rlx_rwid')
            ->join('shixiang s on s.sx_atpid = x.rlx_sxid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();

        $count =$model->join('renwu r on r.rw_atpid = x.rlx_rwid')
            ->join('shixiang s on s.sx_atpid = x.rlx_sxid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取服务器关联事项数据
     */
    public function getZyData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");

        $sev_atpstatus = trim($queryParam['sev_atpstatus']);
        if(!empty($sev_atpstatus)) $where['sev_status'] = array('eq' ,"$sev_atpstatus");
        $ip = trim($queryParam['ip']);
        if(!empty($ip)) $where['sev_ip'] = array('like' ,"%$ip%");

        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['sev_status'] = ['eq', '在用'];
            }else{
                $where[0][1]['sev_status'] = ['neq','在用'];
                $where[0][2]['sev_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }
        $model = M('shixiang x');
        $data = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('it_sev s on i.rlx_zyid = s.sev_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('it_sev s on i.rlx_zyid = s.sev_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取虚拟服务器关联事项数据
     */
    public function getXnData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $sev_atpstatus = trim($queryParam['sevv_status']);
        if(!empty($sev_atpstatus)) $where['sevv_status'] = array('eq' ,"$sev_atpstatus");

        $ip = trim($queryParam['ip']);
        if(!empty($ip)) $where['sev_ip'] = array('like' ,"%$ip%");
        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['sevv_status'] = ['eq', '在用'];
            }else{
                $where[0][1]['sevv_status'] = ['neq','在用'];
                $where[0][2]['sevv_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }
        $model = M('shixiang x');
        $data = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('it_sevv s on i.rlx_zyid = s.sevv_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('it_sevv s on i.rlx_zyid = s.sevv_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取集中存储设备关联事项数据
     */
    public function getJzData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");

        $sev_atpstatus = trim($queryParam['sto_status']);
        if(!empty($sev_atpstatus)) $where['sto_status'] = array('eq' ,"$sev_atpstatus");
        $ip = trim($queryParam['ip']);
        if(!empty($ip)) $where['sto_ip'] = array('like' ,"%$ip%");

        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['sto_status'] = ['eq', '在用'];
            }else{
                $where[0][1]['sto_status'] = ['neq','在用'];
                $where[0][2]['sto_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }

        $model = M('shixiang x');
        $data = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('storage s on i.rlx_zyid = s.sto_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('storage s on i.rlx_zyid = s.sto_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取应用系统关联事项数据
     */
    public function getYyData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
//        $where['rw_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $sev_atpstatus = trim($queryParam['app_status']);
        if(!empty($sev_atpstatus)) $where['app_status'] = array('eq' ,"$sev_atpstatus");
        $name = trim($queryParam['name']);
        if(!empty($name)) $where['s.app_name'] = array('like' ,"%$name%");
        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['app_status'] = ['neq', '下线'];
            }else{
                $where[0][1]['app_status'] = ['eq','下线'];
                $where[0][2]['app_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }
        $model = M('shixiang x');
        $data = $model->field('s.app_name,s.app_atpid,x.sx_name,x.sx_atpid,x.sx_code,r.rw_atpid,r.rw_detail,r.rw_id')
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('it_application s on i.rlx_zyid = s.app_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('it_application s on i.rlx_zyid = s.app_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取防火墙策略关联事项数据
     */
    public function getclData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
//        $where['rw_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $name = trim($queryParam['name']);
        if(!empty($name)) $where['s.cl_clid'] = array('like' ,"%$name%");

        $sev_atpstatus = trim($queryParam['cl_status']);
        if(!empty($sev_atpstatus)) $where['cl_status'] = array('eq' ,"$sev_atpstatus");

        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['cl_status'] = ['eq', '在用'];
            }else{
                $where[0][1]['cl_status'] = ['neq','在用'];
                $where[0][2]['cl_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }
        $model = M('shixiang x');
        $data = $model->field('s.cl_clid,s.cl_atpid,x.sx_name,x.sx_atpid,x.sx_code,r.rw_atpid,r.rw_detail,r.rw_id')
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('fwcl s on i.rlx_zyid = s.cl_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('fwcl s on i.rlx_zyid = s.cl_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取安全产品数据
     */
    public function getSpData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $name = trim($queryParam['name']);
        if(!empty($name)) $where['tl_name'] = array('like' ,"%$name%");
        $sev_atpstatus = trim($queryParam['sp_status']);
        if(!empty($sev_atpstatus)) $where['sp_status'] = array('eq' ,"$sev_atpstatus");
        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['sp_status'] = ['eq', '在用'];
            }else{
                $where[0][1]['sp_status'] = ['neq','在用'];
                $where[0][2]['sp_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }
        $model = M('shixiang x');
        $data = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('security_products s on i.rlx_zyid = s.sp_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('security_products s on i.rlx_zyid = s.sp_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取工具软件数据
     */
    public function getTlData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $name = trim($queryParam['name']);
        if(!empty($name)) $where['tl_name'] = array('like' ,"%$name%");

        $sev_atpstatus = trim($queryParam['tl_status']);
        if(!empty($sev_atpstatus)) $where['tl_status'] = array('eq' ,"$sev_atpstatus");

        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['tl_status'] = ['neq', '停用'];
            }else{
                $where[0][1]['tl_status'] = ['eq','停用'];
                $where[0][2]['tl_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }

        $model = M('shixiang x');
        $data = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('tools s on i.rlx_zyid = s.tl_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('tools s on i.rlx_zyid = s.tl_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }
    /**
     * 获取许可证书数据
     */
    public function getLcData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $name = trim($queryParam['name']);
        if(!empty($name)) $where['lc_name'] = array('like' ,"%$name%");
        $sev_atpstatus = trim($queryParam['lc_status']);
        if(!empty($sev_atpstatus)) $where['lc_status'] = array('eq' ,"$sev_atpstatus");
        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where[0][1]['lc_status'] = ['eq', '在线'];
                $where[0][2]['lc_status'] = ['eq', '试用'];
                $where[0]['_logic'] = 'or';
            }else{
                $where[0][1][1]['lc_status'] = ['neq','在线'];
                $where[0][1][2]['lc_status'] = ['neq','试用'];
                $where[0][1]['_logic'] = 'and';
                $where[0][2]['lc_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }
        $model = M('shixiang x');
        $data = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('license s on i.rlx_zyid = s.lc_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('license s on i.rlx_zyid = s.lc_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取平台数据
     */
    public function getPfData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $name = trim($queryParam['name']);
        if(!empty($name)) $where['pf_name'] = array('like' ,"%$name%");
        $sev_atpstatus = trim($queryParam['pf_status']);
        if(!empty($sev_atpstatus)) $where['pf_status'] = array('eq' ,"$sev_atpstatus");
        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['pf_status'] = ['neq', '下线'];
            }else{
                $where[0][1]['pf_status'] = ['eq','下线'];
                $where[0][2]['pf_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }

        $model = M('shixiang x');
        $data = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('platform s on i.rlx_zyid = s.pf_atpid')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('platform s on i.rlx_zyid = s.pf_atpid')
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    /**
     * 获取数据库用户关联事项数据
     */
    public function getDbData(){
        $queryParam = I('put.');
        $where['sx_atpstatus'] = ['exp', 'is null'];
//        $where['rw_atpstatus'] = ['exp', 'is null'];
        $where['rlx_atpstatus'] = ['exp', 'is null'];
        $atpid = trim($queryParam['atpid']);
        if(!empty($atpid)) $where['rlx_sxid'] = array('eq' ,"$atpid");
        $objType = trim($queryParam['objType']);
        if(!empty($objType)) $where['rlx_type'] = array('eq' ,"$objType");
        $name = strtolower(trim($queryParam['name']));
        if(!empty($name)) $where['lower(s.ts_username)'] = array('like' ,"%$name%");

        $sev_atpstatus = trim($queryParam['ts_status']);
        if(!empty($sev_atpstatus)) $where['ts_status'] = array('eq' ,"$sev_atpstatus");
        $biao  = trim($queryParam['biao']);
        if(!empty($biao)){
            if($biao == 1) {
                $where['ts_status'] = ['eq', '在用'];
            }else{
                $where[0][1]['ts_status'] = ['neq','在用'];
                $where[0][2]['ts_status'] = ['exp','is null'];
                $where[0]['_logic'] = 'or';
            }
        }
        $model = M('shixiang x');
        $data = $model->field('s.ts_atpid,n.in_dns,s.ts_username,p.db_ip,p.db_type,i.rlx_atpid,i.rlx_useage,x.sx_name,x.sx_atpid,x.sx_code,r.rw_atpid,r.rw_detail,r.rw_id')
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('db_tablespace s on i.rlx_zyid = s.ts_atpid',"left")
            ->join("db_instance n on n.in_atpid = s.ts_inid","left")
            ->join("db_plat p on p.db_atpid = n.in_dbid","left")
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model
            ->join('it_relationx i on i.rlx_sxid  = x.sx_atpid')
            ->join('renwu r on r.rw_atpid = i.rlx_rwid','left')
            ->join('db_tablespace s on i.rlx_zyid = s.ts_atpid',"left")
            ->join("db_instance n on n.in_atpid = s.ts_inid","left")
            ->join("db_plat p on p.db_atpid = n.in_dbid","left")
            ->where($where)
            ->count();
        foreach($data as $key => $val){
            $data[$key]['pin'] = $val['sx_code'].'-'.$val['sx_name'].'-'.$val['rw_detail'];
            $data[$key]['text'] = $val['db_ip'].'-'.$val['db_type'];
        }
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }


    public function add(){
        $id = I('get.id');
        $model = M('shixiang');
        if($id){
            $data = $model->where("sx_atpid= '%s'",$id)->find();
            $this->assign('data',$data);
        }
        $arr = ['所在班组'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('group', $arrDic['所在班组']);
        addLog("", "用户访问日志",  "访问事项编辑页面", "成功");
        $this->display();
    }

    public function addData(){
        $data = I('post.');
        $model = M('shixiang');
        $modelRw = M('renwu');
        $dutymanid = $data['sx_dutymanid'];
        $demanderid = $data['sx_demanderid'];
        $dutymandeptid = $data['sx_dutymandeptid'];
        $demanddeptid = $data['sx_demanddeptid'];
        $dutyman  = M('it_person')->where("domainusername = '%s'",$dutymanid)->getField('realusername');
        $demander  = M('it_person')->where("domainusername = '%s'",$demanderid)->getField('realusername');
        $dutymandept  = M('it_depart')->where("id = '%s'",$dutymandeptid)->getField('fullname');
        $demanddept  = M('it_depart')->where("id = '%s'",$demanddeptid)->getField('fullname');
        $data['sx_dutyman'] = $dutyman;
        $data['sx_demander'] = $demander;
        $data['sx_dutymandept'] = $dutymandept;
        $data['sx_demanddept'] = $demanddept;
        $time = date('Y-m-d H:i:s',time());
        $user  = session('user_id');
        if(empty($data['sx_atpid'])){
            $data['sx_atpid'] = makeGuid();
            $data['sx_atpcreatetime'] = $time;
            $data['sx_atpcreateuser'] = $user;
            $code = $model->where("sx_atpstatus is null")->order('sx_code desc')->limit(1)->find();
            $ress=mb_substr($code['sx_code'],2,5,'utf-8');
            $num=$ress+1;
            $val=sprintf("%05d",$num);
            $data['sx_code'] ='SX'.$val;

            $list['rw_atpid'] = makeGuid();
            $list['rw_atpcreatetime'] = $time;
            $list['rw_atpcreateuser'] = $user;
            $list['rw_detail'] = $data['sx_name'];
            $list['rw_sxid'] = $data['sx_atpid'];
            $codeRw = $modelRw->order('rw_id desc')->limit(1)->find();
            $resRw=mb_substr($codeRw['rw_id'],2,5,'utf-8');
            $numRw=$resRw+1;
            $valRw=sprintf("%05d",$numRw);
            $list['rw_id'] ='RW'.$valRw;
            $modelRw->add($list);

            $res = $model->add($data);

            if($res){
                addLog('shixiang', '对象添加日志', '添加主键为'. $data['sx_atpid']  . '成功', '成功');
                echo 'success';die;
            }else{
                addLog('shixiang', '对象添加日志', '添加主键为'. $data['sx_atpid']  . '失败', '失败');
                echo 'error';die;
            }
        }else{
            $data['sx_atpmodifytime'] = $time;
            $data['sx_atpmodifyuser'] = $user;
            $res = $model->where("sx_atpid = '%s'",$data['sx_atpid'])->save($data);
            if($res){
                echo 'success';die;
            }else{
                echo 'error';die;
            }
        }
    }

    /**
     * 删除数据
     */
    public function delData()
    {
        $ids = trim(I('post.sx_atpid'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));
        $time = date('Y-m-d H:i:s', time());
        $user = session('user_id');

        $arr = explode(',', $ids);
        $model = M('shixiang');
        $model->startTrans();
        try {
            foreach ($arr as $k => $id) {
                $data['sx_atpmodifytime'] = $time;
                $data['sx_atpmodifyuser'] = $user;
                $data['sx_atpstatus'] = 'DEL';
                $model->where("sx_atpid='%s'", $id)->save($data);
                $arr['rlx_atpstatus'] = 'DEL';
                M('it_relationx')->where("rlx_sxid = '%s' and rlx_atpstatus is null",$id)->save($arr);
            }
            $model->commit();
            echo 'success';die;
        } catch (\Exception $e) {
            $model->rollback();
            echo 'error';die;
        }
    }

    public function renwu(){
        $ids = trim(I('get.id'));
        if (empty($ids)) exit(makeStandResult(-1, '参数缺少'));
        $this->assign('sxId',$ids);
        $this->display();
    }

    //获取任务数据
    public function getRenwuData(){
        $queryParam = I('put.');
        $code = trim($queryParam['rw_code']);
        if(!empty($code)) $where['rw_id'] = array('like' ,"%$code%");
        $sxId = trim($queryParam['sxId']);
        if(!empty($sxId)) $where['rw_sxid'] = array('eq' ,"$sxId");

        $model = M('renwu r');
        $data = $model->field('s.user_realusername createuser,rw_id,rw_atpid,rw_detail,sx_code,rw_atpcreatetime')
            ->join('shixiang s on s.sx_atpid = r.rw_sxid','left')
            ->join('sysuser s on s.user_id = r.rw_atpcreateuser','left')
            ->where($where)
            ->order("$queryParam[sort] $queryParam[sortOrder] ")
            ->limit($queryParam['offset'], $queryParam['limit'])
            ->select();
        $count = $model->where($where)->count();
        echo json_encode(array( 'total' => $count,'rows' => $data));
    }

    //添加任务
    public function addRw(){
        $id = I('get.id');
        $sxId = I('get.sxId');
        $biao = I('get.biao');
        $model = M('renwu');
        if($id){
            $data = $model->where("rw_atpid= '%s'",$id)->find();
            $this->assign('data',$data);
        }
        $this->assign('sxId',$sxId);
        $this->assign('biao',$biao);
        $this->display();
    }

    public function addRwData(){
        $data = I('post.');
        $detail = I('post.rw_detail');
        $model = M('renwu');
        $time = date('Y-m-d H:i:s',time());
        $user  = session('user_id');
        if(empty($data['rw_atpid'])){
            $data['rw_atpid'] = makeGuid();
            $data['rw_atpcreatetime'] = $time;
            $data['rw_atpcreateuser'] = $user;
            $code = $model->order('rw_id desc')->limit(1)->find();
            $ress=mb_substr($code['rw_id'],2,5,'utf-8');
            $num=$ress+1;
            $val=sprintf("%05d",$num);
            $data['rw_id'] ='RW'.$val;
            $res = $model->add($data);
            if($res){
                echo 'success';die;
            }else{
                echo 'error';die;
            }
        }else{
            $oldDetail = $model->where("rw_atpid = '%s'",$data['rw_atpid'])->getField('rw_detail');
            if($oldDetail != $detail){
                $model->where("rw_atpid = '%s'",$data['rw_atpid'])->setField('rw_atpstatus','1');
                $data['rw_atpid'] = makeGuid();
                $data['rw_atpcreatetime'] = $time;
                $data['rw_atpcreateuser'] = $user;
                $code = $model->order('rw_id desc')->limit(1)->find();
                $ress=mb_substr($code['rw_id'],2,5,'utf-8');
                $num=$ress+1;
                $val=sprintf("%05d",$num);
                $data['rw_id'] ='RW'.$val;
                $res = $model->add($data);
                if($res){
                    echo 'success';die;
                }else{
                    echo 'error';die;
                }
            }else{
                echo 'success';die;
            }
        }
    }


    public function FindId(){
        $username = I('post.username');
        $orgId = M('it_person')->where("domainusername = '%s'",$username)->getfield('orgid');
        $group = M('it_person')->where("domainusername = '%s'",$username)->getfield('groupname');
        $orgName  = M('it_depart')->where("id = '%s'",$orgId)->getField('fullname');
        $iFull = explode('-',$orgName);
        $countiFull = count($iFull);
        unset($iFull[$countiFull-1]);
        if($iFull[$countiFull-2]== '五院本级' || $iFull[$countiFull-2]=='总体部'){
            unset($iFull[$countiFull-2]);
        }
        $orgName = implode('-',$iFull);
        if($orgName){
            exit(makeStandResult(1, [$orgId,$orgName,$group]));
        }else{
            exit(makeStandResult(-1, [$orgId,$orgName,$group]));
        }
    }

    //资源关联事项
    public function relation(){
        $id = I('post.id');
        $model = M('it_relationx');
        $rwId = M('renwu')->where("rw_sxid = '%s'and rw_atpstatus is null",$id)->getField('rw_atpid');
        $zyId = I('post.zyId');
        $objType = I('post.objType');
        if($id)$where['rlx_sxid'] = ['eq',$id];
//        if($rwId)$where['rlx_rwid'] = ['eq',$rwId];
        if($zyId)$where['rlx_zyid'] = ['eq',$zyId];
        $where['rlx_atpstatus'] = ['exp','is null'];
        $res= $model->where($where)->find();
        if($res){
            exit(makeStandResult(-1, '该事项已添加，请勿重复添加！'));
        }
        if($objType == '应用系统'){
            $find = $model->where("rlx_zyid = '%s' and rlx_atpstatus is null and rlx_type = '应用系统'",$zyId)->find();
            if($find){
                exit(makeStandResult(-1, '只能对应一个事项！'));
            }
            $findSx = $model->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '应用系统'",$id)->find();
            if($findSx){
                exit(makeStandResult(-1, '该事项已与应用系统关联！'));
            }
        }
        $model = M('it_relationx');
        $arr = [];
        $arr['rlx_atpid'] = makeGuid();
        $arr['rlx_atpcreatetime'] = date('Y-m-d H:i:s',time());
        $arr['rlx_atpcreateuser'] = session('user_id');
        $arr['rlx_zyid'] = $zyId;
        $arr['rlx_sxid'] = $id;
        $arr['rlx_rwid'] = $rwId;
        $arr['rlx_type'] = $objType;
        $res  = $model->add($arr);
        if($res){
            addLog('it_relationx', '对象添加日志', '添加主键为'.$arr['rlx_atpid'] . '成功', '成功');
            exit(makeStandResult(1, '添加成功！'));
        }else{
            addLog('it_relationx', '对象添加日志', '添加主键为'.$arr['rlx_atpid'] . '失败', '失败');
            exit(makeStandResult(-1, '添加失败！'));
        }
    }

    //删除资源事项关联关系
    public function delRelaData(){
        $data = I('post.');
        $rlx_atpid  = explode(',',$data['rlx_atpid']);
        $model = M('it_relationx');
        $time = date('Y-m-d H:i:s',time());
        $user  = session('user_id');
        $model->startTrans();
        try {
            foreach ($rlx_atpid as $k => $id) {
                $data['rlx_atpmodifytime'] = $time;
                $data['rlx_atpmodifyuser'] = $user;
                $data['rlx_atpstatus'] = 'DEL';
                $model->where("rlx_atpid = '%s'",$id)->save($data);
                addLog('it_relationx', '对象删除日志',  "删除主键为".$id."成功", '成功');
            }
            $model->commit();
            echo 'success';die;
        } catch (\Exception $e) {
            $model->rollback();
            addLog('it_relationx', '对象删除日志',  "删除主键为".$rlx_atpid[0]."失败", '失败');
            echo 'error';die;
        }
    }

    //获取关联资源关联事项的总条数
    public function viewDetail(){
        $id = I('get.id');
        $type = I('get.type');
        $biao  = I('get.biao');
        $model = M('it_relationx r');
        $whereSev = [];
        $whereSevv = [];
        $whereApp = [];
        $whereFire = [];
        $whereDb = [];
        $whereGj = [];
        $whereXk = [];
        $wherePt = [];
        $whereJz = [];
        $whereAq = [];
        if(!empty($biao)){
            if($biao == 1){
                $whereSev['sev_status'] = ['eq','在用'];
                $whereSevv['sevv_status'] = ['eq','在用'];
                $whereApp['app_status'] = ['neq','下线'];
                $whereFire['cl_status'] = ['eq','在用'];
                $whereDb['ts_status'] = ['eq','在用'];
                $whereGj['tl_status'] = ['neq','停用'];
                $whereXk[0][1]['lc_status'] = ['eq','在线'];
                $whereXk[0][2]['lc_status'] = ['eq','试用'];
                $whereXk[0]['_logic'] = 'or';
                $wherePt['pf_status'] = ['neq','下线'];
                $whereJz['sto_status'] = ['eq','在用'];
                $whereAq['sp_status'] = ['eq','在用'];
            }else{
                $whereSev[0][1]['sev_status'] = ['neq','在用'];
                $whereSev[0][2]['sev_status'] = ['exp','is null'];
                $whereSev[0]['_logic'] = 'or';

                $whereSevv[0][1]['sevv_status'] = ['neq','在用'];
                $whereSevv[0][2]['sevv_status'] = ['exp','is null'];
                $whereSevv[0]['_logic'] = 'or';

                $whereApp[0][1]['app_status'] = ['eq','下线'];
                $whereApp[0][2]['app_status'] = ['exp','is null'];
                $whereApp[0]['_logic'] = 'or';

                $whereFire[0][1]['cl_status'] = ['neq','在用'];
                $whereFire[0][2]['cl_status'] = ['exp','is null'];
                $whereFire[0]['_logic'] = 'or';

                $whereDb[0][1]['ts_status'] = ['neq','在用'];
                $whereDb[0][2]['ts_status'] = ['exp','is null'];
                $whereDb[0]['_logic'] = 'or';

                $whereGj[0][1]['tl_status'] = ['eq','停用'];
                $whereGj[0][2]['tl_status'] = ['exp','is null'];
                $whereGj[0]['_logic'] = 'or';

                $whereXk[0][1][1]['lc_status'] = ['neq','在线'];
                $whereXk[0][1][2]['lc_status'] = ['neq','试用'];
                $whereXk[0][1]['_logic'] = 'and';
                $whereXk[0][2]['lc_status'] = ['exp','is null'];
                $whereXk[0]['_logic'] = 'or';


                $wherePt[0][1]['pf_status'] = ['eq','下线'];
                $wherePt[0][2]['pf_status'] = ['exp','is null'];
                $wherePt[0]['_logic'] = 'or';

                $whereJz[0][1]['sto_status'] = ['neq','在用'];
                $whereJz[0][2]['sto_status'] = ['exp','is null'];
                $whereJz[0]['_logic'] = 'or';

                $whereAq[0][1]['sp_status'] = ['neq','在用'];
                $whereAq[0][2]['sp_status'] = ['exp','is null'];
                $whereAq[0]['_logic'] = 'or';
            }
        }
        $wlCount =$model
            ->join('it_sev s on s.sev_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '物理服务器'",$id)->where($whereSev)->count();
        $xnCount =$model
            ->join('it_sevv s on s.sevv_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '虚拟服务器'",$id)->where($whereSevv)->count();
        $yyCount =$model
            ->join('it_application s on s.app_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '应用系统'",$id)->where($whereApp)->count();
        $clCount =$model
            ->join('fwcl s on s.cl_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '防火墙策略'",$id)->where($whereFire)->count();
        $DbCount =$model
            ->join('db_tablespace s on s.ts_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '数据库用户'",$id)->where($whereDb)->count();
        $TlCount =$model
            ->join('tools s on s.tl_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '工具软件'",$id)->where($whereGj)->count();
        $LcCount =$model
            ->join('license s on s.lc_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '许可证书'",$id)->where($whereXk)->count();
        $PfCount =$model
            ->join('platform s on s.pf_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '平台'",$id)->where($wherePt)->count();
        $JzCount =$model
            ->join('storage s on s.sto_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '集中存储设备'",$id)->where($whereJz)->count();
        $spCount =$model
            ->join('security_products s on s.sp_atpid = r.rlx_zyid','left')
            ->where("rlx_sxid = '%s' and rlx_atpstatus is null and rlx_type = '安全产品'",$id)->where($whereAq)->count();
        $this->assign('biao',$biao);
        $this->assign('wlCount',$wlCount);
        $this->assign('xnCount',$xnCount);
        $this->assign('yyCount',$yyCount);
        $this->assign('clCount',$clCount);
        $this->assign('DbCount',$DbCount);
        $this->assign('TlCount',$TlCount);
        $this->assign('LcCount',$LcCount);
        $this->assign('PfCount',$PfCount);
        $this->assign('JzCount',$JzCount);
        $this->assign('spCount',$spCount);
        $this->assign('type',$type);
        $this->assign('id',$id);
        $this->display();
    }

    public function Zytable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态（物理服务器）'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态（物理服务器）']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }

    public function Xntable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(虚拟服务器)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态(虚拟服务器)']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }

    public function Yytable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(应用系统)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态(应用系统)']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }

    public function cltable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['策略状态'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['策略状态']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }
    ///安全产品页面
    public function Sptable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(安全产品)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status',$arrDic['使用状态(安全产品)']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }
    //工具软件页面
    public function Tltable(){
    $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
    if($id)$where['rlx_sxid'] = ['eq',$id];
    if($type)$where['rlx_type'] = ['eq',$type];
    $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(工具软件)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态(工具软件)']);
    $this->assign('ids',$ids);
    $this->assign('id',$id);
    $this->assign('type',$type);
    $this->display();
}
    //许可证书页面
    public function Lctable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(许可)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态(许可)']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }
    //平台页面
    public function Pftable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(应用系统)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态(应用系统)']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }
    public function Dbtable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(数据库用户)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态(数据库用户)']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }
    public function Jztable(){
        $id = I('get.id');
        $type = I('get.type');
        $biao = I('get.biao');
        $this->assign('biao',$biao);
        if($id)$where['rlx_sxid'] = ['eq',$id];
        if($type)$where['rlx_type'] = ['eq',$type];
        $ids = M('it_relationx')->field('rlx_zyid')->where($where)->select();
        $ids = implode(',',removeArrKey($ids,'rlx_zyid'));
        $arr = ['使用状态(集中存储设备)'];
        $arrDic = D('Dic')->getDicValueByName($arr);
        $this->assign('status', $arrDic['使用状态(集中存储设备)']);
        $this->assign('ids',$ids);
        $this->assign('id',$id);
        $this->assign('type',$type);
        $this->display();
    }

    //天基资源事项关联关系
    public function relationAdd(){
        $id = I('post.id');
        $ids = explode(',',$id);
        $sxId = I('post.sxId');
        $objType = I('post.objType');
        $model = M('it_relationx');
        $where=[];
        $where['rlx_sxid'] = ['eq',$sxId];
        $where['rlx_atpstatus'] = ['exp','is null'];
        $model->startTrans();
        try {
            foreach($ids as $key =>$id){
                $where['rlx_zyid'] = ['eq',$id];
                $res = $model->where($where)->find();
                if(empty($res)){
                    $arr['rlx_atpid'] = makeGuid();
                    $arr['rlx_atpcreatetime'] = date('Y-m-d H:i:s',time());
                    $arr['rlx_atpcreateuser'] = session('user_id');
                    $arr['rlx_zyid'] = $id;
                    $arr['rlx_sxid'] = $sxId;
                    $arr['rlx_type'] = $objType;
                    $model->add($arr);
                }
            }
            $model->commit();
            exit(makeStandResult(1, '添加成功！'));
        } catch (\Exception $e) {
            $model->rollback();
            exit(makeStandResult(-1, '添加失败！'));
        }
    }

    public function delZyRelaData(){
        $data = I('post.');
        $Ids  = explode(',',$data['id']);
        $model = M('it_relationx');
        $time = date('Y-m-d H:i:s',time());
        $user  = session('user_id');
        $model->startTrans();
        try {
            foreach ($Ids as $k => $id) {
                $data['rlx_atpmodifytime'] = $time;
                $data['rlx_atpmodifyuser'] = $user;
                $data['rlx_atpstatus'] = '1';
                $model->where("rlx_atpid = '%s'",$id)->save($data);
            }
            $model->commit();
            echo 'success';die;
        } catch (\Exception $e) {
            $model->rollback();
            echo 'error';die;
        }
    }

    //添加用途
    public function Useage(){
        $id = I('post.id');
        $useage = I('post.useage');
        $model = M('it_relationx');
        $list['rlx_useage'] = $useage;
        $res =$model->where("rlx_atpid = '%s'",$id)->save($list);
        if($res){
            echo 'success';die;
        }else{
            echo 'error';die;
        }
    }

}