/* global removeUserConfirm */

(function () {
    'use strict';
    var groupId = $('#twig-attributes').attr('data-group-id');

    function initEvents() {
        $('#search-user-button').click(function () {
            var search = document.getElementById('search-user-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_admin_user_of_group_list_search', {
                    'search': search,
                    'groupId': groupId
                });
            } else {
                window.location.href = Routing.generate('claro_admin_user_of_group_list', {'groupId' :groupId});
            }
        });

        $('.delete-users-button').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(removeUserConfirm,
                {'nbUsers': $('.chk-user:checked').length}
            ));
        });

        $('#modal-cancel-button').click(function () {
            $('#validation-box').modal('hide');
            $('#validation-box-body').empty();
        });

        $('#modal-valid-button').click(function () {
            var parameters = {};
            var array = [];
            var i = 0;
            $('.chk-user:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;
            var route = Routing.generate('claro_admin_multidelete_user_from_group', {'groupId': groupId});
            route += '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-user:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                },
                type: 'DELETE'
            });
            $('#validation-box').modal('hide');
            $('#validation-box-body').empty();
        });
    }

    initEvents();
})();