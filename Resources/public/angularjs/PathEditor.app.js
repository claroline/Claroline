/**
 * Editor App
 * This is this module which assemble others to create the Path Editor
 */
(function () {
    'use strict';

    angular.module('PathEditorApp', [
        'ngSanitize',
        'ui.bootstrap',
        'pageslide-directive',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ui.tree',

        'UtilsModule',
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