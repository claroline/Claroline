var controller = function(locationAPI, $scope, locations, location, $uibModalStack, $uibModal, clarolineAPI) {
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
};

angular.module('LocationManager').controller('EditModalController', controller);