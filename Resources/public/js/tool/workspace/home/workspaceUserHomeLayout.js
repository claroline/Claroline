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
    
    var workspaceId = parseInt($('#hometab-datas-box').data('workspace-id'));

    $('#workspace-home-content').on('click', '.bookmark-hometab-btn', function (e) {
        e.preventDefault();
        var homeTabElement = $(this).parents('.hometab-element');
        var homeTabId = homeTabElement.data('hometab-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_workspace_home_tab_bookmark',
                {'workspace': workspaceId, 'homeTab': homeTabId}
            ),
            doNothing,
            null,
            Translator.trans('home_tab_bookmark_confirm_message', {}, 'platform'),
            Translator.trans('home_tab_bookmark_confirm_title', {}, 'platform')
        );
    });
    
    var doNothing = function () {};
})();