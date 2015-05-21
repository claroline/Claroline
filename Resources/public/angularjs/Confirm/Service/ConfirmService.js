(function () {
    'use strict';

    angular.module('ConfirmModule').factory('ConfirmService', [
        '$modal',
        function ConfirmService($modal) {
            var webDir = EditorApp.webDir;

            return {
                open: function (options, callback) {
                    var title, message, confirmButton;

                    // Get modale options
                    if (options) {
                        if (options.title) {
                            title = options.title;
                        }

                        if (options.message) {
                            message = options.message;
                        }

                        if (options.confirmButton) {
                            confirmButton = options.confirmButton;
                        }
                    }

                    // Display confirm modal
                    var modalInstance = $modal.open({
                        templateUrl: webDir + 'bundles/innovapath/angularjs/Confirm/Partial/confirm.html',
                        controller: 'ConfirmModalCtrl',
                        resolve: {
                            title:         function () { return title },
                            message:       function () { return message },
                            confirmButton: function () { return confirmButton }
                        }
                    });

                    // If callback defined, execute it on confirm
                    if (callback) {
                        modalInstance.result.then(callback);
                    }
                }
            }
        }
    ]);
})();