(function () {
    'use strict';

    $('#announcement-form').on('click', '.datepicker', function (event) {
        var isChecked = $('.visible-chk').attr('checked');

        if (isChecked === 'checked') {
            $(event.currentTarget).datepicker('show');
        }
        else {
            $('.datepicker').each(function () {
                $(this).attr('disabled', 'disabled');
            });
        }
    });

    $('#announcement-form').on('changeDate', '.datepicker', function (event) {
        $(event.currentTarget).datepicker('hide');
    });

    $('#announcement-form').on('keydown', '.datepicker', function (event) {
        event.preventDefault();
        $(event.currentTarget).datepicker('hide');
    });

    $('#announcement-form').on('click', '.visible-chk', function (event) {
        var isChecked = $(event.currentTarget).attr('checked');

        $('.datepicker').each(function () {

            if (isChecked === 'checked') {
                $(this).attr('disabled', false);
            } else {
                $(this).attr('disabled', 'disabled');
            }
        });
    });
})();