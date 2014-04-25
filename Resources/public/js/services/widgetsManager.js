'use strict';

portfolioApp
    .factory('widgetsManager', ['portfolioService', "widgetsConfig", function(portfolioService, widgetsConfig){
        return {
            widgets: {},
            init: function(widgets) {
                this.initWidgets(widgets);
            },
            initWidgets: function(widgets) {
                angular.forEach(widgetsConfig.getTypes(), function(type) {
                    this.widgets[type] = [widgets[type]] || [];
                }, this);
            }
        };
    }]);