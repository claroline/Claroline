var service = function($http, clarolineAPI) {
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
};

angular.module('LocationManager').factory('locationAPI', service);