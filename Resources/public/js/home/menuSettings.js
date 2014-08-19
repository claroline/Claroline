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

    var routing = window.Routing;
    var modal = window.Claroline.Modal;
    var common = window.Claroline.Common;

    function saveSettings(data)
    {
        $.ajax(routing.generate('claroline_content_menu_save_settings', data))
        .success(function () {
            window.location.href = routing.generate('claro_get_content_by_type', {'type': 'menu'});
        })
        .error(function () {
            modal.error();
        });
    }

    function checkPoweredBy()
    {
        if ($('#preview-login').prop('checked') ||
            $('#preview-workspaces').prop('checked') ||
            $('#footerMessage').get(0) !== undefined
        ) {
            common.toggle($('.poweredBy'), false, 'navbar-right');
        } else {
            common.toggle($('.poweredBy'), true, 'navbar-right');
        }
    }

    function checkAll()
    {
        var menu = $('#homeMenu').data('menu') !== undefined ? $('#homeMenu').data('menu') : null;
        var homeMenu = $('#homeMenu').data('id') !== undefined ? $('#homeMenu').data('id') : null;
        var activeButton = homeMenu === menu ? true : $('#homeMenu').prop('checked');

        common.toggle($('.gui-options'), $('#homeMenu').prop('checked'));
        common.toggle($('.gui-preview'), $('#homeMenu').prop('checked'));
        common.toggle($('.panel-footer .btn-primary'), activeButton);
        common.toggle($('.preview-login'), $('#preview-login').prop('checked'));
        common.toggle($('.preview-workspaces'), $('#preview-workspaces').prop('checked'));
        common.toggle($('.localeHeader'), $('#preview-locale').prop('checked'));
        common.toggle($('.localeFooter'), !$('#preview-locale').prop('checked'));

        checkPoweredBy();
    }

    $('body').on('change', '#homeMenu', function () {
        checkAll();
    })
    .on('change', '#preview-login', function () {
        common.toggle($('.preview-login'), $(this).prop('checked'));
        checkPoweredBy();
    })
    .on('change', '#preview-workspaces', function () {
        common.toggle($('.preview-workspaces'), $(this).prop('checked'));
        checkPoweredBy();
    })
    .on('change', '#preview-locale', function () {
        common.toggle($('.localeHeader'), $(this).prop('checked'));
        common.toggle($('.localeFooter'), !$(this).prop('checked'));
    })
    .on('click', '.save-menu-settings', function () {
        var menu = $('#homeMenu').data('menu') !== undefined ? $('#homeMenu').data('menu') : null;
        var homeMenu = $('#homeMenu').data('id') !== undefined ? $('#homeMenu').data('id') : null;
        var footerLogin = $('#preview-login').prop('checked').toString();
        var footerWorkspaces = $('#preview-workspaces').prop('checked').toString();
        var headerLocale = $('#preview-locale').prop('checked').toString();

        if (menu !== null && homeMenu === menu && !$('#homeMenu').prop('checked')) {
            saveSettings(
                {'menu': 'null', 'login': footerLogin, 'workspaces': footerWorkspaces, 'locale': headerLocale}
            );
        } else if ($('#homeMenu').prop('checked')) {
            saveSettings(
                {'menu': homeMenu, 'login': footerLogin, 'workspaces': footerWorkspaces, 'locale': headerLocale}
            );
        }
    });

    $(document).ready(function () {
        $('#homeMenu').parent().removeClass('hide');
        checkAll();
    });

}());
