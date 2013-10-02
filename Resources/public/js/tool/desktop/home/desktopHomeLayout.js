(function () {
    'use strict';

    var displayedHomeTabId = $('#hometab-id-div').attr('hometab-id');
    var configValue = ($('#config-value-div').attr('config-value')).trim();
    var withConfig = (configValue === '') ? 0 : parseInt(configValue);
    var currentHomeTabId;
    var currentHomeTabOrder;
    var currentElement;

    $('#switch-config-mode').click(function () {
        withConfig = (withConfig + 1) % 2;

        if (withConfig === 0) {
            $('.toggle-visible').each(function () {
                $(this).addClass('hidden');
            });

            var currentVisibilityElement = $('#visible-hometab-id-' + displayedHomeTabId);

            if (currentVisibilityElement.hasClass('icon-eye-close')) {
                window.location = Routing.generate(
                    'claro_display_desktop_home_tabs',
                    {'tabId': -1, 'withConfig': withConfig}
                );
            }
        } else {
            $('.toggle-visible').each(function () {
                $(this).removeClass('hidden');
            });
        }
    });

    $('.hometab-link').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        var homeTabId = $(this).attr('home-tab-id');

        window.location = Routing.generate(
            'claro_display_desktop_home_tabs',
            {'tabId': homeTabId, 'withConfig' : withConfig}
        );
    });

    $('.admin-hometab-visibility-btn').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('.hometab-visibility-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var visibilityBtn = $(this);
        var homeTabConfigId = visibilityBtn.attr('hometab-config-id');
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
                    visibilityBtn.attr('visiblility-value', 'visible')
                    visibilityBtn.removeClass('icon-eye-close');
                    visibilityBtn.addClass('icon-eye-open');
                    visibilityBtn.parent().parent().removeClass('toggle-visible');
                } else {
                    visibilityBtn.attr('visiblility-value', 'invisible')
                    visibilityBtn.removeClass('icon-eye-open');
                    visibilityBtn.addClass('icon-eye-close');
                    visibilityBtn.parent().parent().addClass('toggle-visible');
                }
            }
        });
    });

    $('.hometab-delete-btn').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        currentElement = $(this).parent().parent();
        currentHomeTabId = $(this).parent().attr('home-tab-id');
        currentHomeTabOrder = $(this).parent().attr('home-tab-order');
        $('#delete-home-tab-validation-box').modal('show');
    });

    $('.hometab-rename-btn').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('#add-hometab-btn').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('#delete-home-tab-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_user_desktop_home_tab_delete',
                {'homeTabId': currentHomeTabId, 'tabOrder': currentHomeTabOrder}
            ),
            type: 'DELETE',
            success: function () {
                $('#delete-home-tab-validation-box').modal('hide');

                if (displayedHomeTabId === currentHomeTabId) {
                    window.location = Routing.generate(
                        'claro_display_desktop_home_tabs',
                        {'tabId': -1, 'withConfig': withConfig}
                    );
                } else {
                    currentElement.remove();
                }
            }
        });
    });
})();