/**
 * History Factory
 */
(function () {
    'use strict';

    angular.module('HistoryModule').factory('HistoryFactory', [
        '$rootScope',
        'PathFactory',
        function ($rootScope, PathFactory) {
            var disabled = {
                redo: true,
                undo: true
            };

            // History stack
            var history = [];
            var historyState = -1;

            return {
                getDisabled: function () {
                    return disabled;
                },

                canUndo: function () {
                    return !disabled.undo;
                },

                canRedo: function () {
                    return !disabled.redo;
                },

                isEmpty: function () {
                    return 0 === historyState || -1 === historyState;
                },

                /**
                 * Restore default history state (= empty history)
                 * @returns HistoryFactory
                 */
                clear: function () {
                    disabled.redo = true;
                    disabled.undo = true;

                    history = [];
                    historyState = -1;

                    return this;
                },

                /**
                 * Store current path in history
                 * @param path
                 * @returns HistoryFactory
                 */
                update: function (path) {
                    // Increment history state
                    this.incrementHistoryState();

                    // Store path in history stack
                    var pathCopy = angular.extend({}, path);
                    history.push(pathCopy);

                    if (this.getHistoryState() !== 0) {
                        // History is not empty => enable the undo function
                        disabled.undo = false;
                    }
                    disabled.redo = true;

                    return this;
                },

                /**
                 * Get the last path state from history stack and set it as current path
                 * @returns HistoryFactory
                 */
                undo: function () {
                    // Decrement history state
                    this.decrementHistoryState();

                    var path = this.getPathFromHistory(historyState);

                    // Clone object
                    var pathCopy = angular.extend({}, path);

                    disabled.redo = false;
                    if (0 === historyState) {
                        // History stack is empty => disable the undo function
                        disabled.undo = true;
                    }

                    // Inject new path
                    PathFactory.setPath(pathCopy);

                    return this;
                },

                /**
                 * Get the next history state from history stack and set it as current path
                 * @returns HistoryFactory
                 */
                redo: function () {
                    // Increment history state
                    historyState++;

                    var path = this.getPathFromHistory(historyState);

                    // Clone object
                    var pathCopy = angular.extend({}, path);

                    disabled.undo = false;
                    if (historyState == history.length - 1) {
                        disabled.redo = true;
                    }

                    // Inject new path
                    PathFactory.setPath(pathCopy);

                    return this;
                },

                /**
                 *
                 * @returns HistoryFactory
                 */
                incrementHistoryState: function () {
                    // Increment history state
                    this.setHistoryState(this.getHistoryState() + 1);

                    return this;
                },

                /**
                 *
                 * @returns {HistoryFactory}
                 */
                decrementHistoryState: function() {
                    // Decrement history state
                    this.setHistoryState(this.getHistoryState() - 1);

                    return this;
                },

                /**
                 *
                 * @returns {number}
                 */
                getHistoryState: function () {
                    return historyState;
                },

                /**
                 *
                 * @param   {number}         data
                 * @returns {HistoryFactory}
                 */
                setHistoryState: function (data) {
                    historyState = data;

                    return this;
                },

                /**
                 * Get path state stored at position index in history stack
                 * @param   {number} index
                 * @returns {object}
                 */
                getPathFromHistory : function (index) {
                    return history[index];
                }
            };
        }
    ]);
})();