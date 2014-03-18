'use strict';

/**
 * Scenario Controller
 */
function ScenarioCtrl($scope, $modal, HistoryFactory, PathFactory, StepFactory) {
    // Show action buttons for a step in the tree (contains the ID of the step)
    $scope.showButtons = null;
    
    // Configure Tree of steps sortable feature
    $scope.sortableOptions = {
        update: function(e, ui) { $scope.applyTreeChanges(); },
        placeholder: 'placeholder',
        connectWith: '.ui-sortable'
    };

    /**
     * Update Path when Tree is modified with drag n drop
     * @returns void
     */
    $scope.applyTreeChanges = function() {
        PathFactory.setPath($scope.path);
        PathFactory.recalculateStepsLevel($scope.path);

        HistoryFactory.update($scope.path);
        $scope.updatePreviewStep();
    };

    /**
     * Remove a step from Tree
     * @returns void
     */
    $scope.remove = function(step) {
        // Search step to remove function
        function walk(path) {
            var children = path.children;
            var i;

            if (children) {
                i = children.length;
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
            templateUrl: EditorApp.webDir + 'angularjs/Step/Partial/confirm-delete.html',
            controller: 'ConfirmDeleteModalCtrl',
            resolve: {
                step: function () {
                    return ;
                }
            }
        });

        modalInstance.result.then(function() {
            // Confirm
            walk($scope.path.steps[0]);

            HistoryFactory.update($scope.path);
            $scope.updatePreviewStep();
        }, function () {
            // Cancel
        });
    };

    /**
     * Remove all children of the specified step
     * @returns void
     */
    $scope.removeChildren = function(step) {
        step.children = [];
        HistoryFactory.update($scope.path);
        $scope.updatePreviewStep();
    };

    /**
     * Add a new step child to specified step
     * @returns void
     */
    $scope.addChild = function(step) {
        var newStep = StepFactory.generateNewStep(step);
        
        if (typeof step.children == undefined || null == step.children) {
            step.children = [];
        }
        step.children.push(newStep);
        
        HistoryFactory.update($scope.path);
        $scope.updatePreviewStep();
    };

    /**
     * Open modal to create a new template from specified step(s)
     * @returns void
     */
    $scope.editTemplate = function(step) {
        StepFactory.setStep(step);
        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'angularjs/Template/Partial/template-edit.html',
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