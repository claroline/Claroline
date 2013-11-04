(function () {
    'use strict';

    var resizeWindow;

    function responsiveTopBar() {
        $('#top_bar').removeClass('break');

        if ($('#top_bar').outerHeight() > 55 && !$('#top_bar navbar-collapse').hasClass('in')) {
            $('#top_bar').addClass('break');
        }
    }

    $(window).on('resize', function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(responsiveTopBar, 200);
    });

    $(document).ready(function () {
        responsiveTopBar();
    });

}());
