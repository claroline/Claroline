'use strict';

var portfolioApp = angular.module('portfolioApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce',
    'ui.resourcePicker', 'ui.badgePicker', 'ui.datepicker', 'ui.dateTimeInput', 'mgcrea.ngStrap.popover', 'app.translation', 'app.filters']);

portfolioApp.config(["$httpProvider", "$locationProvider", function($http) {
    var elementToRemove = ['views', 'editing', 'new', 'id', 'type', 'unreadComments'];

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
portfolioApp.value('assetPath', window.assetPath);

// Bootstrap portfolio application
angular.element(document).ready(function() {
    angular.bootstrap(document, ['portfolioApp'], {strictDi: true});
});