/* global addUserConfirm */

(function () {
    'use strict';

    $('html, body').animate({
        scrollTop: 0
    }, 0);

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId'),
        loading = false,
        stop = false,
        mode = 0,
        standardRoute = function () {
            return Routing.generate('claro_workspace_unregistered_users_paginated', {
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-user').length
            })
        },
        searchRoute = function () {
            return Routing.generate('claro_workspace_search_unregistered_users', {
                'search': document.getElementById('search-user-txt').value,
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-user').length
            })
        };

    function lazyloadUsers(route) {
        loading = true;
        $('#loading').show();
        Claroline.Utilities.ajax({
            url: route(),
            success: function (users) {
                createUsersChkBoxes(users);
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
            },
            type: 'GET'
        });
    }

    function createUsersChkBoxes(JSONString) {
        var i = 0;
        while (i < JSONString.length)
        {
            var list = '<tr class="row-user">';
            list += '<td align="center">' + JSONString[i].username + '</td>';
            list += '<td align="center">' + JSONString[i].lastname + '</td>';
            list += '<td align="center">' + JSONString[i].firstname + '</td>';
            list += '<td align="center"><input class="chk-user" id="checkbox-user-' + JSONString[i].id +
                '" type="checkbox" value="' + JSONString[i].id + '" id="checkbox-user-' + JSONString[i].id +
                '"></input></td>';
            list += '</tr>';
            $('#user-table-checkboxes-body').append(list);
            i++;
        }
    }

    function initEvents() {
        $('.chk-user').live('change', function () {
            if ($('.chk-user:checked').length) {
                $('.add-users-button').removeAttr('disabled');
            } else {
                $('.add-users-button').attr('disabled', 'disabled');
            }
        });

        $('#search-button').click(function () {
            $('.chk-user').remove();
            $('#user-table-checkboxes-body').empty();
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
                {'nbUsers': $('.chk-user:checked').length}
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
            parameters.ids = array;
            var route = Routing.generate('claro_workspace_multiadd_user', {'workspaceId': twigWorkspaceId});
            route += '?' + $.param(parameters);
            $('#adding').show();
            Claroline.Utilities.ajax({
                url: route,
                success: function () {
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                    $('.add-users-button').attr('disabled', 'disabled');
                    $('.chk-user:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#adding').hide();
                },
                type: 'PUT'
            });
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
    }


    $('.add-users-button').attr('disabled', 'disabled');
    $('.loading').hide();

    initEvents();
    lazyloadUsers(standardRoute);
})();