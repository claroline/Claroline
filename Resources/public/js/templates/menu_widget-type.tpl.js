'use strict';

angular.module('portfolioApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('templates/menu_widget-type.tpl.html',
            '<div class="popover" id="disposition_popover">' +
                '<div class="arrow"></div>' +
                '<div class="popover-content">' +
                    '<div class="btn-group-vertical contents" role="buttongroup">' +
                        '<button type="button" class="btn btn-default" data-ng-repeat="widgetType in widgetTypes" data-ng-click="createWidget(widgetType)">' +
                            '<span class="fa fa-{{ widgetType.icon }}"></span> ' +
                            '{{ widgetType.name + \'_title\' | trans }}' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>');
    }
]);