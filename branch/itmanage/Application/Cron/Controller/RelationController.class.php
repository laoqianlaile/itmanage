<?php
namespace Cron\Controller;
use Think\Controller;
use Think\Exception;
class RelationController extends Controller{

    public function index(){

        $model = M('fwcl');
        $modelSub = M('subelement');
        $limit = M('it_relationx')->field('rlx_zyid')->group('rlx_zyid')->where("rlx_atpstatus is null and rlx_type = '防火墙策略'")->select();
        $rlx_zyids = array_column($limit, 'rlx_zyid');
        $where['cl_atpid'] = ['not in', $rlx_zyids];
        $where['cl_atpstatus'] = ['exp', 'is null'];
        $list = $model->field('cl_atpid,cl_sourceip,cl_objectip,cl_port')->where($where)->select();
        foreach($list as $key =>$val){
            $objectip  = str_replace(';',',',$val['cl_objectip']);
            $objectip  = str_replace('、',',',$objectip);
            $objectip  = str_replace('；',',',$objectip);
            $objectip  = str_replace('，',',',$objectip);
            $objectip  = str_replace(' ',',',$objectip);
            $objectip = explode(',',$objectip);
            foreach($objectip as $v){
                if(!empty($v)){
                    $res['sub_content'] = $v;
                    $res['sub_type'] = '防火墙策略';
                    $res['sub_pid']  = $val['cl_atpid'];
                    $res['sub_field'] = 'cl_port';
                    $modelSub->add($res);
                }
            }
        }
    }

    public function relation(){
        $model = M('subelement');
        $modelRlx = M('it_relationx');
        $modelSev = M('it_sev');
        $modelSevv = M('it_sevv');
        $modelSub = M('subelement');
        $ips = $model->where("sub_field = 'cl_port'")->select();
        $date = date('Y-m-d H:i:s');
        $user = session('user_id');
        set_time_limit(0);
        $count = 0;
        $sevCount = 0;
        $sevvCount = 0;

        foreach($ips as $key =>$val){
            //物理服务器IP地址检索
            $sevIps = $modelSev->field('sev_atpid,sev_ip')->where("sev_ip = '%s' and sev_atpstatus is null",$val['sub_content'])->find();
            if(!empty($sevIps)){
                //判断该资源是否关联事项
                $rlxData = $modelRlx->where("rlx_zyid = '%s' and rlx_type = '物理服务器'  and rlx_atpstatus is null",$sevIps['sev_atpid'])->select();
                if(!empty($rlxData)){
                    foreach($rlxData as $k =>$v){
                        $where['rlx_zyid'] = ['eq',$val['sub_pid']];
                        $where['rlx_sxid'] = ['eq',$v['rlx_sxid']];
                        $where['rlx_rwid'] = ['eq',$v['rlx_rwid']];
                        $where['rlx_type'] = ['eq','防火墙策略'];
                        $where['rlx_atpstatus'] = ['exp','is null'];
                        //查看这条关联关系是否已经存在
                        $res = $modelRlx->field('rlx_atpid')->where($where)->find();
                        if(empty($res)){
                            $arr['rlx_atpid'] = makeGuid();
                            $arr['rlx_atpcreatetime'] = $date;
                            $arr['rlx_atpcreateuser'] = $user;
                            $arr['rlx_zyid'] = $val['sub_pid'];
                            $arr['rlx_sxid'] = $v['rlx_sxid'];
                            $arr['rlx_rwid'] = $v['rlx_rwid'];
                            $arr['rlx_type'] = '防火墙策略';
                            $modelRlx->add($arr);
                            $count++;
                            $sevCount++;
                        }
                    }
                }
            }
            //subelement中的物理服务器子ip检索
            $subip = $modelSub->field('sub_content,sub_pid,sub_field')->where("sub_content = '%s'and sub_field = 'sev_subip'",$val['sub_content'])->find();
            if(!empty($subip)){
                //判断该资源是否关联事项
                $rlxDatas = $modelRlx->where("rlx_zyid = '%s' and rlx_type = '物理服务器'  and rlx_atpstatus is null",$subip['sub_pid'])->select();
                if(!empty($rlxDatas)){
                    foreach($rlxDatas as $ki =>$vi) {
                        $whered['rlx_zyid'] = ['eq',$val['sub_pid']];
                        $whered['rlx_sxid'] = ['eq',$vi['rlx_sxid']];
                        $whered['rlx_rwid'] = ['eq',$vi['rlx_rwid']];
                        $whered['rlx_type'] = ['eq','防火墙策略'];
                        $whered['rlx_atpstatus'] = ['exp','is null'];
                        //判断这条关联关系是否已经存在
                        $ress = $modelRlx->field('rlx_atpid')->where($whered)->find();
                        if(empty($ress)) {
                            $array['rlx_atpid'] = makeGuid();
                            $array['rlx_atpcreatetime'] = $date;
                            $array['rlx_atpcreateuser'] = $user;
                            $array['rlx_zyid'] = $val['sub_pid'];
                            $array['rlx_sxid'] = $vi['rlx_sxid'];
                            $array['rlx_rwid'] = $vi['rlx_rwid'];
                            $array['rlx_type'] = '防火墙策略';
                            $modelRlx->add($array);
                            $count++;
                            $sevCount++;
                        }
                    }
                }
            }

            //虚拟服务器IP地址检索
            $sevvIps = $modelSevv->field('sevv_atpid,sevv_ip')->where("sevv_ip = '%s' and sevv_atpstatus is null",$val['sub_content'])->find();
            if(!empty($sevvIps)){
                //判断该资源是否关联事项
                $rlxDatav = $modelRlx->where("rlx_zyid = '%s' and rlx_type = '虚拟服务器' and rlx_atpstatus is null",$sevvIps['sevv_atpid'])->select();
                if(!empty($rlxDatav)){
                    foreach($rlxDatav as $ky =>$vy){
                        $wheres['rlx_zyid'] = ['eq',$val['sub_pid']];
                        $wheres['rlx_sxid'] = ['eq',$vy['rlx_sxid']];
                        $wheres['rlx_rwid'] = ['eq',$vy['rlx_rwid']];
                        $wheres['rlx_type'] = ['eq','防火墙策略'];
                        $wheres['rlx_atpstatus'] = ['exp','is null'];
                        //判断这条关联关系是否已经存在
                        $resv = $modelRlx->field('rlx_atpid')->where($wheres)->find();
                        if(empty($resv)){
                            $arrs['rlx_atpid'] = makeGuid();
                            $arrs['rlx_atpcreatetime'] = $date;
                            $arrs['rlx_atpcreateuser'] = $user;
                            $arrs['rlx_zyid'] = $val['sub_pid'];
                            $arrs['rlx_sxid'] = $vy['rlx_sxid'];
                            $arrs['rlx_rwid'] = $vy['rlx_rwid'];
                            $arrs['rlx_type'] = '防火墙策略';
                            $modelRlx->add($arrs);
                            $count++;
                            $sevvCount++;
                        }
                    }
                }
            }

            //subelement中的虚拟服务器子ip检索
            $subipv = $modelSub->field('sub_content,sub_pid,sub_field')->where("sub_content = '%s'and sub_field = 'sevv_subip'",$val['sub_content'])->find();
            if(!empty($subipv)){
                //判断该资源是否关联事项
                $rlxDatavs = $modelRlx->where("rlx_zyid = '%s' and rlx_type = '虚拟服务器'  and rlx_atpstatus is null",$subipv['sub_pid'])->select();
                if(!empty($rlxDatavs)){
                    foreach($rlxDatavs as $kiv =>$viv) {
                        $whered['rlx_zyid'] = ['eq',$val['sub_pid']];
                        $whered['rlx_sxid'] = ['eq',$viv['rlx_sxid']];
                        $whered['rlx_rwid'] = ['eq',$viv['rlx_rwid']];
                        $whered['rlx_type'] = ['eq','防火墙策略'];
                        $whered['rlx_atpstatus'] = ['exp','is null'];
                        //判断这条关联关系是否已经存在
                        $ressv = $modelRlx->field('rlx_atpid')->where($whered)->find();
                        if(empty($ressv)) {
                            $arrayv['rlx_atpid'] = makeGuid();
                            $arrayv['rlx_atpcreatetime'] = $date;
                            $arrayv['rlx_atpcreateuser'] = $user;
                            $arrayv['rlx_zyid'] = $val['sub_pid'];
                            $arrayv['rlx_sxid'] = $viv['rlx_sxid'];
                            $arrayv['rlx_rwid'] = $viv['rlx_rwid'];
                            $arrayv['rlx_type'] = '防火墙策略';
                            $modelRlx->add($arrayv);
                            $count++;
                            $sevvCount++;
                        }
                    }
                }
            }
        }
        echo '共添加关联关系'.$count.'条,其中与sev相关新增'.$sevCount.'条,与sevv相关新增'.$sevvCount.'条';
    }

}