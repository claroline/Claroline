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

    var currentElement;
    var currentTagId;
    var currentWorkspaceId;

    // Click on SEARCH button
    $('#search-workspace-button').on('click', function () {
        var searchElement = document.getElementById('search-workspace-txt');
        var search = $(searchElement).val();
        var route;

        if (search !== '') {
            route = Routing.generate(
                'claro_manage_workspace_tag_search',
                {'search': search}
            );
        } else {
            route = Routing.generate('claro_manage_workspace_tag');
        }
        window.location = route;
    });

    // Press ENTER on search input field
    $('#search-workspace-txt').on('keypress', function (e) {

        if (e.keyCode == 13) {
            var search = $(this).val();
            var route;

            if (search !== '') {
                route = Routing.generate(
                    'claro_manage_workspace_tag_search',
                    {'search': search}
                );
            } else {
                route = Routing.generate('claro_manage_workspace_tag');
            }
            window.location = route;
        }
    })

    // Click on a tag delete button ('x')
    $('#workspace-organization-div').on('click', '.remove-tag-button', function () {
        currentElement = $(this).parents('.workspace-tag-element');
        currentWorkspaceId = currentElement.attr('workspace-id');
        currentTagId = currentElement.attr('workspace-tag-id');
        $('#remove-workspace-tag-validation-box').modal('show');
    });

    // Click on OK button of tag remove confirmation modal
    $('#remove-workspace-tag-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_workspace_tag_remove_from_workspace',
                {'workspaceTagId': currentTagId, 'workspaceId': currentWorkspaceId}
            ),
            type: 'DELETE',
            success: function () {
                currentElement.remove();
                $('#remove-workspace-tag-validation-box').modal('hide');
            }
        });
    });

    // Click on Categorize button
    $('#categorize-btn').on('click', function () {
        $('.tag-checked-icon').addClass('hide');
        $('.tag-hierarchy-element').removeClass('claroline-tag-highlight');
        $('.tag-chk').each(function () {
            $(this).attr('checked', false);
        });
        $('#one-tag-alert').addClass('hide');
        $('#tag-hierarchy-modal-box').modal('show');
    });

    // Click on a workspace checkbox
    $('.workspace-chk').on('click', function () {
        if ($('.workspace-chk:checked').length > 0) {
            $('#categorize-btn').attr('disabled', false);
        }
        else {
            $('#categorize-btn').attr('disabled', 'disabled');
        }
    });

    // Click on a tag name on the tag tree in the tag hierarchy modal
    $('.tag-hierarchy-element').on('click', function () {
        var selectedElementId = $(this).attr('tag-id');
        var selectedElementClass = '.tag-hierarchy-element-' + selectedElementId;

        $(selectedElementClass).each(function () {
            var selectedElement = $(this);
            var checkboxElement = selectedElement.children('.tag-chk');
            var checkIcon = selectedElement.children('.tag-checked-icon');

            if (!selectedElement.hasClass('claroline-tag-highlight')) {
                selectedElement.addClass('claroline-tag-highlight');
                checkIcon.removeClass('hide');
                checkboxElement.prop('checked', 'checked');
            } else {
                selectedElement.removeClass('claroline-tag-highlight');
                checkIcon.addClass('hide');
                checkboxElement.prop('checked', false);
            }
        });
    });

    // Click on Validate button in category selection modal
    $('#category-validate-btn').on('click', function () {
        var parameters = {};
        var route = Routing.generate(
            'claro_associate_workspace_tags_to_workspaces'
        );
        var i = 0;
        var j = 0;
        var workspaces = [];
        var tags = [];
        var nbWorkspaces = $('.workspace-chk:checked').length;
        var nbTags = $('.tag-chk:checked').length;

        if (nbWorkspaces > 0 && nbTags > 0) {
            $('.workspace-chk:checked').each(function (index, element) {
                workspaces[i] = element.value;
                i++;
            });
            parameters.workspaceIds = workspaces;
            $('.tag-chk:checked').each(function (index, element) {

                if (tags.indexOf(element.value) === -1) {
                    tags[j] = element.value;
                    j++;
                }
            });
            parameters.tagIds = tags;
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                type: 'POST',
                success: function () {
                    $('.workspace-chk').removeAttr('checked');
                    window.location.reload();
                }
            });
            $('#tag-hierarchy-modal-box').modal('hide');
        } else {
            $('#one-tag-alert').removeClass('hide');
        }
    });
})();
