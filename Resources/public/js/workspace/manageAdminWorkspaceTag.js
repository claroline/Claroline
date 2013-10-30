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
                'claro_manage_admin_workspace_tag_search',
                {'search': search}
            );
        } else {
            route = Routing.generate('claro_manage_admin_workspace_tag');
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
                    'claro_manage_admin_workspace_tag_search',
                    {'search': search}
                );
            } else {
                route = Routing.generate('claro_manage_admin_workspace_tag');
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
                'claro_admin_workspace_tag_remove_from_workspace',
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
        $('#tag-hierarchy-modal-box').modal('show');
    });

    // Click on Validate button in category selection modal
    $('#category-validate-btn').on('click', function () {
        $('#tag-hierarchy-modal-box').modal('hide');
    });
})();