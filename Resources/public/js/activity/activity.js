(function () {
    'use strict';

    var activity = window.Claroline.Activity = {
        'iframeChange': null
    };

    /**
     * resize a iframe
     */
    activity.height = function (element)
    {
        var height = $(element).contents().height();

        if (height) {
            $(element).css('height', height);
        }

        console.log(height);
    };

    /** Events **/

    $('body').on('click', '.activity-tabs > li > a:not(.add-resource)', function () {
        var id = $(this).data('id');

        $('.activity-tabs > li').removeClass('active');
        $(this).parent().addClass('active');
        $('.activities > div').addClass('hide');
        $('#' + id).removeClass('hide');

        clearTimeout(activity.iframeChange);
        activity.iframeChange = setTimeout(function () {
            $('#' + id + ' .activity-iframe').each(function () {
                activity.height(this);
            });
        }, 500);
    });

    $('.activity-iframe').load(function () {
        var iframe = this;
        clearTimeout(activity.iframeChange);
        activity.iframeChange = setTimeout(function () {
            activity.height(iframe);
        }, 500);
    });

    $(window).on('resize', function () {
        clearTimeout(activity.iframeChange);
        activity.iframeChange = setTimeout(function () {
            $('.activity-iframe').each(function () {
                activity.height(this);
            });
        }, 500);
    });

}());
