(function() {
    'use strict';
    angular
        .module('app')
        .run(['$rootScope', function($rootScope){
            $rootScope.pageLoaded = true;
        }]);
})();