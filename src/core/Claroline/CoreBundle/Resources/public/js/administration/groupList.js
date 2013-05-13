/* global removeGroupConfirm */

(function () {
    'use strict';

    function initEvents() {

        $('#search-group-button').click(function () {
            var search = document.getElementById('search-group-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_admin_group_list_search', {
                    'search': search
                });
            } else {
                window.location.href = Routing.generate('claro_admin_group_list');
            }
        });

        $('.delete-groups-button').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html(Twig.render(removeGroupConfirm,
                {'nbGroups' : $('.chk-group:checked').length }
            ));
        });

        $('#modal-valid-button').click(function () {
            var parameters = {};
            var i = 0;
            var array = [];
            $('.chk-group:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;
            var route = Routing.generate('claro_admin_multidelete_group');
            route += '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-group:checked').each(function (index, element) {
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