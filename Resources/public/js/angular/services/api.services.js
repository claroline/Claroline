var clarolineAPI = angular.module('clarolineAPI', []);

clarolineAPI.config(function ($httpProvider) {
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
        },
        //replace element in array whose id is element.id
        replaceById: function(element, elements) {
            var index = null;

            for (var i = 0; i < elements.length; i++) {
                if (element.id === elements[i].id) {
                    index = i;
                    break;
                }
            }

            if (index) {
                elements[index] = element;
            }

            return elements;
        }
    }
});