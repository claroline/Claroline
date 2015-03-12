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
    
    $('#desktop-home-content').on('click', '#add-hometab-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_desktop_home_tab_create_form'),
            openHomeTab,
            function() {}
        );
    });
    
    $('#desktop-home-content').on('click', '.edit-hometab-btn', function (e) {
        e.preventDefault();
        var homeTabId = $(this).parents('.hometab-element').attr('hometab-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_desktop_home_tab_edit_form',
                {'homeTabId': homeTabId}
            ),
            openHomeTab,
            function() {}
        );
    });

    $('#desktop-home-content').on('click', '.delete-hometab-btn', function (e) {
        e.preventDefault();
        var homeTabElement = $(this).parents('.hometab-element');
        var homeTabId = homeTabElement.attr('hometab-id');
        var order = homeTabElement.attr('hometab-order');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_desktop_home_tab_delete',
                {'homeTabId': homeTabId, 'tabOrder': order}
            ),
            removeHomeTab,
            homeTabId,
            Translator.trans('home_tab_delete_confirm_message', {}, 'platform'),
            Translator.trans('home_tab_delete_confirm_title', {}, 'platform')
        );
    });
    
    $('.grid-stack').gridstack({
        width: 12,
        animate: true
    });
    
    $('#widgets-list-panel').on('click', '.close-widget-btn', function () {
        var whcId = $(this).data('widget-hometab-config-id');
        
        console.log(whcId);
    });
    
    $('#widgets-list-panel').on('click', '.edit-widget-btn', function () {
        var whcId = $(this).data('widget-hometab-config-id');
        
        console.log(whcId);
    });
    
    $('#widgets-list-panel').on('change', function (e, items) {
        var wdcIds = [];
        var datas = {};
        
        for (var i = 0; i < items.length; i++) {
            
            if (items[i]['el'] !== undefined) {
                var wdcId = items[i]['el'].data('widget-display-config-id');
                var column = items[i]['el'].attr('data-gs-x');
                var row = items[i]['el'].attr('data-gs-y');
                var width = items[i]['el'].attr('data-gs-width');
                var height = items[i]['el'].attr('data-gs-height');
                wdcIds[i] = wdcId;
                
                if (datas[wdcId] === undefined) {
                    datas[wdcId] = {};
                }
                datas[wdcId]['row'] = row;
                datas[wdcId]['column'] = column;
                datas[wdcId]['width'] = width;
                datas[wdcId]['height'] = height;
            }
        }
        
        if (wdcIds.length > 0) {
            var parameters = {};
            parameters.wdcIds = wdcIds;
            var route = Routing.generate('claro_desktop_update_widgets_display_config');
            route += '?' + $.param(parameters);

            $.ajax({
                url: route,
                type: 'POST',
                data: datas
            });
        }
    });

    var openHomeTab = function (homeTabId) {
        window.location = Routing.generate(
            'claro_display_desktop_home_tab',
            {'tabId': homeTabId}
        );
    };
    
    var removeHomeTab = function (event, homeTabId) {
        var currentHomeTabId = parseInt($('#hometab-datas-box').data('hometab-id'));
        
        if (currentHomeTabId === parseInt(homeTabId)) {
            window.location.reload();
        } else {
            $('#hometab-element-' + homeTabId).remove();
        }
    };
})();