/**
 * Truncate text filter
 */
(function () {
    'use strict';

    angular.module('UtilsModule').filter('truncate', [
        function () {
            return function (text, length, end) {
                var truncated = '';
                if (undefined !== text && null !== text && 0 !== text.length) {
                    // Default length for truncate : 50 characters
                    length = isNaN(length) ? 50 : length;
                    end = end === undefined ? '...' : end;

                    if (text.length <= length || text.length - end.length <= length) {
                        truncated = text;
                    }
                    else {
                        truncated = String(text).substring(0, length - end.length) + end;
                    }
                }

                return truncated;
            };
        }
    ]);
})();