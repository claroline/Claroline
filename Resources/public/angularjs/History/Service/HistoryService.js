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
                    return -1 == historyIndex;
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
                 * @param   {object} data
                 * @returns {boolean}
                 */
                update: function (data) {
                    var updated = false;

                    // Get the last data stored in history to show if something has changed
                    var lastHistoryData     = this.getFromHistory(historyIndex);
                    var lastHistoryDataJson = angular.toJson(lastHistoryData); // Convert to JSON to compare to current data

                    // Create a clean copy of current data (e.g. without Angular JS custom properties)
                    var dataCopy     = angular.copy(data);
                    var dataCopyJson = angular.toJson(dataCopy); // Convert to JSON to compare to last history

                    if (this.isEmpty() || lastHistoryDataJson != dataCopyJson) {
                        // There are changes into data => add them to history
                        // Increment history state
                        this.incrementIndex();

                        // Store a copy of data in history stack
                        history.push(dataCopy);

                        if (this.getIndex() !== 0) {
                            // History is not empty => enable the undo function
                            disabled.undo = false;
                        }
                        disabled.redo = true;

                        updated = true;
                    }

                    return updated;
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

                    return this.restoreData(currentData, data);
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

                    return this.restoreData(currentData, data);
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
                    var data = null;
                    if (typeof history[index] !== 'undefined') {
                        data = history[index];
                    }

                    return data;
                },

                restoreData: function (destination, source) {
                    // Empty the destination object (we need to keep the reference to original object)
                    for (var prop in destination) {
                        if (destination.hasOwnProperty(prop)) {
                            delete destination[prop];
                        }
                    }

                    // Copy data into destination object
                    angular.extend(destination, source);

                    return destination;
                }
            };
        }
    ]);
})();