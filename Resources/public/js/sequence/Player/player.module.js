(function () {
    'use strict';

    var dependencies = [
        'ngSanitize',
        'ngRoute',
        'angular-loading-bar',
        'ui.bootstrap',
        'ui.translation',
        'ngBootbox',
        'Common',
        'Sequence',
        'Correction'
    ];
    // exercise player module
    var SequencePlayerApp = angular.module('SequencePlayerApp', dependencies);
    
    
    SequencePlayerApp.filter(
            'unsafe',
            function ($sce) {
                return $sce.trustAsHtml;
            });

    SequencePlayerApp.config(['cfpLoadingBarProvider', function (cfpLoadingBarProvider) {
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar = false;
            cfpLoadingBarProvider.spinnerTemplate = '<div class="loading">Loading&#8230;</div>';
        }]);
})();