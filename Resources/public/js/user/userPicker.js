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
    
    var currentSearch = $('#user-picker-datas-box').data('search');
//    var currentPage = $('#user-picker-datas-box').data('page');
    var currentMax = $('#user-picker-datas-box').data('max');
    var currentOrderedBy = $('#user-picker-datas-box').data('ordered-by');
    var currentOrder = $('#user-picker-datas-box').data('order');
    
    $('#user-picker-modal').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var url = $(element).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-body').html(datas);
            }
        });
    });

    $('#user-picker-modal').on('click', '#search-user-btn', function () {
        currentSearch = $('#search-user-input').val();
        var route = Routing.generate(
            'claro_users_list_for_user_picker',
            {
                'search': currentSearch,
                'max': currentMax,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-body').html(datas);
            }
        });
    });

    $('#user-picker-modal').on('keypress', '#search-user-input', function(e) {
        
        if (e.keyCode === 13) {
            event.preventDefault();
            currentSearch = $(this).val();
            var route = Routing.generate(
                'claro_users_list_for_user_picker',
                {
                    'search': currentSearch,
                    'max': currentMax,
                    'orderedBy': currentOrderedBy,
                    'order': currentOrder
                }
            );

            $.ajax({
                url: route,
                type: 'GET',
                success: function (datas) {
                    $('#user-picker-body').html(datas);
                }
            });
        }
    });
    
    $('#user-picker-modal').on('change', '#max-select', function () {
        currentMax = $(this).val();
        var route = Routing.generate(
            'claro_users_list_for_user_picker',
            {
                'search': currentSearch,
                'max': currentMax,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-body').html(datas);
            }
        });
    });
})();