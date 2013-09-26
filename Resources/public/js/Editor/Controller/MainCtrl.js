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
        if ($routeParams.id) {
            // Edit existing path
            if (!PathFactory.getPathInstanciated($routeParams.id)) {
                PathFactory.addPathInstanciated($routeParams.id);
                $http.get('../api/index.php/paths/' + $routeParams.id + '.json')
                    .success(function(data) {
                        // Store Path ID
                        data.id = $routeParams.id;

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
                    }
                );
            }
        }
        else {
            // Create new path
            $scope.path = PathFactory.generateNewPath();
            PathFactory.setPath($scope.path);
            
            if ($scope.path.steps.length === 0) {
                // New path => add root step
                var rootStep = StepFactory.generateNewStep();
                rootStep.name = $scope.path.name;
                $scope.path.steps.push(rootStep);
            }
            
            HistoryFactory.update($scope.path);
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
            if ($routeParams.id) {
                // Update existing path
                $http
                    .put(Routing.generate('innova_path_edit_path'), path)
                    .success ( function (data) {
                    });
            } 
            else {
                // Create new path
                var route = Routing.generate('innova_path_add_path');
                
                $http({
                    method: 'POST',
                    url: route,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                    data: 'path=' + angular.toJson(path) + '&workspaceId=2'
                });
                
//                $http.post(route, data)
//                    .success ( function (data) {
//                        // Store generated ID in Path
//                        path.id = data;
//                        PathFactory.setPath(path);
//                        $scope.path = PathFactory.getPath();
//                        $location.path('/path/global/' + data);
//                        alert('success');
//                    });
            }
            
            // Clear history to avoid possibility to get a history state without path ID
            HistoryFactory.clear();
        };
    }
];