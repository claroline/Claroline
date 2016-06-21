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
    var currentSearch = $('#contacts-datas-box').data('search');
    var currentMax = $('#contacts-datas-box').data('max');
    var currentOrderedBy = $('#contacts-datas-box').data('ordered-by');
    var currentOrder = $('#contacts-datas-box').data('order');
    var allContactIdsTxt = '' + $('#contacts-datas-box').data('contacts-id');
    allContactIdsTxt = allContactIdsTxt.trim();
    var allContactIds = allContactIdsTxt !== '' ?
        allContactIdsTxt.split(',') :
        [];
    var currentCategoryId;

    $('#my-contacts-tool').on('click', '#contacts-configure-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_options_configure_form'),
            refreshPage,
            function() {}
        );
    });

    $('#my-contacts-tool').on('click', '#category-create-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_category_create_form'),
            addCategory,
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

    $('#my-contacts-tool').on('click', '#add-contacts-btn', function () {
        var userPicker = new UserPicker();
        var settings = {
            multiple: true,
            picker_name: 'contacts_picker',
            picker_title: Translator.trans(
                'select_users_to_add_to_your_contacts',
                {},
                'platform'
            ),
            blacklist: allContactIds
        };
        userPicker.configure(settings, addContacts);
        userPicker.open();
    });

    $('#my-contacts-tool').on('click', '.add-user-to-category-btn', function () {
        currentCategoryId = $(this).data('category-id');

        var userPicker = new UserPicker();
        var settings = {
            multiple: true,
            picker_name: 'contacts_picker_' + currentCategoryId,
            picker_title: Translator.trans(
                'select_users_to_add_to_your_contacts',
                {},
                'platform'
            ),
            blacklist: allContactIds
        };
        userPicker.configure(settings, addContactsToCategory);
        userPicker.open();
    });

    $('body').on('click', '#all-my-contacts-content-body .pagination a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#all-my-contacts-content-body').html(datas);
            }
        });
    });

    $('#all-my-contacts-content-body').on('click', '.contact-additional-action', function (event) {
        var child = $(this).children('.contact-action');
        var url = child.data('url');
        var displayMode = child.data('display-mode');

        if (displayMode === 'new_small_window') {

            window.open(
                url,
                '',
                'toolbar=0,menubar=0,titlebar=0,location=0,status=0,left=150,top=200,width=200,height=300',
                false
            ).focus();
        } else {
            window.location = url;
        }
    });

    $('#all-my-contacts-content-body').on('change', '#max-select', function() {
        var max = $(this).val();

        $.ajax({
            url: Routing.generate('claro_contact_show_all_my_contacts', {'max': max}),
            type: 'GET',
            success: function (datas) {
                $('#all-my-contacts-content-body').html(datas);
            }
        });
    });

    $('#all-my-contacts-content-body').on('click', '.delete-contact', function () {
        var contactId = $(this).data('contact-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_contact_delete', {'contact': contactId}),
            removeContact,
            contactId,
            Translator.trans('delete_contact_confirm_message', {}, 'platform'),
            Translator.trans('delete_contact', {}, 'platform')
        );
    });

    $('#all-my-contacts-content-body').on('click', '.add-contact-to-category', function () {
        var contactId = $(this).data('contact-id');

        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_categories_transfer_form', {'user': contactId}),
            refreshPage,
            function() {}
        );
    });

    $('#all-visible-users-content-body').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#all-visible-users-content-body').html(datas);
            }
        });
    });

    $('#all-visible-users-content-body').on('click', '.contact-additional-action', function (event) {
        var child = $(this).children('.contact-action');
        var url = child.data('url');
        window.location = url;
    });

    $('#all-visible-users-content-body').on('change', '#max-select', function() {
        var max = $(this).val();

        $.ajax({
            url: Routing.generate('claro_contact_show_all_visible_users', {'max': max}),
            type: 'GET',
            success: function (datas) {
                $('#all-visible-users-content-body').html(datas);
            }
        });
    });

    $('.category-content-body').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');
        var categoryElement = $(element).parents('.category-content-body');
        var categoryId = categoryElement.data('category-id');

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#category-content-body-' + categoryId).html(datas);
            }
        });
    });

    $('.category-content-body').on('click', '.contact-additional-action', function (event) {
        var child = $(this).children('.contact-action');
        var url = child.data('url');
        window.location = url;
    });

    $('.category-content-body').on('change', '#max-select', function () {
        var max = $(this).val();
        var categoryElement = $(this).parents('.category-content-body');
        var categoryId = categoryElement.data('category-id');

        $.ajax({
            url: Routing.generate(
                'claro_contact_show_contacts_by_category',
                {'category': categoryId, 'max': max}
            ),
            type: 'GET',
            success: function (datas) {
                $('#category-content-body-' + categoryId).html(datas);
            }
        });
    });

    $('.category-content-body').on('click', '.remove-contact', function () {
        var contactId = $(this).data('contact-id');
        var categoryId = $(this).data('category-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_contact_category_remove',
                {'user': contactId, 'category': categoryId}
            ),
            removeContactFromCategory,
            {'contact': contactId, 'category': categoryId},
            Translator.trans('remove_contact_from_category_confirm_message', {}, 'platform'),
            Translator.trans('remove_contact_from_category', {}, 'platform')
        );
    });

    $('#my-contacts-tool').on('click', '#search-contact-btn', function () {
        var search = $('#search-contact-input').val();
        window.location = Routing.generate(
            'claro_my_contacts_tool_index',
            {
                'search': search,
                'max': currentMax,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );
    });

    $('#my-contacts-tool').on('keypress', '#search-contact-input', function(e) {

        if (e.keyCode === 13) {
            e.preventDefault();
            var search = $(this).val();
            window.location = Routing.generate(
                'claro_my_contacts_tool_index',
                {
                    'search': search,
                    'max': currentMax,
                    'orderedBy': currentOrderedBy,
                    'order': currentOrder
                }
            );
        }
    });

    $('#searched-contacts').on('change', '#max-select', function() {
        var max = $(this).val();
        window.location = Routing.generate(
            'claro_my_contacts_tool_index',
            {
                'search': currentSearch,
                'max': max,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );
    });

    $('#searched-contacts').on('click', '.delete-contact', function () {
        var contactId = $(this).data('contact-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_contact_delete', {'contact': contactId}),
            removeContact,
            contactId,
            Translator.trans('delete_contact_confirm_message', {}, 'platform'),
            Translator.trans('delete_contact', {}, 'platform')
        );
    });

    $('#searched-contacts').on('click', '.add-contact-to-category', function () {
        var contactId = $(this).data('contact-id');

        window.Claroline.Modal.displayForm(
            Routing.generate('claro_contact_categories_transfer_form', {'user': contactId}),
            refreshPage,
            function() {}
        );
    });

    var refreshPage = function () {
        window.location.reload();
    };

    var addCategory = function (datas) {
        var id = datas['id'];
        var name = datas['name'];

        var categoryElement =
            '<div class="panel panel-info category-element" id="category-box-' +
                id + '" style="overflow: visible">' +

                '<div class="panel-heading">' +
                    '<h4 class="panel-title">' +
                        '<a data-toggle="collapse" href="#category-content-' +
                            id + '" id="category-title-' + id + '">' +

                            name +
                        '</a>' +
                        '&nbsp;&nbsp;' +
                        '<span class="badge" id="category-badge-' + id + '">' +
                            '0' +
                        '</span>' +
                        '<span class="dropdown pull-right">' +
                            '<i class="fa fa-cogs pointer-hand" data-toggle="dropdown">' +
                            '</i>' +
                            '<ul class="dropdown-menu" role="menu">' +
                                '<li role="presentation" class="add-user-to-category-btn" data-category-id="' +
                                    id + '">' +

                                    '<a role="menuitem" tabindex="-1" href="#">' +
                                        '<i class="fa fa-user-plus"></i>&nbsp;' +
                                        Translator.trans('add_contacts', {}, 'platform') +
                                    '</a>' +
                                '</li>' +
                                '<li role="presentation" class="category-edit-btn" data-category-id="' +
                                    id + '">' +

                                    '<a role="menuitem" tabindex="-1" href="#">' +
                                        '<i class="fa fa-edit"></i>&nbsp;' +
                                        Translator.trans('rename', {}, 'platform') +
                                    '</a>' +
                                '</li>' +
                                '<li role="presentation" class="category-delete-btn" data-category-id="' +
                                    id + '">' +

                                    '<a role="menuitem" tabindex="-1" href="#">' +
                                        '<i class="fa fa-times-circle"></i>&nbsp;' +
                                        Translator.trans('delete', {}, 'platform') +
                                    '</a>'+
                                '</li>' +
                            '</ul>' +
                        '</span>' +
                    '</h4>' +
                '</div>' +
                '<div id="category-content-' + id + '" class="panel-collapse collapse">' +
                    '<div class="panel-body" id="category-content-body-' + id + '">' +
                        '<div class="alert alert-warning" id="category-empty-alert-'+ id + '">' +
                            Translator.trans('no_contact', {}, 'platform') +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $('#category-list-box').append(categoryElement);
    };

    var renameCategory = function (datas) {
        var id = datas['id'];
        var name = datas['name'];
        $('#category-title-' + id).html(name);
    };

    var removeCategory = function (event, categoryId) {
        $('#category-box-' + categoryId).remove();
    };

    var addContacts = function (userIds) {

        if (userIds !== null) {
            var parameters = {};
            parameters.userIds = userIds;
            var route = Routing.generate('claro_contacts_add');
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                type: 'GET',
                success: function () {
                    window.location.reload();
                }
            });
        }
    };

    var addContactsToCategory = function (userIds) {

        if (userIds !== null) {
            var parameters = {};
            parameters.userIds = userIds;
            var route = Routing.generate(
                'claro_contacts_add_to_category',
                {'category': currentCategoryId}
            );
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                type: 'GET',
                success: function () {

                    for (var i = 0; i < userIds.length; i++) {

                        if (allContactIds.indexOf(userIds[i]) === -1) {
                            allContactIds.push(userIds[i]);
                        }
                    }
                    var nbAllContacts = parseInt($('#all-my-contacts-badge').html());
                    nbAllContacts += userIds.length;
                    $('#all-my-contacts-badge').html(nbAllContacts);
                    var nbCategoryContacts = parseInt($('#category-badge-' + currentCategoryId).html());
                    nbCategoryContacts += userIds.length;
                    $('#category-badge-' + currentCategoryId).html(nbCategoryContacts);

                    $.ajax({
                        url: Routing.generate('claro_contact_show_all_my_contacts'),
                        type: 'GET',
                        success: function (datas) {
                            $('#all-my-contacts-content-body').html(datas);
                        }
                    });
                    $.ajax({
                        url: Routing.generate(
                            'claro_contact_show_contacts_by_category',
                            {'category': currentCategoryId}
                        ),
                        type: 'GET',
                        success: function (datas) {
                            $('#category-content-body-' + currentCategoryId).html(datas);
                        }
                    });
                }
            });
        }
    };

    var removeContact = function (event, contactId) {
        var index = allContactIds.indexOf('' + contactId);
        var nbContacts = parseInt($('#all-my-contacts-badge').html());
        nbContacts--;
        $('#all-my-contacts-badge').html(nbContacts);

        if (index > -1) {
            allContactIds.splice(index, 1);
        }
        $('.contact-row-' + contactId).each(function () {
            var categoryId = $(this).data('category-id');
            $(this).remove();

            if (categoryId !== undefined) {
                var nbContacts = parseInt($('#category-badge-' + categoryId).html());
                nbContacts--;
                $('#category-badge-' + categoryId).html(nbContacts);
            }
        });
    };

    var removeContactFromCategory = function (event, datas) {
        var contactId = datas['contact'];
        var categoryId = datas['category'];
        var nbContacts = parseInt($('#category-badge-' + categoryId).html());
        nbContacts--;
        $('#category-badge-' + categoryId).html(nbContacts);
        $('.contact-row-' + categoryId + '-' + contactId).remove();
    };
})();
