/**
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').directive('pathShow', [
        function PathShowDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathShowCtrl,
                controllerAs: 'pathShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Path/Partial/show.html',
                scope: {
                    id              : '@', // ID of the path
                    path            : '=', // Data of the path
                    editEnabled     : '@', // User is allowed to edit current path ?
                    userProgression : '=?' // Progression of the current User
                },
                bindToController: true
            };
        }
    ]);
})();