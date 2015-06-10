'use strict';

widgetsApp
    .factory("widgetManager", ["$http", "widgetFactory", function($http, widgetFactory){
        return {
            widgets: [],
            getWidgets: function(widgetType) {
                var widget = widgetFactory.getWidget(widgetType);
                this.widgets[widgetType] = widget.get({}, function(widgets) {
                    //console.log(widgets);
                });

                return this.widgets[widgetType];
            }
        };
    }]);