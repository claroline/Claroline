/**
 * Editor module
 * This is this module which assemble other modules to create the Path Editor
 */
(function () {
    'use strict';

    angular.module('EditorModule', [
        'ngSanitize',
        'ui.bootstrap',
        'ui.pageslide',
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
        'AlertFactory',
        function (AlertFactory) {
            // Get some data from symfony
            var alertTypes = [ 'info', 'success', 'warning', 'danger', 'alert' ];
            if (EditorApp.alerts) {
                for (var i = 0; i < alertTypes.length; i++) {
                    if (EditorApp.alerts[alertTypes[i]]) {
                        var alerts = EditorApp.alerts[alertTypes[i]];

                        for (var j = 0; j < alerts.length; j++) {
                            AlertFactory.addAlert(alertTypes[i], alerts[j]);
                        }
                    }
                }
            }
        }
    ]);
})();