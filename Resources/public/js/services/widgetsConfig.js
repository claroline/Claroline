'use strict';

portfolioApp
    .factory("widgetsConfig", [function(){
        return {
            config: JSON.parse(window.widgetsConfig),
            getTypes: function(excludeTitle) {
                var types = Object.keys(this.config);
                if (excludeTitle) {
                    types.remove('title');
                }
                return types;
            },
            isDeletable: function(widgetType) {
                return this.config[widgetType].isDeletable
            }
        };
    }]);