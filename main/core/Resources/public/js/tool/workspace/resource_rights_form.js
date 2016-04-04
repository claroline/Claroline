/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global resourceRightsRoles */

(function () {
    'use strict';

    var simpleRights = window.Claroline.SimpleRights;
    var modal = window.Claroline.Modal;
    var submitForm = function (formAction, form) {
        var formData = new FormData(form);
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
                var flashbag =
                   '<div class="alert alert-success">' +
                   '<a data-dismiss="alert" class="close" href="#" aria-hidden="true">&times;</a>' +
                   Translator.trans('edit_rights_success', {}, 'platform') +
                   '</div>';
                   $('.panel-body').first().prepend(flashbag);
            }
        });
    };

    $('body').on('change', '#simple input', function () {
        var element = this;

        switch ($(this).attr('id')) {
            case 'everyone':
                simpleRights.everyone(element);
                break;
            case 'anonymous':
                simpleRights.anonymous(element);
                break;
            case 'workspace':
                simpleRights.workspace(element);
                break;
            case 'platform':
                simpleRights.platform(element);
                break;
            case 'recursive-option':
                simpleRights.recursive(element);
                break;
        }
    });

    $('body').on('change', '#general input', function () {
        simpleRights.checkAll(this);
    });

    $(document).ready(function () {
        simpleRights.checkAll($('.panel #general input').first());
    });


    $('body').on('click', '#submit-default-rights-form-button', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget).parents('form').first().attr('action');
        var form = document.getElementById('node-rights-form');
        submitForm(formAction, form);
    });

    $('body').on('click', '#submit-right-form-button', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget.parentElement.parentElement).attr('action');
        var form = document.getElementById('node-right-form');
        submitForm(formAction, form);
    });

    $('body').on('click', '#form-node-creation-rights :submit', function (e) {
        e.preventDefault();
        var formAction = $('#form-node-creation-rights').attr('action');
        var form = document.getElementById('form-node-creation-rights');
        submitForm(formAction, form);
    });

    $('.search-role-btn').on('click', function (e) {
        e.preventDefault();
        var search = $('#role-search-text').val();
        $.ajax({
            url: Routing.generate('claro_resource_find_role_by_code', {'code': search}),
            type: 'GET',
            processData: false,
            contentType: false,
            success: function (workspaces) {
                $('#role-list').empty();
                $('#form-right-wrapper').empty();
                $('#role-list').append(Twig.render(resourceRightsRoles,
                    {'workspaces': workspaces, 'nodeId': $('#data').attr('data-node-id')})
                );
            }
        });
    });

    $('body').on('click', '.role-item', function (event) {
        event.preventDefault();
        $.ajax({
            url: event.currentTarget.getAttribute('href'),
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (form) {
                $('#role-list').empty();
                $('#form-right-wrapper').append(form);
            }
        });
    }).on('click', '.res-creation-options', function (event) {
        event.preventDefault();
        modal.fromUrl(event.currentTarget.getAttribute('href'));
    }).on('click', '.workspace-role-item', function (event) {
        event.preventDefault();
        $.ajax({
            context: this,
            url: event.currentTarget.getAttribute('href'),
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (form) {
                $('#form-rights-tag-wrapper').empty();
                $('#form-rights-tag-wrapper').append(form);
            }
        });
    });

    $('body').on('click', '#search-user-without-rights-btn',function () {
        var search = $('#search-user-without-rights-input').val();
        var nodeId = $('#users-without-rights-datas').attr('data-node-id');

        $.ajax({
            url: Routing.generate(
                'claro_resources_rights_users_without_rights_form',
                {'node': nodeId, 'search': search}
            ),
            type: 'GET',
            success: function (datas) {
                $('#users-without-rights-tab').empty();
                $('#users-without-rights-tab').append(datas);
            }
        });
    });

    $('body').on('click', '#search-user-with-rights-btn',function () {
        var search = $('#search-user-with-rights-input').val();
        var nodeId = $('#users-with-rights-datas').attr('data-node-id');

        $.ajax({
            url: Routing.generate(
                'claro_resources_rights_users_with_rights_form',
                {'node': nodeId, 'search': search}
            ),
            type: 'GET',
            success: function (datas) {
                $('#users-with-rights-tab').empty();
                $('#users-with-rights-tab').append(datas);
            }
        });
    });

    $('body').on('change', '#search-user-with-rights-input', function () {
        var search = $('#search-user-with-rights-input').val();
        var nodeId = $('#users-with-rights-datas').attr('data-node-id');

        $.ajax({
            url: Routing.generate(
                'claro_resources_rights_users_with_rights_form',
                {'node': nodeId, 'search': search}
            ),
            type: 'GET',
            success: function (datas) {
                $('#users-with-rights-tab').empty();
                $('#users-with-rights-tab').append(datas);
            }
        });
    });

    $('#users-with-rights-list').on('click', '.pagination > ul > li > a', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var element = event.currentTarget;
        var url = $(element).attr('href');

        if (url !== '#') {
            $.ajax({
                url: url,
                type: 'GET',
                success: function (datas) {
                    $('#users-with-rights-tab').empty();
                    $('#users-with-rights-tab').append(datas);
                }
            });
        }
    });

    $('#users-with-rights-list').on('click', 'th > a', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var element = event.currentTarget;
        var url = $(element).attr('href');

        if (url !== '#') {
            $.ajax({
                url: url,
                type: 'GET',
                success: function (datas) {
                    $('#users-with-rights-tab').empty();
                    $('#users-with-rights-tab').append(datas);
                }
            });
        }
    });

    $('#users-with-rights-list').on('click', '#add-new-user-rights-btn', function () {
        var rights = $('#rights-list').attr('data-rights');
        var isDir = $('#rights-list').attr('data-is-dir');
        var nodeId = $('#rights-list').attr('data-node-id');
        rights = rights.split(',');
        var picker = new UserPicker();
        var settings = {
            'multiple': true,
            'picker_name': 'user_res_picker',
            'return_datas': true
        };
        picker.configure(
            settings,
            function (users) {
                $.each(users, function(index, val) {
                    //add the row to the tab
                    var twigParams = {
                        'user': val,
                        'isDir': true,
                        'rights': rights,
                        'nodeId': nodeId
                    };

                    var el = Twig.render(ResourceRightsRow, twigParams);
                    $('.rights-single-user').append(el);
                });
            }
        );
        picker.open();
    });

    $('body').on('click', '#search-workspaces-btn',function () {
        var search = $('#search-workspaces-input').val();
        var nodeId = $('#workspaces-datas').data('node-id');
        var max = $('#workspaces-datas').data('max');

        $.ajax({
            url: Routing.generate(
                'claro_all_workspaces_list_pager_for_resource_rights',
                {'resource': nodeId, 'wsSearch': search, 'page': 1, 'wsMax': max}
            ),
            type: 'GET',
            success: function (datas) {
                $('#all-workspaces-panel').empty();
                $('#all-workspaces-panel').append(datas);
            }
        });
    });

    $('body').on('change', '#search-workspaces-input', function () {
        var search = $('#search-workspaces-input').val();
        var nodeId = $('#workspaces-datas').data('node-id');
        var max = $('#workspaces-datas').data('max');

        $.ajax({
            url: Routing.generate(
                'claro_all_workspaces_list_pager_for_resource_rights',
                {'resource': nodeId, 'wsSearch': search, 'page': 1, 'wsMax': max}
            ),
            type: 'GET',
            success: function (datas) {
                $('#all-workspaces-panel').empty();
                $('#all-workspaces-panel').append(datas);
            }
        });
    });

    $('#all-workspaces-panel').on('click', '.pagination > ul > li > a', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var element = event.currentTarget;
        var url = $(element).attr('href');

        if (url !== '#') {
            $.ajax({
                url: url,
                type: 'GET',
                success: function (datas) {
                    $('#all-workspaces-panel').empty();
                    $('#all-workspaces-panel').append(datas);
                }
            });
        }
    });

    $('body').on('click', '#root-dir-icon-edit-btn',function () {
        var nodeId = $(this).data('node-id');

        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_resource_icon_edit_form',
                {'node': nodeId}
            ),
            doNothing,
            function() {},
            'resource-icon-form'
        );
    });

    var doNothing = function () {};
})();
