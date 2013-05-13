(function () {
    'use strict';

    function initEvents() {

        $('#search-msg').click(function () {
            var search = document.getElementById('search-msg-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_message_list_sent_search', {
                    'search': search
                });
            } else {
                window.location.href = Routing.generate('claro_message_list_sent');
            }
        });

        $('.delete-msg').click(function () {
            $('#validation-box').modal('show');
           // $('#validation-box-body').html('delete');
        });

        $('#modal-valid-button').click(function () {
            var parameters = {};
            var i = 0;
            var array = [];
            $('.chk-delete:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;

            var route = Routing.generate('claro_message_delete_from');
            route += '?' + $.param(parameters);
            $('#deleting').show();
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-delete:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#validation-box').modal('hide');
                    $('#allChecked').attr('checked', false);
                },
                type: 'DELETE'
            });
        });

        $('#modal-cancel-button').click(function () {
            $('#validation-box').modal('hide');
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