(function () {
    'use strict';

    angular.module('PathModule').controller('PathFormCtrl', [
        'HistoryService',
        'PathService',
        function PathFormCtrl(HistoryService, PathService) {
            /**
             * Path to edit
             * @type {object}
             */
            this.path = {};

            /**
             * Current state of the history stack
             * @type {object}
             */
            this.historyDisabled = HistoryService.getDisabled();

            this.undo = function () {

            };

            this.redo = function () {

            };

            this.save = function () {
                if (this.path.id) {

                } else {

                }
            };

            this.publishAndPreview = function () {

            };

            this.close = function () {
                /*{{ path('claro_workspace_open_tool', { workspaceId: workspace.id, toolName: 'innova_path' }) }}*/
            };
        }
    ]);
})();