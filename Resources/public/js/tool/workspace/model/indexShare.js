/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var currentType = '';
var typeMap = {'user': [], 'group': []};
var modelId = $('#div-data').attr('data-model-id');

$('#add-user-btn').on('click', function () {
    currentType = 'user';
    typeMap['user'] = [];
    window.Claroline.Modal.confirmContainer(Translator.get('platform:add_user'), '')
    .on('click', '.btn-primary', function(event) { addUsers() });
    displayPager(Routing.generate('ws_share_user_list', {'model': modelId}));
});

$('#add-group-btn').on('click', function () {
    currentType = 'group';
    typeMap['group'] = [];
    window.Claroline.Modal.confirmContainer(Translator.get('platform:add_group'), '')
    .on('click', '.btn-primary', function(event) { addGroups() });
    displayPager(Routing.generate('ws_share_group_list', {'model': modelId}));
});

//from userShare.html.twig
$('body').on('click', '#search-users', function () {
    var search = $('#search-users-txt').val();
    var url = Routing.generate('ws_share_user_list_search', {'search': search, 'model': modelId});
    displayPager(url);
});

//from groupShare.html.twig
$('body').on('click', '#search-groups', function () {
    var search = $('#search-groups-txt').val();
    var url = Routing.generate('ws_share_group_list_search', {'search': search, 'model': modelId});
    displayPager(url);
});

$('body').on('click', '.delete-user', function (event) {
    event.preventDefault();
    window.Claroline.Modal.confirmRequest(
        $(event.currentTarget).attr('href'),
        removeTableRow,
        'user',
        Translator.get('platform:remove_user_confirm'),
        Translator.get('platform:remove_user')
    );
});

$('body').on('click', '.delete-group', function (event) {
    event.preventDefault();
    window.Claroline.Modal.confirmRequest(
        $(event.currentTarget).attr('href'),
        removeTableRow,
        'group',
        Translator.get('platform:remove_group_confirm'),
        Translator.get('platform:remove_group')
    );
});

//select users groups and put them into an array.
$('body').on('click', '.user-chk', function () {
    var userId = $(this).attr('user-id');
    var checked = $(this).prop('checked');
    var index = typeMap[currentType].indexOf(userId);
    checked && index < 0 ? typeMap['user'].push(userId): typeMap[currentType].splice(index, 1);
});

//select groups and put them into an array.
$('body').on('click', '.group-chk', function () {
    var groupId = $(this).attr('group-id');
    var checked = $(this).prop('checked');
    var index = typeMap[currentType].indexOf(groupId);
    checked && index < 0 ? typeMap['group'].push(groupId): typeMap[currentType].splice(index, 1);
});

$('body').on('click', '.pagination > ul > li > a', function (event) {
    event.preventDefault();
    event.stopPropagation();
    var element = event.currentTarget;
    var url = $(element).attr('href');
    displayPager(url);
});

function displayPager(url) {
    $.ajax({
        url: url,
        success: function (datas) {
            $('.modal-body').empty();
            $('.modal-body').append(datas);
            currentType === 'user' ? displayUsersStatus(): displayGroupsStatus();
        },
        type: 'GET'
    });
}

//checks the checkbox of users if they were checked before
function displayUsersStatus()
{
    $('.user-chk').each(function () {
        var contactId = $(this).attr('user-id');

        if (typeMap[currentType].indexOf(contactId) >= 0) {
            $(this).attr('checked', 'checked');
        }
    });
}

//check the checkbox of groups if the were checked before
function displayGroupsStatus()
{
    $('.group-chk').each(function () {
        var contactId = $(this).attr('group-id');

        if (typeMap[currentType].indexOf(contactId) >= 0) {
            $(this).attr('checked', 'checked');
        }
    });
}

function addGroups()
{
    var queryString = {};
    queryString.groupIds = typeMap['group'];
    var route = Routing.generate('ws_share_groups_add', {'model': modelId})
    route += '?' + $.param(queryString);

    $.ajax({
        url: route,
        success: function(data) {
            for (var i = 0; i < data['groups'].length; i++) {
                $('#table-group-body').append(
                    Twig.render(GroupModelRow, {'model': data['model'], 'group': data['groups'][i]})
                );
            }
        }
    });
}

function addUsers()
{
    var queryString = {};
    queryString.userIds = typeMap['user'];
    var route = Routing.generate('ws_share_users_add', {'model': modelId})
    route += '?' + $.param(queryString);

    $.ajax({
        url: route,
        success: function(data) {
            for (var i = 0; i < data['users'].length; i++) {
                $('#table-user-body').append(
                    Twig.render(UserModelRow, {'model': data['model'], 'user': data['users'][i]})
                );
            }
        }
    });
}

function removeTableRow(event, successParameter, data)
{
    $('#' + successParameter + '-' + data.id).remove();
}

