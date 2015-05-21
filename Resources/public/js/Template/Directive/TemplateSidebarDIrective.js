(function () {
    'use strict';

    angular.module('TemplateModule').directive('pathTemplateSidebar', [
        'TemplateResource',
        function (TemplateResource) {
            return {
                restrict: 'E',
                replace: true,
                controller: 'TemplateSidebarCtrl',
                controllerAs: 'templateSidebarCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Template/Partial/sidebar.html',
                scope: {},
                link: function (scope, element, attrs, templateListCtrl) {
                    // Load templates
                    templateListCtrl.templates = TemplateResource.query();
                }
            }
        }
    ]);
})();