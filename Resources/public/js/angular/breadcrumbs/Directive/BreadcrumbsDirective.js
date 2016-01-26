var directive = function () {
    var bindings = {
        breadcrumbs: '='
    };

    return {
        bindToController: bindings,
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/breadcrumbs/Partial/breadcrumbs.html',
        replace: true,
        controller: 'ClarolineBreadcrumbsController',
        controllerAs: 'cbc'
    }
}

angular.module('ClarolineBreadcrumbs').directive('breadcrumbs', [directive]);