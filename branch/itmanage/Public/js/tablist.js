/**
 * Created by Administrator on 2018/10/31.
 */
//tab生成
//var ac = $('.layui-nav-item > a');//指定有效区
function addIframe(Name,url) {
    $('.now').removeClass('now');
    var add_li = ('<li><a class="now" ><span>' + Name + '</span><span class="closed"></span>' +
    '<i class="fa fa-refresh refresh"></i></a></li>');
    var add_ifame = '<iframe frameborder="0" class="append_frame"  name="' + Name + '" src="'+url+'" ></iframe>';
    $('#if_container iframe').hide();
    $('.uu').append(add_li);
    $('#if_container').append(add_ifame);
}
$('body').on('click','.uu li',function(){
    if($(this).children().eq(0).hasClass('now')) return false;
    $('.now').removeClass('now');
    $(this).children().eq(0).addClass('now');
    var index = $(this).index();
    $('#if_container iframe').eq(index).show().siblings().hide();
});
$('#top_tab_list').on('click','ul li a .closed',function(event){
    if($(this).parent().hasClass('now')){
        $('#top_tab_list ul li a').eq($('#top_tab_list ul li').length-2).addClass('now');
    }
    $('.now').removeClass('now');
    var index = $('#top_tab_list ul li').index($(this).parent().parent());
    $(this).parent().parent().remove();
    $('#if_container iframe').eq(index).remove();
    $('#if_container iframe').eq(index-1).show().siblings().hide();
    $('.uu li').eq(index-1).children().eq(0).addClass('now');
    if($('#if_container iframe:visible').length==0){
        $('#if_container iframe:first-child').show();
    }
})
$('#top_tab_list').on('click','ul li a .refresh',function(event){
    var index = $(this).index('.refresh');
    var obj = $('#if_container iframe').eq(index);
    var url = obj.attr('src');
    //obj.attr('src','');
    obj.attr('src',url);
});

$('#title a').on('click', function(){
    var texts = $(this).text();
    var Name = $(this).text();
    var url = $(this).attr('thref');
    if(url == '#' || url == '') return ;
    var show1a = $('#top_tab_list ul li a span:first-child');
    var nowNum = $('.uu').children().length;

    for (var i = 0; i < show1a.length; i++) {
        if (show1a.eq(i).text() == texts) {
            $('.uu li').eq(i).children().addClass('now');
            $('.uu li').eq(i).siblings().children().removeClass('now');
            $('#title').children().removeClass('layui-this');
            $(this).parent().addClass('layui-this');
            $('#if_container iframe').eq(i).show().siblings().hide();
            return false;
        }
    }
    if (nowNum < 8) {
        $(this).attr('target', texts);
        $('.uu li a').removeClass('now');
        addIframe(Name,url);
    } else if (nowNum == 8) {
        layer.alert('您已打开了8个标签。请关闭部分标签后再打开新标签！');
        return false;
    }
})