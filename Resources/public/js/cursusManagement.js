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

    $('#cursus-management-body').on('click', '.edit-cursus-btn', function () {
        var cursusId = $(this).data('cursus-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_edit_form', {'cursus': cursusId}),
            refreshPage,
            function() {}
        );
    });
    
    $('#cursus-management-body').on('click', '.create-cursus-child-btn', function () {
        var parentId = $(this).data('cursus-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_child_create_form', {'parent': parentId}),
            refreshPage,
            function() {}
        );
    });

    $('#cursus-management-body').on('click', '.delete-cursus-btn', function () {
        var cursusId = $(this).data('cursus-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_cursus_delete', {'cursus': cursusId}),
            removeCursusRow,
            cursusId,
            Translator.trans('delete_cursus_confirm_message', {}, 'cursus'),
            Translator.trans('delete_cursus', {}, 'cursus')
        );
    });

    $('#cursus-management-body').on('click', '.remove-course-btn', function () {
        var cursusId = $(this).data('cursus-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_cursus_delete', {'cursus': cursusId}),
            removeCursusRow,
            cursusId,
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
    
    $('#cursus-management-body').on('click', '.add-course-to-cursus-btn', function () {
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
    
    $('#cursus-management-body').on('click', '.create-course-to-cursus-btn', function () {
        var cursusId = $(this).data('cursus-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_course_into_cursus_create_form', {'cursus': cursusId}),
            function(datas) {
                
                for (var i = 0; i < datas.length; i++) {
                    var courseRow =
                        '<li id="cursus-row-' +
                        datas[i]['id'] +
                        '" data-cursus-id="' +
                        datas[i]['id'] +
                        '">' +      
                            '<span>' +
                                '<span class="label label-primary">' +
                                    datas[i]['title'] +
                                '</span>' +
                                '<span class="label label-danger pointer-hand remove-course-btn" data-cursus-id="' +
                                datas[i]['id'] +
                                '">' +
                                    '<i class="fa fa-trash"></i>' +
                                '</span>' +
                            '</span>' +
                        '</li>';
                    $('#collapse-' + cursusId).append(courseRow);
                    $('#collapse-' + cursusId).removeClass('hidden');
                }
            },
            function() {}
        );
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
            success: function (datas) {
                $('#row-course-' + courseId).remove();
                
                for (var i = 0; i < datas.length; i++) {
                    var courseRow =
                        '<li id="cursus-row-' +
                        datas[i]['id'] +
                        '" data-cursus-id="' +
                        datas[i]['id'] +
                        '">' +      
                            '<span>' +
                                '<span class="label label-primary">' +
                                    datas[i]['title'] +
                                '</span>' +
                                '<span class="label label-danger pointer-hand remove-course-btn" data-cursus-id="' +
                                datas[i]['id'] +
                                '">' +
                                    '<i class="fa fa-trash"></i>' +
                                '</span>' +
                            '</span>' +
                        '</li>';
                    $('#collapse-' + cursusId).append(courseRow);
                    $('#collapse-' + cursusId).removeClass('hidden');
                }
            }
        });
    });
    
//    $('.cursus-element').hover(
//        function () {
//            var cursusId = $(this).data('cursus-id');
//            $('#option-btn-' + cursusId).removeClass('hidden');
//            $(this).addClass('claroline-tag-highlight');
//        },
//        function () {
//            var cursusId = $(this).data('cursus-id');
//            $('#option-btn-' + cursusId).addClass('hidden');
//            $(this).removeClass('claroline-tag-highlight');
//            $('body').trigger('click');
//        }
//    );

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
    
    var addCursusRow = function (data) {
        console.log(data);
        var cursusRow =
            '<li id="cursus-row-' + data['id'] + '"' +
                ' data-cursus-id="' + data['id'] + '">' +
                '<div class="cursus-element" data-cursus-id="' + data['id'] + '">' +
                    '<span class="pointer-hand view-cursus-btn"' +
                          ' data-cursus-id="' + data['id'] + '"' +
                          ' data-cursus-title="' + data['title'] + '"' +
                          ' data-toggle="collapse"' +
                          ' href="#collapse-' + data['id'] + '"' +
                    '>' +
                        data['title'] +
                    '</span>' +
                    '&nbsp;' +
                    '<span class="dropdown">' +
                        '<i class="cursus-option-btn fa fa-cog pointer-hand hidden"' +
                           'id="option-btn-' + data['id'] + '"' +
                           ' data-cursus-id="' + data['id'] + '"' +
                           ' data-toggle="dropdown"' +
                        '></i>' +
                        '<ul class="dropdown-menu"' +
                            ' role="menu"' +
                            ' aria-labelledby="option-btn-' + data['id'] + '"' +
                            ' style="white-space: nowrap"' +
                        '>' +
                            '<li role="presentation">' +
                                '<a role="menuitem"' +
                                   ' tabindex="-1"' +
                                   ' class="pointer-hand edit-cursus-btn"' +
                                   ' data-cursus-id="' + data['id'] + '"' +
                                '>' +
                                    '<i class="fa fa-edit"></i>' +
                                    Translator.trans('edit', {}, 'platform') +
                                '</a>' +
                            '</li>' +
                            '<li role="presentation" class="divider"></li>' +
                            '<li role="presentation">' +
                                '<a role="menuitem"' +
                                   ' tabindex="-1"' +
                                   ' class="pointer-hand create-cursus-child-btn"' +
                                   ' data-cursus-id="' + data['id'] + '"' +
                                '>' +
                                    '<i class="fa fa-sitemap"></i>' +
                                    Translator.trans('create_cursus_child', {}, 'cursus') +
                                '</a>' +
                            '</li>' +
                            '<li role="presentation" class="divider"></li>' +
                            '<li role="presentation">' +
                                '<a role="menuitem"' +
                                   ' tabindex="-1"' +
                                   ' class="pointer-hand create-course-to-cursus-btn"' +
                                   ' data-cursus-id="' + data['id'] + '"' +
                                   ' data-cursus-title="' + data['title'] +'"' +
                                '>' +
                                    '<i class="fa fa-plus-circle"></i>' +
                                    Translator.trans('create_course', {}, 'cursus') +
                                '</a>' +
                            '</li>' +
                            '<li role="presentation">' +
                                '<a role="menuitem"' +
                                   ' tabindex="-1"' +
                                   ' class="pointer-hand add-course-to-cursus-btn"' +
                                   ' data-cursus-id="' + data['id'] + '"' +
                                   ' data-cursus-title="' + data['title'] +'"' +
                                '>' +
                                    '<i class="fa fa-plus-square"></i>' +
                                    Translator.trans('add_course_to_cursus', {}, 'cursus') +
                                '</a>' +
                            '</li>' +
                            '<li role="presentation" class="divider"></li>' +
                            '<li role="presentation">' +
                                '<a role="menuitem"' +
                                   ' tabindex="-1"' +
                                   ' class="pointer-hand delete-cursus-btn"' +
                                   ' data-cursus-id="' + data['id'] + '"' +
                                '>' +
                                    '<i class="fa fa-trash"></i>' +
                                    Translator.trans('delete', {}, 'platform') +
                                '</a>' +
                            '</li>' +
                        '</ul>' +
                    '</span>' +
                '</div>' +
                '<ul id="collapse-' + data['id'] + '"' +
                    ' class="collapse in cursus-children hidden"' +
                '>' +
                '</ul>'
            '</li>';
    
            $('#collapse-' + data['parent_id']).append(cursusRow);
            $('#collapse-' + data['parent_id']).removeClass('hidden');
    }
    
    var removeCursusRow = function (event, cursusId) {
        $('#cursus-row-' + cursusId).remove();
    };
    
    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    };
})();