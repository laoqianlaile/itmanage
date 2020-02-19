<?php
namespace Universal\Controller;
use Think\Controller;
use Think\Exception;
class CodeGeneratorController extends Controller{

    /**
     * 展示代码生成页面
     */
    public function index(){
        $model = D('CodeGenerator');
        $searchType = $model->searchType;
        $formType= array_keys($model->formType);
//        echo intval(I('get.show_index'));die;
        $this->assign('searchType', json_encode($searchType));
        $this->assign('formType', json_encode($formType));
        $this->display();
    }

    /**
     * 开始生成代码
     */
    public function buildCode(){
        $this->checkConfigParam(true); //检测配置参数是否可用

        $generatorModel = D('CodeGenerator');
        $res = $generatorModel->beginBuildCode();
        if($res){
            exit(makeStandResult(1, '代码已生成'));
        }else{
            exit(makeStandResult(-1, '代码生成失败'));
        }
    }

    private function sql_str($table = null){
        if($table){
            $table = strtoupper($table);
            return "SELECT
                    A .column_name,
                    data_type,
                    c.comments,
                    DECODE (
                        A .column_name,
                        b.column_name,
                        1,
                        0
                    ) pk
                FROM
                    user_tab_columns A,
                    (
                        SELECT
                            column_name
                        FROM
                            user_constraints c,
                            user_cons_columns col
                        WHERE
                            c.constraint_name = col.constraint_name
                        AND c.constraint_type = 'P'
                        AND c.table_name = '{$table}'
                    ) b,
                    user_col_comments c
                WHERE
                    A.table_name = '{$table}'
                AND c.table_name = '{$table}'
                AND A .column_name = b.column_name (+)
                AND A .column_name = c.column_name (+)";
        }
    }

    /**
     * 检测配置参数是否可用，可用则吐出配置数据表的字段信息
     * @param bool|false $isThinkPhp
     * @return bool
     */
    public function checkConfigParam($isThinkPhp = false){
        $databaseMark = trim(I('post.database_mark'));
        $tableName = trim(I('post.table_name'));
        $controllerName = ucfirst(trim(I('post.controller_name')));
        $moduleName = ucfirst(trim(I('post.module_name')));
        $filterField = intval(I('post.filter_field'));

        if(empty($tableName)) exit(makeStandResult(-1, '请输入数据表全称'));
        if(empty($moduleName)) exit(makeStandResult(-1, '请输入模块名称'));

        //检测模块是否存在
        $generatorModel = D('CodeGenerator');
        $res = $generatorModel->checkModule($moduleName);
        if(!$res) exit(makeStandResult(-1, '模块不存在'));

        //检测控制器是否存在
        $res = $generatorModel->checkController($moduleName, $controllerName);
        if($res) exit(makeStandResult(-1, '控制器已经存在'));
        try{
            if(empty($databaseMark)){
                $model = M($tableName);
            }else{
                $model = M($tableName, '', $databaseMark);
            }
            $tableFields = $model->query($this->sql_str($tableName));
            array_walk($tableFields, function(&$v){
                $v['column_name'] = strtolower($v['column_name']);
            });
            unset($v);
            if($filterField){
                $tableFields = $generatorModel->filterField($tableFields);
            }
            if(empty($tableFields)){
                exit(makeStandResult(-1, '获取表信息失败'));
            }else{
                if($isThinkPhp){
                    return true;
                }else{
                    exit(makeStandResult(1, $tableFields));
                }
            }
        }catch(Exception $e){
            exit(makeStandResult(-1, '数据库配置错误'));
        }
    }

    /**
     * 加载已保存的数据
     */
    public function loadData(){
        $databaseMark = trim(I('post.database_mark'));
        $tableName = trim(I('post.table_name'));

        if(empty($tableName)) exit(makeStandResult(-1, '请输入数据表全称'));

        $key = $databaseMark. $tableName;
        $result = json_decode(S($key));
        if(empty($result)){
            exit(makeStandResult(-1, '未检测到已保存的数据'));
        }else{
            exit(makeStandResult(1, $result));
        }
    }

    /**
     * 保存数据
     */
    public function saveData(){
        //接受post参数
        $data = I('post.');

        //数据库连接标识
        $databaseMark = trim($data['database_mark']);
        //表名
        $tableName = trim($data['table_name']);
        $data['fieldMap'] = empty($data['fieldMap']) ? [] : $data['fieldMap'];
        $data['configSearch'] = empty($data['configSearch']) ? [] : $data['configSearch'];
        if(empty($tableName)) exit(makeStandResult(-1, '请输入数据表全称'));

        $data = json_encode($data);
        //以数据库连接标识和表名作为唯一标识
        $key = $databaseMark. $tableName;
        S($key, $data);
        exit(makeStandResult(1, '保存成功'));
    }
}