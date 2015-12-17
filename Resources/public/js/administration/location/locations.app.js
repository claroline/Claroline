var locationManager = angular.module('LocationManager', ['data-table', 'clarolineAPI', 'ui.bootstrap']);
var translator = window.Translator;

var translate = function(key) {
    return translator.trans(key, {}, 'platform');
}

locationManager.config(function ($httpProvider) {
    $httpProvider.useApplyAsync(true);
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

locationManager.controller('CreateModalController', function(locationAPI, $scope, locations, $uibModalStack, $uibModal) {
    $scope.newLocation = {};

    $scope.submit = function() {
        locationAPI.create($scope.newLocation).then(
            function successHandler (d) {
                $uibModalStack.dismissAll();
                locations.push(d.data);
            },
            function errorHandler (d) {
                if (d.status === 400) {
                    $uibModalStack.dismissAll();
                    $uibModal.open({
                        template: d.data,
                        controller: 'CreateModalController',
                        resolve: {
                            locations: function() {
                                return locations;
                            }
                        }
                    })
                }
            }
        );
    }
});

locationManager.controller('LocationController', function(
    $scope,
    $http,
    $uibModal,
    $uibModalStack,
    locationAPI
    ) {

    $scope.locations = undefined;

    $scope.createForm = function() {
        $uibModal.open({
            templateUrl: Routing.generate('api_get_create_location_form', {'_format': 'html'}),
            controller: 'CreateModalController',
            resolve: {
                locations: function() {
                    return $scope.locations;
                }
            }
        });
    }

    locationAPI.findAll().then(function(d) {
        $scope.locations = d.data;
    });

    $scope.columns = [
        {
            name: translate('name'),
            prop: 'name',
            canAutoResize: false
        },
        {
            name: translate('address'),
            cellRenderer: function() {
                return '<div>{{ $row.street_number}}, {{ $row.street }}, {{ $row.pc }}, {{ $row.town }}, {{ $row.country }}</div>';
            }
        },
        {
            name: translate('actions'),
            cellRenderer: function() {
                return '<div><a class="btn-primary btn-xs" ng-click="editLocation($row)" style="margin-right: 8px;"><span class="fa fa-pencil-square-o"></span></a><a class="btn-danger btn-xs" ng-click="removeLocation($row)"><span class="fa fa-trash"></span></a></div>';
            }
        },
        {
            name: translate('coordinates'),
            cellRenderer: function() {
                '<div> blablabla + liens </div>'
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

locationManager.factory('locationAPI', function($http, clarolineAPI) {
    return {
        findAll: function() {
            return $http.get(Routing.generate('api_get_locations'));
        },
        create: function(newLocation) {
            var data = clarolineAPI.formSerialize('location_form', newLocation);

            return $http.post(
                Routing.generate('api_post_location', {'_format': 'html'}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        }
    }
});