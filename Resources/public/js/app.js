'use strict';

var portfolioApp = angular.module('portfolioApp', ['ngResource', 'ngSanitize']);

// Bootstrap portfolio application
angular.element(document).ready(function() {
    angular.bootstrap(document, ['portfolioApp']);
});