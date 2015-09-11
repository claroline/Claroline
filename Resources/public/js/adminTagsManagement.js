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
    
    $('#search-tag-input').keyup(function(e) {
        var search = $(this).val();
        
        var route = Routing.generate(
            'claro_tag_admin_tags_display',
            {'search': search}
        );
        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#tags-display-box').html(datas);
            }
        });
    });
    
    $('#tags-display-box').on('click', '.delete-tag-btn', function () {
        var tagId = $(this).data('tag-id');
        var tagName = $(this).data('tag-name');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_tag_admin_tag_delete',
                {'tag': tagId}
            ),
            removeTagRow,
            tagId,
            Translator.trans('tag_deletion_message', {}, 'tag'),
            Translator.trans('tag_deletion', {}, 'tag') + ' [' + tagName + ']'
        );
    });
    
    $('#tags-display-box').on('click', '.remove-tag-from-object-btn', function () {
        var taggedObjectId = $(this).data('tagged-object-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_tag_admin_tagged_object_delete',
                {'taggedObject': taggedObjectId}
            ),
            removeTaggedObjectRow,
            taggedObjectId,
            Translator.trans('remove_tag_from_object_message', {}, 'tag'),
            Translator.trans('remove_tag', {}, 'tag')
        );
    });
    
    var removeTagRow = function (event, tagId) {
        $('#tag-row-' + tagId).remove();
    };
    
    var removeTaggedObjectRow = function (event, taggedObjectId) {
        $('#tagged-object-row-' + taggedObjectId).remove();
    };
})();