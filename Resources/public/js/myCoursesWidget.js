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
    
    var currentSearch = $('#my-courses-widget-datas-box').data('search');
    var currentMax = $('#my-courses-widget-datas-box').data('max');
    var currentOrderedBy = $('#my-courses-widget-datas-box').data('ordered-by');
    var currentOrder = $('#my-courses-widget-datas-box').data('order');
    var widgetInstanceId = $('#my-courses-widget-datas-box').data('widget-instance-id');
    
    function refreshCoursesList()
    {
        var route = Routing.generate(
            'claro_cursus_my_courses_list_for_widget',
            {
                'widgetInstance': widgetInstanceId,
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
    
    $('#my-courses-widget').on('click', 'a', function (event) {
        
        if (!$(this).hasClass('standard-link')) {
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
        }
    });

    $('#my-courses-widget').on('click', '#search-course-btn', function () {
        currentSearch = $('#search-course-input').val();
        refreshCoursesList();
    });

    $('#my-courses-widget').on('keypress', '#search-course-input', function(e) {
        
        if (e.keyCode === 13) {
            e.preventDefault();
            currentSearch = $(this).val();
            refreshCoursesList();
        }
    });
})();