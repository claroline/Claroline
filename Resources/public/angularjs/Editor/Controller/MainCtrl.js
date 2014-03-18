'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, HistoryFactory, ClipboardFactory, PathFactory, AlertFactory, ResourceFactory) {
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
    
    /**
     * Display step in the preview zone
     * @returns void
     */
    $scope.setPreviewStep = function(step) {
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

    // Current displayed step in preview zone
    $scope.edit = {};
    $scope.edit.preview = false;
    $scope.previewStep = null;
    if (null === $scope.previewStep) {
        $scope.setPreviewStep();
    }

    // Store Preview step state before start editing to be able to cancel editing
    $scope.previewStepBackup = {};

    /**
     * Display edit step form
     * @returns void
     */
    $scope.editStep = function(step) {
        // Backup step
        $scope.previewStepBackup = jQuery.extend(true, {}, step);
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
}