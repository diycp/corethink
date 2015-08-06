// //切换左侧菜单
// $(function() {
//     var result = window.matchMedia("(min-width: 768px)");
//     if (result.matches) {
//         if ($.cookie('menu-title-hide') == 'true') {
//             $('.menu-title').addClass('hidden');
//             $('#left').css('width', '5%');
//             $('#right').css('width', '95%');
//             $('#left .panel-group').addClass('text-center');
//         }
//         $('#menu-toggle').click(function() {
//             if ($.cookie('menu-title-hide') !== '') {
//                 $('.menu-title').removeClass('hidden');
//                 $('#left').css('width', '16.66666667%');
//                 $('#right').css('width', '83.33333333%');
//                 $('#left .panel-group').removeClass('text-center');
//                 $.cookie('menu-title-hide', '');
//             } else {
//                 $('.menu-title').addClass('hidden');
//                 $('#left').css('width', '5%');
//                 $('#right').css('width', '95%');
//                 $('#left .panel-group').addClass('text-center');
//                 $.cookie('menu-title-hide', 'true');
//             }
//         });
//     }
// });



//弹窗提醒
function alertMessager(message, type, time) {
    type = type ? type : 'danger';
    var messager = '<div style="width:380px;height:auto;margin:0 auto;max-width: 80%;top:52px;left:0;right:0;z-index:99999;"' +
        'class="messager navbar-fixed-top border-none alert alert-' + type + '"><button type="button" class="close" ' +
        'data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>' + message + '</div>';
    $('.messager').remove();
    $('body').prepend(messager);
    setTimeout(function() {
        $('.messager').remove();
    }, time ? time : 2000);
}



//搜索功能
$('body').on('click', '.builder #search', function() {
    console.log("message");
    var url = $(this).attr('url');
    var query = $('.builder .search-form').find('input').serialize();
    query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    query = query.replace(/^&/g, '');
    if (url.indexOf('?') > 0) {
        url += '&' + query;
    } else {
        url += '?' + query;
    }
    window.location.href = url;
});


//回车搜索
$(".builder .search-input").keyup(function(e) {
    if (e.keyCode === 13) {
        $("#search").click();
        return false;
    }
});