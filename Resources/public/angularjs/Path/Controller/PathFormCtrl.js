/**
 * Path Controller
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').controller('PathFormCtrl', [
        'HistoryService',
        'ConfirmService',
        'PathService',
        function PathFormCtrl(HistoryService, ConfirmService, PathService) {
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

            this.summaryOpened = true;

            /**
             * Current state of the history stack
             * @type {object}
             */
            this.historyDisabled = HistoryService.getDisabled();

            /**
             * Open or close summary of the Path
             */
            this.toggleSummary = function () {
                this.summaryOpened = !this.summaryOpened;
            };

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
                    // Inject history data
                    HistoryService.redo(this.path);
                }
            };

            /**
             * Save the path
             */
            this.save = function () {
                PathService.save().then(function () {
                    // Mark path as modified
                    this.modified = true;
                    this.unsaved  = false;
                }.bind(this));
            };

            /**
             * Publish the path modifications
             */
            this.publish = function () {
                PathService.publish().then(function () {
                    this.modified  = false;
                    this.published = true;
                    this.unsaved   = false;
                }.bind(this));
            };

            /**
             * Preview path into player
             */
            this.preview = function () {
                if (this.published) {
                    // Path needs to be published at least once to be previewed

                    var url = Routing.generate('innova_path_player_wizard', {
                        id: this.id
                    });

                    if (this.modified || this.unsaved) {
                        // Path modified => modifications will not be visible before publishing so warn user
                        ConfirmService.open(
                            // Confirm options
                            {
                                title:         Translator.trans('preview_with_pending_changes_title',   {}, 'path_editor'),
                                message:       Translator.trans('preview_with_pending_changes_message', {}, 'path_editor'),
                                confirmButton: Translator.trans('preview_with_pending_changes_button',  {}, 'path_editor')
                            },

                            // Confirm success callback
                            function () {
                                window.open(url, '_blank');
                            }
                        );
                    } else {
                        // Open player to preview the path
                        window.open(url, '_blank');
                    }
                }
            };
        }
    ]);
})();