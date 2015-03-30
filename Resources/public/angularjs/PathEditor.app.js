/**
 * Editor App
 * This is this module which assemble others to create the Path Editor
 */
(function () {
    'use strict';

    angular.module('PathEditorApp', [
        'ngSanitize',
        'ui.bootstrap',
        'ui.pageslide',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ui.tree',

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