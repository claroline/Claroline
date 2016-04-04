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
    var currentParentTagId;
    var isSubcategory;

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

    function openAddTagModal(content)
    {
        $('#add-tag-modal-body').html(content);
        $('#add-tag-modal-box').modal('show');
    }

    function closeAddTagModal()
    {
        $('#add-tag-modal-box').modal('hide');
        $('#add-tag-modal-body').empty();
    }

    function cleanSelection()
    {
        $('.claroline-tag-highlight').each(function () {
            $(this).children('.tag-button-group').addClass('hide');
            $(this).removeClass('claroline-tag-highlight');
        });
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

    function generateTagElement(tagId, tagName, isSubCategory)
    {
        var generatedTagElement = '<li class="hierarchy-tag-parent tag-parent-' + tagId + '"\n' +
                'workspace-tag-id="' + tagId + '"\n' +
            '>' +
                '<span class="tag-element"\n' +
                    'workspace-tag-id="' + tagId + '"\n' +
                    'workspace-tag-name="' + tagName + '"\n' +
                '>' +
                    '<span class="open-tag-btn pointer-hand tag-name-' + tagId + '">\n' +
                        tagName +
                    '\n</span>\n' +

                    '<div class="btn-group tag-button-group hide">\n' +
                        '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">\n' +
                            '<span class="caret"></span>\n' +
                        '</button>\n' +
                        '<ul class="dropdown-menu">\n' +
                           ' <li class="edit-tag-btn">\n' +
                                '<a href="#">\n' +
                                    '<i class="fa fa-pencil"></i>\n' +
                                    Translator.trans('rename_category', {}, 'platform') +
                                '\n</a>\n' +
                            '</li>\n' +
                            '<li class="divider"></li>\n' +
                            '<li class="create-sub-tag-btn">\n' +
                                '<a href="#">\n' +
                                    '<i class="fa fa-plus"></i>\n' +
                                    Translator.trans('create_subcategory', {}, 'platform') +
                                '\n</a>\n' +
                            '</li>' +
                            '<li class="add-tag-btn">\n' +
                                '<a href="#">\n' +
                                    '<i class="fa fa-list-alt"></i>\n' +
                                    Translator.trans('add_subcategory', {}, 'platform') +
                                '\n</a>\n' +
                            '</li>\n' +
                            '<li class="divider"></li>\n';

        if (isSubCategory) {
            generatedTagElement += '<li class="remove-tag-btn">\n' +
                                        '<a href="#">\n' +
                                            '<i class="fa fa-times"></i>\n' +
                                            Translator.trans('remove_subcategory', {}, 'platform') +
                                        '\n</a>\n' +
                                    '</li>\n';
        }
        generatedTagElement += '<li class="delete-tag-btn">\n' +
                                '<a href="#">\n' +
                                    '<i class="fa fa-trash-o"></i>\n' +
                                    Translator.trans('delete_category', {}, 'platform') +
                                '\n</a>\n' +
                            '</li>\n' +
                        '</ul>\n' +
                    '</div>\n' +
                '</span>\n' +

                '<div>\n' +
                    '<ul class="tag-children-list-' + tagId + '"></ul>\n' +
                '</div>\n' +
            '</li>\n';

        return generatedTagElement;
    }

    // Click on the category create button
    $('#create-root-tag-btn').on('click', function () {
        cleanSelection();
        isSubcategory = false;

        $.ajax({
            url: Routing.generate('claro_workspace_tag_create_form'),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.trans('create_category', {}, 'platform'),
                    datas
                );
            }
        });
    });

    // Click on the category edit button
    $('#workspace-organization-div').on('click', '.edit-tag-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        currentElement = $(this).parents('.tag-element');
        currentTagId = currentElement.attr('workspace-tag-id');

        $.ajax({
            url: Routing.generate(
                'claro_workspace_tag_edit_form',
                {'workspaceTagId': currentTagId}
            ),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.trans('rename_category', {}, 'platform'),
                    datas
                );
            }
        });
    });

    // Click on CANCEL button of the tag Create/Rename form modal
    $('#form-modal-box').on('click', '#form-cancel-btn', function () {
        closeFormModal();
    });

    // Click on OK button of the tag Create/Rename form modal
    $('#form-modal-body').on('click', '#form-workspace-tag-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('workspace_tag_form');
        var tagNameInput = document.getElementById('workspace_tag_form_name');
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
                        if (isSubcategory) {
                            $.ajax({
                                url: Routing.generate(
                                    'claro_workspace_tag_add_children',
                                    {'tagId': currentTagId, 'childrenString': data}
                                ),
                                type: 'POST',
                                async: false,
                                success: function () {
                                    var tagChildrenListClass = '.tag-children-list-' + currentTagId;
                                    $(tagChildrenListClass).append(generateTagElement(data, tagName, true));
                                    closeFormModal();
                                }
                            });
                        } else {
                            $('#tags-root').append(generateTagElement(data, tagName, false));
                            $('#no-category-element').remove();
                            closeFormModal();
                        }
                        break;
                    case 204:
                        var tagNameClass = '.tag-name-' + currentTagId;
                        $(tagNameClass).html(tagName);
                        closeFormModal();
                        break;
                    default:
                        $('#form-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });

    // Click on the category remove button
    $('#workspace-organization-div').on('click', '.remove-tag-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        currentElement = $(this).parents('.hierarchy-tag-parent').first();
        currentTagId = $(this).parents('.tag-element').attr('workspace-tag-id');
        currentParentTagId = currentElement.parents('.hierarchy-tag-parent').first().attr('workspace-tag-id');
        $('#remove-workspace-tag-validation-box').modal('show');
    });

    // Click on OK button of workspace tag remove confirmation modal
    $('#remove-workspace-tag-confirm-ok').click(function () {
        if (currentParentTagId) {
            $.ajax({
                url: Routing.generate(
                    'claro_workspace_tag_remove_child',
                    {'parentTagId': currentParentTagId, 'childTagId': currentTagId}
                ),
                type: 'DELETE',
                success: function () {
                    $('#remove-workspace-tag-validation-box').modal('hide');
                    window.location.reload();
                }
            });
        }
    });

    // Click on the name of a tag
    $('#workspace-organization-div').on('click', '.open-tag-btn', function () {
        cleanSelection();
        currentElement = $(this).parents('.tag-element');
        currentElement.addClass('claroline-tag-highlight');
        currentElement.children('.tag-button-group').removeClass('hide');
    });

    // Click on the subcategory add button
    $('#workspace-organization-div').on('click', '.add-tag-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        currentElement = $(this).parents('.hierarchy-tag-parent').first();
        currentTagId = $(this).parents('.tag-element').attr('workspace-tag-id');

        $.ajax({
            url: Routing.generate(
                'claro_workspace_tag_check_children_pager',
                {'workspaceTagId': currentTagId}
            ),
            type: 'GET',
            success: function (datas) {
                openAddTagModal(datas);
            }
        });
    });

    // Click on ADD button of the tag Add form modal
    $('#add-workspace-tag-confirm-ok').on('click', function () {
        var possibleSelected = [];
        $('input:checkbox[name=tag-possible-child]:checked').each(function () {
            possibleSelected.push($(this).val());
        });
        var possibleSelectedString = possibleSelected.join();

        if (possibleSelectedString !== '') {
            $.ajax({
                url: Routing.generate(
                    'claro_workspace_tag_add_children',
                    {'tagId': currentTagId, 'childrenString': possibleSelectedString}
                ),
                type: 'POST',
                success: function () {
                    $('input:checkbox[name=tag-possible-child]:checked').each(function () {
                        var possibleChildElement = $(this).parents('.possible-child-element');
                        var possibleChildId = possibleChildElement.attr('tag-id');
                        possibleChildElement.remove();
                        var tagChildrenListClass = '.tag-children-list-' + currentTagId;
                        var tagParentRootClass = '#tags-root > .tag-parent-' + possibleChildId;
                        var tagParentRootElement = $(tagParentRootClass);

                        if (tagParentRootElement.length > 0) {
                            var removeTagDivider = $('.remove-tag-divider-' + possibleChildId);
                            var removeTagLine =
                                '<li class="remove-tag-btn">\n' +
                                    '<a href="#">\n' +
                                        '<i class="fa fa-times"></i>\n' +
                                        Translator.trans('remove_subcategory', {}, 'platform') +
                                    '\n</a>\n' +
                                '</li>\n';
                            removeTagDivider.after(removeTagLine);
                            $(tagChildrenListClass).append(tagParentRootElement.clone());
                            tagParentRootElement.remove();
                        } else {
                            var tagParentClass = '.tag-parent-' + possibleChildId;
                            var tagParentElement = $(tagParentClass).first();
                            $(tagChildrenListClass).append(tagParentElement.clone());
                        }
                    });
                }
            });
        }
    });

    // Click on pager buttons on add tag modal
    $('#add-tag-modal-box').on('click', '.pagination > ul > li > a', function (event) {
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
                    'claro_workspace_tag_check_children_pager_search',
                    {
                        'workspaceTagId': currentTagId,
                        'page': page,
                        'search': search
                    }
                );
            } else {
                route = Routing.generate(
                    'claro_workspace_tag_check_children_pager',
                    {'workspaceTagId': currentTagId, 'page': page}
                );
            }

            $.ajax({
                url: route,
                type: 'GET',
                success: function (data) {
                    $('#add-tag-modal-body').html(data);
                }
            });
        }
    });

    // Click on SEARCH button of category list modal
    $('#add-tag-modal-box').on('click', '#search-tag-button', function () {
        var searchElement = document.getElementById('search-tag-txt');
        var search = $(searchElement).val();
        var route;

        if (search !== '') {
            route = Routing.generate(
                'claro_workspace_tag_check_children_pager_search',
                {
                    'workspaceTagId': currentTagId,
                    'search': search
                }
            );
        } else {
            route = Routing.generate(
                'claro_workspace_tag_check_children_pager',
                {'workspaceTagId': currentTagId}
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (data) {
                $('#add-tag-modal-body').html(data);
            }
        });
    });

    // Press ENTER on category list modal
    $('#add-tag-modal-box').on('keypress', '#search-tag-txt', function (e) {

        if (e.keyCode == 13) {
            var searchElement = document.getElementById('search-tag-txt');
            var search = $(searchElement).val();
            var route;

            if (search !== '') {
                route = Routing.generate(
                    'claro_workspace_tag_check_children_pager_search',
                    {
                        'workspaceTagId': currentTagId,
                        'search': search
                    }
                );
            } else {
                route = Routing.generate(
                    'claro_workspace_tag_check_children_pager',
                    {'workspaceTagId': currentTagId}
                );
            }

            $.ajax({
                url: route,
                type: 'GET',
                success: function (data) {
                    $('#add-tag-modal-body').html(data);
                }
            });
        }
    })

    // Click on the subcategory create button
    $('#workspace-organization-div').on('click', '.create-sub-tag-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        isSubcategory = true;
        currentElement = $(this).parents('.hierarchy-tag-parent').first();
        currentTagId = $(this).parents('.tag-element').attr('workspace-tag-id');

        $.ajax({
            url: Routing.generate('claro_workspace_tag_create_form'),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.trans('create_subcategory', {}, 'platform'),
                    datas
                );
            }
        });
    });

    // Click on the category delete button
    $('#workspace-organization-div').on('click', '.delete-tag-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        currentElement = $(this).parents('.hierarchy-tag-parent').first();
        currentTagId = $(this).parents('.tag-element').attr('workspace-tag-id');
        $('#delete-workspace-tag-validation-box').modal('show');
    });

    // Click on OK button of workspace tag delete confirmation modal
    $('#delete-workspace-tag-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_workspace_tag_delete',
                {'workspaceTagId': currentTagId}
            ),
            type: 'DELETE',
            success: function () {
                $('#delete-workspace-tag-validation-box').modal('hide');
                window.location.reload();
            }
        });
    });
})();
