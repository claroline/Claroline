'use strict';

angular.module('portfolioApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('templates/menu_widget-type.tpl.html',
            '<div class="popover" id="widget_types_popover">' +
                '<div class="arrow"></div>' +
                '<div class="popover-content">' +
                    '<ul class="list-inline widget_type_list">' +
                        '<li class="widget_type" data-ng-repeat="widgetType in widgetTypes" data-ng-click="createWidget(widgetType)">' +
                            '<span class="fa fa-{{ widgetType.icon }}"></span> ' +
                            '<span>{{ widgetType.name + \'_title\' | trans }}</span>' +
                        '</li>' +
                    '</ul>' +
                '</div>' +
            '</div>');
    }
]);