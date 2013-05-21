(function () {
    'use strict';

    function initEvents() {

        $('#search-msg').click(function () {
            var search = document.getElementById('search-msg-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_message_list_removed_search', {
                    'search': search
                });
            } else {
                window.location.href = Routing.generate('claro_message_list_removed');
            }
        });

        $('.delete-msg').click(function () {
            $('#delete-validation-box').modal('show');
        });

        $('#delete-modal-valid-button').click(function () {
            var parameters = {};
            var i = 0;
            var array = [];
            $('.chk-delete:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;

            var route = Routing.generate('claro_message_delete_trash');
            route +=  '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-delete:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#delete-validation-box').modal('hide');
                    $('#allChecked').attr('checked', false);
                },
                type: 'DELETE'
            });
        });

        $('#delete-modal-cancel-button').click(function () {
            $('#delete-validation-box').modal('hide');
        });

        $('#restore-msg').click(function () {
            var parameters = {};
            var i = 0;
            var array = [];
            $('.chk-delete:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;

            var route = Routing.generate('claro_message_restore_from_trash');
            route +=  '?' + $.param(parameters);
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-delete:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#allChecked').attr('checked', false);
                },
                type: 'DELETE'
            });
        });

        $('#allChecked').click(function () {
            if ($('#allChecked').is(':checked')) {
                $(' INPUT[@class=' + 'chk-delete' + '][type="checkbox"]').attr('checked', true);
            }
            else {
                $(' INPUT[@class=' + 'chk-delete' + '][type="checkbox"]').attr('checked', false);
            }
        });
    }

    initEvents();
})();