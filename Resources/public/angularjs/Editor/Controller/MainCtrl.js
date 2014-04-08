'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, $modal, $location, HistoryFactory, ClipboardFactory, PathFactory, AlertFactory, StepFactory, ResourceFactory) {
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

    // Flag to know if we are editing a step (need to use an object to can access this var in child controllers)
    $scope.edit = {};
    $scope.edit.preview = false;

    // Store current previewed step
    $scope.previewStep = null;

    // Store Preview step state before start editing to be able to cancel editing
    $scope.previewStepBackup = {};

    $scope.saveAndClose = false;

    /**
     * Display step in the preview zone
     * @returns void
     */
    $scope.setPreviewStep = function(step) {
        if (!$scope.edit.preview) {
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
        }
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
     * Display edit step form
     * @returns void
     */
    $scope.editStep = function(step) {
        $scope.edit.preview = false;
        $scope.setPreviewStep(step);

        // Backup step
        $scope.previewStepBackup = jQuery.extend(true, {}, step);

        if (typeof step.who === 'undefined' || null === step.who || step.who.length === 0) {
            var whoDefault = StepFactory.getWhoDefault();
            step.who = whoDefault.id + "";
        }

        if (typeof step.where === 'undefined' || null === step.where || step.where.length === 0) {
            var whereDefault = StepFactory.getWhereDefault();
            step.where = whereDefault.id + "";
        }

        $scope.edit.preview = true;
    };

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