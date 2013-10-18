(function () {
    'use strict';

    var currentParentTagId = 0;
    var currentElement;
    var currentTagId;
    var currentWorkspaceId;

    function openFormModal(title, content)
    {
        $('#form-modal-title').html(title);
        $('#form-modal-body').html(content);
        $('#form-modal-box').modal('show');
    }

    function closeFormModal()
    {
        $('#form-modal-box').modal('hide');
        $('#form-modal-title').empty();
        $('#form-modal-body').empty();
    }

    function openTagModal(title, content)
    {
        $('#tag-modal-title').html(title);
        $('#tag-modal-body').html(content);
        $('#tag-modal-box').modal('show');
    }

    function closeTagModal()
    {
        $('#tag-modal-box').modal('hide');
        $('#tag-modal-title').empty();
        $('#tag-modal-body').empty();
    }

    function openWorkspaceModal(title, content)
    {
        $('#workspace-modal-title').html(title);
        $('#workspace-modal-body').html(content);
        $('#workspace-modal-box').modal('show');
    }

    function closeWorkspaceModal()
    {
        $('#workspace-modal-box').modal('hide');
        $('#workspace-modal-title').empty();
        $('#workspace-modal-body').empty();
    }

    function openTagHierarchyModal(title, content)
    {
        $('#tag-hierarchy-modal-title').html(title);
        $('#tag-hierarchy-modal-body').html(content);
        $('#tag-hierarchy-modal-box').modal('show');
    }

    function closeTagHierarchyModal()
    {
        $('#tag-hierarchy-modal-box').modal('hide');
        $('#tag-hierarchy-modal-title').empty();
        $('#tag-hierarchy-modal-body').empty();
    }

    function generateTagElement(tagId, tagName, hasRemoveBtn)
    {
        var tagElement =
            '<span class="tag-whole-element">\n'
            + '<span class="label label-danger pointer-hand tag-element" workspace-tag-id="'
            + tagId + '">'
            + '<span class="open-tag-btn">\n' + tagName + '\n</span>\n&nbsp;\n'
            + '<i class="icon-pencil pointer-hand edit-tag-btn"></i>\n';

        if (hasRemoveBtn) {
            tagElement += '&nbsp;\n<i class="icon-remove-sign pointer-hand remove-tag-btn"></i>\n';
        }
        tagElement += '</span>\n&nbsp;\n</span>';

        return tagElement;
    }

    function generateWorkspaceElement(workspaceId, workspaceName, workspaceCode)
    {
        var workspaceElement =
            '<span class="workspace-whole-element">\n'
            + '<span class="label label-success workspace-element" workspace-id="'
            + workspaceId + '">\n'
            + '<i class="icon-book"></i>\n'
            + workspaceName
            + '\n<small>(' + workspaceCode + ')</small>\n'
            + '&nbsp;\n'
            + '<i class="icon-remove-sign pointer-hand remove-workspace-btn"></i>\n'
            + '</span>\n'
            + '&nbsp;\n'
            + '</span>';

        return workspaceElement;
    }

    function getPage(tab)
    {
        var page = 1;

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'page') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    page = tab[i + 1];
                }
                break;
            }
        }

        return page;
    }

    function getSearch(tab)
    {
        var search = '';

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'search') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    search = tab[i + 1];
                }
                break;
            }
        }

        return search;
    }

    function loadTagData(tagId, tagName)
    {
        $.ajax({
            url: Routing.generate(
                'claro_children_tag_list_by_admin_tag',
                {'workspaceTagId': tagId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#category-title').html(Translator.get('platform' + ':' + 'subcategories'));
                $('#current-tag-name-label').removeClass('hide');
                $('#current-tag-name').html(tagName);
                $('#workspace-tag-list').html(datas);
            }
        });

        $.ajax({
            url: Routing.generate(
                'claro_workspace_list_by_admin_tag',
                {'workspaceTagId': tagId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#workspace-list').html(datas);
            }
        });
    }

    // Click on a category name
    $('#workspace-tag-list').on('click', '.open-tag-btn', function () {
        var tagName = $(this).html();
        var tagElement = $(this).parents('.tag-element');
        currentParentTagId = parseInt(tagElement.attr('workspace-tag-id'));

        loadTagData(currentParentTagId, tagName);

        var trackElement = '<span class="tag-track" tag-id="'
            + currentParentTagId + '" tag-name="' + tagName + '">\n'
            + '&nbsp;\n'
            + '>\n'
            + '&nbsp;\n'
            + '<span class="tag-track-name pointer-hand">'
            + tagName
            + '</span>\n'
            + '</span>';
        $('#tag-tracks').append(trackElement);
    });

    // Click on a tag track name
    $('#tag-tracks').on('click', '.tag-track-name', function () {
        var tagTrackElement = $(this).parents('.tag-track');
        currentParentTagId = parseInt(tagTrackElement.attr('tag-id'));
        var tagName = tagTrackElement.attr('tag-name');

        loadTagData(currentParentTagId, tagName);
        tagTrackElement.nextAll().each(function () {
            $(this).remove();
        });
    });

    // Click on the category create button
    $('#workspace-tag-list').on('click', '.create-tag-btn', function () {
        $.ajax({
            url: Routing.generate('claro_admin_workspace_tag_create_form'),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.get('platform' + ':' + 'create_category'),
                    datas
                );
            }
        });
    });

    // Click on OK button of the tag Create/Rename form modal
    $('body').on('click', '#form-workspace-tag-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('workspace_tag_form');
        var tagNameInput = document.getElementById('admin_workspace_tag_form_name');
        var tagName = $(tagNameInput).val();
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
                switch (jqXHR.status) {
                    case 201:
                        var tagsGroupElement = $('#workspace-tag-list').children('.workspace-tag-group');
                        var newTag;

                        if (currentParentTagId === 0) {
                            newTag = generateTagElement(data, tagName, false);
                            tagsGroupElement.append(newTag);
                        } else {
                            $.ajax({
                                url: Routing.generate(
                                    'claro_workspace_admin_tag_add_children',
                                    {'tagId': currentParentTagId, 'childrenString': data}
                                ),
                                type: 'GET',
                                success: function () {
                                    newTag = generateTagElement(data, tagName, true);
                                    tagsGroupElement.append(newTag);
                                }
                            });
                        }
                        closeFormModal();
                        break;
                    case 204:
                        currentElement.children('.open-tag-btn').html(tagName);
                        closeFormModal();
                        break;
                    default:
                        $('#form-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });

    // Click on CANCEL button of the tag Create/Rename form modal
    $('#form-modal-box').on('click', '#form-cancel-btn', function () {
        closeFormModal();
    });

    // Click on the category edit button
    $('#workspace-tag-list').on('click', '.edit-tag-btn', function () {
        currentElement = $(this).parents('.tag-element');
        currentTagId = currentElement.attr('workspace-tag-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_workspace_tag_edit_form',
                {'workspaceTagId': currentTagId}
            ),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.get('platform' + ':' + 'rename_category'),
                    datas
                );
            }
        });
    });

    // Click on the category remove button
    $('#workspace-tag-list').on('click', '.remove-tag-btn', function () {
        currentElement = $(this).parents('.tag-whole-element');
        currentTagId = $(this).parents('.tag-element').attr('workspace-tag-id');
        $('#remove-workspace-tag-validation-box').modal('show');
    });

    // Click on OK button of workspace tag remove confirmation modal
    $('#remove-workspace-tag-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_admin_workspace_tag_remove_child',
                {'parentTagId': currentParentTagId, 'childTagId': currentTagId}
            ),
            type: 'DELETE',
            success: function () {
                currentElement.remove();
                $('#remove-workspace-tag-validation-box').modal('hide');
            }
        });
    });

    // Click on the category add button
    $('#workspace-tag-list').on('click', '.add-tag-btn', function () {
        $.ajax({
            url: Routing.generate(
                'claro_admin_workspace_tag_check_children_pager',
                {'workspaceTagId': currentParentTagId}
            ),
            type: 'GET',
            success: function (datas) {
                openTagModal(
                    Translator.get('platform' + ':' + 'add_category'),
                    datas
                );
            }
        });
    });

    // Click on CLOSE button of the tag Add form modal
    $('#tag-modal-box').on('click', '.close-current-page-btn', function () {
        closeTagModal();
    });

    // Click on ADD button of the tag Add form modal
    $('#tag-modal-box').on('click', '.add-selected-tag-btn', function () {
        var possibleSelected = [];
        $('input:checkbox[name=tag-possible-child]:checked').each(function () {
            possibleSelected.push($(this).val());
        });
        var possibleSelectedString = possibleSelected.join();

        if (possibleSelectedString !== '') {
            $.ajax({
                url: Routing.generate(
                    'claro_workspace_admin_tag_add_children',
                    {'tagId': currentParentTagId, 'childrenString': possibleSelectedString}
                ),
                type: 'GET',
                success: function () {
                    $('input:checkbox[name=tag-possible-child]:checked').each(function () {
                        var tagId = parseInt($(this).attr('value'));
                        var possibleChildElement = $(this).parents('.possible-child-element');
                        var tagName = possibleChildElement.children('.possible-child-tag-name').html();
                        var newTagElement = generateTagElement(tagId, tagName, true);
                        var tagsGroupElement = $('#workspace-tag-list').children('.workspace-tag-group');
                        tagsGroupElement.append(newTagElement);
                        possibleChildElement.remove();
                    });
                }
            });
        }
    });

    // Click on pager buttons on tag modal
    $('#tag-modal-box').on('click', '.pagination > ul > li > a', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var element = event.currentTarget;
        var url = $(element).attr('href');
        var route;

        if (url !== '#') {
            var urlTab = url.split('/');
            var page = getPage(urlTab);
            var search = getSearch(urlTab);

            if (search !== '') {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_children_pager_search',
                    {
                        'workspaceTagId': currentParentTagId,
                        'page': page,
                        'search': search
                    }
                );
            } else {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_children_pager',
                    {'workspaceTagId': currentParentTagId, 'page': page}
                );
            }

            $.ajax({
                url: route,
                type: 'GET',
                success: function (data) {
                    $('#tag-modal-body').html(data);
                }
            });
        }
    });

    // Click on SEARCH button of category list modal
    $('#tag-modal-box').on('click', '#search-tag-button', function () {
        var searchElement = document.getElementById('search-tag-txt');
        var search = $(searchElement).val();
        var route;

        if (search !== '') {
            route = Routing.generate(
                'claro_admin_workspace_tag_check_children_pager_search',
                {
                    'workspaceTagId': currentParentTagId,
                    'search': search
                }
            );
        } else {
            route = Routing.generate(
                'claro_admin_workspace_tag_check_children_pager',
                {'workspaceTagId': currentParentTagId}
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (data) {
                $('#tag-modal-body').html(data);
            }
        });
    });

    // Press ENTER on category list modal
    $('#tag-modal-box').on('keypress', '#search-tag-txt', function (e) {

        if (e.keyCode == 13) {
            var searchElement = document.getElementById('search-tag-txt');
            var search = $(searchElement).val();
            var route;

            if (search !== '') {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_children_pager_search',
                    {
                        'workspaceTagId': currentParentTagId,
                        'search': search
                    }
                );
            } else {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_children_pager',
                    {'workspaceTagId': currentParentTagId}
                );
            }

            $.ajax({
                url: route,
                type: 'GET',
                success: function (data) {
                    $('#tag-modal-body').html(data);
                }
            });
        }
    })

    // Click on the workspace add button
    $('#workspace-list').on('click', '.add-workspace-btn', function () {
        $.ajax({
            url: Routing.generate(
                'claro_admin_workspace_tag_check_workspace_pager',
                {'workspaceTagId': currentParentTagId}
            ),
            type: 'GET',
            success: function (datas) {
                openWorkspaceModal(
                    Translator.get('platform' + ':' + 'add_workspace'),
                    datas
                );
            }
        });
    });

    // Click on CLOSE button of the workspace Add form modal
    $('#workspace-modal-box').on('click', '.close-current-page-btn', function () {
        closeWorkspaceModal();
    });

    // Click on ADD button of the workspace Add form modal
    $('#workspace-modal-box').on('click', '.add-selected-workspace-btn', function () {
        $('input:checkbox[name=workspace]:checked').each(function () {
            var workspaceId = parseInt($(this).val());
            var workspaceElement = $(this).parents('.workspace-element');
            var workspaceDatasElement = workspaceElement.children('.workspace-datas');
            var workspaceCode = workspaceDatasElement.attr('workspace-code');
            var workspaceName = workspaceDatasElement.attr('workspace-name');

            $.ajax({
                url: Routing.generate(
                    'claro_admin_workspace_tag_add_to_workspace',
                    {
                        'workspaceId': workspaceId,
                        'workspaceTagId': currentParentTagId
                    }
                ),
                type: 'POST',
                success: function () {
                    var newWorkspaceElement = generateWorkspaceElement(
                        workspaceId,
                        workspaceName,
                        workspaceCode
                    );
                    var workspaceGroupElement = $('#workspace-list').children('.workspace-group');
                    workspaceGroupElement.append(newWorkspaceElement);
                    workspaceElement.remove();
                }
            });
        });
    });

    // Click on pager buttons on workspace modal
    $('#workspace-modal-box').on('click', '.pagination > ul > li > a', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var element = event.currentTarget;
        var url = $(element).attr('href');
        var route;

        if (url !== '#') {
            var urlTab = url.split('/');
            var page = getPage(urlTab);
            var search = getSearch(urlTab);

            if (search !== '') {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_workspace_pager_search',
                    {
                        'workspaceTagId': currentParentTagId,
                        'page': page,
                        'search': search
                    }
                );
            } else {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_workspace_pager',
                    {'workspaceTagId': currentParentTagId, 'page': page}
                );
            }

            $.ajax({
                url: route,
                type: 'GET',
                success: function (data) {
                    $('#workspace-modal-body').html(data);
                }
            });
        }
    });

    // Click on SEARCH button of workspace list modal
    $('#workspace-modal-box').on('click', '#search-workspace-button', function () {
        var searchElement = document.getElementById('search-workspace-txt');
        var search = $(searchElement).val();
        var route;

        if (search !== '') {
            route = Routing.generate(
                'claro_admin_workspace_tag_check_workspace_pager_search',
                {
                    'workspaceTagId': currentParentTagId,
                    'search': search
                }
            );
        } else {
            route = Routing.generate(
                'claro_admin_workspace_tag_check_workspace_pager',
                {'workspaceTagId': currentParentTagId}
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (data) {
                $('#workspace-modal-body').html(data);
            }
        });
    });

    // Press ENTER on workspace list modal
    $('#workspace-modal-box').on('keypress', '#search-workspace-txt', function (e) {

        if (e.keyCode == 13) {
            var searchElement = document.getElementById('search-workspace-txt');
            var search = $(searchElement).val();
            var route;

            if (search !== '') {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_workspace_pager_search',
                    {
                        'workspaceTagId': currentParentTagId,
                        'search': search
                    }
                );
            } else {
                route = Routing.generate(
                    'claro_admin_workspace_tag_check_workspace_pager',
                    {'workspaceTagId': currentParentTagId}
                );
            }

            $.ajax({
                url: route,
                type: 'GET',
                success: function (data) {
                    $('#workspace-modal-body').html(data);
                }
            });
        }
    })

    // Click on the workspace remove button
    $('#workspace-list').on('click', '.remove-workspace-btn', function () {
        currentElement = $(this).parents('.workspace-whole-element');
        currentWorkspaceId = $(this).parents('.workspace-element').attr('workspace-id');
        $('#remove-workspace-from-tag-validation-box').modal('show');
    });

    // Click on OK button of workspace remove confirmation modal
    $('#remove-workspace-from-tag-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_admin_workspace_tag_remove_from_workspace',
                {'workspaceTagId': currentParentTagId, 'workspaceId': currentWorkspaceId}
            ),
            type: 'DELETE',
            success: function () {
                currentElement.remove();
                $('#remove-workspace-from-tag-validation-box').modal('hide');
            }
        });
    });

    // Click on the home icon of the track bar
    $('#home-track-btn').on('click', function () {
        window.location = Routing.generate('claro_admin_workspace_tag_organize');
    });

    // Click on the view tag hierarchy button
    $('#view-tag-hierarchy').on('click', function () {
        $.ajax({
            url: Routing.generate('claro_display_admin_workspace_tag_hierarchy'),
            type: 'GET',
            success: function (datas) {
                openTagHierarchyModal(
                    Translator.get('platform' + ':' + 'category_structure'),
                    datas
                );
            }
        });
    });

    // Click on a tag in the tag hierarchy modal
    $('#tag-hierarchy-modal-box').on('click', '.tag-hierarchy-element', function () {
        var tagElement = $(this);
        currentParentTagId = tagElement.attr('tag-id');
        var tagName = tagElement.attr('tag-name');
        var parentTagsElements = tagElement.parents('.hierarchy-tag-parent').get().reverse();
        $('#home-track-btn').nextAll().each(function () {
            $(this).remove();
        });

        $(parentTagsElements).each(function () {
            var childTag = $(this).children('.tag-hierarchy-element');
            var childTagId = childTag.attr('tag-id');
            var childTagName = childTag.attr('tag-name');
            var trackElement = '<span class="tag-track" tag-id="'
                + childTagId + '" tag-name="' + childTagName + '">\n'
                + '&nbsp;\n'
                + '>\n'
                + '&nbsp;\n'
                + '<span class="tag-track-name pointer-hand">'
                + childTagName
                + '</span>\n'
                + '</span>';
            $('#tag-tracks').append(trackElement);
        });
        loadTagData(currentParentTagId, tagName);
        closeTagHierarchyModal();
    });
})();