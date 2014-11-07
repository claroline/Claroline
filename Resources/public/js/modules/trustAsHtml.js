'use strict';

var appFilters = angular.module('app.filters', []);

appFilters
    .filter('trustAsHtml', ["$sce", function($sce) {
        return function(val) {
            return $sce.trustAsHtml(val);
        };
    }]);