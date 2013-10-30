'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, $http, $window, $location, $modal, HistoryFactory, ClipboardFactory, PathFactory, StepFactory, AlertFactory) {
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    // Set active tab
    $scope.activeTab = 'Global';
    $scope.$on('$routeChangeSuccess', function(event, current, previous) {
        $scope.activeTab = current.activeTab;
    });

    // Hide templates by default (it's only used in scenario with the tree view)
    $scope.templateSidebar = {};
    $scope.templateSidebar.show = true;
    
    $scope.alerts = AlertFactory.getAlerts();
    $scope.path = null;

    // Init placeholderX
    $scope.placeholderName = PathFactory.getPlaceholderName();
    $scope.placeholderDescription = PathFactory.getPlaceholderDescription();

    $scope.pathName = {};
    $scope.pathName.isUnique = true;
    
    $scope.initPath = function(path) {
        $scope.path = path;

        if ($scope.path.steps.length === 0) {
            // Missing root step => add it
            var rootStep = StepFactory.generateNewStep();
            rootStep.name = $scope.path.name;
            $scope.path.steps.push(rootStep);
        }

        // Update History if needed
        if (-1 === HistoryFactory.getHistoryState()) {
            HistoryFactory.update($scope.path);
        }
    };

    /**
     * Open Help modal
     * @returns void
     */
    $scope.openHelp = function() {
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'js/Help/Partial/help.html',
            controller: 'HelpModalCtrl'
        });
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
    };

    /**
     * Check if path name is unique for current user and current workspace
     */
    $scope.checkNameIsUnique = function() {
        $http({
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            url: Routing.generate('innova_path_check_unique_name'),
            data: 'pathName=' + $scope.path.name + '&workspaceId=' + EditorApp.workspaceId
        })
        .success(function (data) {
            $scope.pathName.isUnique = data == 'true' ? true : false;
        });
    };
    
    /**
     * Save Path modifications in DB
     * @returns void
     */
    $scope.save = function(path) {
        var method = null;
        var route = null;

        if (undefined == path.name || null == path.name || path.name.length == 0) {
            // Path name is empty
            AlertFactory.addAlert('danger', 'Path can not be saved : path name is empty.');
        }
        else if (!$scope.pathName.isUnique) {
            // Name is not unique
            AlertFactory.addAlert('danger', 'Path can not be saved : path name is already used in this workspace.');
        }
        else {
            // All is fine => process save
            var data = 'pathName=' + path.name + '&path=' + angular.toJson(path) + '&workspaceId=' + EditorApp.workspaceId;

            if (EditorApp.pathId) {
                // Update existing path
                method = 'PUT';
                route = Routing.generate('innova_path_edit_path', {id: EditorApp.pathId});
            }
            else {
                // Create new path
                method = 'POST';
                route = Routing.generate('innova_path_add_path');
            }

            $http({
                method: method,
                url: route,
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                data: data
            })
            .success(function (data) {
                if (EditorApp.pathId) {
                    // Update success
                    AlertFactory.addAlert('success', 'Path updated.');
                }
                else {
                    EditorApp.pathId = data;
                    
                    // Create success
                    AlertFactory.addAlert('success', 'Path created.');
                    
                    var newRoute = $location.protocol() + '://' + $location.host();
                    
                    // Get symfony route
                    newRoute += Routing.generate('innova_path_editor', {workspaceId: EditorApp.workspaceId, pathId: EditorApp.pathId});
                    
                    // Add angular part
                    newRoute += '#' + $location.path();
                    
                    // TODO : find a way to not reload page
                    $window.location = newRoute;
                }
            })
            .error(function(data, status) {
                AlertFactory.addAlert('danger', 'Error while saving Path.');
            });

            // Clear history to avoid possibility to get a history state without path ID
            HistoryFactory.clear();
        }
    };
}