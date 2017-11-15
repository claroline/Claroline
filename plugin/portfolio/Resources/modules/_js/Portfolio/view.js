import angular from 'angular/index'

import 'angular-sanitize'
import '#/main/core/html-truster/module'

var viewApp = angular.module('viewApp', ['ngSanitize', 'gridster', 'ui.html-truster'])

// Bootstrap portfolio application
angular.element(document).ready(function () {
  angular.bootstrap(document, ['viewApp'], {strictDi: true})
})

viewApp.controller('viewCtrl', ['$scope', function ($scope) {
  $scope.gridsterOptions = {
    columns:    16, // the width of the grid, in columns
    swapping:   true, // whether or not to have items of the same size switch places instead of pushing down if they are the same size
    floating:   true, // whether to automatically float items up so they stack (you can temporarily disable if you are adding unsorted items with ng-repeat)
    margins:    [10, 10], // the pixel distance between each widget
    minColumns: 1, // the minimum columns the grid must have
    minRows:    1, // the minimum height of the grid, in rows
    maxRows:    100,
    resizable: {
      enabled: false
    },
    draggable: {
      enabled: false
    }
  }
  $scope.portfolioWidgets = window.widgets
}])
