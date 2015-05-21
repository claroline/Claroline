/**
 * Path summary
 */
(function () {
    'use strict';

    angular.module('PathModule').controller('PathSummaryCtrl', [
        '$timeout',
        '$modal',
        '$location',
        '$routeParams',
        'IdentifierService',
        'HistoryService',
        'ClipboardService',
        'ConfirmService',
        'PathService',
        'StepService',
        function PathSummaryCtrl($timeout, $modal, $location, $routeParams, IdentifierService, HistoryService, ClipboardService, ConfirmService, PathService, StepService) {
            this.webDir = EditorApp.webDir;

            this.structure = [];

            this.maxDepth = 8; // Do not allow adding children to steps at the max depth

            /**
             * Information about current view
             * @type {null}
             */
            this.current = $routeParams;

            /**
             * Current state of the clipboard
             * @type {object}
             */
            this.clipboardDisabled = ClipboardService.getDisabled();

            // Show action buttons for a step in the tree (contains the ID of the step)
            this.showButtons = null;

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

                    // Recalculate step levels
                    PathService.reorderSteps(this.structure);
                }.bind(this)
            };

            /**
             * Is the summary opened ?
             * @type {boolean}
             */
            this.opened = false;

            this.close = function () {
                this.opened = false;
            };

            /**
             * Initialize an empty structure for path
             */
            this.createNew = function () {
                // Create a generic root step
                var rootStep = StepService.new();

                this.structure.push(rootStep);

                // Set root step as current step
                this.goTo(rootStep);
            };

            /**
             * Initialize the structure from a selected template
             */
            this.createFromTemplate = function () {
                // Open select modal

                // Get the root of the template as current step
            };

            this.goTo = function goTo(step) {
                $timeout(function(){
                    console.log('goTo');
                    if (angular.isObject(step)) {
                        $location.path('/' + step.id);
                    } else {
                        $location.path('/');
                    }
                },1);

            };

            /**
             * Add a new step child to specified step
             */
            this.addStep = function (parentStep) {
                if (parentStep.lvl < this.maxDepth) {
                    // Create a new step
                    var step = StepService.new(parentStep);

                    // Open created step
                    this.goTo(step);
                }
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
                // Paste clipboard content into children of the step
                ClipboardService.paste(step.children, function (clipboardData) {
                    // Change step IDs before paste them
                    PathService.browseSteps([ clipboardData ], function (parentStep, step) {
                        step.id = IdentifierService.generateUUID();

                        // Override name
                        step.name = step.name ? step.name + ' ' : '';
                        step.name += '(' + Translator.trans('copy', {}, 'path_editor') + ')';
                    });
                });

                // Recalculate step levels
                PathService.reorderSteps(this.structure);
            };

            /**
             * Remove a step from Tree
             */
            this.removeStep = function (step) {
                ConfirmService.open(
                    // Confirm options
                    {
                        title:         Translator.trans('step_delete_title',   { stepName: step.name }, 'path_editor'),
                        message:       Translator.trans('step_delete_confirm', {}                     , 'path_editor'),
                        confirmButton: Translator.trans('step_delete',         {}                     , 'path_editor')
                    },

                    // Confirm success callback
                    function () {
                        // Check if we are deleting the current editing step
                        var updatePreview = false;
                        if (step === this.currentStep) {
                            // Need to update preview
                            updatePreview = true;
                        }

                        // Effective remove
                        PathService.removeStep(this.structure, step);

                        // Update current editing step
                        if (updatePreview) {
                            if (this.structure[0]) {
                                // Display root step
                                this.goTo(this.structure[0]);
                            } else {
                                // There is no longer steps into the path => hide step form
                                this.goTo(null);
                            }
                        }
                    }.bind(this)
                );
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