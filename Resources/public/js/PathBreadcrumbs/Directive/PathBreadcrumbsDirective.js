(function () {
    'use strict';

    angular.module('PathBreadcrumbsModule').directive('pathBreadcrumbsShow', [
        function PathBreadcrumbsShowDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathBreadcrumbsShowCtrl,
                controllerAs: 'pathBreadcrumbsShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathBreadcrumbs/Partial/show.html',
                scope: {

                },
                bindToController: true
            };
        }
    ]);
})();