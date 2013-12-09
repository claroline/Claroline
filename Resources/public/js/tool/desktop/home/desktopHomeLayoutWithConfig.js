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

    var tabs = window.Claroline.Tabs;

    tabs.rename(false);

    $('#add-hometab-btn').click(function (event) {
        event.preventDefault();
        event.stopPropagation();

        tabs.openHomeTabModal(Routing.generate('claro_desktop_home_tab_create_form'), 'home_tab_creation');
    });

    // Click on OK button of delete confirmation modal
    $('#delete-hometab-confirm-ok').click(function () {
        tabs.dele(
            Routing.generate(
                'claro_desktop_home_tab_delete',
                {'homeTabId': tabs.currentHomeTabId, 'tabOrder': tabs.currentHomeTabOrder}
            ),
            Routing.generate(
                'claro_display_desktop_home_tabs_with_config',
                {'tabId': -1}
            )
        );
    });

    // Click on OK button of the Create/Rename HomeTab form modal
    $('body').on('click', '#form-hometab-ok-btn', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();

        tabs.create(
            Routing.generate('claro_display_desktop_home_tabs_with_config', {'tabId': 0}),
            Routing.generate('claro_display_desktop_home_tabs_with_config', {'tabId': tabs.currentHomeTabId})
        );
    });
})();
