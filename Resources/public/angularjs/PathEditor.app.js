/**
 * Editor App
 * This is this module which assemble others to create the Path Editor
 */
(function () {
    'use strict';

    angular.module('PathEditorApp', [
        'ngSanitize',
        'ngRoute',
        'ui.bootstrap',
        'pageslide-directive',
        'ui.tinymce',
        'ui.translation',
        'ui.tree',

        'AlertModule',
        'ClipboardModule',
        'ConfirmModule',
        'HistoryModule',
        'PathSummaryModule',
        'PathModule',
        'StepModule',
        'TemplateModule'
    ]);
})();