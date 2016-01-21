var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/location/Partial/locations_main.html',
        replace: true,
        controller: 'LocationController',
        controllerAs: 'lc'
    }
}

angular.module('LocationManager').directive('locationsmanager', [directive]);
