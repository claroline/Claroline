(function () {
    'use strict';

    var resizeWindow;

    function responsiveTopBar() {
        $('#top_bar').removeClass('break');

        if ($('#top_bar .navbar-collapse').outerHeight() > 55) {
            $('#top_bar').addClass('break');
        } else {
            $('#top_bar').css('overflow', 'visible');
        }
    }

    $(window).on('resize', function () {
        $('#top_bar').css('overflow', 'hidden');
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(responsiveTopBar, 200);
    });

    $(document).ready(function () {
        responsiveTopBar();
    });

}());
