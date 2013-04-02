(function () {
    'use strict';

    var stackedRequests = 0,
        loading = false,
        stop = false,
        mode = 0,
        standardRoute = function () {
            return Routing.generate('claro_message_list_removed', {
                'offset' : $('.row-user-message').length
            });
        },
        searchRoute = function () {
            return Routing.generate('claro_message_list_removed_search', {
                'offset' : $('.row-user-message').length,
                'search': document.getElementById('search-msg-txt').value
            });
        };

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

    function lazyloadUserMessage(route) {
        loading = true;
        $('#loading').show();
        $.ajax({
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
                    lazyloadUserMessage(route);
                }
            }
        });
    }

    function initEvents() {
        $('.chk-delete').live('change', function () {
            if ($('.chk-delete:checked').length) {
                $('.button-msg').removeAttr('disabled');
            } else {
                $('.button-msg').attr('disabled', 'disabled');
            }
        });

        $(window).scroll(function () {
            if  (($(window).scrollTop() + 100 >= $(document).height() - $(window).height()) &&
                loading === false && stop === false) {
                if (mode === 0) {
                    lazyloadUserMessage(standardRoute);
                } else {
                    lazyloadUserMessage(searchRoute);
                }
            }
        });

        $('#search-msg').click(function () {
            $('#message-table-body').empty();
            stop = false;
            if (document.getElementById('search-msg-txt').value !== '') {
                mode = 1;
                lazyloadUserMessage(searchRoute);
            } else {
                mode = 0;
                lazyloadUserMessage(standardRoute);
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
                    $('.button-msg').attr('disabled', 'disabled');
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
                    $('.button-msg').attr('disabled', 'disabled');
                    $('#allChecked').attr('checked', false);
                },
                type: 'DELETE'
            });
        });

        $('#allChecked').click(function () {
            if ($('#allChecked').is(':checked')) {
                $(' INPUT[@class=' + 'chk-delete' + '][type="checkbox"]').attr('checked', true);
                $('.button-msg').removeAttr('disabled');
            }
            else {
                $(' INPUT[@class=' + 'chk-delete' + '][type="checkbox"]').attr('checked', false);
                $('.button-msg').attr('disabled', 'disabled');
            }
        });
    }

    $('html, body').animate({scrollTop: 0}, 0);
    $('#deleting').hide();
    $('.button-msg').attr('disabled', 'disabled');

    initEvents();
    lazyloadUserMessage(standardRoute);
})();