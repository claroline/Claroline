'use strict';

portfolioApp
    .filter('trans', ["translationService", function(translationService) {
        return function(key) {
            return translationService.trans(key);
        };
    }]);