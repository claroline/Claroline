'use strict';

var widgetsApp = angular.module('widgetsApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce',
    'ui.resourcePicker', 'ui.badgePicker', 'ui.datepicker', 'ui.dateTimeInput', 'mgcrea.ngStrap.popover',
    'ui.bootstrap.collapse', 'app.translation', 'app.interpolator', 'app.directives']);

widgetsApp.config(["$httpProvider", function($http) {
    var elementToRemove = ['views', 'isCollapsed', 'isEditing', 'isUpdating', 'isDeleting', 'isNew'];

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
widgetsApp.value('assetPath', window.assetPath);