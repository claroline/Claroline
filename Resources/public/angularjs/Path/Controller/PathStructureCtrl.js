/**
 * Structure Controller
 * Manages the tree of steps into the Path
 */
(function () {
    'use strict';

    angular.module('PathModule').controller('PathStructureCtrl', [
        '$modal',
        'HistoryService',
        'ClipboardService',
        'ConfirmService',
        'PathService',
        'StepService',
        function PathStructureCtrl($modal, HistoryService, ClipboardService, ConfirmService, PathService, StepService) {
            this.webDir = EditorApp.webDir;

            this.structure = [];

            this.currentStep = null;

            // Show action buttons for a step in the tree (contains the ID of the step)
            this.showButtons = null;

            /**
             * Initialize an empty structure for path
             */
            this.createNew = function () {
                // Create a generic root step
                var rootStep = StepService.new();

                this.structure = [];
                this.structure.push(rootStep);

                // Set root step as current step
                this.setCurrentStep(rootStep);
            };

            /**
             * Initialize the structure from a selected template
             */
            this.createFromTemplate = function () {
                // Open select modal

                // Get the root of the template as current step
            };

            this.setCurrentStep = function (step) {
                this.currentStep = step;
            };

            /**
             * Copy step into clipboard
             */
            this.copy = function (step) {
                ClipboardService.copy(step);
            };

            /**
             * Paste clipboard content
             */
            this.paste = function (step) {
                ClipboardService.paste(step);
                /*HistoryService.update(this.path);*/
            };

            // Configure sortable Tree feature
            this.treeOptions = {
                dragStart: function (event) {
                    // Disable tooltip on drag handlers
                    $('.angular-ui-tree-handle').tooltip('disable');

                    // Hide tooltip for the dragged element
                    if (event.source && event.source.nodeScope && event.source.nodeScope.$element) {
                        event.source.nodeScope.$element.find('.angular-ui-tree-handle').tooltip('toggle');
                    }
                },
                dropped: function (event) {
                    // Enable tooltip on drag handlers
                    $('.angular-ui-tree-handle').tooltip('enable');

                    // Reorder steps tree
                    this.applyTreeChanges();
                }.bind(this)
            };

            /**
             * Update Path when Tree is modified with drag n drop
             */
            this.applyTreeChanges = function() {
                PathService.setPath(this.path);
                PathService.recalculateStepsLevel(this.path);

                /*HistoryService.update(this.path);*/
            };

            /**
             * Remove a step from Tree
             */
            this.removeStep = function (step) {
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

                ConfirmService.open(
                    // Confirm options
                    {
                        title:         Translator.trans('step_delete_title',   { stepName: step.name }, 'path_editor'),
                        message:       Translator.trans('step_delete_confirm', {}                     , 'path_editor'),
                        confirmButton: Translator.trans('step_delete',         {}                     , 'path_editor')
                    },

                    // Confirm success callback
                    function() {
                        // Confirm
                        walk(this.structure);

                        /*HistoryService.update(this.path);*/
                        /*this.updatePreviewStep();*/
                    }.bind(this)
                );
            };

            /**
             * Add a new step child to specified step
             */
            this.addChild = function (parentStep) {
                StepService.new(parentStep);

                /*HistoryService.update(this.path);*/
            };

            /**
             * Open modal to create a new template from specified step(s)
             */
            this.saveAsTemplate = function (step) {
                var modalInstance = $modal.open({
                    templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Template/Partial/modal-form.html',
                    controller: 'TemplateFormModalCtrl',
                    controllerAs: 'templateFormModalCtrl',
                    resolve: {
                        step: function () {
                            return step;
                        }
                    }
                });
            };
        }
    ]);
})();
