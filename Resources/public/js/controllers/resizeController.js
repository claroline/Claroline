var resizerApp = angular.module('resizerApp', ['utilitiesApp']);

resizerApp.controller( 'resizeController', ['$scope', function ( $scope ) {
    this.$resize = angular.noop;
}]);