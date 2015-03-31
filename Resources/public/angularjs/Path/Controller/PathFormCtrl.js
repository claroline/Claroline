/**
 * Path Controller
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').controller('PathFormCtrl', [
        'HistoryService',
        function PathFormCtrl(HistoryService) {
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
        }
    ]);
})();