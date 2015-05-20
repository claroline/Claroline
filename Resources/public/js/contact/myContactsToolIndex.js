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

    $('#my-contacts-tool').on('click', '#contacts-configure-btn', function () {
        console.log('configure');
    });

    $('#my-contacts-tool').on('click', '#category-create-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_category_create_form'),
            refreshPage,
            function() {}
        );
    });

    $('#my-contacts-tool').on('click', '.category-edit-btn', function () {
        var categoryId = $(this).data('category-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_category_edit_form', {'category': categoryId}),
            renameCategory,
            function() {}
        );
    });

    $('#my-contacts-tool').on('click', '.category-delete-btn', function () {
        var categoryId = $(this).data('category-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_contact_category_delete', {'category': categoryId}),
            removeCategory,
            categoryId,
            Translator.trans('delete_category_confirm_message', {}, 'platform'),
            Translator.trans('delete_category_confirm_title', {}, 'platform')
        );
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
    
    var renameCategory = function (datas) {
        var id = datas['id'];
        var name = datas['name'];
        $('#category-title-' + id).html(name);
    };
    
    var removeCategory = function (event, categoryId) {
        $('#category-box-' + categoryId).remove();
    };
})();