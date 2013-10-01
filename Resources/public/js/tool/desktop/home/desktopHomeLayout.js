(function () {
    'use strict';

    var configValue = ($('#config-value-div').attr('config-value')).trim();
    var withConfig = (configValue === '') ? 0 : parseInt(configValue);
    var currentHomeTabId;

    $('#switch-config-mode').click(function () {
        withConfig = (withConfig + 1) % 2;

        if (withConfig === 0) {
            $('.toggle-visible').each(function () {
                $(this).addClass('hidden');
            });
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

        window.location.href = Routing.generate(
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

        currentHomeTabId = $(this).parent().attr('home-tab-id');
        alert(currentHomeTabId);
    });
})();