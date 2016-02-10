var clarolineAPI = angular.module('clarolineAPI', ['ui.bootstrap', 'ui.bootstrap.tpls', 'ngSanitize']);
var modal = window.Claroline.Modal;

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

clarolineAPI.factory('clarolineAPI', function($http, $httpParamSerializerJQLike, $uibModal) {
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
            var serialized = angular.copy(parameters);
            //remove the id too
            delete serialized.id;

            //quick and dirty fix for array of checkboxes. It probably won't work for (multi)select and radio buttons but... hey. It's a start.
            //I do all of this because by default, the serializer expects an array for sf2 BUT ng-init will do an object and it won't work.
            for (var key in parameters) {
                if (typeof parameters[key] === 'object') {
                    var array = [];
                    var object = parameters[key];

                    for (var el in object) {
                        if (object[el] === true) {
                            array.push(el);
                        }
                    }

                    serialized[key] = array;
                }
            }

            ///q&d fixe for submission
            data[formName] = serialized;

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
        },
        removeElements: function(toRemove, elements) {
            var idxs = [];

            for (var i = 0; i < toRemove.length; i++) {
                idxs.push(elements.indexOf(toRemove[i]));
            }

            for (var i = 0; i < idxs.length; i++) {
                elements.splice(idxs[i] - i, 1);
            }

            return elements;
        },
        confirm: function(urlObject, callback, title, content) {
            $uibModal.open({
                templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/services/views/confirm_modal.html',
                controller: 'ConfirmModalController',
                resolve: {
                    callback: function() {return callback;},
                    urlObject: function() {return urlObject;},
                    title: function() {return title;},
                    content: function() {return content;}
                }
            });
        },
        generateQueryString: function(array, name) {
            var qs = '';

            for (var i = 0; i < array.length; i++) {
                var id = (array[i].id) ? array[i].id: array[i];
                qs += name + '[]=' + id + '&';
            }

            return qs;
        }
    }
});

clarolineAPI.controller('ConfirmModalController', function(callback, urlObject, title, content, $http, $scope, $uibModalInstance, $sce) {
    $scope.title = title;
    $scope.content = content;

    $scope.submit = function() {
        $http(urlObject).then(
            function successHandler(d) {
                $uibModalInstance.close();
                callback(d.data);
            },
            function errorHandler (d) {
                alert('An error occured');
                $uibModalInstance.close();
            }
        );
    }
});