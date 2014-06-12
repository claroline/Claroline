(function () {
    'use strict';

    $('body').on('click', '.activity-tabs > li > a:not(.add-resource)', function () {
        $('.activity-tabs > li').removeClass('active');
        $(this).parent().addClass('active');
        $('.activities > div').addClass('hide');
        $('#' + $(this).data('id')).removeClass('hide');
    });

}());
