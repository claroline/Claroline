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

    $('body')
    .on('click', '.delete-role-btn', function(event) {
        var roleId = $(event.currentTarget).attr('data-role-id');
        var url = Routing.generate('platform_roles_remove', {'role': roleId})
        var roleName = $(event.currentTarget).attr('data-role-name');

        var html = Twig.render(
            ModalWindow,
            {
                'confirmFooter': true,
                'modalId': 'confirm-modal',
                'body': Translator.trans('remove_role_confirm', {'name': roleName}, 'platform'),
                'header': Translator.trans('remove_role', {}, 'platform')
            }
        );

        $('body').append(html);
        //display validation modal
        $('#confirm-modal').modal('show');
        //destroy the modal when hidden
        $('#confirm-modal').on('hidden.bs.modal', function () {
            $(this).remove();
        });

        $('#confirm-ok').on('click', function(event) {
            $.ajax({
                url: url,
                success: function(data) {
                    $('#tr-role-' + roleId).remove();
                    $('#confirm-modal').modal('hide');
                }
            });
        });
    })
    .on('click', '.initialize-role-btn', function(event) {
        var url = Routing.generate('platform_role_initialize', {'role': $(event.currentTarget).attr('data-role-id')});
        var row = $($(event.currentTarget)[0].parentNode.parentNode);

        $.ajax({
            url: url,
            type: 'POST',
            success: function(data) {
                setRoleMaxUsers(data, row);
            },
            error: function() {
                //todo: error handling
            }
        });
    })
    .on('click', '.increase-user-btn', function(event) {
        var row = $($(event.currentTarget)[0].parentNode.parentNode);
        var amount = row.find('.increase-user-field').val();
        var roleId = $(event.currentTarget).attr('data-role-id');
        var url = Routing.generate('platform_role_increase_limit', {'role': roleId, 'amount': amount});

        $.ajax({
            url: url,
            type: 'POST',
            success: function(data) {
                setRoleMaxUsers(data, row);
            }
        });
    })
    .on('click', '.edit-role-name-btn', function(event) {
        var roleId = $(event.currentTarget).attr('data-role-id');
        var row = $($(event.currentTarget)[0].parentNode.parentNode);
        var newName = row.find('.change-name-field').val();
        var url = Routing.generate('platform_role_name_edit', {'role': roleId, 'name': newName});

        $.ajax({
            url: url,
            type: 'POST',
            success: function(data) {
                changeNameCallback(data, row);
            }
        });
    })
    .on('click', '#create-role-btn', function(event) {
        var url = Routing.generate('claro_admin_create_platform_role_form');

        $.ajax({
            url: url,
            success: function(data, textStatus, jqXHR) {
                window.Claroline.Modal.hide();
                window.Claroline.Modal.create(data).on('click', 'button.btn', function(event) {
                    event.preventDefault();
                    submitForm('create-role-form', addRoleRow);
                });
            }
        });
    })
    .on('input', '.change-name-field', function(event) {
            var btn = $($(event.currentTarget)[0].parentNode.parentNode).find('.edit-role-name-btn');
            isBlank($(event.currentTarget).val()) ?
                btn.attr('disabled', 'disabled'):
                btn.removeAttr('disabled');
    })
    .on('input', '.increase-user-field', function(event) {
        var btn = $($(event.currentTarget)[0].parentNode.parentNode).find('.increase-user-btn');
        isNegative($(event.currentTarget).val()) || isBlank($(event.currentTarget).val()) ?
            btn.attr('disabled', 'disabled'):
            btn.removeAttr('disabled');
    })
    .on('click', '.pws-chk', function(event) {
        var roleId = $(event.currentTarget).attr('data-role-id');
        var url = Routing.generate('platform_role_workspace_creation_edit', {'role': roleId});
        $.ajax({
            url: url,
            type: 'GET'
        });
    });

    //HELPER
    function submitForm(formId, successHandler) {
        var formData = new FormData(document.getElementById(formId));
        var url = $('#' + formId).attr('action');

        $.ajax({
            url: url,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    $('.modal').modal('hide');
                    successHandler(data, textStatus, jqXHR);
                } else {
                    $('#create-role-modal').replaceWith(data);
                }
            }
        });
    }

    function showAlert(text, alertClass) {
        var html = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
            text + '</div>';
        $('.panel-body').prepend(html)
    }

    function isBlank(str) {
        return (!str || /^\s*$/.test(str));
    }

    function isNegative(str) {
        return parseInt(str) < 0 ? true: false;
    }

    //CALLBACKS
    var setRoleMaxUsers = function(data, row) {
        $(row.find('.td-role-limit')).html(data['limit']);
        showAlert(Translator.trans('user_limit_success_update', {}, 'platform'), 'alert-success');
    }

    var changeNameCallback = function(data, row) {
        row.find('.change-name-field').val(Translator.trans('platform', data['translationKey']));
        showAlert(Translator.trans('role_name_changed_success', {}, 'platform'), 'alert-success');
    }

    var addRoleRow = function(data) {
        $('#role-table-body').append(Twig.render(RowFormAdminRoleList, {'role': data, 'count': 0}));
    }
})();
