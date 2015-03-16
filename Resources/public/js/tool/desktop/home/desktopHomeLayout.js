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
        var homeTabId = $(this).parents('.hometab-element').data('hometab-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_desktop_home_tab_edit_form',
                {'homeTabId': homeTabId}
            ),
            renameHomeTab,
            function() {}
        );
    });

    $('#desktop-home-content').on('click', '.delete-hometab-btn', function (e) {
        e.preventDefault();
        var homeTabElement = $(this).parents('.hometab-element');
        var homeTabId = homeTabElement.data('hometab-id');
        var order = homeTabElement.data('hometab-order');

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
    
    $('#desktop-hometabs-list').sortable({
        items: '.movable-hometab',
        cursor: 'move'
    });

    $('#desktop-hometabs-list').on('sortupdate', function (event, ui) {

        if (this === ui.item.parents('#desktop-hometabs-list')[0]) {
            var hcId = $(ui.item).data('hometab-config-id');
            var nextHcId = -1;
            var nextElement = $(ui.item).next();
            
            if (nextElement !== undefined && nextElement.hasClass('movable-hometab')) {
                nextHcId = nextElement.data('hometab-config-id');
            }
            
            $.ajax({
                url: Routing.generate(
                    'claro_home_tab_config_reorder',
                    {
                        'homeTabConfig': hcId,
                        'nextHomeTabConfigId': nextHcId
                    }
                ),
                type: 'POST'
            });
        }
//        if (this === ui.item.parents('.cursus-children')[0]) {
//            var cursusId = $(ui.item).data('cursus-id');
//            var otherCursusId = $(ui.item).next().data('cursus-id');
//            var mode = 'previous';
//            var execute = false;
//
//            if (otherCursusId !== undefined) {
//                mode = 'next';
//                execute = true;
//            } else {
//                otherCursusId = $(ui.item).prev().data('cursus-id');
//
//                if (otherCursusId !== undefined) {
//                    execute = true;
//                }
//            }
//
//            if (execute) {
//                $.ajax({
//                    url: Routing.generate(
//                        'claro_cursus_update_order',
//                        {
//                            'cursus': cursusId,
//                            'otherCursus': otherCursusId,
//                            'mode': mode
//                        }
//                    ),
//                    type: 'POST'
//                });
//            }
//        }
    });
    
    $('.grid-stack').gridstack({
        width: 12,
        animate: true
    });
    
    $('#widgets-list-panel').on('click', '.close-widget-btn', function () {
        var whcId = $(this).data('widget-hometab-config-id');
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_desktop_widget_home_tab_config_delete',
                {'widgetHomeTabConfigId': whcId}
            ),
            removeWidget,
            whcId,
            Translator.trans('widget_home_tab_delete_confirm_message', {}, 'platform'),
            Translator.trans('widget_home_tab_delete_confirm_title', {}, 'platform')
        );
    });
    
    $('#widgets-list-panel').on('click', '.edit-widget-btn', function () {
        var widgetHomeTabId = $(this).data('widget-hometab-config-id');
        var widgetDisplayConfigId = $(this).data('widget-display-config-id');
        var widgetInstanceId = $(this).data('widget-instance-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_desktop_widget_config_edit_form',
                {
                    'widgetInstance': widgetInstanceId,
                    'widgetHomeTabConfig': widgetHomeTabId,
                    'widgetDisplayConfig': widgetDisplayConfigId
                }
            ),
            updateWidget,
            function() {}
        );
    });
    
    $('body').on('focus', '#widget_display_config_form_color', function () {
        $(this).colorpicker();
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

    var renameHomeTab = function (datas) {
        var id = datas['id'];
        var name = datas['name'];
        
        $('#hometab-name-' + id).html(name);
    };
    
    var removeHomeTab = function (event, homeTabId) {
        var currentHomeTabId = parseInt($('#hometab-datas-box').data('hometab-id'));
        
        if (currentHomeTabId === parseInt(homeTabId)) {
            window.location.reload();
        } else {
            $('#hometab-element-' + homeTabId).remove();
        }
    };
    
    var removeWidget = function (event, widgetHomeTabConfigId) {
        var widgetElement = $('#widget-element-' + widgetHomeTabConfigId);
        var grid = $('.grid-stack').data('gridstack');
        grid.remove_widget(widgetElement);
    }
    
    var updateWidget = function (datas) {
        var id = datas['id'];
        var color = (datas['color'] === null) ? '' : datas['color'];
        $('#widget-element-title-' + id).html(datas['title']);
        $('#widget-element-header-' + id).css('background-color', color);
        $('#widget-element-content-' + id).css('border-color', color);
    };
})();