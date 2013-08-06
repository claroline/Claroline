(function () {
    'use strict';

    $('#search-workspace-button').click(function () {
        var search = document.getElementById('search-workspace-txt').value;

        if (search !== '') {
            window.location.href = Routing.generate('claro_admin_registration_management_search', {
                'search': search
            });
        } else {
            window.location.href = Routing.generate('claro_admin_registration_management');
        }
    });

    $('.accordion-checkbox').click(function (event) {
        event.stopPropagation();
        var element = event.currentTarget;
        var checkedValue = $(element).attr('checked') === 'checked' ? true : false;
        var value = $(element).attr('value');
        var subMenus = 'input[class^="chk-workspaces-' + value + '"]';
        var subElements = 'input[class^="chk-workspace-' + value + '"]';
        $(subMenus).each(function (index, element) {
            $(element).attr('checked', checkedValue);
        });
        $(subElements).each(function (index, element) {
            $(element).attr('checked', checkedValue);
        });

        if ($('.workspace-check:checked').length > 0) {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', false);
        }
        else {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', 'disabled');
        }
    });

    $('.subscribe-user-button, .subscribe-group-button').click(function () {
        var route;
        var type = $(this).attr('subject-type');
        var parameters = {};
        var array = [];
        var i = 0;
        $('.workspace-check:checked').each(function (index, element) {
            if (array.indexOf(element.value) === -1) {
                array[i] = element.value;
                i++;
            }
        });
        parameters.ids = array;

        if (type === 'user') {
            route = Routing.generate('claro_admin_registration_management_users');
        }
        else {
            route = Routing.generate('claro_admin_registration_management_groups');
        }
        route += '?' + $.param(parameters);

        window.location.href = route;
    });

    $('#workspace-list-div').on('click', '.workspace-check', function () {
        if ($('.workspace-check:checked').length > 0) {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', false);
        }
        else {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', 'disabled');
        }
    });
})();