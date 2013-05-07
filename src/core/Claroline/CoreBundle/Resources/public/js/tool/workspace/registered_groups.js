/* global removeGroupConfirm */

(function () {
    'use strict';

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');

    function initEvents() {
        $('.delete-groups-button').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(removeGroupConfirm,
                {'nbGroups': $('.chk-group:checked').length}
            ));
        });

        $('#modal-valid-button').click(function () {
            var parameters = {};
            var array = [];
            var i = 0;
            $('.chk-group:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });

            parameters.ids = array;
            var route = Routing.generate('claro_workspace_delete_groups', {'workspaceId': twigWorkspaceId});
            route += '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-group:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                    $('.delete-groups-button').attr('disabled', 'disabled');
                    $('#deleting').hide();
                },
                type: 'DELETE'
            });
        });

        $('#modal-cancel-button').click(function () {
            $('#validation-box').modal('hide');
            $('#validation-box-body').empty();
        });

        $('#search-group-button').click(function () {
            var search = document.getElementById('search-group-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_workspace_registered_group_list_search', {
                    'search': search,
                    'workspaceId': twigWorkspaceId
                });
            } else {
                window.location.href = Routing.generate('claro_workspace_registered_group_list', {
                    'workspaceId': twigWorkspaceId
                });
            }
        });

        $('.button-parameters-group').live('click', function () {
            var route = Routing.generate(
                'claro_workspace_tools_show_group_parameters',
                {'groupId': $(this).parent().parent().attr('data-group-id'), 'workspaceId': twigWorkspaceId}
            );

            window.location.href = route;
        });
    }

    initEvents();
})();
