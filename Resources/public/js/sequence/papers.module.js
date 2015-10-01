(function () {
    'use strict';

    // exercise papers module
    angular.module('PapersApp', [
        'ngSanitize',
        'ui.bootstrap',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ngBootbox',
        'Common'
    ])
    .filter(
    'unsafe', 
    function($sce) { 
        return $sce.trustAsHtml; 
    });
})();