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

    function getTagId(tab) {
        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'tag') {
                return tab[i + 1];
            }
        }

        return -1;
    }

    function getPage(tab) {
        var page = 1;

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'page') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    page = tab[i + 1];
                }
                break;
            }
        }

        return page;
    }

    $('#workspace-list-div').on('click', '.pagination > ul > li > a', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var element = event.currentTarget;
        var url = $(element).attr('href');
        var route;

        if (url !== '#') {
            var urlTab = url.split('/');
            var tagId = getTagId(urlTab);
            var page = getPage(urlTab);

            if (tagId === -1) {
                route = Routing.generate(
                    'claro_all_workspaces_list_with_self_reg_pager',
                    {'page': page}
                );
            }
            else {
                route = Routing.generate(
                    'claro_workspace_list_with_self_reg_pager',
                    {'workspaceTagId': tagId, 'page': page}
                );
            }
            $.ajax({
                url: route,
                success: function (result) {
                    var source = $(element).parent().parent().parent().parent();
                    $(source).children().remove();
                    $(source).append(result);
                },
                type: 'GET'
            });
        }
    });

    var twigUserId = document.getElementById('twig-self-registration-user-id').getAttribute('data-user-id');
    var workspaceId;
    var workspaceName;
    var workspaceCode;
    var registrationValidation;
    var registerButtonClass;
    var cancelButtonClass;

    $('body').on('click', '.register-user-to-workspace', function (e) {
        e.preventDefault();
        workspaceId = $(this).attr('data-workspace-id');
        registerButtonClass = '.register-button-' + workspaceId;
        workspaceName = $(this).attr('data-workspace-name');
        workspaceCode = $(this).attr('data-workspace-code');
        registrationValidation = $(this).attr('data-workspace-validation');
        $('#registration-confirm-message').html(workspaceName + ' [' + workspaceCode + ']');
        $('#confirm-registration-validation-box').modal('show');
    });

    $('#registration-confirm-ok').click(function () {
        var resultText;
        var registrationRoute;

        if (registrationValidation === 'validation') {
            registrationRoute = Routing.generate(
                'claro_workspace_add_user_queue',
                {'workspace': workspaceId, 'user': twigUserId}
            );
            resultText = Translator.trans('pending', {}, 'platform');
        } else {
            registrationRoute = Routing.generate(
                'claro_workspace_add_user',
                {'workspace': workspaceId, 'user': twigUserId}
            );
            resultText = Translator.trans('registered', {}, 'platform');
        }

        $.ajax({
            url: registrationRoute,
            type: 'POST',
            success: function () {
                $(registerButtonClass).each(function () {
                    $(this).empty();
                    $(this).html(
                        '<span><i class="fa fa-share-square-o"></i> ' +
                        resultText +
                        ' <i class="pointer-hand fa fa-times-circle cancel-workspace-pending" data-workspace-id="' +
                        workspaceId +
                        '" data-workspace-name="' +
                        workspaceName +
                        '" data-workspace-code="' +
                        workspaceCode +
                        '" data-workspace-validation="' +
                        registrationValidation +
                        '"></i></span>'
                    );
                    $(this).attr('class', 'pull-right label label-success cancel-button-' + workspaceId);
                });
                $('#confirm-registration-validation-box').modal('hide');
                $('#registration-confirm-message').empty();
            }
        });
    });

    $('#search-workspace-btn').on('click', function () {
        var search = $('#search-workspace-input').val();

        window.location.href = Routing.generate(
            'claro_list_workspaces_with_self_registration',
            {'search': search}
        );
    });

    $('#search-workspace-input').on('change', function () {
        var search = $('#search-workspace-input').val();

        window.location.href = Routing.generate(
            'claro_list_workspaces_with_self_registration',
            {'search': search}
        );
    });

    $('body').on('click', '.cancel-workspace-pending', function () {
        workspaceId = $(this).data('workspace-id');
        workspaceName = $(this).attr('data-workspace-name');
        workspaceCode = $(this).attr('data-workspace-code');
        registrationValidation = $(this).attr('data-workspace-validation');
        cancelButtonClass = '.cancel-button-' + workspaceId;
        $('#confirm-queue-removal-box').modal('show');
    });

    $('#workspace-queue-cancel-confirm-ok').click(function () {
        var resultText = Translator.trans('platform' + ':' + 'register');

        $.ajax({
            url: Routing.generate(
                'claro_workspace_remove_user_from_queue',
                {'workspace': workspaceId}
            ),
            type: 'POST',
            success: function () {
                $(cancelButtonClass).each(function () {
                    $(this).empty();
                    $(this).html(
                        '<span><i class="fa fa-plus-circle"></i> ' +
                        resultText +
                        '</span>'
                    );
                    $(this).attr(
                        'class',
                        'pull-right pointer-hand label label-primary register-user-to-workspace register-button-'
                        + workspaceId
                    );
                    $(this).attr('data-workspace-id', workspaceId);
                    $(this).attr('data-workspace-name', workspaceName);
                    $(this).attr('data-workspace-code', workspaceCode);
                    $(this).attr('data-workspace-validation', registrationValidation);
                });
                $('#confirm-queue-removal-box').modal('hide');
            }
        });
    });
})();
