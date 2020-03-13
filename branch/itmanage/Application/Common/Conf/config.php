<?php
return array(
    'DB_TYPE'=>'Oracle',// 数据库类型

    'DB_HOST'=>'10.78.103.11',// 服务器地址
    'DB_NAME'=>'zhouxun',// 数据库名
    'DB_USER'=>'ITMANAGE',// 用户名
    'DB_PWD'=>'ITMANAGE',// 密码
   /*'DB_HOST'=>'wydb_mis.hq.cast',// 服务器地址
    'DB_NAME'=>'mis',// 数据库名
    'DB_USER'=>'itmanage',// 用户名
   'DB_PWD'=>'itmanage',// 密码*/
    'DB_PORT' => 1521,// 端口
    'DB_PREFIX'=>'',// 数据库表前缀
    'DB_CHARSET'=>'utf8',// 数据库字符集

    //运维系统clob数据类型转换需要
    'appmanagedocclob'=>[
        'DB_TYPE'=>'Oracleclob',// 数据库类型

        'DB_HOST'=>'10.78.103.11',// 服务器地址
        'DB_NAME'=>'zhouxun',// 数据库名
        'DB_USER'=>'ITMANAGE',// 用户名
        'DB_PWD'=>'ITMANAGE',// 密码
        // 'DB_HOST'=>'wydb_mis.hq.cast',// 服务器地址
        // 'DB_NAME'=>'mis',// 数据库名
        // 'DB_USER'=>'ITMANAGE',// 用户名
        // 'DB_PWD'=>'ITMANAGE',// 密码
        'DB_PORT' => 1521,// 端口
        'DB_PREFIX'=>'',// 数据库表前缀
        'DB_CHARSET'=>'utf8',// 数据库字符集
    ],// 数据库字符集

    //小Q系统数据库连接
    'q'=>[
        'DB_TYPE'=>'mysql',// 数据库类型

        'DB_HOST'=>'127.0.0.1',// 服务器地址
        'DB_NAME'=>'technicalsupportcenter',// 数据库名
        'DB_USER'=>'root',// 用户名
        'DB_PWD'=>'',// 密码
        //  'DB_HOST'=>'10.64.3.32',// 服务器地址
        // 'DB_NAME'=>'technicalsupportcenter',// 数据库名
        // 'DB_USER'=>'root',// 用户名
        // 'DB_PWD'=>'root',// 密码
        'DB_PORT'=>3306,// 端口
        'DB_PREFIX'=>'tsc_',// 数据库表前缀
        'DB_CHARSET'=>'utf8'
    ],// 数据库字符集

    'DB_OLD'=>[
        'DB_TYPE'   => 'OSCAR', // 数据库类型
        'DB_HOST'   => '10.78.72.236', // 服务器地址
        'DB_NAME'   => 'OSRDB', // 数据库名
        'DB_USER'   => 'itmanage', // 用户名
        'DB_PWD'    => 'itmanage', // 密码
        'DB_PORT'   => 2003, // 端口
        'DB_PREFIX' => '', // 数据库表前缀
        'DB_CHARSET'=> 'utf8', // 字符集
    ],
    'ExcelServer_CONFIG'=>array(
        'DB_TYPE'=>'sqlsrv',// 数据库类型
        'DB_HOST'=>'10.64.2.18',// 服务器地址
        'DB_NAME'=>'HRAssessPerformance',// 数据库名
        'DB_USER'=>'sa',// 用户名
        'DB_PWD'=>'Guanli200*',// 密码
        'DB_PORT'=>1433,// 端口
        'DB_PREFIX'=>'',// 数据库表前缀
        'DB_CHARSET'=>'utf8',// 数据库字符集
        //'URL_CASE_INSENSITIVE' => false
    ),
    'Publicdb_CONFIG'=>array(
        'DB_TYPE'=>'Oracle',// 数据库类型
        'DB_HOST'=>'wydb_mis.hq.cast',// 服务器地址
        'DB_NAME'=>'mis',// 数据库名
        'DB_USER'=>'Publicdb',// 用户名
        'DB_PWD'=>'Publicdb',// 密码
        'DB_PORT'=>1521,// 端口
        'DB_PREFIX'=>'',// 数据库表前缀
        'DB_CHARSET'=>'utf8',// 数据库字符集
    ),

    //-----------------------系统相关-------------------
    //‘项目管理员’的角色id
    'PROJECTMANAGERID' => '6',
    //‘系统配置管理员’的角色id
    'SYSSETTINGMANAGERID' => '4',
    //‘安全管理员’的角色id
    'SAFEMANAGERID' => '3',
    //系统登录密码加密的盐
    'PWD_SALT' => 'xm_',
    //普通用户角色id
    'COMMONUSERID' => 'T94AEC057423743E5AAD7B43A2',


    //----------------------会话相关-------------------
    // Cookie有效期
    'COOKIE_EXPIRE' =>  86400,
    // Cookie前缀 避免冲突
    'COOKIE_PREFIX' =>  'itmanage_c_',
    // session 前缀
//    'SESSION_PREFIX'=>  'itmanage_s_',


    //----------------------邮件相关--------------------
    'EMAIL_FROM' => '501MsgCenter@hq.cast', //邮件发件箱地址


    //--------------------- 表单相关--------------------
    //总体部表单系统创建表单链接
    'ZONGTIBUFORM_CREATE_ADDRESS' => 'http://10.78.72.76:8088/portal/bice/bice_jsp/aws_createtask.jsp?userId=%s&uuid=%s',
    //总体部表单系统查看、编辑表单链接
    'ZONGTIBUFORM_READ_ADDRESS' => 'http://10.78.72.76:8088/portal/bice/bice_jsp/aws_opentask_log.jsp?createuser=%s&bindid=%s&taskid=%s',
    //院表单系统创建表单链接
    'YUANFORM_CREATE_ADDRESS' => 'http://10.64.1.129/System/SSO_Login_5y.asp?username=%s@5y&templatename=%s&DataID=%s&Link=1&checkId=',
    //院表单系统查看、编辑表单链接
    'YUANFORM_READ_ADDRESS' => 'http://10.64.1.129/System/SSO_Login_5y.asp?username=%s@5y&templatename=%s&DataID=%s&Link=1&checkId=',


    //---------------------文件相关--------------------
    //加密文件路径时的key，解密用该参数解密
    'FILE_KEY' => "a;sdka;lk'-=031ropk",
    //文件内容加密开关
    'FILECONTENT_ENCRYPT' => true,
    //文件加密函数
    'FILECONTENT_ENCRYPT_FUNC' => 'base64_encode',
    //文件解密函数
    'FILECONTENT_DECIPHERING_FUNC' => 'base64_decode',


    //加载其他配置文件
    'LOAD_EXT_CONFIG' => 'redis_key_config',


    //服务器管理frame页面细化授权 菜单 => 角色
    'SevRoleViewConfig' => [
        '物理服务器' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Sev/index'
        ],
        '虚拟服务器' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Sev/virtual'
        ]
    ],

    //电源机柜管理frame页面细化授权 菜单 => 角色
    'DianRoleViewConfig' => [
        '电源管理' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Dianyuan/index'
        ],
        '机柜管理' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Jigui/index'
        ]
    ],

    //数据库管理frame页面细化授权 菜单 => 角色
    'DbRoleViewConfig' => [
        '数据库用户' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Databases/tablesIndex'
        ],
        '数据库实例' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Databases/instanceIndex'
        ],
        '数据库平台' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Databases/index'
        ]
    ],
    //交换机管理frame页面细化授权 菜单 => 角色
    'NetdeviceRoleViewConfig' => [
        '交换机' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Netdevice/index'
        ],
        '光纤交换机' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Netdevice/OpticalFiber'
        ]
    ],
    //邮件发送审计frame页面细化授权 菜单 => 角色
    'AnymailRoleViewConfig' => [
        '疑似问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Anymail/index'
        ],
        '误报问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Anymail/problem'
        ]
    ],
    //U盘违规操作审计frame页面细化授权 菜单 => 角色
    'UlogRoleViewConfig' => [
        '疑似问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Ulog/index'
        ],
        '误报问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Ulog/Uproblem'
        ]
    ],
    //违规外联审计frame页面细化授权 菜单 => 角色
    'IlogRoleViewConfig' => [
        '疑似问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Ilog/index'
        ],
        '误报问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Ilog/Iproblem'
        ]
    ],
    //U盘违规操作审计frame页面细化授权 菜单 => 角色
    'AlogRoleViewConfig' => [
        '疑似问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Alog/index'
        ],
        '误报问题' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '管理员',
                'T94AEC057423743E5AAD7B43A2' => '普通用户'
            ],
            'path' => 'Home/Alog/Aproblem'
        ]
    ],
    '各单位管理人员角色id' => 'TD7466EB50CB14A53B8EAF064',
    'publicUrl' => 'http://localhost/itmanage/trunk/src/index.php',
    'ipStatusAdd'=>['在用','关机','未上线','上架','外借'],
    'ipStatusDel'=>['回收','下架'],

    'OCI' => oci_connect('itmanage','itmanage','10.78.103.11/zhouxun','utf8')
    // 'OCI' => oci_connect('itmanage','itmanage','wydb_mis.hq.cast/mis','utf8')
);
