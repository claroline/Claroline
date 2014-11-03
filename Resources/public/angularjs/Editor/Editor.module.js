/**
 * Editor module
 * This is this module which assemble other modules to create the Path Editor
 */
(function () {
    'use strict';

    angular.module('EditorModule', [
        'ngRoute',
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
    ]);
})();