/**
 * Activity Form directive
 * Directive Documentation : https://docs.angularjs.org/guide/directive
 */
(function () {
    'use strict';

    angular.module('Page').directive('pageEdit', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PageEditCtrl',
                controllerAs: 'pageEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/player/Page/Partials/page.edit.html',
                scope: {
                    pages: '='
                },
                link: function (scope, element, attr, pageEditCtrl) {
                    // set current page to first page
                    console.log('page edit directive link method called');
                    //pageEditCtrl.setCurrentPage(scope.pages[0]);
                    pageEditCtrl.setPages(scope.pages);
                    //console.log(scope.pages[0].description);
                    
                    //pageEditCtrl.setPlayer(scope.player);

                    /*
                    console.log(scope.player);
                    console.log(scope.last);
                    console.log(scope.first);
                    console.log(scope.pages);
                    */
                }
            };
        }
    ]);
})();
