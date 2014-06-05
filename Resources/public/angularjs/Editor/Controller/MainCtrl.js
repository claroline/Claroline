'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, $modal, HistoryFactory, ClipboardFactory, PathFactory, AlertFactory, ResourceFactory) {
    $scope.path = EditorApp.currentPath;
    PathFactory.setPath($scope.path);
    
    if (null === $scope.path.name || $scope.path.name.length === 0) {
        // Add default name to Root step
        if (undefined != $scope.path.steps[0]) {
            $scope.path.steps[0].name = Translator.get('path_editor:root_default_name');
        }
    }

    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    // Update History if needed
    if (-1 === HistoryFactory.getHistoryState()) {
        HistoryFactory.update($scope.path);
    }
    
    $scope.alerts = AlertFactory.getAlerts();

    // Store current previewed step
    $scope.previewStep = null;

    $scope.saveAndClose = false;

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
        HistoryFactory.undo();
        $scope.path = PathFactory.getPath();

        $scope.updatePreviewStep();
    };

    /**
     * Redo last history modifications
     * @returns void
     */
    $scope.redo = function() {
        HistoryFactory.redo();
        $scope.path = PathFactory.getPath();

        $scope.updatePreviewStep();
    };

    $scope.closeEditor = function (returnUrl) {
        if (0 === HistoryFactory.getHistoryState()) {
            // Path is not modified => exit without confirm
            window.location = returnUrl;
        }
        else {
            // There are pending modifications => ask for confirmation to know what to do
            // Display confirm modal
            var modalInstance = $modal.open({
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Editor/Partial/confirm-exit.html',
                controller: 'ConfirmExitModalCtrl'
            });

            modalInstance.result.then(function(method) {
                if ('save' === method) {
                    $scope.saveAndClose = true;

                    // Force submit the form
                    document[EditorApp.formName].submit();
                }
                else if ('discard') {
                    window.location = returnUrl;
                }

                return true;
            });
        }
    };
}