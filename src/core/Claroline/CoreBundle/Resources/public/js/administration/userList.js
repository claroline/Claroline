/* global removeUserConfirm */

(function () {
    'use strict';

    function initEvents() {
        $('#search-user-button').click(function () {
            var search = document.getElementById('search-user-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_admin_user_list_search', {
                    'search': search
                });
            } else {
                window.location.href = Routing.generate('claro_admin_user_list');
            }
        });

        $('.delete-users-button').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(removeUserConfirm,
                {'nbUsers':  $('.chk-user:checked').length }
            ));
        });

        $('#modal-valid-button').click(function () {
            var parameters = {};
            var i = 0;
            var array = [];
            $('.chk-user:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;

            var route = Routing.generate('claro_admin_multidelete_user');
            route += '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-user:checked').each(function (index, element) {
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