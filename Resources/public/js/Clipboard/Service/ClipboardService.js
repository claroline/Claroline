/**
 * Clipboard Service
 */
(function () {
    'use strict';

    angular.module('ClipboardModule').factory('ClipboardService', [
        function ClipboardService() {
            // Clipboard content
            var clipboard = null;

            var disabled = {
                copy:  false,
                paste: true
            };

            return {
                /**
                 * Empty clipboard
                 *
                 * @returns ClipboardService
                 */
                clear: function () {
                    clipboard = null;

                    // Disable paste buttons
                    disabled.paste = true;

                    return this;
                },

                getDisabled: function () {
                    return disabled;
                },

                /**
                 * Copy selected steps into clipboard
                 *
                 * @param {object}   data
                 * @param {function} [callback] a callback to execute on data before add them into clipboard
                 * @returns ClipboardService
                 */
                copy: function (data, callback) {
                    var tempData = angular.copy(data)

                    if (typeof callback === 'function') {
                        // Process data before copy them into clipboard
                        callback(tempData);
                    }

                    // Store processed data into clipboard
                    clipboard = tempData;

                    // Disabled paste buttons
                    disabled.paste = false;

                    return this;
                },

                /**
                 * Paste data form clipboard into destination
                 *
                 * @param {object}   destination
                 * @param {function} [callback] a callback to execute on data to paste
                 * @returns ClipboardService
                 */
                paste: function (destination, callback) {
                    // Can paste only if clipboard is not empty
                    if (null !== clipboard) {
                        var dataCopy = angular.copy(clipboard);

                        if (typeof callback === 'function') {
                            // Process data before paste them
                            callback(dataCopy);
                        }

                        // Push processed data into
                        destination.push(dataCopy);
                    }

                    return this;
                }
            };
        }
    ]);
})();