'use strict';

portfolioApp
    .factory('widgetsConfig', [function(){
        return {
            getTypes: function() {
                return ['title', 'userInformation'];
            }
        };
    }]);