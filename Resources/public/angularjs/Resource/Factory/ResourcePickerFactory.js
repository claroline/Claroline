(function () {
    'use strict';

    angular.module('ResourceModule').factory('ResourcePickerFactory', [
        function () {
            var baseConfig = {
                isPickerMultiSelectAllowed: false,
                webPath:                    EditorApp.webDir,
                appPath:                    EditorApp.appDir,
                directoryId:                EditorApp.wsDirectoryId,
                resourceTypes:              EditorApp.resourceTypes
            };

            return {
                getPicker: function (pickerName, callback, whiteList, blackList) {

                }
            };
        }
    ]);
})();