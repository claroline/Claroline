var locationManager = angular.module('LocationManager', ['data-table', 'clarolineAPI', 'ui.bootstrap']);
var translator = window.Translator;

var translate = function(key) {
    return translator.trans(key, {}, 'platform');
}

locationManager.config(function ($httpProvider) {
    $httpProvider.interceptors.push(function ($q) {
        return {
            'request': function(config) {
                $('.please-wait').show();

                return config;
            },
            'requestError': function(rejection) {
                $('.please-wait').hide();

                return $q.reject(rejection);
            },  
            'responseError': function(rejection) {
                $('.please-wait').hide();

                return $q.reject(rejection);
            },
            'response': function(response) {
                $('.please-wait').hide();

                return response;
            }
        };
    });
});

locationManager.controller('LocationController', function(
    $scope,
    $log,
    $http,
    $modal,
    $compile,
    $rootElement,
    locationAPI,
    clarolineAPI
    ) {

    $scope.createForm = function() {
        $modal.open({
            templateUrl: Routing.generate('claro_admin_location_create_form'),
            controller: 'LocationController'
        });
    }

    $scope.submit = function() {
        console.log($scope.newLocation);
    }

    $scope.locations = undefined;
    $scope.newLocation = {};

    locationAPI.findAll().then(function(d) {
        $scope.locations = d.data;
    });

    $scope.columns = [
        {
            name: translate('name'),
            prop: 'name',
            width: 250, 
            canAutoResize: false
        },
        {
            name: translate('address'),
            width: 300,
            cellRenderer: function() {
                return '<div>{{ $row.street_number}}, {{ $row.street }}, {{ $row.pc }}, {{ $row.town }}, {{ $row.country }}</div>'
            }
        }
    ];

    $scope.dataTableOptions = {
        scrollbarV: true,
        columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        columns: $scope.columns
    };
});

locationManager.directive('locationsmanager', [
    function locationsmanager() {
        return {
            restrict: 'E',
            templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/location/views/locationsmanager.html',
            replace: true
        }
    }
]);

locationManager.factory('locationAPI', function($http) {
    return {
        findAll: function() {
            return $http.get(Routing.generate('api_get_locations'));
        }
    }
});