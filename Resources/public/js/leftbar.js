(function () {
    'use strict';

    window.ClarolineLeftBar = null;

    if ($('#left-bar').html().replace(/^\s+/g, '').replace(/\s+$/g, '') !== '') {
        $('#left-bar').parent().removeClass('hide');
        $('#left-bar .list-group-item.disabled').html(
            '<i class="icon-caret-right"></i>' + $('#left-bar .list-group-item.disabled'
        ).html());
        $('body').addClass('left-bar-push');
        $('#top_bar').addClass('left-bar-push');
        $('.impersonalitation > .navbar-fixed-top').addClass('left-bar-push');
    }

    $('body').on('mouseenter', '#left-bar', function (event) {
        //if correct bug chrome inside select autside element
        if (event.clientX !== 0 && event.clientY !== 0) {
            var element = this;
            clearTimeout(window.ClarolineLeftBar);
            window.ClarolineLeftBar = setTimeout(function () {
                $(element).animate({width: '400px'}, 300);
            }, 200);
        }
    });

    $('body').on('mouseleave', '#left-bar', function () {
        var element = this;
        clearTimeout(window.ClarolineLeftBar);
        window.ClarolineLeftBar = setTimeout(function () {
            $('.in', element).each(function () {
                $(this).removeClass('in');
                $(this).addClass('collapse');
            });

            $(element).animate({width: '40px'}, 300);
        }, 200);
    });

}());
