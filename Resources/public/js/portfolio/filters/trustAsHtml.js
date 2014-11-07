'use strict';

portfolioApp
    .filter('trustAsHtml', ["$sce", function($sce) {
        return function(val) {
            return $sce.trustAsHtml(val);
        };
    }]);