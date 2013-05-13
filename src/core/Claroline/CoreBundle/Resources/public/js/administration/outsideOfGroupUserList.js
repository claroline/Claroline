/* global addUserConfirm */

(function () {
    'use strict';
    var groupId = $('#twig-attributes').attr('data-group-id');

    function initEvents()
    {
        $('#search-user-button').click(function () {
            var search = document.getElementById('search-user-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_admin_outside_of_group_user_list_search', {
                    'search': search,
                    'groupId': groupId
                });
            } else {
                window.location.href = Routing.generate('claro_admin_outside_of_group_user_list', {
                    'groupId' :groupId
                });
            }
        });

        $('.add-users-button').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(addUserConfirm,
                { 'nbUsers': $('.chk-user:checked').length}
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
            parameters.userIds = array;
            var route = Routing.generate('claro_admin_multiadd_user_to_group', {'groupId': groupId});
            route += '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                    $('.delete-users-button').attr('disabled', 'disabled');
                    $('#deleting').hide();
                    $('.add-users-button').attr('disabled', 'disabled');
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

