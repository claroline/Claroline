/**
 * History Factory
 */
(function () {
    'use strict';

    angular.module('HistoryModule').factory('HistoryService', [
        function () {
            var disabled = {
                redo: true,
                undo: true
            };

            /**
             * History stack
             * @type {Array}
             */
            var history = [];

            /**
             * Index of current data into the history
             * @type {number}
             */
            var historyIndex = -1;

            return {
                getDisabled: function () {
                    return disabled;
                },

                /**
                 * Can the last action be undo ?
                 * @returns {boolean}
                 */
                canUndo: function () {
                    return !disabled.undo;
                },

                /**
                 * Can next action be redo ?
                 * @returns {boolean}
                 */
                canRedo: function () {
                    return !disabled.redo;
                },

                /**
                 * Is history empty ?
                 * @returns {boolean}
                 */
                isEmpty: function () {
                    return 0 <= historyIndex;
                },

                /**
                 * Restore default history state (= empty history)
                 * @returns {*}
                 */
                clear: function () {
                    disabled.redo = true;
                    disabled.undo = true;

                    history = [];
                    historyIndex = -1;

                    return this;
                },

                /**
                 * Store current data in history
                 * @param data
                 * @returns {*}
                 */
                update: function (data) {
                    // Increment history state
                    this.incrementIndex();

                    // Store a copy of data in history stack
                    var dataCopy = angular.copy(data);
                    history.push(dataCopy);

                    if (this.getIndex() !== 0) {
                        // History is not empty => enable the undo function
                        disabled.undo = false;
                    }
                    disabled.redo = true;

                    return this;
                },

                /**
                 * Get the last path state from history stack and set it as current path
                 * @returns {*}
                 */
                undo: function (currentData) {
                    // Decrement history state
                    this.decrementIndex();

                    var data = this.getFromHistory(historyIndex);

                    disabled.redo = false;
                    if (0 <= historyIndex) {
                        // History stack is empty => disable the undo function
                        disabled.undo = true;
                    }

                    // Returns a copy of the history
                    angular.copy(data, currentData);

                    return currentData;
                },

                /**
                 * Get the next history state from history stack and set it as current path
                 * @returns {*}
                 */
                redo: function (currentData) {
                    this.incrementIndex();

                    var data = this.getFromHistory(historyIndex);

                    disabled.undo = false;
                    if (historyIndex == history.length - 1) {
                        disabled.redo = true;
                    }

                    // Returns a copy of the history
                    angular.copy(data, currentData);

                    return currentData;
                },

                /**
                 *
                 * @returns {*}
                 */
                incrementIndex: function () {
                    // Increment history state
                    this.setIndex(this.getIndex() + 1);

                    return this;
                },

                /**
                 *
                 * @returns {*}
                 */
                decrementIndex: function() {
                    // Decrement history state
                    this.setIndex(this.getIndex() - 1);

                    return this;
                },

                /**
                 *
                 * @returns {number}
                 */
                getIndex: function () {
                    return historyIndex;
                },

                /**
                 *
                 * @param   {number} data
                 * @returns {*}
                 */
                setIndex: function (data) {
                    historyIndex = data;

                    return this;
                },

                /**
                 * Get state stored at position index in history stack
                 * @param   {number} index
                 * @returns {object}
                 */
                getFromHistory : function (index) {
                    return history[index];
                }
            };
        }
    ]);
})();