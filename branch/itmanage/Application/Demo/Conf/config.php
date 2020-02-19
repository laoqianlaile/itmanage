<?php
return array(
    'DB_TYPE'   => 'OSCAR', // 数据库类型
    'DB_HOST'   => '10.78.72.236', // 服务器地址
    'DB_NAME'   => 'OSRDB', // 数据库名
    'DB_USER'   => 'itmanage', // 用户名
    'DB_PWD'    => 'itmanage', // 密码
    'DB_PORT'   => 2003, // 端口
    'DB_PREFIX' => 'it_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'ORACLE_CONFIG'=>array(
        'DB_TYPE'=>'Oracle',// 数据库类型
        'DB_HOST'=>'10.78.72.40',// 服务器地址
        'DB_NAME'=>'brightsm',// 数据库名
        'DB_USER'=>'newyunwei',// 用户名
        'DB_PWD'=>'newyunwei',// 密码
        'DB_PORT'=>1521,// 端口
        'DB_PREFIX'=>'',// 数据库表前缀
        'DB_CHARSET'=>'utf8',// 数据库字符集
        //'URL_CASE_INSENSITIVE' => false
    ),
    'ORACLE_CONFIG1'=>array(
        'DB_TYPE'=>'Oracle',// 数据库类型
        'DB_HOST'=>'wydb_mis.hq.cast',// 服务器地址
        'DB_NAME'=>'mis',// 数据库名
        'DB_USER'=>'itsupport_read',// 用户名
        'DB_PWD'=>'Guanli2017',// 密码
        'DB_PORT'=>1521,// 端口
        'DB_PREFIX'=>'',// 数据库表前缀
        'DB_CHARSET'=>'utf8',// 数据库字符集
        //'URL_CASE_INSENSITIVE' => false
    ),
    'BD_CONFIG'=>array(
        'DB_TYPE'=>'sqlsrv',// 数据库类型
        'DB_HOST'=>'10.64.1.129',// 服务器地址
        'DB_NAME'=>'BjsascForm',// 数据库名
        'DB_USER'=>'yunwei4',// 用户名
        'DB_PWD'=>'yunwei4',// 密码
        'DB_PORT'=>1433,// 端口
        'DB_PREFIX'=>'',// 数据库表前缀
        'DB_CHARSET'=>'utf8',// 数据库字符集
        //'URL_CASE_INSENSITIVE' => false
    ),
    'ATP_CONFIG'=>[
        'DB_TYPE'   => 'OSCAR', // 数据库类型
        'DB_HOST'   => '10.78.72.236', // 服务器地址
        'DB_NAME'   => 'OSRDB', // 数据库名
        'DB_USER'   => 'atp', // 用户名
        'DB_PWD'    => 'atp', // 密码
        'DB_PORT'   => 2003, // 端口
        'DB_PREFIX' => '', // 数据库表前缀
        'DB_CHARSET'=> 'utf8', // 字符集
    ],
    'DB_NEW'=>[
        'DB_TYPE'=>'Oracle',// 数据库类型
        'DB_HOST'=>'10.78.103.11',// 服务器地址
        'DB_NAME'=>'zhouxun',// 数据库名
        'DB_USER'=>'ITMANAGE',// 用户名
        'DB_PWD'=>'ITMANAGE',// 密码
        'DB_PORT' => 1521,// 端口
        'DB_PREFIX'=>'',// 数据库表前缀
        'DB_CHARSET'=>'utf8',// 数据库字符集
    ],
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
    ],

    //
    'MeetRoleViewConfig' => [
        '本处室管理活动管理' => [
            'roles' => [
                'T2233AA12393D480B9A1AF13B' => '科研部处室管理员',
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处',
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工'
            ],
            'path' => 'Demo/Meet/index'
        ],
        '部管理活动查询' => [
            'roles' => [
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工',
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处'
            ],
            'path' => 'Demo/Meet/indexNopower'
        ],
        '部管理活动管理' => [
            'roles' => [
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处'
            ],
            'path' => 'Demo/Meet/indexAll'
        ],
    ],
    //院级待办frame页面细化授权 菜单 => 角色
    'YuanRoleViewConfig' => [
        '待办事项维护' => [
            'roles' => [
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处'
            ],
            'path' => 'Demo/Yuan/index'
        ],
        '待办事项提交' => [
            'roles' => [
                'T3243F9BFBAA84FC7B532669B' => '各单位科技处管理人员'
            ],
            'path' => 'Demo/Yuan/tijiao'
        ],
        '待办事项确认' => [
            'roles' => [
                'T061AB8E3062D48849070093E' => '科研部部领导',
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处',
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工',
                'T5079277345D0405C9FC132B5' => '科研部处室领导'
            ],
            'path' => 'Demo/Yuan/confirm'
        ],
        '待办事项查询' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '院领导',
                'T061AB8E3062D48849070093E' => '科研部部领导',
                'T5079277345D0405C9FC132B5' => '科研部处室领导',
                'T2233AA12393D480B9A1AF13B' => '科研部处室管理员',
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处',
                'T41DB99CD01564605AC063A78' => '各单位责任人',
                'T3243F9BFBAA84FC7B532669B' => '各单位科技处管理人员',
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工'

            ],
            'path' => 'Demo/Yuan/rogatory'
        ]
    ],
    //部门待办frame页面细化授权
    'DeptRoleViewConfig' => [
        '部门待办事项维护' => [
            'roles' => [
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处'
            ],
            'path' => 'Demo/Dept/index'
        ],
        '部门待办事项提交' => [
            'roles' => [
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工',
            ],
            'path' => 'Demo/Dept/tijiao'
        ],
        '部门待办事项确认' => [
            'roles' => [
                'T061AB8E3062D48849070093E' => '科研部部领导',
            ],
            'path' => 'Demo/Dept/confirm'
        ],
        '部门待办事项查询' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '院领导',
                'T061AB8E3062D48849070093E' => '科研部部领导',
                'T5079277345D0405C9FC132B5' => '科研部处室领导',
                'T2233AA12393D480B9A1AF13B' => '科研部处室管理员',
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处',
                'T41DB99CD01564605AC063A78' => '各单位责任人',
                'T3243F9BFBAA84FC7B532669B' => '各单位科技处管理人员',
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工',
            ],
            'path' => 'Demo/Dept/rogatory'
        ]
    ],
    //月计划待办frame页面细化授权
    'MonthRoleViewConfig' => [
        '月计划维护' => [
            'roles' => [
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处',
                'T2233AA12393D480B9A1AF13B' => '科研部处室管理员'
            ],
            'path' => 'Demo/Month/index'
        ],
        '本处室月计划维护' => [
            'roles' => [
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处',
                'T2233AA12393D480B9A1AF13B' => '科研部处室管理员'
            ],
            'path' => 'Demo/Month/indexBc'
        ],
        '月计划提交' => [
            'roles' => [
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工',
            ],
            'path' => 'Demo/Month/tijiao'
        ],
        '月计划确认' => [
            'roles' => [
                'T061AB8E3062D48849070093E' => '科研部部领导',
            ],
            'path' => 'Demo/Month/confirm'
        ],
        '月计划查询' => [
            'roles' => [
                'T886B426D95924386A24DA736' => '院领导',
                'T061AB8E3062D48849070093E' => '科研部部领导',
                'T5079277345D0405C9FC132B5' => '科研部处室领导',
                'T2233AA12393D480B9A1AF13B' => '科研部处室管理员',
                'T7CDE3A78C46F4E9EA327DC96' => '科研部综合处',
                'T41DB99CD01564605AC063A78' => '各单位责任人',
                'T3243F9BFBAA84FC7B532669B' => '各单位科技处管理人员',
                'T0F0CDEF8D7394B118C10DFED' => '科研部员工',
            ],
            'path' => 'Demo/Month/rogatory'
        ]
    ],
    'WyToDo'=>[
        'mt_name' => '会议名称',
        'td_name' => '待办事项',
        'td_planfinishdate' => '计划完成时间',
        'td_unit' => '责任单位',
        'td_kybchargeman' => '督办人',
        'td_kybunit' => '责任处室',
        'td_kybleader' => '责任领导',
    ],
    'DeptToDo'=>[
        'utd_meetname' => '会议名称',
        'utd_name' => '待办事项',
        'utd_finishtype' => '完成形式',
        'utd_planfinishdate' => '计划完成时间',
        'utd_kybchargeman' => '责任人',
        'utd_kybunit' => '责任处室',
        'utd_kybleader' => '责任领导',
    ],
    'MonthToDo'=>[
        'ytd_worktype' => '工作分类',
        'ytd_name' => '工作事项',
        'ytd_content' => '行动项目',
        'ytd_planfinishdate' => '计划完成时间',
        'ytd_finishtype' => '完成形式',
        'ytd_kybchargeman' => '责任人',
        'ytd_kybunitleader' => '主管处领导',
        'ytd_kybunit' => '责任处室',
        'ytd_kybleader' => '主管部领导',
        'ytd_dealmethod' => '处置措施',
    ],
);