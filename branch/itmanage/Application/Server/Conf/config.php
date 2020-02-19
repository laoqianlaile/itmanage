<?php
return array(
    'EQUIPMENT_TYPE' => [
        '涉密设备入网单' => [
            0 => '154111', // 打印机及刷卡器
            1 => '154110', // 瘦客户机
            2 => '154113', // 视频终端
            3 => '154115', // 试验设备
            4 => '155274', // 扫描仪
            5 => '157014'  // 导出专用光盘刻录机
        ]
    ],
    'COMPAREFIELD'=>[
        'zd_type'            => '设备类型',
        'zd_devicecode'      => '设备编码',
        'zd_senqno'          => '设备序列号',
        'zd_name'            => '设备名称',
        'zd_ipaddress'       => 'IP地址',
        'zd_macaddress'      => 'MAC地址',
        'zd_useman'          => '使用人',
        'zd_dutyman'         => '责任人',
        'zd_area'            => '地区',
        'zd_belongfloor'     => '楼宇',
        'zd_roomno'          => '房间号',
        'zd_factoryname'     => '厂家',
        'zd_modelnumber'     => '型号',
        'zd_secretlevel'     => '密级',
        'zd_isisolate'       => '是否隔离',
        'zd_isinstalljammer' => '是否安装干扰器',
        'zd_memo'            => '说明',
        'zd_harddiskseq'     => '硬盘序列号',
        'zd_osinstalltaime'  => '操作系统安装时间'
    ],
    'DICTIONARYFIELD'=>[
        'zd_type',
        'zd_area',
        'zd_belongfloor',
        'zd_dutyman',
        'zd_factoryname',
        'zd_modelnumber',
        'zd_secretlevel'
    ]
);
