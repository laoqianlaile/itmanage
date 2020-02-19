<?php

/**
 *  系统函数
 */

//生成GUID
function makeGuid(){
    //距离Oracle列最大宽度还差5位
    return sprintf('T%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535));
}

/**
 * 更改数据编码
 * @param $data
 * @param $beforeCoding
 * @param $afterCoding
 * @author baijingqi
 * @date 2017-12-05
 * @return string
 */
function changeCoding($data,$beforeCoding, $afterCoding){
    if(is_array($data)){
        foreach($data as $key=>$value){
            if(is_array($value)){
                $data[$key] = changeCoding($value, $beforeCoding, $afterCoding);
            }else{
                $data[$key] = iconv($beforeCoding, $afterCoding, $value);
            }
        }
    }else{
        $data = iconv($beforeCoding, $afterCoding, $data);
    }
    return $data;
}

/**
 * 得到webservice操作对象
 * @param string $address
 * @param string $charset
 * @param bool|false $decode
 * @author baijingqi
 * @return \SoapClient
 */
function getWebServiceObj($address = '', $charset = 'UTF-8',$decode = false){
    $client = new \SoapClient($address);
    $client->soap_defencoding = $charset;
    $client->decode_utf8 = $decode;
    $client->xml_encoding = $charset;
    return $client;
}

/**
 * 二维数组排序
 * @param array $arr
 * @param string $keys
 * @param string $type
 * @param string $sortType (默认按照数字处理，可指定按照字符串处理)
 * @param bool $resetKey 是否需要重组键值
 * @return array
 * @date 2017-10-14
 * @author baijingqi
 */
function arraySort($arr = array(), $keys = '', $type = 'asc',$sortType = 'number',$resetKey = FALSE){
    $keysValue = $newArray = array();
    foreach($arr as $key=>$value){
        $keysValue[$key] = $value[$keys];
    }
    if($sortType == 'number'){
        $type == 'asc' ? asort($keysValue) : arsort($keysValue);
    }else{
        $type == 'asc' ? asort($keysValue,SORT_STRING) : arsort($keysValue,SORT_STRING);
    }
    reset($keysValue);
    if($resetKey == TRUE){
        foreach($keysValue as $k=>$v){
            $newArray [] = $arr[$k];
        }
    }else{
        foreach($keysValue as $k=>$v){
            $newArray [$k] = $arr[$k];
        }
    }
    return $newArray;
}

/**
 * 递归处理层级 数组需要id，pid两个字段
 * @param array $data 需要处理的数组
 * @param $pid 最上级父级id
 * @param $level
 * @author baijingqi
 * @return array
 */
function getLevelData($data = [], $pid, $level = 0){
    if(empty($data)) return $data;
    $tree = [];
    foreach($data as $key =>$v){
        if($v['pid'] == $pid){
            $v['level'] = $level;
            $tree[] = $v;
            $tree = array_merge($tree, getLevelData($data, $v['id'], $level+1));
        }
    }
    return $tree;
}

/**
 * 柱状图(所需数据：name-名称，value-值,baifenbi-百分比：可选)
 * @param array $data
 * @date 2017-9-8
 * @author baijingqi
 * @return array
 */
function barChart($data = array()){
    if(empty($data)) return false;
    $baifenbi = array();
    $names = array();
    $values = array();
    foreach ($data as $k=>$v) {
        array_push($names,$v['name']);
        array_push($values,$v['value']);
        if(isset($values['baifenbi'])){
            array_push($baifenbi, $v['baifenbi']);
        }
    }
    $result = array();
    $result['names_str'] =  "'".implode("','", $names)."'";
    $result['values_str'] =  "'".implode("','", $values)."'";
    if(!empty($baifenbi))   $result['baifenbi_str'] =  "'".implode("','", $baifenbi)."'";
    return $result;
}

/**
 * 饼图(所需数据：name-名称，value-值)
 * @param array $data
 * @date 2017-9-8
 * @author baijingqi
 * @return array
 */
function pieChart($data = array()){
    if(empty($data)) return false;
    $names = array();
    $values = array();
    foreach ($data as $k=>$v) {
        array_push($names,$v['name']);
        array_push($values,"{value:".$v['value'].",name:'".$v['name']."'}");
    }
    $result['names_str'] = "'".implode("','", $names)."'";
    $result['values_str'] = implode(",", $values);
    return $result;
}

/**
 * excel导出，在导出数据较少时可以选用该方法，大量数据导出请使用csv导出。该方法不对数据做任何处理，请处理好数据再传入
 * @param array $tableHeader excel 需要生成的excel表头，默认第一列为序号列
 * @param array $result 需要导出的数据
 * @param bool $isAjaxDown 需要直接下载还是需要ajax下载
 * @throws PHPExcel_Writer_Exception
 * @author baijingqi
 * @return array file
 */
function excelExport( $tableHeader = array(),$result = array(), $isAjaxDown = true){
    if(empty($tableHeader)) exit(makeStandResult(-1, '缺少表头'));
    if(empty($result)) exit(makeStandResult(-2, '数据为空'));

    if($tableHeader[0] != '序号') array_unshift($tableHeader, '序号');
    $headerLength = count($tableHeader);
    if($headerLength > 200) exit(makeStandResult(-3, '最大支持导出200列'));

    $letter = getEnglishLetter($headerLength); //获取excel列名


    vendor("PHPExcel.PHPExcel");
    $excel = new \PHPExcel();
    for ($i = 0; $i < $headerLength; $i++) {
        $excel->getActiveSheet()->setCellValue($letter[$i].'1', $tableHeader[$i]); //写入表头
    }
    $data = array();
    //将二维关联数组处理成索引数组
    foreach($result as $key=>$value){
        $arr = $value;
        if(isset($arr['numrow'])) unset($arr['numrow']);
        $arr = array_values($arr);
        array_unshift($arr, $key + 1);
        $data[$key] = $arr;
    }
    $dataLength = count($data);
    for ($i = 2; $i <= $dataLength + 1; $i++) {
        $j = 0;
        foreach ($data[$i - 2] as $key => $value) {
            $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
            $j++;
        }
    }
    $write = new \PHPExcel_Writer_Excel2007($excel);
    if($isAjaxDown === true){
        $filename = date('Ymd').time().rand(0,1000).'.xlsx';
        $savePath = 'Public/download/excel/'.date('Y-m-d');
        if(!is_dir($savePath)) mkdir($savePath, 0777, true);
        $filePath = $savePath. '/' .$filename;
        $write->save($filePath);

        $fileRootPath =__ROOT__;
        exit(json_encode(array('code' => 1, 'message' =>$fileRootPath.'/'. $filePath)));
    }else{
        header("Pragma: public");
        header('Content-Disposition:attachment;filename='.date('Ymd').time().rand(0,1000).'.xlsx');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
}

/**
 * excel导出生成英文字母列头
 * @param int $length 默认从A开始返回
 * @param bool $upperCase 默认返回大写字母
 * @return array
 */
function getEnglishLetter($length = 26, $upperCase = true){
    $data = array();
    $function = $upperCase ? 'strtoupper' : 'strtolower';
    $start = 65;
    if($length <= 26){
        $end = $start + $length;
    }else{
        $end = 91;
    }
    for($i = $start; $i<$end; $i++){
        $data[] = $function(chr($i));
    }
    if($length > 26){
        $diff = $length - 26;
        $count = 0;
        $initData = [];
        foreach($data as $letter){
            foreach($data as $le){
                if($count == $diff) break;
                $initData[] = $letter . $le;
                $count++;
            }
        }
        $data = array_merge($data, $initData);
    }
    return $data;
}

/**
 * csv导出
 * @param array $header 表头
 * @param array $result 导出结果  结果不做任何处理，请处理好传入
 * @param bool|false $isAjaxDown 直接下载还是ajax下载
 * @author baijingqi
 * @date 2018-01-16
 * @return array
 */
function csvExport($header = array(), $result = array(), $isAjaxDown = false){
    if(empty($header)) return json_encode(array('code' => -1,'message' => '缺少表头'));
    if($header[0] != '序号') array_unshift($header, '序号');
    if(empty($result)) return json_encode(array('code' => -2,'message' => '数据为空'));
    foreach($header as &$val){
        $val = iconv('utf-8','gbk',$val);
    }
    $filename = date('Ymd').time().rand(0,1000).'.csv';
    $filePath = 'Public/download/csv/'.date('Y-m-d');
    if(!is_dir($filePath)) mkdir($filePath, 0777, true);

    $fp = fopen($filePath.'/'.$filename,'w');
    fputcsv($fp, $header);
    foreach($result as $key=>$value){
        $data = $value;
        if(isset($data['numrow'])) unset($data['numrow']);
        $data = array_values($data);
        array_unshift($data, $key+1);

        $data = changeCoding($data, 'utf-8', 'gbk');
        fputcsv($fp, $data);
    }

    $fileFullPath = $filePath.'/'.$filename;
    $fileRootPath =__ROOT__;
    if($isAjaxDown === true){
        exit(json_encode(array('code' => 1, 'message' => $fileRootPath.'/'.$fileFullPath)));
    }else{
        header("location:".$fileRootPath.$fileFullPath);
    }
}

/**
 * 根据传入年份按照星期划分时间段
 * @param $year
 * @return array
 */
function getWeekendsByYear($year){
    $year = intval($year);
    $data = [];
    if(empty($year)) return $data;
    $beginDate = $year.'-01-01';
    $endDate = $year.'-12-31';
    $today = date('Y-m-d');
    for($i=$beginDate; $i<$endDate; $i=date('Y-m-d',strtotime($i)+86400)){
        //从一年的第一个星期一开始计算
        if(date('w', strtotime($i)) == 1){
            $sunday = date('Y-m-d',strtotime($i)+518400); //周日=周一+518400s

            //如果此次循环中周日大于本年的最后一天,需要终止循环
            if($sunday > $endDate) {
                if($today >= $i && $today<= $sunday){
                    $data[] = ['date' => $i . '~' . $endDate, 'check' => 1];
                }else{
                    $data[] = ['date' => $i . '~' . $endDate];
                }
                break;
            }

            //判断今天是否在本次循环中的周一至周日区间之内
            if($today >= $i && $today<= $sunday){
                $data[] = ['date' => $i.'~'.$sunday,'check' => 1];
            }else{
                $data[] = ['date' => $i.'~'.$sunday];
            }
            $i = $sunday;
        }
    }
    return $data;
}

/**
 * 生成可解密字符串
 * @param string $str 需要加密的字符串
 * @param string $key 相当于为加密字符串生成一把钥匙，必须拿着把钥匙才能解开加密字符串
 * @param int $expire 有效期（秒） 0 为永久有效，默认一天过期
 * @return string
 */
function encryption($str = '',$key = '', $expire = 86400){
    $obj = new \Think\Crypt();
    return $obj->encrypt($str, $key, $expire);
}

/**
 * 解密字符串
 * @param string $str 需要解密的字符串
 * @param string $key 解密字符串用的钥匙
 * @return string
 */
function deciphering($str = '', $key = ''){
    $obj = new \Think\Crypt();
    return $obj->decrypt($str,$key);
}

/**
 * 获取json标准结果
 * @param int $code
 * @param string $message
 * @param type 1:json 其他:array
 * @return string
 */
function makeStandResult($code = 0, $message = '', $type = 1){
    $result = array(
        'code' => $code,
        'message' => $message,
    );
    if($type == 1){
        return json_encode($result);
    }else{
        return $result;
    }
}

/**
 * 验证码检测
 * @param $code
 * @param string $id
 * @return bool
 */
function check_verify($code, $id = ''){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 取出二维数组的某一列值
 * @param $data
 * @param $key
 * @return array
 */
function removeArrKey($data, $key){
    return array_column($data, $key);
}

/**
 * 二维数组去重
 * @param $array2D
 * @param bool|false $stkeep
 * @param bool|true $ndformat
 * @return mixed
 */
function uniqueArr($array2D,$stkeep=false,$ndformat=true){
    //判断是否保留一级数组键（一级数组键可以为非数字）
    if($stkeep) $strArr = array_keys($array2D);

    //判断是否保留二级数组键（所有二级数组键必须相同）
    if($ndformat) $ndArr = array_keys(end($array2D));

    //降维，也可以用implode，将一维数组转换为用逗号连接的字符串
    $temp = [];
    foreach($array2D as $v){
        $v = join(",",$v);
        $temp[] = $v;
    }
    //去掉重复字符串，也就是重复的一维数组
    $temp = array_unique($temp);

    //再将拆开的数组重新组装
    foreach($temp as $k=>$v){
        if($stkeep) $k = $strArr[$k];
        if($ndformat){
            $tempArr = explode(",",$v);
            foreach($tempArr as $ndkey=>$ndval) $output[$k][$ndArr[$ndkey]] = $ndval;
        }else{
            $output[$k] = explode(",",$v);
        }
    }

    return array_values($output);
}

/**
 * 根据用户权限判断是否展示页面
 */
function showViewsByPower(){
    return true;
    $view =  MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
    $powers = cookie('operate_view');
    if(in_array(strtolower($view), $powers)){
        return true;
    }else{
        $obj = new \Think\View();
        $obj->display(T('Admin@Public/error'));die;
    }
}


/**
 * 递归替换数组
 * @param array $data 要处理的数组
 * @param string $search 要查找什么
 * @param string $replace 要替换成什么
 * @author baijingqi
 * @date 2018-04-04
 * @return array
 */
function recursionReplace($data,$search = null, $replace = ''){
    foreach($data as &$value){
        if(is_array($value)){
            $value = recursionReplace($value, $search, $replace);
        }else{
            if($value == $search) $value = $replace;
        }
    }
    return $data;
}

/**
 * 清空文件下数据
 * @param $filePath
 */
function clearFile($filePath){
    if(is_dir($filePath)){
        $arr = scandir($filePath);
        foreach($arr  as $key=>$value) {
            if ($value != '.' && $value != '..'){
                $file = $filePath . '/' . $value;
                if (is_dir($file)) {
                    clearFile($file);
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }
        }

    }else{
        unlink($filePath);
    }
    rmdir($filePath);
}

/**
 * 生成操作日志内容
 * @param $table -操作数据表
 * @param $logType -日志类型
 * @param $content -日志内容
 * @param $result -结果
 * @param string $user -操作用户，默认不传自动从session获取
 * @return mixed
 */
function addLog($table, $logType, $content, $result, $mainId, $user = ''){
    $model = M('oplog');
    $data = [
        'opl_id' => makeGuid(),
        'opl_user' => empty($user) ? session('realusername') . '(' . session('user_account') . ')' : $user,
        'opl_ip' => get_client_ip(),
        'opl_time' => time(),
        'opl_logtype' => $logType,
        'opl_object' => $table,
        'opl_firstcontent' => $content,
        'opl_mainid' => $mainId,
        'opl_result' => $result
    ];
    $data['opl_logcode']=md5($data['opl_id'].$data['opl_user'].$data['opl_ip'].$data['opl_time'].$data['opl_logtype'].$data['opl_object'].$data['opl_firstcontent'].$data['opl_result']);
    $res = $model->add($data);
    return $res;
}

function LogContent($data,$list){

    $diffData = array_diff_assoc($list,$data);
    $diffDatas = array_diff_assoc($data,$list);
    unset($diffData['numrow']);
    $content = '';
    foreach($diffData as $key =>$val){
        $content .= '把字段'.$key.'由'.$val.'改为'.$diffDatas[$key].',';
    }
    return $content;
}

/**
 * 创建缓存key
 * @param $key
 * @param array $param
 * @return string
 */
function makeCacheKey($key, $param = array()){
    $key = C($key);
    return vsprintf($key, $param);
}

/**
 * 分割字符串
 * @param $string
 * @param int $splitLength
 * @return array|bool
 */
function mb_str_split($string, $splitLength = 1){
    if ($splitLength == 1) {
        return preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);
    } elseif ($splitLength > 1) {
        $return_value = [];
        $stringLength = mb_strlen($string, "UTF-8");
        for ($i = 0; $i < $stringLength; $i += $splitLength) {
            $return_value[] = mb_substr($string, $i, $splitLength, "UTF-8");
        }
        return $return_value;
    } else {
        return false;
    }
}

/**
 * 加密文件路径
 * @param $path
 * @return string
 */
function encryptFilePath($path){
    $fileKey = C('FILE_KEY');
    return encryption($path, $fileKey, 0);
}

/**
 * 下载文件
 * @param $filePath -eg:Public/download/excel/2019-02-20/201902201550652158949.xlsx
 */
function downloadFile($filePath){
    if (file_exists ($filePath)) {
        header ( 'Content-Description: File Transfer' );
        header ( 'Content-Type: application/octet-stream' );
        header ( 'Content-Disposition: attachment; filename=' . basename( $filePath ));
        header ( 'Content-Transfer-Encoding: binary' );
        header ( 'Expires: 0' );
        header ( 'Cache-Control: must-revalidate' );
        header ( 'Pragma: public' );
        header ( 'Content-Length: '. filesize ($filePath ));
        ob_clean ();
        flush ();

        //判断是否需要解密
        if(C('FILECONTENT_ENCRYPT')){
            //读取文件内容
            $contents = file_get_contents($filePath);

            //获取解密方法，进行解密
            $func = (string) C('FILECONTENT_DECIPHERING_FUNC');

            echo $func($contents);
        }else{
            readfile($filePath);
        }
        exit;
    }else{
        E('文件不存在', 404);
        exit;
    }
}
/*
   * 老页面需要使用的方法
   *
   * */

/**
 * 根据atpid获取person表中realusername字段
 * @param string $username
 * @return string
 */
function getRealusername($username)
{
    $personinfos = S('personinfo');
    if(empty($personinfos)){
        $Model          = M();
        $sql_select     = "SELECT p.username,p.realusername FROM it_person p";
        $personinfo = $Model->query($sql_select);
        if(!empty($personinfo)){
            $personinfos = [];
            foreach($personinfo as $item){
                $personinfos[$item['username']] = $item['realusername'];
            }
        }
        S('personinfo',$personinfos,3600);
    }
    if(isset($personinfos[$username])){
        return $personinfos[$username];
    }else{
        return $username;
    }
}

function buildSqlPage($sql,$start,$length)
{
    $oraclestart = $start;
    $oracleend = $start + $length;
    return "
        Select * From
        (
          Select fp.*,Rownum RN From
          (
          $sql
          ) fp
          Where Rownum<=$oracleend
        )
        Where RN>$oraclestart";
}

/**
 * 记录操作日志
 * @param $type
 * @param $module
 * @param $content
 * @return mixed
 */
function recordLog($type, $module, $content,$table = '',$atpid = '',$beizhu='',$bdid = ''){
    $optime = date('Y-m-d H:i:s',time());
    $data = array(
        'l_atpid'      => makeGuid(),
        'l_optime'     => $optime,
        'l_ipaddress'  => get_client_ip(),
        'l_optype'     => $type,
        'l_opuserid'   => session('user_name'),
        'l_opusername' => session('realusername'),
        'l_modulename' => $module,
        'l_detail'     => $content,
        'l_tablename'  => $table,
        'l_mainid'     => $atpid,
        'l_beizhu'     => $beizhu,
        'l_bdid'       => $bdid
    );
    $res = M('it_log')->add($data);
    return $res;
}

/**
 *  记录接口调用的日志
 */
function writeWebLog($type='',$content=''){
    if(!$type || !$content){
        return false;
    }
    $dir = getcwd().DIRECTORY_SEPARATOR.'Public'.DIRECTORY_SEPARATOR.'weblogs'.DIRECTORY_SEPARATOR.date('Y-m-d').DIRECTORY_SEPARATOR.$type;
    if(!is_dir($dir)){
        if(!mkdir($dir,0777,true)){
            return false;
        }
    }
    $filename = $dir.DIRECTORY_SEPARATOR.time().'log.txt';
    $logs = include $filename;
    if($logs && !is_array($logs)){
        unlink($filename);
        return false;
    }
    $time = "[".date('Y-m-d H:i:s')."]";
    $logs = "";
    foreach($content as $contentKey => $contentValue){
        $logs .= $time.$contentKey."：". var_export($contentValue,true)."\r\n";
    }
    $logs .= "-------------------------------------------------------------END\r\n\r\n";
    if(!$fp = @fopen($filename,'wb')){
        return false;
    }
    if(!fwrite($fp,$logs)) return false;
    fclose($fp);
    return true;
}
