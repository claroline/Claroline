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

    var hidden = true;

    function initializeForm()
    {
        $('#team_form_resourceTypes > .checkbox > input').prop('checked', 'checked');
        $('.advanced-param').parents('.form-group').addClass('advanced-param-block');
        $('.advanced-param-block').addClass('hidden');
    }

    function switchHiddenElement()
    {
        hidden = !hidden;

        if (hidden) {
            $('.advanced-param-block').addClass('hidden');
            $('#show-advanced-btn-icon').removeClass('fa-eye-slash');
            $('#show-advanced-btn-icon').addClass('fa-eye');
            $('#show-advanced-btn-text').text(Translator.trans('show_advanced_parameters', {}, 'team'));
        } else {
            $('.advanced-param-block').removeClass('hidden');
            $('#show-advanced-btn-icon').removeClass('fa-eye');
            $('#show-advanced-btn-icon').addClass('fa-eye-slash');
            $('#show-advanced-btn-text').text(Translator.trans('hide_advanced_parameters', {}, 'team'));
        }
    }

    $('#show-advanced-params-btn').on('click', function () {
        switchHiddenElement();
    });

    initializeForm();
})();
