/* global removeUserConfirm */

(function () {
    'use strict';

    var loading = false,
        stop = false,
        mode = 0,
        searchRoute = function () {
            return Routing.generate('claro_admin_paginated_search_user_list', {
                'offset': $('.row-user').length,
                'search': document.getElementById('search-user-txt').value
            })

        },
        standardRoute = function () {
            return Routing.generate('claro_admin_paginated_user_list', {
                'offset' : $('.row-user').length
            })
        };

    function lazyloadUsers(route) {
        loading = true;
        $('#loading').show();
        Claroline.Utilities.ajax({
            type: 'GET',
            url: route(),
            success: function (users) {
                $('#user-table-body').append(users);
                loading = false;
                $('#loading').hide();
                if (users.length === 0) {
                    stop = true;
                }
            },
            complete: function () {
                if ($(window).height() >= $(document).height() && stop === false) {
                    lazyloadUsers(route);
                }
            }
        });
    }

    function initEvents() {
        $('.chk-user').live('change', function () {
            if ($('.chk-user:checked').length) {
                $('.delete-users-button').removeAttr('disabled');
            } else {
                $('.delete-users-button').attr('disabled', 'disabled');
            }
        });

        $(window).scroll(function () {
            if  (($(window).scrollTop() + 100 >= $(document).height() - $(window).height()) &&
                loading === false &&
                stop === false) {
                if (mode === 0) {
                    lazyloadUsers(standardRoute);
                } else {
                    lazyloadUsers(searchRoute);
                }
            }
        });

        $('#search-user-button').click(function () {
            $('#user-table-body').empty();
            stop = false;
            if (document.getElementById('search-user-txt').value !== '') {
                mode = 1;
                lazyloadUsers(searchRoute);
            } else {
                mode = 0;
                lazyloadUsers(standardRoute);
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
            $('#deleting').show();
            Claroline.Utilities.ajax({
                url: route,
                success: function () {
                    $('.chk-user:checked').each(function (index, element) {
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
    }

    $('html, body').animate({scrollTop: 0}, 0);
    $('#deleting').hide();
    $('.delete-users-button').attr('disabled', 'disabled');
    initEvents();
    lazyloadUsers(standardRoute);

})();