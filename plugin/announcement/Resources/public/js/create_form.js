/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    function enableNotification()
    {
        var value = $('#announcement_form_visible_from').val();
        
        if (value === '') {
            $('#announcement_form_notify_user').prop('disabled', false);
        } else {
            $('#announcement_form_notify_user').prop('checked', false);
            $('#announcement_form_notify_user').prop('disabled', 'disabled');
        }
    }

    $('.datepicker').on('click', function (event) {
        $(event.currentTarget).datepicker('show');

    });

    $('.visible-chk').on('click', function (){
        var isChecked = $('.visible-chk').attr('checked');
        if (isChecked === 'checked') {
            $('.datepicker').each(function () {
                $(this).prop('disabled', false);
            });
        }
        else {
            $('.datepicker').each(function () {
                $(this).attr('disabled', 'disabled');
            });
        }
    });

    $('#announcement_form_visible_from').on('change', function () {
        enableNotification();
    });
    
    enableNotification();
})();

