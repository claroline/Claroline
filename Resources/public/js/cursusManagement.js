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

    $('.view-cursus-btn').on('click', function () {
        var cursusId = $(this).data('cursus-id');
        var cursusTitle = $(this).data('cursus-title');
        
        $.ajax({
            url: Routing.generate('claro_cursus_view', {'cursus': cursusId}),
            type: 'GET',
            success: function (datas) {
                $('#view-cursus-header').html(cursusTitle);
                $('#view-cursus-body').html(datas);
                $('#view-cursus-box').modal('show');
            }
        });
    });

    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    }
})();