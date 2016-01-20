var locationManager = angular.module('LocationManager', ['data-table', 'clarolineAPI', 'ui.bootstrap', 'ui.bootstrap.tpls']);
var translator = window.Translator;

var translate = function(key) {
    return translator.trans(key, {}, 'platform');
}

locationManager.controller('CreateModalController', function(locationAPI, $scope, locations, $uibModalStack, $uibModal) {
    $scope.location = {};

    $scope.submit = function() {
        locationAPI.create($scope.location).then(
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
                        bindToController: true,
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

locationManager.controller('EditModalController', function(locationAPI, $scope, locations, location, $uibModalStack, $uibModal, clarolineAPI) {
    $scope.location = {};

    $scope.submit = function() {
        locationAPI.update(location.id, $scope.location).then(
            function successHandler (d) {
                $uibModalStack.dismissAll();
                clarolineAPI.replaceById(d.data, locations);
            },
            function errorHandler (d) {
                if (d.status === 400) {
                    $uibModalStack.dismissAll();
                    $uibModal.open({
                        template: d.data,
                        controller: 'EditModalController',
                        resolve: {
                            locations: function() {
                                return locations;
                            },
                            location: function() {
                                return location;
                            }
                        }
                    })
                }
            }
        );
    }
});

locationManager.controller('LocationController', ['$http', 'locationAPI', '$uibModalStack', '$uibModal', function(
    $http,
    locationAPI,
    $uibModalStack,
    $uibModal
) {
    this.locations = undefined;

    var removeLocation = function(location) {
        var index = this.locations.indexOf(location);
        if (index > -1 ) this.locations.splice(index, 1);
    }.bind(this)

    this.createForm = function() {
        console.log('clickclick');
        $uibModal.open({
            templateUrl: Routing.generate('api_get_create_location_form', {'_format': 'html'}),
            controller: 'CreateModalController',
            resolve: {
                locations: function() {
                    return this.locations;
                }
            }
        });
    }.bind(this);

    this.editLocation = function(location) {
        $uibModal.open({
            //bust = no cache
            templateUrl: Routing.generate('api_get_edit_location_form', {'_format': 'html', 'location': location.id}) + '?bust=' + Math.random().toString(36).slice(2),
            controller: 'EditModalController',
            resolve: {
                locations: function() {
                    return this.locations;
                },
                location: function() {
                    return location;
                }
            }
        });
    }.bind(this)

    this.removeLocation = function(location) {
        locationAPI.delete(location.id).then(function(d) {
            removeLocation(location);
        });
    }

    locationAPI.findAll().then(function(d) {
        this.locations = d.data;
    }.bind(this));

    this.columns = [
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
                return '<button class="btn-primary btn-xs" ng-click="lc.editLocation($row)" style="margin-right: 8px;"><i class="fa fa-pencil-square-o"></i></button><button class="btn-danger btn-xs" ng-click="lc.removeLocation($row)"><i class="fa fa-trash"></i></button>';
            }
        },
        {
            name: translate('coordinates'),
            cellRenderer: function() {
                return '<div>' + translate('latitude') + ': {{ $row.latitude }} | ' + translate('longitude') + ': {{ $row.longitude }} </div>'
            }
        }
    ];

    this.dataTableOptions = {
        scrollbarV: true,
        columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        columns: this.columns
    };
}]);

locationManager.directive('locationsmanager', [
    function locationsmanager() {
        return {
            restrict: 'E',
            templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/location/views/locationsmanager.html',
            replace: true,
            controller: 'LocationController',
            controllerAs: 'lc'
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
        },
        delete: function(locationId) {
            return $http.delete(Routing.generate('api_delete_location', {'location': locationId}));
        },
        update: function(locationId, updatedLocation) {
            var data = clarolineAPI.formSerialize('location_form', updatedLocation);

            return $http.put(
                Routing.generate('api_put_location', {'location': locationId, '_format': 'html'}),
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            );
        }
    }
});