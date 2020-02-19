/**
 * Created by Administrator on 2018/3/29.
 */
function scrollConBox(o){
    var $$ = function(el){
        return document.querySelector(el);
    };
    var getStyle = function(obj,name){
        if(obj.currentStyle){
            return obj.currentStyle[name];
        }else{
            return getComputedStyle(obj,false)[name];
        }
    };
    var con = $$(o.el);
    var scrollHtml = '<div class="scrollConBox" style="position: relative;overflow: hidden">' +
        '<div class="scrollCon" style="overflow-x: hidden;overflow-y: auto">"+con.innerHtml+"</div>></div>'
}
