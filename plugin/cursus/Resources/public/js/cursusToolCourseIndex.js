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
    
    $('#course-create-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_course_create_form'),
            refreshPage,
            function() {}
        );
    });

    $('.edit-course-btn').on('click', function () {
        var courseId = $(this).data('course-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_course_edit_form', {'course': courseId}),
            refreshPage,
            function() {}
        );
    });

    $('.delete-course-btn').on('click', function () {
        var courseId = $(this).data('course-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_cursus_course_delete', {'course': courseId}),
            refreshPage,
            null,
            Translator.trans('delete_course_confirm_message', {}, 'cursus'),
            Translator.trans('course_deletion', {}, 'cursus')
        );
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
                $('#view-course-description-header').html(courseTitle);
                $('#view-course-description-body').html(datas);
                $('#view-course-description-box').modal('show');
            }
        });
    });

    $('#search-course-btn').on('click', function () {
        var search = $('#search-course-input').val();
        var orderedBy = $(this).data('ordered-by');
        var order = $(this).data('order');
        var max = $(this).data('max');
        var route = Routing.generate(
            'claro_cursus_tool_course_index',
            {
                'orderedBy': orderedBy,
                'order': order,
                'max': max,
                'search': search
            }
        );

        window.location.href = route;
    });

    $('#search-course-input').keypress(function(e) {
        if (e.keyCode === 13) {
            var search = $(this).val();
            var orderedBy = $(this).data('ordered-by');
            var order = $(this).data('order');
            var max = $(this).data('max');
            var route = Routing.generate(
                'claro_cursus_tool_course_index',
                {
                    'orderedBy': orderedBy,
                    'order': order,
                    'max': max,
                    'search': search
                }
            );

            window.location.href = route;
        }
    });

    $('#import-courses-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_courses_import_form'),
            refreshPage,
            function() {},
            'courses-import-form'
        );
    });
    
    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    };
})();