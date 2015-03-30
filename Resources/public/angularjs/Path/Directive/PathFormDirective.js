/**
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').directive('pathForm', [
        '$window',
        'HistoryService',
        function ($window, HistoryService) {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PathFormCtrl',
                controllerAs: 'pathFormCtrl',
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Path/Partial/form.html',
                scope: {
                    path: '='
                },
                link: function (scope, element, attrs, pathFormCtrl) {
                    scope.$watch('path', function (newValue) {
                        console.log('path modified');

                        if (typeof newValue === 'string') {
                            pathFormCtrl.path = JSON.parse(newValue);
                        } else {
                            pathFormCtrl.path = newValue;
                        }
                    });

                    // Display a confirm on page close with pending modifications
                    function closeEditor(event) {
                        if (!HistoryService.isEmpty()) {
                            var confirmMessage = Translator.trans('save_path_changes', {}, 'path_editor');

                            if (event) {
                                event.returnValue = confirmMessage;
                            }

                            return confirmMessage;
                        }
                    }

                    window.addEventListener('beforeunload', closeEditor);

                    // Unbind event on directive destroy
                    scope.$on('$destroy', function handleDestroyEvent() {
                        window.removeEventListener('beforeunload', closeEditor);
                    });
                }
            };
        }
    ]);
})();