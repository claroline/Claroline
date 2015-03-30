(function () {
    'use strict';

    angular.module('PathModule').directive('pathStructure', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PathStructureCtrl',
                controllerAs: 'pathStructureCtrl',
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Path/Partial/structure.html',
                scope: {
                    structure: '='
                },
                link: function (scope, element, attrs, pathStructureCtrl) {
                    /*scope.$watch('structure', function (newValue) {
                        console.log('structure updated');

                        if (typeof newValue === 'string') {
                            pathStructureCtrl.structure = JSON.parse(newValue);
                        } else {
                            pathStructureCtrl.structure = newValue;
                        }
                    });*/
                }
            };
        }
    ]);
})();