'use strict';

angular.module('portfolioApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('templates/menu_widget-type.tpl.html',
            '<div class="add_widget" data-ng-init="isadding = false" data-ng-show="!isadding">' +
                '<div class="panel-body">' +
                    '<p class="add_widget_icon" data-ng-click="isadding = true">' +
                        '<span class="fa-stack fa-lg">' +
                            '<i class="fa fa-circle-o fa-stack-2x"></i>' +
                            '<i class="fa fa-plus fa-stack-1x"></i>' +
                        '</span>' +
                    '</p>' +
                '</div>' +
            '</div>' +
            '<div class="add_widget" data-ng-show="isadding">' +
                '<div class="panel-body">' +
                    '<span class="fa fa-close pull-right" data-ng-click="isadding = false" role="button"></span>' +
                    '<ul class="list-inline widget_type_list">' +
                        '<li class="widget_type" data-ng-repeat="widgetType in widgetTypes" data-ng-click="$parent.isadding = false; createWidget(widgetType.name, col)" role="button">' +
                            '<span class="fa fa-{{ widgetType.icon }}"></span>' +
                            '<span>{{ widgetType.name + \'_title\' | trans }}</span>' +
                        '</li>' +
                    '</ul>' +
                '</div>' +
            '</div>');
    }
]);