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

    window.Claroline.Tabs = {};
    var tabs = window.Claroline.Tabs;
    tabs.displayedHomeTabId = $('#hometab-id-div').attr('hometab-id');
    tabs.configValue = ($('#config-value-div').attr('config-value')).trim();
    tabs.workspaceId = $('#workspace-id-div').attr('workspace-id');
    var translator = window.Translator;

    tabs.openHomeTabModal = function (url, title) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function (data) {
                $('#hometab-modal-title').html(translator.get('platform' + ':' + title));
                $('#hometab-modal-body').html(data);
                $('#hometab-modal-box').modal('show');
            }
        });
    };

    tabs.closeHomeTabModal = function ()
    {
        $('#hometab-modal-box').modal('hide');
        $('#hometab-modal-title').empty();
        $('#hometab-modal-body').empty();
    };

    tabs.rename = function (isWorkspace)
    {
        $('.hometab-rename-btn').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            tabs.currentElement = $(this).parents('.hometab-element');
            tabs.currentHomeTabId = tabs.currentElement.attr('hometab-id');
            $('.dropdown', tabs.currentElement).removeClass('open');
            var url = Routing.generate('claro_desktop_home_tab_edit_form', {'homeTabId': tabs.currentHomeTabId});
            if (isWorkspace) {
                url = Routing.generate(
                    'claro_workspace_home_tab_edit_form',
                    {'homeTabId': tabs.currentHomeTabId, 'workspaceId': tabs.workspaceId}
                );
            }

            tabs.openHomeTabModal(url, 'home_tab_edition');
        });
    };

    tabs.dele = function (url, reload)
    {
        $.ajax({
            url: url,
            type: 'DELETE',
            success: function () {
                $('#delete-hometab-validation-box').modal('hide');

                if (tabs.displayedHomeTabId === tabs.currentHomeTabId) {
                    window.location = reload;
                } else {
                    tabs.currentElement.remove();
                }
            }
        });
    };

    tabs.create = function (created, nocontent) {
        var form = document.getElementById('hometab-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            complete: function (jqXHR) {
                switch (jqXHR.status) {
                    case 201:
                        tabs.closeHomeTabModal();
                        window.location = created;
                        break;
                    case 204:
                        tabs.closeHomeTabModal();
                        window.location = nocontent;
                        break;
                    default:
                        $('#hometab-modal-body').html(jqXHR.responseText);
                }
            }
        });
    };

    /************* EVENTS  ************/

    $('.hometab-visibility-btn').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var visibilityBtn = $(this);
        tabs.currentElement = visibilityBtn.parents('.hometab-element');
        var homeTabConfigId = tabs.currentElement.attr('hometab-config-id');
        var visible = (visibilityBtn.attr('visiblility-value')).trim();
        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';

        $.ajax({
            url: Routing.generate(
                'claro_home_tab_update_visibility',
                {'homeTabConfigId': homeTabConfigId, 'visible': newVisible}
            ),
            type: 'POST',
            success: function () {
                if (newVisible === 'visible') {
                    $('.hometab-link .hometab-visibility-btn', tabs.currentElement.get(0)).html(' ' + translator.get('platform:hide'));
                    $('.hometab-link', tabs.currentElement.get(0)).removeClass('strike');
                    visibilityBtn.attr('visiblility-value', 'visible');
                    visibilityBtn.removeClass('icon-eye-open');
                    visibilityBtn.addClass('icon-eye-close');
                    tabs.currentElement.removeClass('toggle-visible');
                } else {
                    $('.hometab-link .hometab-visibility-btn', tabs.currentElement.get(0)).html(' ' + translator.get('platform:display'));
                    $('.hometab-link', tabs.currentElement.get(0)).addClass('strike');
                    visibilityBtn.attr('visiblility-value', 'invisible');
                    visibilityBtn.removeClass('icon-eye-close');
                    visibilityBtn.addClass('icon-eye-open');
                    tabs.currentElement.addClass('toggle-visible');
                }
                $('.dropdown', tabs.currentElement).removeClass('open');
            }
        });
    });

    // Click on left reorder button of a hometab
    $('.hometab-reorder-left-btn').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        tabs.currentElement = $(this).parents('.hometab-element');
        var homeTabConfigId = tabs.currentElement.attr('hometab-config-id');

        $.ajax({
            url: Routing.generate(
                'claro_home_tab_config_change_order',
                {'homeTabConfigId': homeTabConfigId, 'direction': -1}
            ),
            type: 'POST',
            success: function (data) {
                if (data === '-1') {
                    var previousSibling = tabs.currentElement.prev();
                    previousSibling.before(tabs.currentElement);
                    $('.dropdown', tabs.currentElement).removeClass('open');
                }
            }
        });
    });

    // Click on right reorder button of a hometab
    $('.hometab-reorder-right-btn').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        tabs.currentElement = $(this).parents('.hometab-element');
        var homeTabConfigId = tabs.currentElement.attr('hometab-config-id');

        $.ajax({
            url: Routing.generate(
                'claro_home_tab_config_change_order',
                {'homeTabConfigId': homeTabConfigId, 'direction': 1}
            ),
            type: 'POST',
            success: function (data) {
                if (data === '1') {
                    var nextSibling = tabs.currentElement.next();
                    nextSibling.after(tabs.currentElement);
                    $('.dropdown', tabs.currentElement).removeClass('open');
                }
            }
        });
    });

    $('.hometab-delete-btn').click(function (event) {
        event.preventDefault();
        event.stopPropagation();

        tabs.currentElement = $(this).parents('.hometab-element');
        tabs.currentHomeTabId = tabs.currentElement.attr('hometab-id');
        tabs.currentHomeTabOrder = tabs.currentElement.attr('hometab-order');
        $('#delete-hometab-validation-box').modal('show');
        $('.dropdown', tabs.currentElement).removeClass('open');
    });

    // Click on CANCEL button of the Create/Rename HomeTab form modal
    $('body').on('click', '#form-hometab-cancel-btn', function () {
        tabs.closeHomeTabModal();
    });
})();

