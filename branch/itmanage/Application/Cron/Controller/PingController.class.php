<?php
namespace Cron\Controller;
use Think\Controller;
use Think\Exception;

class PingController extends Controller{

    public function ping(){
        $start=$_SERVER['argv'][2];
        $end=$_SERVER['argv'][3];
        $id=$_SERVER['argv'][4];
//        $start = '10.78.103.1';
//        $end = '10.78.103.10';
//        $id = '123';
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        ini_set('default_socket_timeout', 600);
        $this->diao($start,$end,$id);
    }

    public function diao($start,$end,$id){
        $time = 2;
        $info = [];
        $i = $start;
        $j = $end;
        $i = ip2long($i);
        $j = ip2long($j);
        $serModel = M('scanserver');
        $num = 0;
        $arr= [];
        $weekNow = date('w',time());
        $where=[];
        $where['ss_week'] = ['eq',$weekNow];
        $where['ss_srid'] = ['eq',$id];
        $serModel->where($where)->delete();
        // $serModel->startTrans();
       // try{
            for($l=$i;$l<= $j;$l++){
                $m= long2ip($l);
                exec("ping $l -n $time",$info[]);
                $find = strpos($info[$num][2],'TTL=');
                $arr['ss_atpid'] = makeGuid();
                $arr['ss_atpcreatedatetime'] = date('Y-m-d H:i:s',time());
                $arr['ss_ipaddress'] = $m;
                $arr['ss_week'] = $weekNow;
                $arr['ss_srid'] = $id;
                if(!empty($find)){
                    $arr['ss_status'] = 1;
                }else{
                    $arr['ss_status'] = 0;
                }
                $serModel->add($arr);
                $num ++;
            }
//            $serModel->commit();
//        }catch (Exception $e){
//            $serModel->rollback();
//        }
    }
}