/* global removeUserConfirm */

(function () {
    'use strict';

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');

    function initEvents() {

        $('.button-parameters-user').live('click', function () {
            var route = Routing.generate(
                'claro_workspace_tools_show_user_parameters',
                {'userId': $(this).attr('data-user-id'), 'workspaceId': twigWorkspaceId}
            );

            window.location.href = route;
        });

        $('#search-user-button').click(function () {
            var search = document.getElementById('search-user-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_workspace_registered_user_list_search', {
                    'search': search,
                    'workspaceId': twigWorkspaceId
                });
            } else {
                window.location.href = Routing.generate('claro_workspace_registered_user_list', {
                    'workspaceId': twigWorkspaceId
                });
            }
        });

        $('#delete-user-button').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(removeUserConfirm,
                {'nbUsers': $('.chk-delete-user:checked').length }
            ));
        });

        $('#modal-valid-button').click(function () {
            var parameters = {};
            var array = [];
            var i = 0;
            $('.chk-delete-user:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;
            var route = Routing.generate('claro_workspace_delete_users', {'workspaceId': twigWorkspaceId});
            route += '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-delete-user:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                },
                type: 'DELETE'
            });
        });

        $('#modal-cancel-button').click(function () {
            $('#validation-box').modal('hide');
            $('#validation-box-body').empty();
        });
    }

    initEvents();
})();
