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
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/player/Page/Partials/edit.html',
                scope: {
                    page: '=',
                    player: '='                    
                },
                link: function (scope, element, attr, pageEditCtrl) {
                    /*pageEditCtrl.sayHello(scope.name);
                    console.log('page ' + scope.page.title);
                    console.log('player id ' + scope.player.id);
                    console.log('player name ' + scope.player.name);
                    console.log('player description ' + scope.player.description);*/
                }
            };
        }
    ]);
})();


