(function () {
    'use strict';

    // exercise player module
    var SequencePlayerApp = angular.module('SequencePlayerApp', [
        'ngSanitize',
        'angular-loading-bar',
        'ui.bootstrap',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ngBootbox',
        'Common',
        'Sequence',
        'Correction'
    ]);
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