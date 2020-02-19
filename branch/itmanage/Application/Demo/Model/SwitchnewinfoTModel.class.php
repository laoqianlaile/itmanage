<?php
namespace Demo\Model;
use Think\Model;
class SwitchnewinfoTModel extends BaseModel {

    /**
     * 根据MAC获取数据
     */
    function gtOneMacInfoData($macaddress,$queryparam){
        $Model = M();
        $sql_select="
                select * from it_switchnewinfo_t s where s.sw_macaddress='".$macaddress."'";
        $sql_count="
                select count(*) c from it_switchnewinfo_t s where s.sw_macaddress='".$macaddress."'";
        if (null != $queryparam['sort']) {
            $sql_select = $sql_select . " order by " . $queryparam['sort'] . ' ' . $queryparam['sortOrder'] . ' ';
        } else {
            $sql_select = $sql_select . " order by s.sw_atpid  asc  ";
        }
        if (null != $queryparam['limit']) {
            if ('0' == $queryparam['offset']) {
                $sql_select = $this->buildSqlPage($sql_select, 0, $queryparam['limit']);
            } else {
                $sql_select = $this->buildSqlPage($sql_select, $queryparam['offset'], $queryparam['limit']);
            }
        }
//        echo $sql_count;die;
        $Result = $Model->query($sql_select);
        $Count   = $Model->query($sql_count);
        return json_encode(array( 'total' => $Count[0]['c'],'rows' => $Result));
    }

    /**
     * 交换机端口DOWN表数据
     */
    function SWPortDownData($queryparam){
        $map[0]['sw_status'] = ['eq','administratively down'];
        if ($queryparam['offset'] == ''){
            $offset = 0;
        }else{
            $offset = $queryparam['offset'];
        }
        $limit = $queryparam['limit'];

        if ("" != $queryparam['ipaddress']){
            $searchcontent = trim($queryparam['ipaddress']);
            $map[0]['sw_ipaddress'] = ['like',"%".$searchcontent."%"];
        }
        if ("" != $queryparam['macaddress']){
            $searchcontent = trim($queryparam['macaddress']);
            $searchcontent = strtolower($searchcontent);
            $map[0]['sw_macaddress'] = ['like',"%".$searchcontent."%"];
        }
        if ("" != $queryparam['area']){
            $searchcontent = trim($queryparam['area']);
            $map[0]['sw_mainarea'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['building']){
            $searchcontent = trim($queryparam['building']);
            $map[0]['sw_mainbelongfloor'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['sort']) {
            $order = array($queryparam['sort']=>$queryparam['sortOrder']);
            $result = M('switchnewinfoT')->where($map)->where('sw_macaddress is not null')->order($order)->limit($limit,$offset)->select();
        }else{
            $result = M('switchnewinfoT')->where($map)->where('sw_macaddress is not null')->order("sw_atpid desc")->limit($limit,$offset)->select();
        }
//        echo M('switchnewinfoT')->_sql();
        $count  = M('switchnewinfoT')->field('count(*) c')->where($map)->where('sw_macaddress is not null')->select();

        return [$result,$count[0]['c']];
    }

    /**
     * 交换机端口UP表数据
     */
    function SWPortUpData($queryparam){
        $map[0]['sw_status'] = ['eq','up'];
        if ($queryparam['offset'] == ''){
            $offset = 0;
        }else{
            $offset = $queryparam['offset'];
        }
        $limit = $queryparam['limit'];

        if ("" != $queryparam['ipaddress']){
            $searchcontent = trim($queryparam['ipaddress']);
            $map[0]['sw_ipaddress'] = ['like',"%".$searchcontent."%"];
        }
        if ("" != $queryparam['area']){
            $searchcontent = trim($queryparam['area']);
            $map[0]['sw_mainarea'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['building']){
            $searchcontent = trim($queryparam['building']);
            $map[0]['sw_mainbelongfloor'] = ['eq',$searchcontent];
        }
        if ("" != $queryparam['sort']) {
            $order = array($queryparam['sort']=>$queryparam['sortOrder']);
            $result = M('switchnewinfoT')->where($map)->where('sw_macaddress is null')->order($order)->limit($limit,$offset)->select();
        }else{
            $result = M('switchnewinfoT')->where($map)->where('sw_macaddress is null')->order("sw_atpid desc")->limit($limit,$offset)->select();
        }
//        echo M('switchnewinfoT')->_sql();die;
        $count  = M('switchnewinfoT')->field('count(*) c')->where($map)->where('sw_macaddress is null')->select();

        return [$result,$count[0]['c']];
    }

    /**
     * 根据楼宇获取信息
     */
    public function getSWInfoByIP($switchip){
        $switchConfigureport = C('SwitchConfigureport');
        $where = [];
        $where[0]['sw_ipaddress'] = $switchip;
        if($switchConfigureport){
            foreach($switchConfigureport as $key=>$val){
                $where[1][$key] = $val;
            }
        }
        $portInfo = M('switchnewinfoT')->where($where)->field('sw_ipaddress,sw_interface,sw_vlan')->select();
//        echo M("switchnewinfoT")->_sql();
        return $portInfo;
    }
}