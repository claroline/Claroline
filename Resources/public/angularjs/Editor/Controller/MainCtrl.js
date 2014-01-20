'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, $http, $window, $location, $modal, HistoryFactory, ClipboardFactory, PathFactory, StepFactory, AlertFactory) {
    $scope.path = EditorApp.currentPath;
    PathFactory.setPath($scope.path);
        
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    // Update History if needed
    if (-1 === HistoryFactory.getHistoryState()) {
        HistoryFactory.update($scope.path);
    }
    
    $scope.alerts = AlertFactory.getAlerts();

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
    };
}