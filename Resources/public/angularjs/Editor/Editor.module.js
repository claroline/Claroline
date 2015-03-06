/**
 * Editor module
 * This is this module which assemble other modules to create the Path Editor
 */
(function () {
    'use strict';

    angular.module('EditorModule', [
        'ngSanitize',
        'ui.bootstrap',
        'pageslide-directive',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ui.tree',

        'TruncateModule',
        'AlertModule',
        'ClipboardModule',
        'ConfirmModule',
        'HistoryModule',
        'PathModule',
        'StepModule',
        'ResourceModule',
        'TemplateModule'
    ])
    .run([
        'AlertService',
        function (AlertService) {
            // Get some data from symfony
            var alertTypes = [ 'info', 'success', 'warning', 'danger', 'alert' ];
            if (EditorApp.alerts) {
                for (var i = 0; i < alertTypes.length; i++) {
                    if (EditorApp.alerts[alertTypes[i]]) {
                        var alerts = EditorApp.alerts[alertTypes[i]];

                        for (var j = 0; j < alerts.length; j++) {
                            AlertService.addAlert(alertTypes[i], alerts[j]);
                        }
                    }
                }
            }
        }
    ]);
})();