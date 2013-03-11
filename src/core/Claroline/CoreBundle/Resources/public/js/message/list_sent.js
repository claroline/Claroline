(function () {
    'use strict';

    var loading = false,
        stop = false,
        mode = 0,
        stackedRequests = 0,
        standardRoute = function () {
            return Routing.generate('claro_message_list_sent',
                {'offset' : $('.row-message').length}
        )},
        searchRoute = function () {
            return Routing.generate('claro_message_list_sent_search',
                {'offset' : $('.row-message').length, 'search': document.getElementById('search-msg-txt').value}
        )};

    $.ajaxSetup({
        beforeSend: function () {
            stackedRequests++;
            $('.please-wait').show();
        },
        complete: function () {
            stackedRequests--;
            if (stackedRequests === 0) {
                $('.please-wait').hide();
            }
        }
    });

    function lazyloadMessage(route) {
        loading = true;
        $('#loading').show();
        Claroline.Utilities.ajax({
            type: 'GET',
            url: route(),
            success: function (messages) {
                $('#message-table-body').append(messages);
                loading = false;
                $('#loading').hide();
                if (messages.length === 0) {
                    stop = true;
                }
                stackedRequests--;
                if (stackedRequests === 0) {
                    $('.please-wait').hide();
                }
            },
            complete: function () {
                if ($(window).height() >= $(document).height() && stop === false) {
                    lazyloadMessage(route);
                }
            }
        });
    }

    function initEvents() {
        $('.chk-delete').live('change', function () {
            if ($('.chk-delete:checked').length) {
                $('.delete-msg').removeAttr('disabled');
            } else {
                $('.delete-msg').attr('disabled', 'disabled');
            }
        });

        $(window).scroll(function () {
            if  (($(window).scrollTop() + 100 >= $(document).height() - $(window).height()) &&
                loading === false && stop === false) {
                if (mode === 0) {
                    lazyloadMessage(standardRoute);
                } else {
                    lazyloadMessage(searchRoute);
                }
            }
        });

        $('#search-msg').click(function () {
            $('#message-table-body').empty();
            stop = false;
            if (document.getElementById('search-msg-txt').value !== '') {
                mode = 1;
                lazyloadMessage(searchRoute);
            } else {
                mode = 0;
                lazyloadMessage(standardRoute);
            }
        });

        $('.delete-msg').click(function () {
            $('#validation-box').modal('show');
            $('#validation-box-body').html('delete');
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
            Claroline.Utilities.ajax({
                url: route,
                success: function () {
                    $('.chk-delete:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                    $('.delete-users-button').attr('disabled', 'disabled');
                    $('#deleting').hide();
                },
                type: 'DELETE'
            });
        });

        $('#modal-cancel-button').click(function () {
            $('#validation-box').modal('hide');
            $('#validation-box-body').empty();
        });

        $('#allChecked').click(function () {
            if ($('#allChecked').is(':checked')) {
                $(' INPUT[@class=' + 'chk-delete' + '][type="checkbox"]').attr('checked', true);
                $('.delete-msg').removeAttr('disabled');
            }
            else {
                $(' INPUT[@class=' + 'chk-delete' + '][type="checkbox"]').attr('checked', false);
                $('.delete-msg').attr('disabled', 'disabled');
            }
        });
    }

    $('html, body').animate({scrollTop: 0}, 0);
    $('.loading').hide();
    $('.delete-msg').attr('disabled', 'disabled');
    initEvents();
    lazyloadMessage(standardRoute);
})();