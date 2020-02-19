<?php
namespace Home\Model;
use Think\Model;
class DbModel extends Model{
    public function adminuser(){
        $q = $_POST['data']['q'];
        $user=M('sysuser');
        $sql="select  p.username id,p.realusername||'('||p.username||')'||'-'||d.name text from  it_person p,it_depart d  where
                    (p.username like '%".$q."%' or p.realusername like '%".$q."%') and p.orgid = d.id";
//        $data=$user->field('user_id','user_realusername')->where('user_realusername','like','$%q')->select();
//        print_r($data);
        $res=$user->query($sql);
        return json_encode(array('q' =>$q, 'results' => $res));


//        $q = $_POST['data']['q'];
//        $Model = M();
//        $sql_select="select  p.username id,p.realusername||'('||p.username||')'||'-'||d.name text from  it_person p,it_depart d  where
//                    (p.username like '%".$q."%' or p.realusername like '%".$q."%') and p.orgid = d.id";
//        $result=$Model->query($sql_select);
//        return json_encode(array('q' =>$q, 'results' => $result));
    }
}