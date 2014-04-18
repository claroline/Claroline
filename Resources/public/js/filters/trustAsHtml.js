'use strict';

portfolioApp
    .filter('trustAsHtml', function($sce) {
        return function(val) {
            return $sce.trustAsHtml(val);
        };
    });