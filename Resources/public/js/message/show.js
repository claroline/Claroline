(function () {
    'use strict';

    $('#message_form_to').offsetParent().html(
        '<div class="input-group">' +
            $('#message_form_to').offsetParent().html() +
            '<span class="input-group-btn">' +
                '<button id="contacts-button" class="btn btn-primary" type="button">' +
                    '<i class="icon-user"></i>' +
                '</button>' +
            '</span>' +
        '</div>'
    );

    var currentType = 'user';
    var users = [];
    var groups = [];
    var usersTemp = [];
    var groupsTemp = [];

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
        usersTemp = users.slice();
        groupsTemp = groups.slice();
    }

    function displayCheckBoxStatus()
    {
        if (currentType === 'user') {
            $('.contact-chk').each(function () {
                var contactId = $(this).attr('contact-id');

                if (usersTemp.indexOf(contactId) >= 0) {
                    $(this).attr('checked', 'checked');
                }
            });
        }
        else {
            $('.contact-chk').each(function () {
                var contactId = $(this).attr('contact-id');

                if (groupsTemp.indexOf(contactId) >= 0) {
                    $(this).attr('checked', 'checked');
                }
            });
        }
    }

    function displayUsers()
    {
        currentType = 'user';

        $.ajax({
            url: Routing.generate(
                'claro_message_contactable_users'
            ),
            type: 'GET',
            success: function (datas) {
                $('#groups-nav-tab').attr('class', '');
                $('#users-nav-tab').attr('class', 'active');
                $('#contacts-list').empty();
                $('#contacts-list').append(datas);
                displayCheckBoxStatus();
            }
        });
    }

    function displayGroups()
    {
        currentType = 'group';

        $.ajax({
            url: Routing.generate(
                'claro_message_contactable_groups'
            ),
            type: 'GET',
            success: function (datas) {
                $('#groups-nav-tab').attr('class', 'active');
                $('#users-nav-tab').attr('class', '');
                $('#contacts-list').empty();
                $('#contacts-list').append(datas);
                displayCheckBoxStatus();
            }
        });
    }

    function updateContactInput()
    {
        var parameters = {};
        var route;

        if (users.length > 0) {
            parameters.userIds = users;
            route = Routing.generate('claro_usernames_from_users');
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                statusCode: {
                    200: function (datas) {
                        $('#message_form_to').attr('value', datas);
                    }
                },
                type: 'GET',
                async: false
            });
        }
        else {
            $('#message_form_to').attr('value', '');
        }

        if (groups.length > 0) {
            parameters.groupIds = groups;
            route = Routing.generate('claro_names_from_groups');
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                statusCode: {
                    200: function (datas) {
                        var currentValue = $('#message_form_to').attr('value');
                        currentValue += datas;
                        $('#message_form_to').attr('value', currentValue);
                    }
                },
                type: 'GET'
            });
        }
    }

    $('#contacts-button').click(function () {
        initTempTab();
        displayUsers();
        $('#contacts-box').modal('show');
    });

    $('#users-nav-tab').on('click', function () {
        $('#groups-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
        displayUsers();
    });

    $('#groups-nav-tab').on('click', function () {
        $('#users-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
        displayGroups();
    });

    $('body').on('click', '.pagination > li > a', function (event) {
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
                if (search !== '') {
                    route = Routing.generate(
                        'claro_message_contactable_users_search',
                        {'page': page, 'search': search}
                    );
                } else {
                    route = Routing.generate(
                        'claro_message_contactable_users',
                        {'page': page}
                    );
                }
            }
            else {
                if (search !== '') {
                    route = Routing.generate(
                        'claro_message_contactable_groups_search',
                        {'page': page, 'search': search}
                    );
                } else {
                    route = Routing.generate(
                        'claro_message_contactable_groups',
                        {'page': page}
                    );
                }
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
        var checked = $(this).attr('checked');
        var index;

        if (currentType === 'user') {
            if (checked === 'checked') {
                index = usersTemp.indexOf(contactId);

                if (index < 0) {
                    usersTemp.push(contactId);
                }
            }
            else {
                index = usersTemp.indexOf(contactId);

                if (index >= 0) {
                    usersTemp.splice(index, 1);
                }
            }
        }
        else {
            if (checked === 'checked') {
                index = groupsTemp.indexOf(contactId);

                if (index < 0) {
                    groupsTemp.push(contactId);
                }
            }
            else {
                index = groupsTemp.indexOf(contactId);

                if (index >= 0) {
                    groupsTemp.splice(index, 1);
                }
            }
        }
    });

    $('#add-contacts-confirm-ok').click(function () {
        users = usersTemp.slice();
        groups = groupsTemp.slice();
        updateContactInput();
        $('#contacts-box').modal('hide');
    });
})();