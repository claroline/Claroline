'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, $http, $q, $route, $location, $modal, HistoryFactory, ClipboardFactory, PathFactory, StepFactory, AlertFactory) {
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;

    // Set active tab
    $scope.activeTab = 'Global';
    $scope.$on('$routeChangeSuccess', function(event, current, previous) {
        $scope.activeTab = current.activeTab;
    });

    $scope.alerts = AlertFactory.getAlerts();
    $scope.path = null;
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
     * Save Path modifications in DB
     * @returns void
     */
    $scope.save = function(path) {
        var method = null;
        var route = null;

        // issue #62 :
        // Quand on veut ajouter un parcours, deux zones sont affichées : "Name" et "Description" et ces zones sont valorisées.
        // Quand on clique dessus, les valeurs par défaut ne s'effacent pas.

        // Init var
        var name = path.name;

        // Conditions to create a test :
        // Name is required, not null and not undefined (one character is not ok for exemple)
        // If test is OK then I add this path ...
        if (undefined != name && null != name && path.name.length != 0) {
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
                    // Create success
                    AlertFactory.addAlert('success', 'Path created.');
                }

                EditorApp.pathId = data;
            })
            .error(function(data, status) {
                AlertFactory.addAlert('danger', 'Error while saving Path.');
            });

            // Clear history to avoid possibility to get a history state without path ID
            HistoryFactory.clear();
        }
        // else I put a message on the screen and I display the previous screen.
        else
        {
            AlertFactory.addAlert('danger', 'Path Name is empty or undefined.');
            method = 'POST';
            route = Routing.generate('innova_path_add_path');
        }

    };
}