/**
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').directive('pathForm', [
        'HistoryService',
        'PathService',
        function PathFormDirective(HistoryService, PathService) {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PathFormCtrl',
                controllerAs: 'pathFormCtrl',
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Path/Partial/path-form.html',
                scope: {
                    id          : '@', // ID of the path
                    path        : '=', // Data of the path
                    modified    : '@', // Is Path have pending modifications ?
                    published   : '@'  // Is path published ?
                },
                link: function (scope, element, attrs, pathFormCtrl) {
                    // Set controller variables
                    pathFormCtrl.id          = scope.id;
                    pathFormCtrl.path        = scope.path;
                    pathFormCtrl.modified    = scope.modified;
                    pathFormCtrl.published   = scope.published;

                    // Store ID of the Path
                    PathService.setId(pathFormCtrl.id);

                    // Update history each time a path is changed
                    scope.$watch('path', function (newValue) {
                        var empty   = HistoryService.isEmpty();
                        var updated = HistoryService.update(newValue);

                        if (!empty && updated) {
                            // Initialization is already done, so mark path as unsaved for each modification
                            pathFormCtrl.unsaved = true;
                        }

                        // Store path
                        console.log('update path');
                        PathService.setPath(newValue);
                    }, true);
                }
            };
        }
    ]);
})();