'use strict';

portfolioApp
    .factory("widgetsConfig", [function(){
        return {
            config: JSON.parse(window.widgetsConfig),
            getTypes: function() {
                return Object.keys(this.config);
            }
        };
    }]);