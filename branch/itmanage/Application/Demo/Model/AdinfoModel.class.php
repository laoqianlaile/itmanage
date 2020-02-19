<?php
namespace Demo\Model;
use Think\Model;
class AdinfoModel extends BaseModel {

    /**
     * 获取Ad所有数据
     */
    function gatAllAdData(){
        $adInfo = S('Adinfo');
        if(empty($adInfo)){
            $adInfo    = M('adinfo')->select();
            S('Adinfo',$adInfo,3600);
        }
        return $adInfo;
    }

    function gatAllAdDld(){
        $adInfo = S('AdinfoDld');
        if(empty($adInfo)){
            $adInfo    = M('adinfo')->field('ad_user,ad_workstations')->select();
            S('AdinfoDld',$adInfo,3600);
        }
        return $adInfo;
    }

    function getAdDiffData(){
        $adDiffData = S('adDiffData');
        if(empty($adDiffData)){
            //ad登录到信息
            $adInfo = $this->gatAllAdDld();
            //terminal资产表信息
            $tmnInfo = D('Terminal')->zdUserName();
            //usbkey信息
            $usbkeyInfo = D('Terminal')->UsbKeyName();
            //登录到白名单
            $AllowLogin = D('Dictionary')->AllowLogin();
            $Result = [];
            foreach($adInfo as $key=>$val){
                $ad_workstations = strtoupper($val['ad_workstations']);
                if(empty($ad_workstations)){
                    $Result[$val['ad_user']]['ad_user']     = $val['ad_user'];
                    $Result[$val['ad_user']]['ad_dld']      = 'ALL';
                    if(!empty($tmnInfo[$val['ad_user']])){
                        $Result[$val['ad_user']]['zd_user'] = $val['ad_user'];
                        $Result[$val['ad_user']]['zd_dld']  = $tmnInfo[$val['ad_user']];
                    }else{
                        $Result[$val['ad_user']]['zd_user'] = '';
                        $Result[$val['ad_user']]['zd_dld']  = '';
                    }
                    unset($adInfo[$key]);
                }else{
                    $dld = explode(',',$ad_workstations);
                    foreach($dld as $k=>$v){
                        $tmp = strpos($AllowLogin,$v);

                        if($tmp !== false){
                            continue;
                        }else{
                            $tmp1 = strpos($tmnInfo[$val['ad_user']],$v);
                            $tmp2 = strpos($usbkeyInfo[$val['ad_user']],$v);
                            if(($tmp1 === false) && ($tmp2 === false)){
                                $Result[$val['ad_user']]['ad_user'] = $val['ad_user'];
                                $Result[$val['ad_user']]['ad_dld']  = $ad_workstations;
                                $Result[$val['ad_user']]['zd_user'] = $val['ad_user'];
                                $Result[$val['ad_user']]['zd_dld']  = $tmnInfo[$val['ad_user']];
                                break;
                            }else{
                                continue;
                            }
                        }
                    }
                }
            }
            S('adDiffData',$Result,3600);
            $adDiffData = $Result;
        }
        return $adDiffData;
    }

    function AdData($queryparam){
        $adDiffData = $this->getAdDiffData();
        if(!empty($queryparam['ad_user'])){
            $ad_user = trim($queryparam['ad_user']);
            if(empty($adDiffData[$ad_user])){
                $adDiffData = [];
            }else{
                $adDiffData = array($adDiffData[$ad_user]);
            }
        }
        if(!empty($queryparam['ad_dld'])){
            $ad_dld = trim($queryparam['ad_dld']);
            $ad_dld = strtoupper($ad_dld);
            $tmp    = [];
            foreach($adDiffData as $key=>$val){
                $tmp1 = strpos($val['ad_dld'],$ad_dld);
                if($tmp1 === false){
                    continue;
                }else{
                    $tmp[] = $val;
                }
            }
            $adDiffData = $tmp;
        }
        $limit = $queryparam['limit'];
        $offset = empty($queryparam['offset']) ? 1:$queryparam['offset'];
//        print_r($adDiffData);die;
        $adDiffData = array_values($adDiffData);
        if(!empty($adDiffData)){
            $list   = array_slice($adDiffData, $offset - 1, $limit);
            $result = array(
                'total' => count($adDiffData),
                'rows' => $list
            );
            exit(json_encode($result));
        }
    }

    /**
     * 域控使用人部门信息（格式：ad_user->ad_compnay,zd_postofficebox,zd_postalcode）
     */
    function getAdUserInfo(){
        $userInfo  = M('adinfo')->field('ad_user,ad_company,ad_postofficebox,ad_postalcode')->select();
        $userInfos = [];
        foreach($userInfo as $key=>$val){
            if(empty($val['zd_postofficebox'])){
                $userInfos[$val['ad_user']]['ad_dept'] = $val['ad_company'];
            }else{
                $userInfos[$val['ad_user']]['ad_dept'] = $val['ad_postofficebox'];
            }
            $userInfos[$val['ad_user']]['ad_office'] = $val['ad_postalcode'];
        }
        return $userInfos;
    }
}