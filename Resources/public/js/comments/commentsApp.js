'use strict';

var commentsApp = angular.module('commentsApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce',
    'app.translation', 'app.interpolator', 'app.directives']);

commentsApp.config(["$httpProvider", function($http) {
    var elementToRemove = ['title', 'id', 'type', 'unreadComments'];

    $http.defaults.transformRequest.push(function(data) {
        data = angular.fromJson(data);
        angular.forEach(data, function(element, index) {
            if(elementToRemove.inArray(index)) {
                delete data[index];
            }
        });
        return JSON.stringify(data);
    });
}]);
commentsApp.value('assetPath', window.assetPath);