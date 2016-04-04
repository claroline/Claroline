/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    $('#message_form_to').offsetParent().html(
        '<div class="input-group">' +
            $('#message_form_to').offsetParent().html() +
            '<span class="input-group-btn">' +
                '<button id="contacts-button" class="btn btn-primary" type="button">' +
                    '<i class="fa fa-user"></i>' +
                '</button>' +
            '</span>' +
        '</div>'
    );

    var currentType = 'user';

    var users = [];
    var groups = [];
    var workspaces = [];

    var typeMap = {
        'user': [],
        'group': [],
        'workspace': []
    }

    function getPage(tab)
    {
        var page = 1;

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'page') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    page = tab[i + 1];
                }
                break;
            }
        }

        return page;
    }

    function getSearch(tab)
    {
        var search = '';

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'search') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    search = tab[i + 1];
                }
                break;
            }
        }

        return search;
    }

    function initTempTab()
    {
        typeMap['user'] = users.slice();
        typeMap['group'] = groups.slice();
        typeMap['workspace'] = workspaces.slice();
    }

    function displayCheckBoxStatus()
    {
        $('.contact-chk').each(function () {
            var contactId = $(this).attr('contact-id');

            if (typeMap[currentType].indexOf(contactId) >= 0) {
                $(this).attr('checked', 'checked');
            }
        });
    }

    function displayPager(type, normalRoute, searchRoute, callback)
    {
        currentType = type;
        var toList = $('#message_form_to').val();
        var toListArray = toList.split(';');
        var search = toListArray[toListArray.length - 1].trim();
        var route;

        if (search === '') {
            route = Routing.generate(normalRoute);
        } else {
            route = Routing.generate(
                searchRoute, {'search': search}
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#contacts-list').empty();
                $('#contacts-list').append(datas);
                displayCheckBoxStatus();

                if (callback) {
                    callback();
                }
            }
        });
    }

    function getUsersFromInput(route, elements, queryStringKey)
    {
        var parameters = {};

        if (elements.length > 0) {
            parameters[queryStringKey] = elements;
            route = Routing.generate(route);
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                statusCode: {
                    200: function (datas) {
                        var currentValue = $('#message_form_to').val();

                        if (currentValue === undefined) {
                            currentValue = '';
                        }

                        currentValue += datas;
                        $('#message_form_to').val(currentValue);
                    }
                },
                type: 'GET',
                async: false
            });
        }
    }

    function updateContactInput()
    {
        $('#message_form_to').val('');
        getUsersFromInput('claro_usernames_from_users', users, 'userIds');
        getUsersFromInput('claro_names_from_groups', groups, 'groupIds');
        getUsersFromInput('claro_names_from_workspaces', workspaces, 'workspaceIds');
    }

    function setActiveTab(target) {
        ['#users-nav-tab', '#groups-nav-tab', '#workspaces-nav-tab'].forEach(function (tab) {
            $(tab)[tab === target ? 'addClass' : 'removeClass']('active');
        });
    }

    $('#contacts-button').click(function () {
        initTempTab();
        setActiveTab('#users-nav-tab');
        displayPager(
            'user',
            'claro_message_contactable_users',
            'claro_message_contactable_users_search',
            function () {
                $('#contacts-box').modal('show');
            }
        );
    });

    $('#users-nav-tab').on('click', function () {
        setActiveTab('#users-nav-tab');
        displayPager(
            'user',
            'claro_message_contactable_users',
            'claro_message_contactable_users_search'
        );
    });

    $('#groups-nav-tab').on('click', function () {
        setActiveTab('#groups-nav-tab');
        displayPager(
            'group',
            'claro_message_contactable_groups',
            'claro_message_contactable_groups_search'
        );
    });

    $('#workspaces-nav-tab').on('click', function () {
        setActiveTab('#workspaces-nav-tab');
        displayPager(
            'workspace',
            'claro_message_contactable_workspaces',
            'claro_message_contactable_workspaces_search'
        );
    });

    $('body').on('click', '.pagination > ul > li > a', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var element = event.currentTarget;
        var url = $(element).attr('href');
        var route;

        if (url !== '#') {
            var urlTab = url.split('/');
            var page = getPage(urlTab);
            var search = getSearch(urlTab);

            if (currentType === 'user') {
                route = (search !== '') ?
                    Routing.generate('claro_message_contactable_users_search', {'page': page, 'search': search}):
                    Routing.generate('claro_message_contactable_users', {'page': page});
            }

            if (currentType === 'group') {
                route = (search !== '') ?
                    Routing.generate('claro_message_contactable_groups_search', {'page': page, 'search': search}):
                    Routing.generate('claro_message_contactable_groups', {'page': page});
            }

            if (currentType === 'workspace') {
                route = (search !== '') ?
                    Routing.generate('claro_message_contactable_workspaces', {'page': page}):
                    Routing.generate('claro_message_contactable_workspaces', {'page': page});
            }

            $.ajax({
                url: route,
                success: function (datas) {
                    $('#contacts-list').empty();
                    $('#contacts-list').append(datas);
                    displayCheckBoxStatus();
                },
                type: 'GET'
            });
        }
    });

    $('body').on('click', '.contact-chk', function () {
        var contactId = $(this).attr('contact-id');
        var checked = $(this).prop('checked');
        var index = typeMap[currentType].indexOf(contactId);

        if (checked && index < 0) {
            typeMap[currentType].push(contactId);
        }
        else {
            typeMap[currentType].splice(index, 1);
        }
    });

    $('#add-contacts-confirm-ok').click(function () {
        users = typeMap['user'].slice();
        groups = typeMap['group'].slice();
        workspaces = typeMap['workspace'].slice();
        updateContactInput();
        $('#contacts-box').modal('hide');
    });
})();
