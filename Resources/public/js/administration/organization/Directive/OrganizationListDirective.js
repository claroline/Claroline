var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/organization/views/Partial/organization_main.html',
        replace: true,
        controller: 'OrganizationController',
        controllerAs: 'oc'
    }
}

angular.module('OrganizationManager').directive('organizationslist', [directive]);