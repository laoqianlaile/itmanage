/**
 * Created by Administrator on 2018/5/15.
 */
window.onload=function(){
    document.onkeypress = doKey;
    document.onkeydown = doKey;
}

function doKey(e){
    var ev = e || window.event;
    var obj = ev.target || ev.srcElement;
    var t = obj.type || obj.getAttribute('type');
    if(ev.keyCode == 8 && t!= 'password' && t!='text' && t!= 'textarea'){
        return false;
    }
}