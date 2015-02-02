/**
 * Scenario Controller
 */
(function () {
    'use strict';

    angular.module('EditorModule').controller('ScenarioCtrl', [
        '$scope',
        '$modal',
        'HistoryFactory',
        'PathFactory',
        'StepFactory',
        function ($scope, $modal, HistoryFactory, PathFactory, StepFactory) {
            // Show action buttons for a step in the tree (contains the ID of the step)
            $scope.showButtons = null;

            // Configure sortable Tree feature
            $scope.treeOptions = {
                dragStart: function (event) {
                    // Disable tooltip on drag handlers
                    $('.angular-ui-tree-handle').tooltip('disable');

                    // Hide tooltip for the dragged element
                    if (event.source && event.source.nodeScope && event.source.nodeScope.$element) {
                        e.source.nodeScope.$element.find('.angular-ui-tree-handle').tooltip('toggle');
                    }
                },
                dropped: function (event) {
                    // Enable tooltip on drag handlers
                    $('.angular-ui-tree-handle').tooltip('enable');

                    // Reorder steps tree
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
                        title: function () { return Translator.trans('step_delete_title', { stepName: step.name }, 'path_editor') },
                        message: function () { return Translator.trans('step_delete_confirm', {}, 'path_editor') },
                        confirmButton: function () { return Translator.trans('step_delete', {}, 'path_editor') }
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
                // Display confirm modal
                var modalInstance = $modal.open({
                    templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Confirm/Partial/confirm.html',
                    controller: 'ConfirmModalCtrl',
                    resolve: {
                        title: function () { return Translator.trans('step_delete_children_title', { stepName: step.name }, 'path_editor') },
                        message: function () { return Translator.trans('step_delete_children_confirm', {}, 'path_editor') },
                        confirmButton: function () { return Translator.trans('step_delete_children', {}, 'path_editor') }
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
                var newStep = StepFactory.generateNewStep(step);

                if (typeof step.children == 'undefined' || null == step.children) {
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
            };
        }
    ]);
})();