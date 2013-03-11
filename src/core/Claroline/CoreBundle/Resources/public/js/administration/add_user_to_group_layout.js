/* global addUserConfirm */
/* global userListShort */

(function () {
    'use strict';

    var loading = false,
        stop = false,
        mode = 0,
        groupId = document.getElementById('twig-attributes').getAttribute('data-group-id'),
        standardRoute = Routing.generate('claro_admin_groupless_users', {
            'offset' : $('.row-user').length,
            'groupId': groupId
        }),
        searchRoute = Routing.generate('claro_admin_search_groupless_users', {
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

    function initEvents()
    {
        $('.chk-user').live('change', function () {
            if ($('.chk-user:checked').length) {
                $('.add-users-button').removeAttr('disabled');
            } else {
                $('.add-users-button').attr('disabled', 'disabled');
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
            $('.checkbox-user-name').remove();
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
            $('#adding').show();
            Claroline.Utilities.ajax({
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
                    $('#adding').hide();
                },
                type: 'PUT'
            });
        });
    }

    $('html, body').animate({
        scrollTop: 0
    }, 0);

    $('.loading').hide();
    $('.add-users-button').attr('disabled', 'disabled');
    initEvents();
    lazyloadUsers(standardRoute);

})();

