<?php
namespace Cron\Controller;
use Think\Controller;
use Think\Exception;

class RangeController extends Controller{

    public function range(){
        $model = M('scanrange');
        $list = $model->field('sr_startip,sr_endip,sr_atpid')->select();
        foreach($list as $val){
            $path = 'C:\xampp\php\php.exe -q   C:\xampp\htdocs\itmanage\branch\itmanage\cli.php Cron/Ping/ping '.$val['sr_startip'].' '.$val['sr_endip'].' '.$val['sr_atpid'];
            $tmp = copy('./Bat/start.bat','./Bat/'.$val['sr_startip'].".bat");
            $handle = fopen('./Bat/'.$val['sr_startip'].".bat",'w');
            fwrite ( $handle ,  $path );
            fclose($handle);
        }

    }

}