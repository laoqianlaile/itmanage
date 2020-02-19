<?php

return array(

    //老系统的配置
    'SwitchConfigureWs'=>'http://10.78.72.240:8080/Script/Script.asmx?wsdl',
    '公用计算机'=>'guidDA36A81A-15B6-4D11-9857-B67391CF9C0A',
    'SwitchConfiguresw'=>[
        'netdevice_net'=>['eq','guid79946862-B77B-4687-A242-73BF8D046D74'], //涉密网
        'netdevice_usage'=>[['eq','楼层交换机'],['eq','室内交换机'],'OR']
    ],
    'SwitchConfigureport'=>[
        'upper(sw_interface)'=>['notlike',['VLAN%','TRUNK%'],'OR']
    ],
    'FORMTYPE'=>[
        '116' => '涉密网公共机入网申请表',
        '117' => '涉密网公共机变更入网申请表',
        '20'  => '涉密网公共机撤销入网申请表',
        '119' => '涉密网设备入网申请表',
        '122' => '涉密网设备撤销入网申请表',
        '308' => '涉密网设备变更入网申请表',
        '110' => '涉密网计算机入网申请',
        '15'  => '涉密网计算机变更入网申请',
        '18'  => '涉密网计算机新增入网申请',
        '21'  => '涉密网计算机撤销入网申请',
        '501' => '涉密网测试用机入网申请表',
        '502' => '涉密网测试用机变更入网申请表',
        '503' => '涉密网测试用机撤销入网申请表'
    ]
    //关联关系
    // 'TABLE_CONFIG'=>[
    //     '物理服务器-光纤交换机'=>[
    //         [
    //             'it_sev'=>[
    //                 'id'=>'rl_cmainid',
    //                 'name'=>'rl_cname',
    //                 'ip'=>'rl_cip',
    //                 'type'=>'rl_ctype',
    //                 'table'=>'rl_ctable'
    //             ]
    //         ],
    //         [
    //             'it_netdevice'=>[
    //                 'id'=>'rl_rmainid',
    //                 'name'=>'rl_rname',
    //                 'ip'=>'rl_rip',
    //                 'type'=>'rl_rtype',
    //                 'table'=>'rl_rtable'
    //             ]
    //         ]
    //     ],
    //     '物理服务器-应用系统'=>[
    //         [
    //             'it_sev'=>[
    //                 'id'=>'rl_cmainid',
    //                 'name'=>'rl_cname',
    //                 'ip'=>'rl_cip',
    //                 'type'=>'rl_ctype',
    //                 'table'=>'rl_ctable'
    //             ]
    //         ],
    //         [
    //             'it_application'=>[
    //                 'id'=>'rl_rmainid',
    //                 'name'=>'rl_rname',
    //                 'ip'=>'rl_rip',
    //                 'type'=>'rl_rtype',
    //                 'table'=>'rl_rtable'
    //             ]
    //         ]
    //     ],
    //     '应用系统-数据库'=>[],
    //     '物理服务器-机柜'=>[],
    //     '集中存储-光纤交换机'=>[],
    //     '集中存储-机柜'=>[],
    //     '光纤交换机-机柜'=>[],
    //     '虚拟服务器-物理服务器'=>[
    //         [
    //             'it_sev'=>[
    //                 'id'=>'rl_cmainid',
    //                 'name'=>'rl_cname',
    //                 'ip'=>'rl_cip',
    //                 'type'=>'rl_ctype',
    //                 'table'=>'rl_ctable'
    //             ]
    //         ],
    //         [
    //             'it_sev'=>[
    //                 'id'=>'rl_rmainid',
    //                 'name'=>'rl_rname',
    //                 'ip'=>'rl_rip',
    //                 'type'=>'rl_rtype',
    //                 'table'=>'rl_rtable'
    //             ]
    //         ]
    //     ],
    //     '虚拟服务器-应用系统'=>[
    //         [
    //             'it_sev'=>[
    //                 'id'=>'rl_cmainid',
    //                 'name'=>'rl_cname',
    //                 'ip'=>'rl_cip',
    //                 'type'=>'rl_ctype',
    //                 'table'=>'rl_ctable'
    //             ]
    //         ],
    //         [
    //             'it_application'=>[
    //                 'id'=>'rl_rmainid',
    //                 'name'=>'rl_rname',
    //                 'ip'=>'rl_rip',
    //                 'type'=>'rl_rtype',
    //                 'table'=>'rl_rtable'
    //             ]
    //         ]
    //     ]
//        '应用系统-数据库'=>[],
//        '应用系统-数据库'=>[],
//        '应用系统-数据库'=>[],
//        '应用系统-数据库'=>[],
//        '应用系统-数据库'=>[],
//        '应用系统-数据库'=>[]
    // ]
);

