/**
 * Created by Administrator on 2018/5/2.
 */
$(function(){
    $('.arrow').click(function(){
        if($('#treearea').is(':visible')){
            $('#atp_wrapper').stop().animate({left:'5px'});
            $('.arrow').stop().animate({left:'2px'});
            $('#treearea').hide(300);
            $('.arrow i').removeClass('fa fa-angle-double-left').addClass('fa fa-angle-double-right');
        }else{
            $('#atp_wrapper').stop().animate({left:'310px'});
            $('.arrow').stop().animate({left:'292px'});
            $('#treearea').show(300);
            $('.arrow i').removeClass('fa fa-angle-double-right').addClass('fa fa-angle-double-left');
        }
    })
})
