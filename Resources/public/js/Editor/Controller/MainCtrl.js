'use strict';

/**
 * Main Controller
 */
var MainCtrlProto = [
    '$scope',
    '$routeParams',
    '$location',
    '$http',
    '$modal',
    'HistoryFactory',
    'PathFactory',
    'StepFactory',
    function($scope, $routeParams, $location, $http, $modal, HistoryFactory, PathFactory, StepFactory) {
        // Store symfony base partials route
        $scope.templateRoute = EditorApp.templateRoute;
        
        // Set active tab
        $scope.activeTab = 'Global';
        $scope.$on('$routeChangeSuccess', function(event, current, previous) {
            $scope.activeTab = current.activeTab;
        });
        
        // Load current path
        if (EditorApp.pathId) {
            // Edit existing path
            $http({
                method: 'GET',
                url: Routing.generate('innova_path_get_path', {id: EditorApp.pathId})
            })
            .success(function (data) {
                $scope.path = data;
                PathFactory.setPath($scope.path);
                
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
            })
            .error(function(data, status) {

            });
        }
        else {
            // Create new path
            $scope.path = PathFactory.generateNewPath();
            PathFactory.setPath($scope.path);
            
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
        }
        
        /**
         * Open Help modal
         * @returns void
         */
        $scope.openHelp = function() {
            var modalInstance = $modal.open({
                templateUrl: EditorApp.templateRoute + 'Help/help-modal.html',
                controller: 'HelpModalCtrl'
            });
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
         * 
         * @todo add confirm messages
         */
        $scope.save = function(path) {
            if (EditorApp.pathId) {
                // Update existing path
                $http({
                    method: 'PUT',
                    url: Routing.generate('innova_path_edit_path'),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                    data: 'path-id=' + EditorApp.pathId + '&path-name=' + path.name + '&path=' + angular.toJson(path) + '&workspace-id=' + EditorApp.workspaceId
                })
                .success(function (data) {
                    alert('success');
                });
            } 
            else {
                // Create new path
                $http({
                    method: 'POST',
                    url: Routing.generate('innova_path_add_path'),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                    data: 'path-name=' + path.name + '&path=' + angular.toJson(path) + '&workspace-id=' + EditorApp.workspaceId
                })
                .success(function (data) {
                    // Store generated ID
                    EditorApp.pathId = data;
                    alert('success');
                });
            }
            
            // Clear history to avoid possibility to get a history state without path ID
            HistoryFactory.clear();
        };
    }
];