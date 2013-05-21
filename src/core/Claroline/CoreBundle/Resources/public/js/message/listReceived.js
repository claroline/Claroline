(function () {
    'use strict';

    function initEvents() {
        $('#search-msg').click(function () {
            var search = document.getElementById('search-msg-txt').value;

            if (search !== '') {
                window.location.href = Routing.generate('claro_message_list_received_search', {
                    'search': search
                });
            } else {
                window.location.href = Routing.generate('claro_message_list_received');
            }
        });

        $('.delete-msg').click(function () {
            $('#validation-box').modal('show');
        });

        $('.mark-as-read').live('click', function (e) {
            e.preventDefault();
            $.ajax({
                type: 'GET',
                url: $(e.currentTarget).attr('href'),
                success: function () {
                    $(e.target).css('color', 'green');
                    $(e.target).attr('class', 'icon-ok-sign');
                }
            });
        });

        $('#modal-valid-button').click(function () {
            var parameters = {},
                i = 0,
                array = [],
                route =  Routing.generate('claro_message_delete_to');

            $('.chk-delete:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            parameters.ids = array;

            route += '?' + $.param(parameters);
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