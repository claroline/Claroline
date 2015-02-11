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
    
    var hasBeenMofified = false;

    $('.edit-cursus-btn').on('click', function () {
        var cursusId = $(this).data('cursus-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_edit_form', {'cursus': cursusId}),
            refreshPage,
            function() {}
        );
    });
    
    $('.create-cursus-child-btn').on('click', function () {
        var parentId = $(this).data('cursus-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_child_create_form', {'parent': parentId}),
            refreshPage,
            function() {}
        );
    });

    $('.delete-cursus-btn').on('click', function () {
        var cursusId = $(this).data('cursus-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_cursus_delete', {'cursus': cursusId}),
            refreshPage,
            null,
            Translator.trans('delete_cursus_confirm_message', {}, 'cursus'),
            Translator.trans('delete_cursus', {}, 'cursus')
        );
    });

    $('.remove-course-btn').on('click', function () {
        var cursusId = $(this).data('cursus-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_cursus_delete', {'cursus': cursusId}),
            refreshPage,
            null,
            Translator.trans('remove_course_confirm_message', {}, 'cursus'),
            Translator.trans('remove_course', {}, 'cursus')
        );
    });

//    $('.view-cursus-btn').on('click', function () {
//        var cursusId = $(this).data('cursus-id');
//        var cursusTitle = $(this).data('cursus-title');
//        
//        $.ajax({
//            url: Routing.generate('claro_cursus_view', {'cursus': cursusId}),
//            type: 'GET',
//            success: function (datas) {
//                $('#view-cursus-header').html(cursusTitle);
//                $('#view-cursus-body').html(datas);
//                $('#view-cursus-box').modal('show');
//            }
//        });
//    });
    
    $('.add-course-to-cursus-btn').on('click', function () {
        var cursusId = $(this).data('cursus-id');
        var cursusTitle = $(this).data('cursus-title');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_add_courses_users_list',
                {'cursus': cursusId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-courses-header').html(cursusTitle);
                $('#view-courses-body').html(datas);
                $('#view-courses-box').modal('show');
            }
        });
    });

    $('#view-courses-body').on('click', 'a', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function (result) {
                $('#view-courses-body').html(result);
            }
        });
    });

    $('#view-courses-body').on('click', '#search-course-btn', function () {
        var cursusId = $(this).data('cursus-id');
        var orderedBy = $(this).data('ordered-by');
        var order = $(this).data('order');
        var max = $(this).data('max');
        var search = $('#search-course-input').val();

        $.ajax({
            url: Routing.generate(
                'claro_cursus_add_courses_users_list',
                {
                    'cursus': cursusId,
                    'search': search,
                    'orderedBy': orderedBy,
                    'order': order,
                    'max': max
                }
            ),
            type: 'GET',
            success: function (result) {
                $('#view-courses-body').html(result);
            }
        });
    });

    $('#view-courses-body').on('keypress', '#search-course-input', function (e) {
        if (e.keyCode === 13) {
            var cursusId = $(this).data('cursus-id');
            var orderedBy = $(this).data('ordered-by');
            var order = $(this).data('order');
            var max = $(this).data('max');
            var search = $(this).val();

            $.ajax({
                url: Routing.generate(
                    'claro_cursus_add_courses_users_list',
                    {
                        'cursus': cursusId,
                        'search': search,
                        'orderedBy': orderedBy,
                        'order': order,
                        'max': max
                    }
                ),
                type: 'GET',
                success: function (result) {
                    $('#view-courses-body').html(result);
                }
            });
        }
    });
    
    $('#view-courses-body').on('click', '.add-course-btn', function () {
        var cursusId = $(this).data('cursus-id');
        var courseId = $(this).data('course-id');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_add_course',
                {
                    'cursus': cursusId,
                    'course': courseId
                }
            ),
            type: 'POST',
            success: function () {
                hasBeenMofified = true;
            }
        });
    });
    
    $('#view-courses-modal-close-btn').on('click', function () {
        
        if (hasBeenMofified) {
            window.location.reload();
        }
    });
    
    $('.cursus-element').hover(
        function () {
            var cursusId = $(this).data('cursus-id');
            $('#option-btn-' + cursusId).removeClass('hidden');
            $(this).addClass('claroline-tag-highlight');
        },
        function () {
            var cursusId = $(this).data('cursus-id');
            $('#option-btn-' + cursusId).addClass('hidden');
            $(this).removeClass('claroline-tag-highlight');
            $('body').trigger('click');
        }
    );

    $('.cursus-children').sortable({
        items: '> li',
        cursor: 'move'
    });

    $('.cursus-children').on('sortupdate', function (event, ui) {

        if (this === ui.item.parents('.cursus-children')[0]) {
            var cursusId = $(ui.item).data('cursus-id');
            var otherCursusId = $(ui.item).next().data('cursus-id');
            var mode = 'previous';
            var execute = false;

            if (otherCursusId !== undefined) {
                mode = 'next';
                execute = true;
            } else {
                otherCursusId = $(ui.item).prev().data('cursus-id');

                if (otherCursusId !== undefined) {
                    execute = true;
                }
            }

            if (execute) {
                $.ajax({
                    url: Routing.generate(
                        'claro_cursus_update_order',
                        {
                            'cursus': cursusId,
                            'otherCursus': otherCursusId,
                            'mode': mode
                        }
                    ),
                    type: 'POST'
                });
            }
        }
    });
    
    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    };
})();