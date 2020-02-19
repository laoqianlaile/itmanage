$_hide = function () {
    //引入cookie脚本
    document.write("<script src='../../../Public/js/jquery.cookie.js'></script>");

    //当前脚本路径
    var currentView = '';

    //当前脚本cookie的键名
    var currentCookieKey = '';

    //cookie 键的前缀
    var cookieKeyPrefix = 'aimmanage_c_';

    //当前脚本的cookie值的前缀
    var cookieValuePrefix = 'thinkphp:';

    //当前脚本的cookie
    var currentViewCookie = cookieValuePrefix;

    //cookie option
    var cookieOption = {
        expires:7, // 有效期 ：7天
        path : '/',  //有效路径
        secure : false, //如果为true cookie的传输需要使用https协议
        domain : '', // 域名
        raw:false //如果为true，则读取和写入时自动进行编码和解码（使用encodeURIComponent编码，decodeURIComponent解码）
    };

    return {
        //初始化当前路径eg：模块/控制器/方法
        init : function(){
            var scriptPath = $('script').eq(0).attr('src');
            //判断根目录
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

            //生成当前页面的 模块/控制器/方法
            currentView = currentPath.replace(rootPath, '').replace('index.php', '');
            var splitCurrentView = currentView.split('/');
            currentView = [];
            $.each(splitCurrentView, function(k,v){
                if(v != '') currentView.push(v);
            });
            currentView = currentView.join('/');
            var splitAgainLength = currentView.split('/').length;
            if(splitAgainLength > 3 || splitAgainLength < 2 ){
                throw new error('脚本路径信息生成出错');
            }
            if(splitAgainLength == 2) currentView += '/index';
        },

        //重置当前路径（多个页面公用一个cookie时调用该方法）
        resetView : function(){
            currentView = view;
        },

        //读取当前页面的cookie
        readHideColumnsCookie : function(){
            currentCookieKey = cookieKeyPrefix + currentView + '_hidecolumn';

            var cookie = $.cookie(currentCookieKey);
            if(typeof cookie !== 'undefined'){
                currentViewCookie = $.cookie(currentCookieKey);
                return currentViewCookie.replace(cookieValuePrefix, '').split(',');
            }else{
                return '';
            }
        },

        //读取当前页面cookie，隐藏表格中的列
        hideColumnsByCookie : function(allColumns){
            var willHideColumns = this.readHideColumnsCookie();
            if(willHideColumns.length <= 0) return allColumns;
            $.each(allColumns, function(k, v){
                if($.inArray(v.field, willHideColumns) >= 0){
                    allColumns[k]['visible'] = false;
                }
            });
            return allColumns;
        },

        //记录隐藏列
        recordHideColumn:function(cloumn){
            var cookie = currentViewCookie.replace(cookieValuePrefix, '');
            if(cookie == ''){
                currentViewCookie = cookieValuePrefix+cloumn;
                $.cookie(currentCookieKey, currentViewCookie, cookieOption);
            }else {
                var cookieArr = cookie.split(',');
                if ($.inArray(cloumn, cookieArr) < 0) {
                    if (cookieArr[0] != '') currentViewCookie += ',';
                    currentViewCookie += cloumn;
                    $.cookie(currentCookieKey, currentViewCookie, cookieOption);
                }
            }
        },

        //删除隐藏列
        removeHideColumn  : function(cloumn){
            var cookie = currentViewCookie.replace(cookieValuePrefix, '');
            if(cookie != ''){
                var cookieArr = cookie.split(',');
                var cloumnIndex = $.inArray(cloumn, cookieArr);
                if(cloumnIndex >= 0){
                    cookieArr.splice(cloumnIndex, 1);
                    currentViewCookie = cookieValuePrefix;
                    if(cookieArr.length > 0){
                        currentViewCookie += cookieArr.join(',');
                    }
                    $.cookie(currentCookieKey, currentViewCookie, cookieOption);
                }
            }
        }
    };
}(jQuery);
$_hide.init();
