(function () {
    'use strict';

    var home = window.Claroline.Home;

    $('body').on('click', '.logos .logo', function () {
        $('.logos .logo').removeClass(function (index, css) {
            return (css.match(/\balert-\w+/g) || []).join(' ');
        });
        $('.logos .logo').addClass('alert-warning');

        $(this).removeClass('alert-warning');
        $(this).addClass('alert-info');
        $('.logos input[type="hidden"]').val($(this).data('logo'));
    });

    $('body').on('click', '.logos .logo .close', function (event) {
        event.stopPropagation();
        var element = $(this).parents('.logo');
        home.modal('content/confirm', 'delete-logo', element);
    });

    $('body').on('click', '#delete-logo .btn.delete', function () {
        var element = $('#delete-logo').data('element');
        var logo = $(element).data('logo');



        if (logo && element) {
            $.ajax(home.path + 'admin/delete/logo/' + logo)
            .done(
                function (data)
                {
                    if (data === 'true') {
                        $(element).hide('slow', function () {
                            $(this).remove();
                        });
                    } else {
                        home.modal('content/error');
                    }
                }
            )
            .error(
                function ()
                {
                    home.modal('content/error');
                }
            );
        }
    });

})();
