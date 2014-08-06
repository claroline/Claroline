/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


$('#add-user-btn').on('click', function () {
    window.Claroline.Modal.simpleContainer(Translator.get('platform:add_user'), '')
    displayPager(
        'ws_share_user_list',
        'ws_share_user_list_search',
        function() {}
    );
});

$('#add-group-btn').on('click', function () {
    window.Claroline.Modal.simpleContainer(Translator.get('platform:add_group'), '')
    displayPager(
        'ws_share_group_list',
        'ws_share_group_list_search',
        function() {}
    );
});

$('body').on('click', '.pagination > ul > li > a', function (event) {
    event.preventDefault();
    event.stopPropagation();
    var element = event.currentTarget;
    var url = $(element).attr('href');

    $.ajax({
        url: url,
        success: function (datas) {
            $('.modal-body').empty();
            $('.modal-body').append(datas);
        },
        type: 'GET'
    });
});

function displayPager(normalRoute, searchRoute, callback) {
    var route;
    route = Routing.generate(normalRoute);

    $.ajax({
        url: route,
        type: 'GET',
        success: function (datas) {
            $('.modal-body').empty();
            $('.modal-body').append(datas);

            if (callback) {
                callback();
            }
        }
    });
}

