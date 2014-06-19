'use strict';

/**
 * Scenario Controller
 */
function ScenarioCtrl($scope, $modal, HistoryFactory, PathFactory, StepFactory) {
    // Show action buttons for a step in the tree (contains the ID of the step)
    $scope.showButtons = null;
    
    // Configure sortable Tree feature
    $scope.treeOptions = {
        dropped : function(e){
            $scope.applyTreeChanges(); 
        }
    };

    /**
     * Update Path when Tree is modified with drag n drop
     */
    $scope.applyTreeChanges = function() {
        PathFactory.setPath($scope.path);
        PathFactory.recalculateStepsLevel($scope.path);

        HistoryFactory.update($scope.path);
    };

    /**
     * Remove a step from Tree
     */
    $scope.removeStep = function(step) {     
        // Search step to remove function
        function walk(path) {
            var children = path.children;

            if (children) {
                var i = children.length;
                while (i--) {
                    if (children[i] === step) {
                        return children.splice(i, 1);
                    } else {
                        walk(children[i]);
                    }
                }
            }
        }

        StepFactory.setStep(step);

        // Display confirm modal
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Confirm/Partial/confirm.html',
            controller: 'ConfirmModalCtrl',
            resolve: {
                title: function () { return Translator.get('path_editor:step_delete_title', { stepName: step.name }) },
                message: function () { return Translator.get('path_editor:step_delete_confirm') },
                confirmButton: function () { return Translator.get('path_editor:step_delete') }
            }
        });

        modalInstance.result.then(function() {
            // Confirm
            walk($scope.path.steps[0]);

            HistoryFactory.update($scope.path);
            $scope.updatePreviewStep();
        });
    };

    /**
     * Remove all children of the specified step
     */
    $scope.removeChildren = function(step) {
        console.log('564');
        // Display confirm modal
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Confirm/Partial/confirm.html',
            controller: 'ConfirmModalCtrl',
            resolve: {
                title: function () { return Translator.get('path_editor:step_delete_children_title', { stepName: step.name }) },
                message: function () { return Translator.get('path_editor:step_delete_children_confirm') },
                confirmButton: function () { return Translator.get('path_editor:step_delete_children') }
            }
        });

        modalInstance.result.then(function() {
            step.children = [];
            HistoryFactory.update($scope.path);
        });
    };

    /**
     * Add a new step child to specified step
     */
    $scope.addChild = function(step) {
        console.log('+');
        var newStep = StepFactory.generateNewStep(step);
        
        if (typeof step.children == undefined || null == step.children) {
            step.children = [];
        }
        step.children.push(newStep);
        
        HistoryFactory.update($scope.path);
    };

    /**
     * Open modal to create a new template from specified step(s)
     */
    $scope.editTemplate = function(step) {
        StepFactory.setStep(step);
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Template/Partial/template-edit.html',
            controller: 'TemplateModalCtrl'
        });

        modalInstance.result.then(function() {
            // Confirm
            $scope.pageslideOpen();
        }, function () {
            // Cancel
        });
    };
}