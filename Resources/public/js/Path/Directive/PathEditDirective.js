/**
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').directive('pathEdit', [
        'HistoryService',
        function PathEditDirective(HistoryService) {
            return {
                restrict: 'E',
                replace: true,
                controller: PathEditCtrl,
                controllerAs: 'pathEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Path/Partial/edit.html',
                scope: {
                    id        : '@', // ID of the path
                    path      : '=', // Data of the path
                    modified  : '@', // Is Path have pending modifications ?
                    published : '@'  // Is path published ?
                },
                bindToController: true
            };
        }
    ]);
})();