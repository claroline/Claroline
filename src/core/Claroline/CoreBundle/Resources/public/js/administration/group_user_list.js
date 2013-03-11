/* global userListShort */
/* global removeUserConfirm */

(function () {
    'use strict';

    var loading = false,
        stop = false,
        mode = 0,
        groupId = document.getElementById('twig-attributes').getAttribute('data-group-id'),
        standardRoute = Routing.generate('claro_admin_paginated_group_user_list', {
            'offset' : $('.row-user').length,
            'groupId': groupId
        }),
        searchRoute = Routing.generate('claro_admin_paginated_search_group_user_list', {
            'offset' : $('.row-user').length,
            'groupId': groupId,
            'search':  document.getElementById('search-user-txt').value
        });

    function lazyloadUsers(route) {
        loading = true;
        $('#loading').show();
        Claroline.Utilities.ajax({
            url: route,
            type: 'GET',
            success: function (users) {
                $('#user-table-body').append(Twig.render(userListShort, {
                    'users': users
                }));
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
            parameters.userIds = array;
            var route = Routing.generate('claro_admin_multidelete_user_from_group', {'groupId': groupId});
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
    }

    $('html, body').animate({
        scrollTop: 0
    }, 0);
    $('.loading').hide();
    $('.delete-users-button').attr('disabled', 'disabled');
    initEvents();
    lazyloadUsers(standardRoute);
})();