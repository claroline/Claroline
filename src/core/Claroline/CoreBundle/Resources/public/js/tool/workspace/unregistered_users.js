/* global addUserConfirm */

(function () {
    'use strict';

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');

    function initEvents() {
        $('#search-button').click(function () {
            var search = document.getElementById('search-user-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_workspace_unregistered_user_list_search', {
                    'search': search,
                    'workspaceId': twigWorkspaceId
                });
            } else {
                window.location.href = Routing.generate('claro_workspace_unregistered_user_list', {
                    'workspaceId': twigWorkspaceId
                });
            }
        });

        $('.add-users-button').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(addUserConfirm,
                {'nbUsers': $('.chk-user:checked').length}
            ));
        });

        $('#modal-valid-button').on('click', function () {
            var parameters = {};
            var i = 0;
            var array = [];
            $('.chk-user:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;
            var route = Routing.generate('claro_workspace_multiadd_user', {'workspaceId': twigWorkspaceId});
            route += '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                    $('.chk-user:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                },
                type: 'PUT'
            });
        });
    }

    initEvents();
})();