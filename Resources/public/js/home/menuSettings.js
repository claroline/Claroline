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

    //var home = window.Claroline.Home;
    //var modal = window.Claroline.Modal;

    function previewToggle(element, condition)
    {
        if (condition) {
            element.removeClass('hide');
        } else {
            element.addClass('hide');
        }
    }

    $('body').on('change', '#mainMenu', function () {
        previewToggle($('.gui-options'), $(this).prop('checked'));
        previewToggle($('.gui-preview'), $(this).prop('checked'));
        previewToggle($('.panel-footer .btn-primary'), $(this).prop('checked'));
    })
    .on('change', '#preview-login', function () {
        previewToggle($('.preview-login'), $(this).prop('checked'));
    })
    .on('change', '#preview-workspaces', function () {
        previewToggle($('.preview-workspaces'), $(this).prop('checked'));
    })
    .on('change', '#preview-locale', function () {
        previewToggle($('.localeHeader'), $(this).prop('checked'));
        previewToggle($('.localeFooter'), !$(this).prop('checked'));
    })
    .on('click', '.save-menu-settings', function () {
        var mainMenu = $('#mainMenu').prop('checked');
        var footerLogin = $('#preview-login').prop('checked');
        var footerWorkspaces = $('#preview-workspaces').prop('checked');
        var headerLocale = $('#preview-locale').prop('checked');

        console.log(mainMenu, footerLogin, footerWorkspaces, headerLocale);
    });

}());
