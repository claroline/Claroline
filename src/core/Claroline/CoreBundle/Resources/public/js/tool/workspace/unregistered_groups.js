/* global addGroupConfirm */

(function () {
    'use strict';

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');

    function initEvents() {
        $('.add-groups-button').on('click', function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(addGroupConfirm,
                { 'nbGroups': $('.chk-grp:checked').length}
            ));
        });

        $('#modal-valid-button').on('click', function () {
            var parameters = {};
            var array = [];
            var i = 0;
            $('.chk-grp:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;
            var route = Routing.generate('claro_workspace_multiadd_group', {'workspaceId': twigWorkspaceId});
            route += '?' + $.param(parameters);
            $('#adding').show();
            $.ajax({
                url: route,
                success: function () {
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                    $('.chk-grp:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('.add-groups-button').attr('disabled', 'disabled');
                },
                type: 'PUT'
            });
        });

        $('#search-group-button').click(function () {
            var search = document.getElementById('search-group-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_workspace_unregistered_group_list_search', {
                    'search': search,
                    'workspaceId': twigWorkspaceId
                });
            } else {
                window.location.href = Routing.generate('claro_workspace_unregistered_group_list', {
                    'workspaceId': twigWorkspaceId
                });
            }
        });

        $('#modal-cancel-button').click(function () {
            $('#validation-box').modal('hide');
            $('#validation-box-body').empty();
        });
    }

    initEvents();
})();
