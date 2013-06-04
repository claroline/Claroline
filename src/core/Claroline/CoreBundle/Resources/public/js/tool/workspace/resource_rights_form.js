/* global resourceRightsRoles */

(function () {
    'use strict';

    var submitForm = function (formAction, formData) {
        alert('presm');
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
                alert('success');
                window.location.href = Routing.generate('claro_workspace_open_tool',
                {'toolName': 'parameters', 'workspaceId': $('#data').attr('data-workspace-id') });
            }
        });
    };

    $('body').on('click', '#submit-default-rights-form-button', function (e) {
        var formAction = $(e.currentTarget.parentElement).attr('action');
        var form = document.getElementById('resource-rights-form');
        var formData = new FormData(form);
        e.preventDefault();
        submitForm(formAction, formData);
    });

    $('body').on('click', '#submit-right-form-button', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget.parentElement.parentElement).attr('action');
        var form = document.getElementById('resource-right-form');
        var formData = new FormData(form);
        submitForm(formAction, formData);
    });

    $('body').on('click', '#form-resource-creation-rights :submit', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget.parentElement).attr('action');
        var form = document.getElementById('form-resource-creation-rights');
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
                $('#form-right-wrapper').empty();
                $('#role-list').empty();
                $('#role-list').append(Twig.render(resourceRightsRoles,
                    {'workspaces': workspaces, 'resourceId': $('#data').attr('data-resource-id')})
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

        if (event.currentTarget.getAttribute('data-toggle') !== 'tab') {
            $.ajax({
                url: event.currentTarget.getAttribute('href'),
                type: 'POST',
                processData: false,
                contentType: false,
                success: function (form) {
                    $('#form-right-wrapper').empty();
                    $('#form-right-wrapper').append(form);
                }
            });
        }
    });
})();