/* global removeGroupConfirm */

(function () {
    'use strict';

    var loading = false,
        stop = false,
        mode = 0,
        standardRoute = function () {
            return Routing.generate('claro_admin_paginated_group_list', {
                'offset': $('.row-group').length
            });
        },
        searchRoute = function () {
            return Routing.generate('claro_admin_paginated_search_group_list', {
                'offset': $('.row-group').length,
                'search': document.getElementById('search-group-txt').value
            });
        };

    function lazyloadGroups(route) {
        loading = true;
        $('#loading').show();
        $.ajax({
            url: route(),
            type: 'GET',
            success: function (groups) {
                $('#group-table-body').append(groups);
                loading = false;
                $('#loading').hide();
                if (groups.length === 0) {
                    stop = true;
                }
            },
            complete: function () {
                if ($(window).height() >= $(document).height() && stop === false) {
                    lazyloadGroups(route);
                }
            }
        });
    }

    function initEvents() {
        $('.chk-group').live('change', function () {
            if ($('.chk-group:checked').length) {
                $('.delete-groups-button').removeAttr('disabled');
            } else {
                $('.delete-groups-button').attr('disabled', 'disabled');
            }
        });

        $(window).scroll(function () {
            if  (($(window).scrollTop() + 100 >= $(document).height() - $(window).height()) &&
                loading === false &&
                stop === false) {
                if (mode === 0) {
                    lazyloadGroups(standardRoute);
                } else {
                    lazyloadGroups(searchRoute);
                }
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
            $('#deleting').show();
            $.ajax({
                url: route,
                success: function () {
                    $('.chk-group:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                    $('#validation-box').modal('hide');
                    $('#validation-box-body').empty();
                    $('.delete-groups-button').attr('disabled', 'disabled');
                    $('#deleting').hide();
                },
                type: 'DELETE'
            });
        });

        $('#modal-cancel-button').click(function () {
            $('#validation-box').modal('hide');
            $('#validation-box-body').empty();
        });

        $('#search-group-button').click(function () {
            $('#group-table-body').empty();
            stop = false;
            if (document.getElementById('search-group-txt').value !== '') {
                mode = 1;
                lazyloadGroups(searchRoute);
            } else {
                mode = 0;
                lazyloadGroups(standardRoute);
            }
        });
    }

    $('html, body').animate({scrollTop: 0}, 0);
    $('.loading').hide();
    $('.delete-groups-button').attr('disabled', 'disabled');

    initEvents();
    lazyloadGroups(standardRoute);
})();