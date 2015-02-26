/**
 * Main Controller
 */
(function () {
    'use strict';

    angular.module('EditorModule').controller('MainCtrl', [
        '$scope',
        '$modal',
        '$timeout',
        'HistoryFactory',
        'ClipboardFactory',
        'PathFactory',
        'AlertFactory',
        'ResourceFactory',
        function ($scope, $modal, $timeout, HistoryFactory, ClipboardFactory, PathFactory, AlertFactory, ResourceFactory) {
            $scope.path = EditorApp.currentPath;
            PathFactory.setPath($scope.path);
            if (null === $scope.path.name || $scope.path.name.length === 0) {
                // Add default name to Root step
                if (undefined != $scope.path.steps[0]) {
                    $scope.path.steps[0].name = Translator.trans('root_default_name', {}, 'path_editor');
                }
            }

            // Store symfony base partials route
            $scope.webDir = EditorApp.webDir;

            // Update History if needed
            if (-1 === HistoryFactory.getHistoryState()) {
                HistoryFactory.update($scope.path);
            }

            // Store current previewed step
            $scope.previewStep = null;

            $scope.toSaveAndClose = false;
            $scope.toPublishAndPreview = false;

            $scope.historyDisabled = HistoryFactory.getDisabled();

            /**
             * Update History when general data change
             */
            $scope.updateHistory = function() {
                HistoryFactory.update($scope.path);
            };

            /**
             * Display step in the preview zone
             * @returns void
             */
            $scope.setPreviewStep = function(step) {
                // We are not editing a step => we can change the preview
                var isRootStep = false;
                var rootStep = null;
                if (undefined !== $scope.path && null !== $scope.path && undefined !== $scope.path.steps[0]) {
                    rootStep = $scope.path.steps[0];
                }

                if (step) {
                    $scope.previewStep = step;
                    if (step.id === rootStep.id) {
                        isRootStep = true;
                    }
                }
                else if (rootStep) {
                    $scope.previewStep = rootStep;
                    isRootStep = true;
                }

                $scope.stepIsRootNode = isRootStep;
                $scope.inheritedResources = ResourceFactory.getInheritedResources($scope.previewStep);
            };

            /**
             * Reload preview step to apply last changes
             * @returns void
             */
            $scope.updatePreviewStep = function() {
                // Update preview step
                var step = null;
                if (null !== $scope.previewStep) {
                    step = PathFactory.getStepById($scope.previewStep.id);
                }
                $scope.setPreviewStep(step);
            };

            if (null === $scope.previewStep) {
                $scope.setPreviewStep();
            }

            /**
             * Copy data into clipboard
             * @returns void
             */
            $scope.copy = function(data) {
                ClipboardFactory.copy(data);
            };

            /**
             * Paste current clipboard content
             * @returns void
             */
            $scope.paste = function(step) {
                ClipboardFactory.paste(step);
                HistoryFactory.update($scope.path);
            };

            /**
             * Undo last user modifications
             * @returns void
             */
            $scope.undo = function() {
                if (HistoryFactory.canUndo()) {
                    HistoryFactory.undo();
                    $scope.path = PathFactory.getPath();

                    $scope.updatePreviewStep();
                }
            };

            /**
             * Redo last history modifications
             * @returns void
             */
            $scope.redo = function() {
                if (HistoryFactory.canRedo()) {
                    HistoryFactory.redo();
                    $scope.path = PathFactory.getPath();

                    $scope.updatePreviewStep();
                }
            };

            /**
             * Submit the Path form
             * @returns void
             */
            $scope.save = function () {
                document[EditorApp.formName].submit();
            };

            /**
             * Add a flag to the form to redirect user to the list of paths and save the form
             * @returns void
             */
            /*$scope.saveAndClose = function () {
                $scope.toSaveAndClose = true;

                $scope.$apply();

                // Save the Path
                $scope.save();
            };*/

            /**
             * Add a flag to the form to publish the Path and redirect User to Player and save the form
             * @returns void
             */
            $scope.publishAndPreview = function () {
                $scope.toPublishAndPreview = true;

                $scope.$apply();

                // Save the Path
                $scope.save();
            };

            /**
             * Close the Path editor
             * If there are pending modifications, ask to the user what to do : save changes, discard change or abort closing editor
             * @param {string} returnUrl
             * @returns void
             */
            $scope.closeEditor = function (returnUrl) {
                if (HistoryFactory.isEmpty()) {
                    // Path is not modified => exit without confirm
                    window.location = returnUrl;
                } else {
                    // There are pending modifications => ask for confirmation to know what to do
                    // Display confirm modal
                    var modalInstance = $modal.open({
                        templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Confirm/Partial/confirm-exit.html',
                        controller: 'ConfirmExitModalCtrl',
                        scope: $scope
                    });

                    modalInstance.result.then(function (method) {
                        if ('save' === method) {
                            $scope.$emit('saveAndClose');
                        } else if ('discard' === method) {
                            window.location = returnUrl;
                        }

                        return true;
                    });
                }
            };

            $scope.$on('saveAndClose', function (event) {
                $timeout(function() {
                    $scope.toSaveAndClose = true;
                }, 0);
                /*$scope.$apply(function () {*/

                /*});*/

                // Save the Path
                $scope.save();
            });
        }
    ]);
})();