<?php
/**
 * Created by PhpStorm.
 * User: baijingqi
 * Date: 2019/04/11
 * Time: 10:15
 */
namespace Universal\Model;
use Think\Exception;
use Think\Model;
class CodeGeneratorModel extends Model{
    Protected $autoCheckFields = false;
    public $searchType = [
        1 => '模糊查询',
        2 => '下拉框查询',
        3 => '普通范围查询',
        4 => '日期范围查询'
    ]; //搜索类型

    public $formType = [
        '普通文本框' => '<input type="text" class="form-control" style="width:99%;" name="%s1" id="%s2" value="%s3">',
        '日期文本框' => <<<EOF
<input type="text" class="form-control" style="width:99%;" name="%s1" id="%s2" value="%s3" onClick="WdatePicker({dateFmt:'yyyy-MM-dd'})">
EOF
        ,
        '普通下拉搜索框' => '<select  name="%s1" id="%s2" class="chosen-select" ></select>',
        '多选下拉搜索框' => '<select name="%s1" data-placeholder="请输入关键字进行检索" class="long-chosen-select" id="%s2" multiple ></select>',
        '文本域' => '<textarea name="%s1" id="%s2"   style="height:40px" class="form-control">%s3</textarea>'
    ]; //表单类型

    public $controllerPrefix = 'Controller.class.php'; //控制器后缀

    //搜索表单元素
    public $searchForm = [
        1 => '    <div class="form-group">
                    <label class="control-label" >%s1</label>
                    <div class="formEl-div" >
                        <input type="text" class="form-control" style="width:99%;"  id="%s2">
                    </div>
                </div>',
        2 => '    <div class="form-group">
                    <label class="control-label" >%s1</label>
                    <div class="formEl-div" >
                        <select id="%s2" class="chosen-select" ></select>
                    </div>
                </div>',
        3 => '    <div class="form-group">
                    <label class="control-label" >%s1</label>
                    <div class="formEl-div" >
                        <input type="text" class="form-control" style="width: 48%;" id="%s2">-<input type="text" class="form-control" style="width: 48%;"  id="%s3">
                    </div>
                </div>',
        4 => <<<EOD
    <div class="form-group">
                    <label class="control-label" >%s1</label>
                    <div class="formEl-div" >
                        <input type="text" class="form-control" style="width: 48%;"  onClick="WdatePicker({dateFmt:'yyyy-MM-dd'})" id="%s2">-<input type="text" class="form-control" style="width: 48%;" onClick="WdatePicker({dateFmt:'yyyy-MM-dd'})" id="%s3">
                    </div>
                </div>
EOD
    ];

    public $moduleName,$controllerName,$viewName,$tableName,$databaseMask,$pk = ''; //模块，控制器，视图，表名，数据库配置，主键字段名

    public $tableFields,$fieldMap,$configSearch = []; //表字段

    public $code = ''; //以utf8格式写入文件

    public $tab = '        '; //代码缩进

    public $smallTab = '    '; //代码缩进

    public $hasSelect, $hasDate = false; //默认不存在下拉菜单搜索，日期搜索

    public $tableHeader = '';  //展示表头、导出表头、批量增加表头

    public $addFromEleMent = [];  //添加页面表单包含的字段

    /**
     * 检测模块是否存在
     * @param $moduleName
     * @return bool
     */
    public function checkModule($moduleName){
        $modules = $this->getAllModules();
        if(in_array($moduleName, $modules)) return true;
        return false;
    }

    /**
     * 检测模块下控制器是否存在
     * @param $moduleName
     * @param $controllerName
     * @return bool
     */
    public function checkController($moduleName, $controllerName){
        $controllers = $this->getAllControllers($moduleName);
        if(in_array(ucfirst($controllerName), $controllers)) return true;
        return false;
    }

    /**
     * 获取模块下所有控制器
     * @param $moduleName
     * @return array
     */
    public function getAllControllers($moduleName){
        $path = dirname(dirname(__DIR__)).'\\'. $moduleName.'\\'.'Controller';
        $files = scandir($path);
        foreach($files as $key=>$value){
            if($value== '.' || $value == '..'){
                unset($files[$key]);
            }else{
                $files[$key] = str_replace($this->controllerPrefix, '', $value);
            }
        }
        reset($files);
        return $files;
    }

    /**
     * 获取当前框架的所有模块
     * @return array
     */
    public function getAllModules(){
        $files = scandir(dirname(dirname(__DIR__)));
        $banFile = ['runtime', 'common', '.', '..'];
        foreach($files as $key=>$value){
            if(in_array(strtolower($value), $banFile)) unset($files[$key]);
        }
        reset($files);
        return $files;
    }

    /**
     * 创建控制器和视图
     * @param $moduleName
     * @param $controllerName
     * @return bool
     */
    public function createControllerAndView($moduleName, $controllerName){
        return $this->createController($moduleName, $controllerName)  && $this->createView($moduleName, $controllerName);
    }

    /**
     * 生成空白控制器
     * @param $moduleName
     * @param $controllerName
     * @return bool
     */
    public function createController($moduleName, $controllerName){
        $path = dirname(dirname(__DIR__)).'\\'. $moduleName.'\\'.'Controller';
        $res = fopen($path.'\\' .$controllerName .$this->controllerPrefix, 'a');
        if($res) return true;
        return false;
    }

    /**
     * 创建视图目录及空白模板文件
     * @param $moduleName
     * @param $controllerName
     * @return bool
     */
    public function createView($moduleName, $controllerName){
        $path = dirname(dirname(__DIR__)).'\\'. $moduleName.'\\'.'View';
        $res = mkdir($path.'\\' .$controllerName);
        $this->createViewHtml($moduleName, $controllerName);
        if($res) return true;
        return false;
    }

    /**
     * 创建视图文件
     * @param $moduleName
     * @param $controllerName
     * @return int
     */
    public function createViewHtml($moduleName, $controllerName){
        $path = dirname(dirname(__DIR__)).'\\'. $moduleName.'\\'.'View'.'\\'.$controllerName;
        $handle1 = fopen($path.'\\' .'index.html', 'w+');
        $handle2 = fopen($path.'\\' .'add.html', 'w+');

        if($handle1 && $handle2){
            fclose($handle1);
            fclose($handle2);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 开始生成代码
     */
    public function beginBuildCode(){
        $this->controllerName = ucfirst(trim(I('post.controller_name')));
        $this->moduleName = ucfirst(trim(I('post.module_name')));
//        $this->moduleName = 'Admin';
        $this->viewName = ucfirst(trim(I('post.view_name')));
        $this->viewName = empty($this->viewName) ? 'xxx': $this->viewName;
        $this->databaseMark = trim(I('post.database_mark'));
        $this->tableName = trim(I('post.table_name'));
        $this->pk = trim(I('post.pkField'));

        if(empty($databaseMark)){
            $model = M($this->tableName);
        }else{
            $model = M($this->tableName, '', $this->databaseMark);
        }

        $this->tableFields = $this->filterField($model->getDbFields()); //获取字段信息
        $res = $this->createControllerAndView($this->moduleName,  $this->controllerName);
        if(!$res) exit(makeStandResult(-1, '创建控制器、视图失败'));

        $this->configSearch = I('post.configSearch');  //搜索参数
        $fieldMap = I('post.fieldMap'); //字段映射信息

        //按照顺序排列数组
        array_multisort(array_column($fieldMap, 'order'), SORT_ASC, $fieldMap);

        $addField = [];  //初始化添加form元素
        $initFieldMap = [];
        foreach($fieldMap as $key=>$value){
            $initFieldMap[$value['field']] = $value;
            $sort[] = $value['order'];
            if($value['is_add'] === 'true'){
                $addField[] = $value;
            }
        }
        $this->fieldMap = $initFieldMap;
        //按照添加顺序排列表单数组
        array_multisort(array_column($addField, 'add_order'), SORT_ASC, $addField);
        $this->addFromEleMent = $addField;

        try{
            $this->buildHeader();
            $this->buildIndexCode();
            $this->buildAddCode();
            $this->buildAddDataCode();
            $this->buildGetDataCode();
            $this->buildDelCode();
            $this->writeController(); //代码全部生成，写入控制器

            $this->code = ''; //初始化代码，开始生成index HTML代码
            $this->buildIndexHtmlCode();
            $this->writeIndexHtml(); //代码生成完毕，写入html

            $this->code = ''; //初始化代码，开始生成add HTML代码
            $this->buildAddHtmlCode();
            $this->writeAddHtml(); //代码生成完毕，写入html
            return true;
        }catch(Exception $e){
            return false;
        }
    }

    /***
     * 构建控制器头部
     */
    public function buildHeader(){
        $this->code .= <<<EOF
<?php
namespace {$this->moduleName}\Controller;
use Think\Controller;
class {$this->controllerName}Controller extends BaseController {
EOF;
        return true;
    }

    /**
     * 控制器index方法
     */
    public function buildIndexCode(){
        $this->code .=  <<<EOF
    \r
    public function index(){
        addLog("","用户访问日志","访问{$this->viewName}页面","成功");
        \$this->display();
    }
EOF;
        return true;
    }

    /**
     * 控制器添加、修改页面方法
     */
    public function buildAddCode(){
        $filedStr = implode(',', $this->tableFields);
        $this->code .=  <<<EOF
    \r
    /**
    * {$this->viewName}添加或修改
    */
    public function add(){
        \$id = trim(I('get.{$this->pk}'));
        if(!empty(\$id)){
            \$model = M('{$this->tableName}');
            \$data = \$model->field('{$filedStr}')->where("{$this->pk}='%s'", \$id)->find();
            \$this->assign('data', \$data);
        }
        addLog('','用户访问日志',"访问{$this->viewName}添加、编辑页面",'成功');
        \$this->display();
    }
EOF;
    }

    /**
     * 生成添加数据代码
     */
    public function buildAddDataCode(){
        $this->code .= <<<EOF
    \r
    /**
     * 数据添加、修改
     */
    public function addData(){
        \$data = I('post.');
        \$id = trim(\$data['{$this->pk}']);
        // 这里根据实际需求,进行字段的过滤

        \$model = M('{$this->tableName}');

        // 下面代码请跟据实际需求进行修改：记录创建时间、创建人，完善日志内容
        if(empty(\$id)){
            \$data['{$this->pk}'] = makeGuid();
            \$data = \$model->create(\$data);
            \$res = \$model->add(\$data);

            if(empty(\$res)){
                // 修改日志
                addLog('{$this->tableName}', '对象添加日志',  '添加xxx'. '失败', '失败');
                exit(makeStandResult(-1,'添加失败'));
            }else{
                // 修改日志
                addLog('{$this->tableName}', '对象添加日志', '添加xxx'. '成功','成功');
                exit(makeStandResult(1,'添加成功'));
            }
        }else{
            \$data = \$model->create(\$data);
            \$res = \$model->where("{$this->pk}='%s'", \$id)->save(\$data);
            if(empty(\$res)){
                // 修改日志
                addLog('{$this->tableName}', '对象修改日志', '修改xxx'. '失败', '失败');
                exit(makeStandResult(-1,'修改失败'));
            }else{
                // 修改日志
                addLog('{$this->tableName}', '对象修改日志', '修改xxx'. '成功','成功');
                exit(makeStandResult(1,'修改成功'));
            }
        }
    }
EOF;
        return true;
    }

    /**
     * 查询、导出代码生成
     */
    public function buildGetDataCode(){
        $selectFiledStr = implode(',', array_keys($this->fieldMap));
        $exportFiledStr = '';
        if(!empty($this->pk)) $exportFiledStr .= $selectFiledStr.", {$this->pk}";
        $this->code .=  <<<EOF
    \r
    /**
     * 获取{$this->viewName}数据
     */
    public function getData(\$isExport = false){
        if(\$isExport){
            \$queryParam = I('post.');
            \$filedStr = '$selectFiledStr';
        }else{
            \$filedStr = '$exportFiledStr';
            \$queryParam = I('put.');
        }
        // 过滤方法这里统一为trim，请根据实际需求更改
        \$where = [];\r
EOF;

        $headerStr = "'".implode("','", removeArrKey($this->fieldMap, 'name')) ."'";
        $this->tableHeader = $headerStr;
        $logic = false;
        foreach($this->configSearch as $key=>$value){
            $filedVariable = $this->getVariableByField($value['field']);

            $this->code .= $this->tab."\$$filedVariable".' = trim($queryParam['."'".$value['field']."'".']);'. "\r";
            $this->code .= $this->tab."if(!empty(\$$filedVariable))";
            $type = (int)$value['search_type'];
            switch($type){
                case 1:
                case 2:
                    $this->hasSelect = true;
                    $this->code .= " \$where['{$value['field']}'] = ['like', ".'"'.'%'."\$$filedVariable".'%'.'"'."];\r";
                    break;
                case 3:
                case 4:
                    if($type == 4) $this->hasDate = true;
                    $logic = true;
                    $filedVariableEnd = $this->getVariableByField($value['field'].'_end');
                    $this->code .= " \$where['{$value['field']}'] = ['EGT', \$$filedVariable];\r".$this->tab."\r";
                    $this->code .= $this->tab."\$$filedVariableEnd".' = trim($queryParam['."'".$value['field']."_end'".']);'. "\r";
                    $this->code .= $this->tab."if(!empty(\$$filedVariableEnd))";
                    $this->code .= " \$where['{$value['field']}'] = ['ELT', \$$filedVariableEnd];\r";
                    break;
                default: ;
            }
            $this->code .= $this->tab."\r";
        }
        if(empty($this->databaseMark)){
            $this->code .= $this->tab."\$model = M('$this->tableName');\r";
        }else{
            $this->code .= $this->tab."\$model = M('$this->tableName', '', '$this->databaseMark'');\r";
        }
        if($logic) $this->code .= $this->tab."\$where['_logic'] = 'and';\r";
        $this->code .= <<<EOF
        \$count = \$model->where(\$where)->count();
        \$obj = \$model->field(\$filedStr)
            ->where(\$where)
            ->order("\$queryParam[sort] \$queryParam[sortOrder]");

        if(\$isExport){
            \$data = \$obj->select();

            \$header = [$headerStr];
            if(\$count <= 0){
              exit(makeStandResult(-1, '没有要导出的数据'));
            } else if( \$count > 1000){
                csvExport(\$header, \$data, true);
            }else{
                excelExport(\$header, \$data, true);
            }
        }else{
            \$data = \$obj->limit(\$queryParam['offset'], \$queryParam['limit'])
                ->select();
            exit(json_encode(array( 'total' => \$count,'rows' => \$data)));
        }
    }
EOF;
        return true;
    }

    /**
     * 生成删除代码
     * @return bool
     */
    public function buildDelCode(){
        if(empty($this->pk)) return true;
        $this->code .= <<<EOF
    \r
    /**
     * 删除数据
     */
    public function delData(){
        \$id = trim(I('post.{$this->pk}'));
        if(empty(\$id)) exit(makeStandResult(-1,'参数缺少'));
        \$where = [];
        if(strpos(\$id, ',') !== false){
            \$id = explode(',', \$id);
            \$where['{$this->pk}'] = ['in', \$id];
        }else{
            \$where['{$this->pk}'] = ['eq', \$id];
        }

        \$model = M('{$this->tableName}');
        // 获取旧数据记录日志
        //\$oldData = \$model->where(\$where)->field('td_name')->select();
        //\$names = implode(',', removeArrKey(\$oldData, 'td_name'));
        \$res = \$model->where(\$where)->delete();
        if(\$res){
            // 修改日志
            addLog('{$this->tableName}', '对象删除日志', "删除xxx 成功", '成功');
            exit(makeStandResult(1, '删除成功'));
        }else{
            // 修改日志
            addLog('{$this->tableName}', '对象删除日志', "删除xxx 失败", '失败');
            exit(makeStandResult(1, '删除成功'));
        }
    }
EOF;
        return true;
    }

    /**
     * 写入控制器
     */
    public function writeController(){
        $this->code .= "\r}";
        $path = dirname(dirname(__DIR__)).'\\'. $this->moduleName.'\\'.'Controller';
        $handle = fopen($path.'\\' .$this->controllerName.$this->controllerPrefix, 'w+');
        fwrite($handle, $this->code);
        fclose($handle);
        return true;
    }

    /**
     * 生成index.html代码
     */
    public function buildIndexHtmlCode(){
        $this->code .= <<<EOF
<?php showViewsByPower() ?>
<include file="Universal@Public:tableheader" />
EOF;
        if($this->hasSelect) $this->code .= "\r".'<link href="__PUBLIC__/vendor/chosen/chosen.css" rel="stylesheet">';
        $this->code .= <<<EOF
\r<style>
    th{
        text-align: center;
    }
    .form-control{
        display: inline-block;
    }
    .wrapper .wrapper-content{
        padding-bottom: 0;
    }
    .control-label{
        width: 30%;
        float: left;
        text-align:center;
    }
    .form-group{
        display: inline-block;
        width: 24%;
        margin-top: -7px;
    }
    .formEl-div{
        float: left;
    }
    ._box {
        height:32px;
        margin: 18px 0px 0px !important;
    }
    .fixed-table-container{
        padding-bottom: 0px !important;
    }
    table{
        table-layout: fixed;
        word-break: break-all;
    }
    .formEl-div{
        width:70%
    }
</style>
<body class="gray-bg" style="overflow:hidden">
<div class="wrapper wrapper-content ">
    <div class="row">
        <div class="col-sm-12" id='search_div'>
EOF;

        $configSearch = $this->configSearch;
        $fieldMap = $this->fieldMap;
        $searchForm = $this->searchForm;
        $searchNum = count($configSearch);
        $lineNum = ceil($searchNum / 4);
        $initLineNum = 0;
        if(!empty($configSearch)){
            $this->code .= "\r".$this->tab.$this->smallTab.'<div class="_box" style="margin-top: 5px;">' ."\r";
            for($i = 0; $i < $searchNum; $i++){
                $field = $configSearch[$i]['field'];
                $searchType = (int)$configSearch[$i]['search_type'];
                $name = $fieldMap[$field]['name'];
                $searchFormElement = $searchForm[$searchType];
                switch($searchType){
                    case 1:
                    case 2:
                        $searchFormElement = str_replace(['%s1', '%s2'], [$name, $field], $searchFormElement);
                        break;
                    case 3:
                    case 4:
                    $searchFormElement = str_replace(['%s1', '%s2', '%s3'], [$name, $field, $field.'_end'], $searchFormElement);
                        break;
                    default:;
                }

                $this->code .= "\r".$this->tab.$this->smallTab.$searchFormElement;
                if(($i+1) % 4 == 0){
                    $initLineNum ++;
                    if($initLineNum < $lineNum) {
                        $this->code .= "\r" . $this->tab . $this->smallTab . '</div>' . "\r";
                        $this->code .= "\r" . $this->tab . $this->smallTab . '<div class="_box" style="margin-top: 4px;">' . "\r";
                    }
                }
            }
            $this->code .= "\r". $this->tab.$this->smallTab.'</div>'."\r";
        }
        $tableHeader = str_replace("'", '', $this->tableHeader);
        $this->code .= <<<EOF
            <div class="_box" style="">
                <button class="btn btn-info" style="background-color: forestgreen;border-color: forestgreen;" type="button" id="sys_refresh">查询</button>
                <a class="btn btn-info " style="margin-left: 10px;background-color: yellowgreen;border-color: yellowgreen;" type="button" id="sys_add" >新增</a>
                <button class="btn btn-warning" style="margin-left: 10px;" type="button" data-head="{$tableHeader}" data-extraparam="" data-remark="支持从Excel批量拷贝粘贴" id="sys_batchadd" data-method="{$this->moduleName}/{$this->controllerName}/saveCopyTables">批量增加</button>
                <button class="btn btn-info" style="margin-left: 10px;background-color: cadetblue;border-color: cadetblue;" type="button" id="sys_del">删除</button>
                <button class="btn btn-info" style="margin-left: 10px;" type="button" id="sys_exp">导出</button>
            </div>
       </div>
        <div class="col-sm-12">
            <table id="atpbiztable" ></table>
        </div>
    </div>
</div>
<div class="modal fade" id="loading" role="dialog" data-backdrop='static'>
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">处理中</h4>
            </div>
            <div class="modal-body">
                <img src="__PUBLIC__/img/loading/loading9.gif" style='display: block;margin: 0 auto'>
                <div id="loadingText" style="text-align: center"></div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="sort" >
<input type="hidden" id="sortOrder" >
</body>
<script src="__PUBLIC__/js/tablecopy.js"></script>
EOF;

        if($this->hasDate) $this->code .= "\r". '<script src="__PUBLIC__/vendor/My97DatePicker/WdatePicker.js"></script>';
        if($this->hasSelect) $this->code .= "\r".'<script src="__PUBLIC__/vendor/chosen/chosen.jquery.js"></script>' ;
        $this->code .= <<<EOF
\r<script>
    layui.use('layer', function() {
        layer = layui.layer;
    })
EOF;
        if($this->hasSelect){
            $this->code .= "\r".$this->smallTab."var formEl_div_width = parseInt($('.formEl-div').eq(0).width());";
            $this->code .=  "\r".$this->smallTab.'$(".chosen-select").chosen({disable_search_threshold: 0, search_contains: true, width: formEl_div_width+"px"})';
        }

        //生成表格column
        $columns = '[';
        $columns .= "
                        {checkbox: true},
                        {
                            title: '序号', width: 55,
                            formatter: function (value, row, index) {
                                var option = $('#atpbiztable').bootstrapTable('getOptions');
                                return option.pageSize * (option.pageNumber - 1) + index + 1;
                            }
                        },"."\r";
        foreach($fieldMap as $key=>$value){
            $columns .= '                        '."{field: '{$value['field']}', title: '{$value['name']}', sortable: true, width: 120},"."\r";
        }
        $columns .= <<<EOF
                        {
                           field: '{$this->pk}',title: '操作', sortable: false,width: 80,
                           formatter: function (value, row, index) {
                               var inp = "'" + value + "'";
                               return '<a  class="btn btn-info btn-xs"  style="margin:0" onclick="updateInRow(' + inp + ')">编辑</a>&nbsp;<a  class="btn btn-info btn-xs" onclick="deleteInRow(' + inp + ')" style="margin:0;background: #AB154D;border-color: #AB154D;">删除</a>';                           }
                        }
                    }
EOF;
        $columns = substr($columns, 0, -2);
        $columns .= "\r".'                   ]';

        //生成查询参数
        $param = '';
        $whiteSpace = '            ';
        foreach($configSearch as $key=>$value){
            $searchType = (int)$value['search_type'];

            switch($searchType){
                case 1:
                    $param .= $whiteSpace.$value['field'].':'."$('#{$value['field']}').val(),"."\r";
                    break;
                case 2:
                    $param .= $whiteSpace.$value['field'].':'."$('#{$value['field']} option:selected').val(),"."\r";
                    break;
                case 3:
                case 4:
                    $param .= $whiteSpace.$value['field'].':'."$('#{$value['field']}').val(),"."\r";
                    $param .= $whiteSpace.$value['field'].'_end:'."$('#{$value['field']}_end').val(),"."\r";
                    break;
                default:;
            }
        }
        $param = substr($param, 0, -2);
        $sortName = array_keys($fieldMap)[0];
        $comma = empty($param) ? '' : ',';
        $this->code .= <<<EOF
        \r
    var height = document.documentElement.clientHeight -70;
    var searchDivHeight = parseInt($('#search_div').height());
    var TableObj = {
        oTableInit: function () {
            $('#atpbiztable').bootstrapTable({
                url: '__CONTROLLER__/getData',      //请求后台的URL（*）
                method: 'post',                     //请求方式（*）
                toolbar: '#atpbiztoolbar',          //工具按钮用哪个容器
                striped: true,                      //是否显示行间隔色
                cache: false,                       //是否使用缓存，默认为true，所以一般情况下需要设置一下这个属性（*）
                pagination: true,                   //是否显示分页（*）
                iconSize: 'outline',
                sortable: true,                     //是否启用排序
                sortName: "{$sortName}",
                sortOrder: "desc",                  //排序方式
                queryParams: queryParams,//传递参数（*）
                sidePagination: "server",           //分页方式：client客户端分页，server服务端分页（*）
                pageNumber: 1,                      //初始化加载第一页，默认第一页
                pageSize: 15,                       //每页的记录行数（*）
                pageList: [15, 25, 50, 100],        //可供选择的每页的行数（*）
                minimumCountColumns: 2,             //最少允许的列数
                clickToSelect: true,                //是否启用点击选中行
                uniqueId: "f_id",                   //每一行的唯一标识，一般为主键列
                detailView: false,                  //是否显示父子表
                columns: [
                   {$columns}
                ],
                onDblClickRow: function (row) {
                    updateInRow(row['{$this->pk}']);
                },
                onLoadSuccess:function(data){
                     var tabheight = $('.fixed-table-container').height();
                    tabheight = parseInt(tabheight);
                    if(tabheight < (height-searchDivHeight)){
                        $('.fixed-table-container').css('height', 'auto');
                    }else{
                        $('#atpbiztable').bootstrapTable("resetView",{height:height-searchDivHeight});
                    }
                }
            });
        }
    };
    TableObj.oTableInit();

    function queryParams(params) {  //配置参数
        $('#sort').val(params.sort);
        $('#sortOrder').val(params.order);
        var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
            limit: params.limit,   //页面大小
            offset: params.offset,  //页码
            sort: params.sort,  //排序列名
            sortOrder: params.order,//排位命令（desc，asc）\r{$param}
        };
        return temp;
    }

    $('#sys_refresh').on('click',function() {
        $('#atpbiztable').bootstrapTable('destroy');
        TableObj.oTableInit();
    });

    $('#sys_add').on('click',function() {
        updateInRow('');
    });

    $('#sys_del').on('click',function() {
        var tablerow = $('#atpbiztable').bootstrapTable('getSelections');
        if (tablerow.length == 0) {
            layer.alert("您尚未选择数据");
        } else {
            var ids = [];
            $.each(tablerow, function () {
                ids.push(this['{$this->pk}']);
            });
            deleteInRow(ids.join(','));
        }
    });

    //数据编辑触发该方法
    function updateInRow(id) {
        layer.open({
            title:'数据编辑',
            closeBtn:1,
            type: 2,
            shadeClose:false,
            content: '__CONTROLLER__/add?{$this->pk}='+id,
            area: ['80%', '80%']
        });
    }
    //数据删除触发该方法
    function deleteInRow(id){
        layer.confirm('确认删除选中数据?',
        {btn:['确定','取消']},
        function(){
            $.ajax({
                type:'post',
                url:'__CONTROLLER__/delData',
                data:{{$this->pk}: id},
                dataType :'json',
                success:function(data){
                    if(data.code > 0){
                        layer.msg('操作成功');
                        $('#sys_refresh').click();
                    }else{
                        layer.alert(data.message);
                    }
                },error:function(){
                    layer.alert('出错啦！请稍后再试');
                }
            })
        })
    }
    $('#sys_exp').click(function(){
        $('#loading').modal('show');
        var sort=$('#sort').val();
        var sortOrder=$('#sortOrder').val();
        var t = "__CONTROLLER__/getDataWithExport";
        t = encodeURI(t);
        $.ajax({
            type:'post',
            url: t,
            dataType:'json',
            data:{
                sort:sort,
                sortOrder:sortOrder{$comma}
                {$param}
            },
            success:function(data){
                $('#loading').modal('hide');
                if(data.code > 0){
                    location.href = data.message;
                }else{
                    layer.msg(data.message);
                }
            },error:function(){
                $('#loading').modal('hide');
                layer.alert('出错了！请联系管理员！');
            }
        })
    })
</script>
</html>
EOF;
        return true;
    }

    /**
     * 生成add.html代码
     */
    public function buildAddHtmlCode(){
        $addFormElement = $this->addFromEleMent;
        if(empty($addFormElement)) return false;
        $this->code = <<<EOF
<?php showViewsByPower() ?>
<include file="Universal@Public:header" />
EOF;
        $initForm = [];

        $hasSelect = false;
        $hasLongSelect = false;

        $ruleStr = '';
        $ruleMessageStr = '';
        //引入所需js、css,格式化字段、name、id,生成必填规则，记录每个表单元素所占行数
        foreach($addFormElement as $key=>$value){
            $mustFilter = $value['is_require'] === 'true' ? '<span class="must_filter">*</span>' : '';
            $addFormElement[$key]['need_line_num'] = 0.5;
            if($value['is_require'] === 'true'){
                $ruleStr .= $value['field']. ":'required',";
                $ruleMessageStr .= $value['field']. ":'请输入{$value['name']}',";
            }
            switch($value['form_type']){
                case '普通文本框':
                    $currentFromElement = str_replace(['%s1', '%s2', '%s3'], [$value['field'], $value['field'], "<?php if(!empty(\$data['{$value['field']}']))echo \$data['{$value['field']}']; ?>"], $this->formType[$value['form_type']]);
                    break;
                case '日期文本框':
                    $this->code .= "\r".'<script src="__PUBLIC__/vendor/My97DatePicker/WdatePicker.js"></script>';
                    $currentFromElement = str_replace(['%s1', '%s2', '%s3'], [$value['field'], $value['field'], "<?php if(!empty(\$data['{$value['field']}']))echo \$data['{$value['field']}']; ?>"], $this->formType[$value['form_type']]);
                    break;
                case '普通下拉搜索框':
                    $hasSelect = true;
                    $this->code .= "\r". '<link href="__PUBLIC__/vendor/chosen/chosen.css" rel="stylesheet">';
                    $this->code .= "\r". '<script src="__PUBLIC__/vendor/chosen/chosen.jquery.js"></script>';
                    $currentFromElement = str_replace(['%s1', '%s2'], [$value['field'], $value['field']], $this->formType[$value['form_type']]);
                    break;
                case '多选下拉搜索框':
                    $addFormElement[$key]['need_line_num'] = 1;
                    $hasLongSelect = true;
                    $this->code .= "\r".'<script src="__PUBLIC__/vendor/chosen-ajax-addition/chosen.ajaxaddition.jquery.js"></script>';
                    $currentFromElement = str_replace(['%s1', '%s2'], [$value['field'], $value['field']], $this->formType[$value['form_type']]);
                    break;
                case '文本域':
                    $currentFromElement = str_replace(['%s1', '%s2', '%s3'], [$value['field'], $value['field'], "<?php if(!empty(\$data['{$value['field']}']))echo \$data['{$value['field']}']; ?>"], $this->formType[$value['form_type']]);
                    break;
                default:
                    $currentFromElement = '';
            }

            if($addFormElement[$key]['need_line_num'] == 1){
                $str = '                <div style="width: 100%;float: left">'."\r";
            }else{
                $str = '                <div style="width: 50%;float: left">'."\r";
            }
            $str .= "                 "."<label class=' col-sm-2 control-label'>{$value['name']} {$mustFilter}</label>";
            $str .= "\r"."                    ".'<div class="col-sm-3" >'."\r"."                    ".$currentFromElement."\r                   </div>\r                </div>\r";
            $initForm[] = $str;  //格式化表单元素
        }
        $ruleStr = substr($ruleStr, 0, -1);
        $ruleMessageStr = substr($ruleMessageStr, 0, -1);
        $selectCss = '';
        $selectJs = '';
        if($hasLongSelect){
            $selectCss = <<<EOF
    .chosen-container .chosen-results {
        max-height: 180px;
    }
EOF;
            $selectJs .= <<<EOF
        var long_select_width = parseInt($('.form-control').eq(0).css('width').replace('px', '')) * 2.75;
        $('.long-chosen-select').chosen({disable_search_threshold: 0, search_contains: true, width: long_select_width+'px'});
EOF;
            $selectJs .= "\r"."        ". "$('.long-chosen-select').ajaxChosen({
            dataType: 'json',
            type: 'post',
            url:'__CONTROLLER__/XXXXXXXXXXXXXXX'
        });";
        }
        if($hasSelect){
            $selectCss = <<<EOF
    .chosen-container .chosen-results {
        max-height: 180px;
    }
     .chosen-container{
        height: 100%;
    }
EOF;
            $selectJs .= <<<EOF
        var input_width = parseInt($('.form-control').eq(0).css('width').replace('px', ''));
        $('.chosen-select').chosen({disable_search_threshold: 0, search_contains: true, width: input_width+'px'});
EOF;
        }

        $this->code .= <<<EOF
\r
<title>{$this->viewName}添加编辑</title>
<style>
    .form-group{
        margin-top: 26px;
    }
    .control-label{
        width:150px  !important;
    }
     .must_filter{
        color: red;
    }
    .form-control{
        display:inline-block;
    }
    .col-sm-3{
        width:60%  !important;
    }
    .chosen-container{
        top: -1px;
    }
\r$selectCss
</style>
<body style="margin: 0 auto;">
<form id="sys_dlg_form1" role="form" class="form-horizontal" enctype="multipart/form-data">
    <div class="tab-content" >
        <div class="panel-body">
EOF;
        //将表单元素写入表单
        $lastLineNum = 1;
        foreach($addFormElement as $key=>$value){
            $element = $initForm[$key];
            $needLineNum = $value['need_line_num'];
            if($needLineNum == 1 && $lastLineNum == 1){
                $this->code .= "\r"."           ".' <div class="form-group">';
                $this->code .= "\r".$element;
                $this->code .= "\r"."           "."</div>";
                $lastLineNum = 1;
            }else if($needLineNum == 1 && $lastLineNum == 0.5){
                $this->code .= "\r"."           "."</div>";
                $this->code .= "\r"."           ".'<div class="form-group" >';
                $this->code .= "\r".$element;
                $this->code .= "\r"."           "."</div>";
                $lastLineNum = 1;
            }else if($needLineNum == 0.5 && $lastLineNum == 1){
                $this->code .=  "\r"."           ".'<div class="form-group">';
                $this->code .= "\r".$element;
                $lastLineNum = 0.5;
            }else if($needLineNum == 0.5 && $lastLineNum == 0.5){
                $this->code .= "\r".$element;
                $this->code .= "\r"."           ".'</div>';
                $lastLineNum = 1;
            }
        }
        $this->code .="\r"."            ". '</div>';
        $this->code .= <<<EOF
\r        <input type="hidden" value="<?php if(!empty(\$data['{$this->pk}'])) echo \$data['{$this->pk}']; ?>" name="{$this->pk}">
       </div>
    </form>
<div class="modal-footer" style="margin-top: 15px;text-align: center;width: 100%;">
    <button type="button" data-dismiss="modal" id="sys_dlg_submit" class="btn btn-primary" style="display:inline-block">保存</button>
</div>
</body>
<script type="text/javascript" src="__PUBLIC__/vendor/ie8/jquery.form.js"></script>
<script src="__PUBLIC__/vendor/validate/jquery.validate.min.js"></script>
<script>
    $(function () {
        layui.use('layer', function() {
            layer = layui.layer;
        })
        $('#sys_dlg_submit').click(function(){
            $('#sys_dlg_form1').submit();
        })
        $selectJs

        $.validator.setDefaults({
            highlight:function(element){
                $(element).parent().remove('has-success').addClass('has-error');
            },
            success:function(element){
                $(element).parent().remove('has-error').addClass('has-success');
            },
            errorPlacement:function(error,element){
                if(element.is(":radio") || element.is(":checkbox")){
                    error.appendTo(element.parent());
                }else{
                    error.appendTo(element.parent());
                }
            },
            errorClass:"help-block m-b-none",
            validClass:"help-block m-b-none"
        });
        $('#sys_dlg_form1').validate({
            onclick:false,
            onfocusout:false,
            onkeyup:false,
            // 如果是下拉菜单，这里的require是不生效的，需要自己在submitHandle方法中添加验证
            rules:{
                {$ruleStr}
            },
            messages:{
                {$ruleMessageStr}
            },submitHandler:function(){
                var formBody = $('#sys_dlg_form1');
                $.post('__CONTROLLER__/addData',formBody.serialize(), function (data) {
                    if (data.code > 0) {
                        parent.$('#atpbiztable').bootstrapTable('refresh');
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    } else {
                        layer.alert(data.message);
                    }
                },'JSON');
            }
        });
    });
    //如果验证下拉框必填，请使用该方法
//    function checkSelectForm(objArr, messageArr){
//        var len = objArr.length;
//        for(var i=0;i<len;i++){
//            var obj = objArr[i];
//            var val = $('#'+obj).val();
//            if(!val){
//                layer.msg('请选择'+messageArr[i]);
//                $('#'+obj).trigger('chosen:open');
//                return false;
//            }
//        }
//        return true;
//    }
</script>
EOF;
        return true;
    }

    /**
     * 写入index.html
     */
    public function writeIndexHtml(){
        $path = dirname(dirname(__DIR__)).'\\'. $this->moduleName.'\\'.'View'.'\\'.$this->controllerName;
        $handle = fopen($path.'\\' .'index.html', 'w+');
        fwrite($handle, $this->code);
        fclose($handle);
        return true;
    }

    /**
     * 写入add.html
     */
    public function writeAddHtml(){
        $path = dirname(dirname(__DIR__)).'\\'. $this->moduleName.'\\'.'View'.'\\'.$this->controllerName;
        $handle = fopen($path.'\\' .'add.html', 'w+');
        fwrite($handle, $this->code);
        fclose($handle);
        return true;
    }

    /**
     * 根据字段名生成驼峰式变量名
     * @param $string
     * @return string
     */
    public function getVariableByField($string){
        if(strpos($string, '_') !== false){
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
        }else{
            return $string;
        }
    }

    /**
     * 过滤字段中的创建添加时间、人
     * @param $tableFields
     * @return mixed
     */
    public function filterField($tableFields){
        foreach($tableFields as  $key=>$value){
            if(strpos($value['column_name'],'createtime') !== false){
                unset($tableFields[$key]);
                continue;
            }
            if(strpos($value['column_name'], 'createuser')!== false){
                unset($tableFields[$key]);
                continue;
            }
            if(strpos($value['column_name'], 'modifytime')!== false){
                unset($tableFields[$key]);
                continue;
            }
            if(strpos($value['column_name'],'modifyuser')!== false){
                unset($tableFields[$key]);
                continue;
            }
        }
        return $tableFields;
    }

}
