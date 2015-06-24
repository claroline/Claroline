'use strict';

var portfolioApp = angular.module('portfolioApp', ['ngResource', 'ngSanitize', 'ui.tinymce',
    'mgcrea.ngStrap.popover', 'app.translation', 'app.interpolator', 'app.directives', 'gridster', 'ui.bootstrap']);

portfolioApp.config(["$httpProvider", function($http) {
    var elementToRemove = ['views', 'isUpdating', 'isDeleting', 'id', 'type', 'unreadComments', 'toSave'];

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