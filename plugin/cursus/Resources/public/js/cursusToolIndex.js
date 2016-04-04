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
    
    $('#cursus-create-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_create_form'),
            refreshPage,
            function() {}
        );
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

    $('.view-cursus-hierarchy-btn').on('click', function () {
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

    $('.edit-cursus-btn').on('click', function () {
        var cursusId = $(this).data('cursus-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_edit_form', {'cursus': cursusId}),
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

    $('#import-cursus-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_cursus_import_form'),
            refreshPage,
            function() {},
            'cursus-import-form'
        );
    });

    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    };
})();