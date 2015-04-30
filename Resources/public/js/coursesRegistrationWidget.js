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
    
    var currentSearch = $('#courses-registration-widget-datas-box').data('search');
    var currentMax = $('#courses-registration-widget-datas-box').data('max');
    var currentOrderedBy = $('#courses-registration-widget-datas-box').data('ordered-by');
    var currentOrder = $('#courses-registration-widget-datas-box').data('order');
    
    function refreshCoursesList()
    {
        var route = Routing.generate(
            'claro_cursus_courses_list_for_registration_widget',
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
                $('#courses-list').html(datas);
            }
        });
    }
    
    $('#courses-registration-widget').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#courses-list').html(datas);
            }
        });
    });

    $('#courses-registration-widget').on('click', '#search-course-btn', function () {
        currentSearch = $('#search-course-input').val();
        refreshCoursesList();
    });

    $('#courses-registration-widget').on('keypress', '#search-course-input', function(e) {
        
        if (e.keyCode === 13) {
            event.preventDefault();
            currentSearch = $(this).val();
            refreshCoursesList();
        }
    });
})();