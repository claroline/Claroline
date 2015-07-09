/**
 * Activity Form directive
 * Directive Documentation : https://docs.angularjs.org/guide/directive
 */
(function () {
    'use strict';

    angular.module('Player').directive('playerEdit', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PlayerEditCtrl',
                controllerAs: 'playerEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/player/Player/Partials/player.edit.html',
                scope: {
                    player: '='
                },
                link: function (scope, element, attr, playerEditCtrl) {
                    // set current page to first page
                    console.log('player directive link method called');
                    playerEditCtrl.setPlayer(scope.player);                   
                    
                }
            };
        }
    ]);
})();


