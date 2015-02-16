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
    
    $('#search-cursus-btn').on('click', function () {
        var search = $('#search-cursus-input').val();
        var route = Routing.generate(
            'claro_cursus_tool_registration_index_with_search',
            {
                'search': search
            }
        );

        window.location.href = route;
    });

    $('#search-cursus-input').keypress(function(e) {
        if (e.keyCode === 13) {
            var search = $(this).val();
            var route = Routing.generate(
                'claro_cursus_tool_registration_index_with_search',
                {
                    'search': search
                }
            );

            window.location.href = route;
        }
    });
    
    $('.view-cursus-hierarchy').on('click', function () {
        var cursusId = $(this).data('cursus-id');
        var cursusTitle = $(this).data('cursus-title');
        
        $.ajax({
            url: Routing.generate('claro_cursus_view_hierarchy', {'cursus': cursusId}),
            type: 'GET',
            success: function (datas) {
                $('#view-cursus-hierarchy-header').html(cursusTitle);
                $('#view-cursus-hierarchy-body').html(datas);
                $('#view-cursus-hierarchy-box').modal('show');
            }
        });
    });
    
    $('.searched-cursus-title-btn').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var cursusId = $(this).data('cursus-id');
        $('.searched-cursus-title-btn').removeClass('claroline-tag-highlight');
        $(this).addClass('claroline-tag-highlight');
        
        $.ajax({
            url: Routing.generate('claro_cursus_view_related_hierarchy', {'cursus': cursusId}),
            type: 'GET',
            success: function (datas) {
                $('#selected-cursus-display-box').html(datas);
                $('#selected-cursus-display-box').removeClass('hidden');
            }
        });
    });
    
    $('#close-search-alert-btn').on('click', function () {
        var route = Routing.generate('claro_cursus_tool_registration_index', {});

        window.location.href = route;
    });
    
    $('.view-cursus-description-btn').on('click', function () {
        var cursusId = $(this).data('cursus-id');
        var cursusTitle = $(this).data('cursus-title');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_display_description',
                {'cursus': cursusId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-description-header').html(cursusTitle);
                $('#view-description-body').html(datas);
                $('#view-description-box').modal('show');
            }
        });
    });
    
    $('.view-course-description-btn').on('click', function () {
        var courseId = $(this).data('course-id');
        var courseTitle = $(this).data('course-title');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_display_description',
                {'course': courseId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-description-header').html(courseTitle);
                $('#view-description-body').html(datas);
                $('#view-description-box').modal('show');
            }
        });
    });
    
    $('.cursus-element').hover(
        function () {
            $(this).children('.registration-btn').removeClass('hidden');
        },
        function () {
            $(this).children('.registration-btn').addClass('hidden');
        }
    );
    
    $('#selected-cursus-display-box').on('mouseenter', '.cursus-element', function () {
        $(this).children('.registration-btn').removeClass('hidden');
    });
    
    $('#selected-cursus-display-box').on('mouseleave', '.cursus-element', function () {
        $(this).children('.registration-btn').addClass('hidden');
    });
})();