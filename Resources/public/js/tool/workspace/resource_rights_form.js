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
    var submitForm = function (formAction, formData) {
        //change the redirection
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
                //window.location.href = Routing.generate('claro_workspace_open_tool',
                //{'toolName': 'parameters', 'workspaceId': $('#data').attr('data-workspace-id') });
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
                simpleRights.recursive(element)
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
        var formData = new FormData(form);
        e.preventDefault();
        submitForm(formAction, formData);
    });

    $('body').on('click', '#submit-right-form-button', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget.parentElement.parentElement).attr('action');
        var form = document.getElementById('node-right-form');
        var formData = new FormData(form);
        submitForm(formAction, formData);
    });

    $('body').on('click', '#form-node-creation-rights :submit', function (e) {
        e.preventDefault();
        var formAction = $('#form-node-creation-rights').attr('action');
        var form = document.getElementById('form-node-creation-rights');
        var formData = new FormData(form);
        submitForm(formAction, formData);
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

    $('.role-item').live('click', function (event) {
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
    });

    $('.res-creation-options').live('click', function (event) {
        event.preventDefault();
        modal.fromUrl(event.currentTarget.getAttribute('href'));
    });

    $('.workspace-role-item').live('click', function (event) {
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
})();
