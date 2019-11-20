$(function(){
    $('.i-list1 li').hover(function(){
        $(this).toggleClass('hover');
    });
    if (! (/msie [6|7|8|9]/i.test(navigator.userAgent))) {
        new WOW().init();
    };
})