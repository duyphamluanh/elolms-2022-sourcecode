if ($('#back-to-top').length) {
    var scrollTrigger = 200, // px
        backToTop = function () {
            var scrollTop = $(window).scrollTop();
            if (scrollTop > scrollTrigger) {
                $('#back-to-top').addClass('show');
            } else {
                $('#back-to-top').removeClass('show');
            }
        };
    backToTop();
    $(window).on('scroll', function () {
        backToTop();
    });
    $('#back-to-top').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 700);
    });
}
$(function (){
    $('.topnavsearch-icon').click(function(){
        $('.boxsearch').toggleClass('showbuttonsearchnav');
        $('#page').removeClass('fixpage');
        $('#nav-drawer').removeClass('fixnav-drawer');
    });
    
    $(window).on('scroll', function () {
        var scrollTriggerelo = 200;
        var scrollBottom = $(window).scrollTop();

        if (scrollBottom < scrollTriggerelo) {
            $('.boxsearch').addClass('showbuttonsearchnav');
            $('#page').addClass('fixpage');
            $('#nav-drawer').addClass('fixnav-drawer');
        } 
        else {
            $('.boxsearch').removeClass('showbuttonsearchnav');
            $('#page').removeClass('fixpage');
            $('#nav-drawer').removeClass('fixnav-drawer');
        }
    });
    
});
