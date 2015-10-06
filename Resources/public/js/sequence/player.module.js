(function () {
    'use strict';

    // exercise player module
    var SequencePlayerApp = angular.module('SequencePlayerApp', [
        'ngSanitize',
        'ui.bootstrap',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ngBootbox',
        'Common',
        'Sequence'
    ]);
    SequencePlayerApp.filter(
            'unsafe',
            function ($sce) {
                return $sce.trustAsHtml;
            });
})();