var clarolineAPI = angular.module('clarolineAPI', []);

clarolineAPI.factory('clarolineAPI', function($http, $httpParamSerializerJQLike) {
    return {
        formEncode: function(formName, parameters) {
            var data = new FormData();

            for (var key in parameters) {
                data.append(formName + '[' + key + ']', parameters[key]);
            }

            return data;
        },
        formSerialize: function(formName, parameters) {
            var data = {};
            data[formName] = parameters;

            return $httpParamSerializerJQLike(data);
        }
    }
});