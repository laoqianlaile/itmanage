/**
 * html数据复制粘贴引入该文件
 */
$('#sys_batchadd').click(function () {
    var head = $(this).attr('data-head');
    if(typeof(head)== 'undefined' || head == ''){
        var th = $('thead>tr>th');
        var thHead = [];
        $.each(th, function(k, v){
            var text = $(v).text();
            if(text != '' && text != '操作'){
                thHead.push(text);
            }
        });
        head = thHead.join(',');
    }
    var method = $(this).attr('data-method');
    var remark = $(this).attr('data-remark');
    var extraParam = $(this).attr('data-extraparam');
    
    var scriptPath = $('script').eq(0).attr('src');
    var currentPath = window.location.pathname;
    var scriptLength = scriptPath.length;
    var rootPath = '';
    for(var i=0; i< scriptLength; i++){
        if(scriptPath[i] == currentPath[i]){
            rootPath+=currentPath[i];
        }else{
            break;
        }
    }
    var url = rootPath +'/index.php/Universal/TableCopy/tableCopy';

    layer.open({
        title:'项目编辑',
        closeBtn:1,
        type: 2,
        shadeClose:false,
        content:url + '?head='+head+'&method='+method+'&remark='+remark+'&extraparam='+extraParam,
        area: ['90%', '85%']
    });
});