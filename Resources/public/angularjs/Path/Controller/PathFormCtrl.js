/**
 * Path Controller
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').controller('PathFormCtrl', [
        'HistoryService',
        'PathService',
        function PathFormCtrl(HistoryService, PathService) {
            /**
             * ID of the current path
             * @type {number}
             */
            this.id = null;

            /**
             * Path to edit
             * @type {object}
             */
            this.path = {};

            this.modified  = false;
            this.published = false;
            this.unsaved   = false;

            /**
             * Current state of the history stack
             * @type {object}
             */
            this.historyDisabled = HistoryService.getDisabled();

            /**
             * Undo last action
             */
            this.undo = function () {
                if (HistoryService.canUndo()) {
                    // Inject history data
                    HistoryService.undo(this.path);
                }
            };

            /**
             * Redo last action
             */
            this.redo = function () {
                if (HistoryService.canRedo()) {
                    HistoryService.redo(this.path);
                }
            };

            /**
             * Save the path
             */
            this.save = function () {
                PathService.save(this.id, this.path).then(function () {
                    // Mark path as modified
                    this.modified = true;
                }.bind(this));
            };

            /**
             * Publish the path modifications
             */
            this.publish = function () {
                PathService.publish(this.id).then(function () {
                    this.modified  = false;
                    this.published = true;
                    this.unsaved   = false;
                }.bind(this));
            };

            /**
             * Preview path into player
             */
            this.preview = function () {
                if (!this.published) {
                    // Path not published => there is nothing to preview
                } else if (this.modified) {
                    // Path modified => modifications will not be visible before publishing
                } else {
                    // Open player to preview the path
                }
            };
        }
    ]);
})();