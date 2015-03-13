(function () {
    'use strict';

    angular.module('TemplateModule').directive('pathTemplateList', [
        'TemplateService',
        function (TemplateService) {
            return {
                restrict: 'E',
                replace: true,
                controller: 'TemplateListCtrl',
                controllerAs: 'templateListCtrl',
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Template/Partial/list.html',
                scope: {},
                link: function (scope, element, attrs, templateListCtrl) {
                    // Load templates
                    TemplateService.all().then(function (data) {
                        templateListCtrl.templates = data;
                    });
                }
            }
        }
    ]);
})();