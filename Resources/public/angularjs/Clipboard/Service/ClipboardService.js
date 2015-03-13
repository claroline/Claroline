/**
 * Clipboard Factory
 */
(function () {
    'use strict';

    angular.module('ClipboardModule').factory('ClipboardService', [
        '$rootScope',
        'PathService',
        function ($rootScope, PathService) {
            // Clipboard content
            var clipboard = null;

            // Enable paste buttons when clipboard is not empty
            $rootScope.pasteDisabled = true;

            return {
                /**
                 * Empty clipboard
                 *
                 * @returns ClipboardService
                 */
                clear: function() {
                    clipboard = null;
                    this.setPasteDisabled(true);

                    return this;
                },

                /**
                 * Copy selected steps into clipboard
                 *
                 * @param steps
                 * @returns ClipboardService
                 */
                copy: function(steps) {
                    clipboard = steps;
                    this.setPasteDisabled(false);

                    return this;
                },

                /**
                 * Paste steps form clipboards into current Path tree
                 *
                 * @param step
                 * @returns ClipboardService
                 */
                paste: function(step) {
                    if (null !== clipboard) {
                        // Clone voir : http://stackoverflow.com/questions/122102/most-efficient-way-to-clone-an-object
                        var stepCopy = jQuery.extend(true, {}, clipboard);

                        // Replace IDs before inject steps in path
                        this.replaceStepsId(stepCopy);
                        this.replaceResourcesId(stepCopy);

                        step.children.push(stepCopy);

                        PathService.recalculateStepsLevel();
                    }

                    return this;
                },

                /**
                 *
                 * @param step
                 * @returns ClipboardService
                 */
                replaceResourcesId: function(step, replacedIds) {
                    if (typeof replacedIds === 'undefined' || null === replacedIds) {
                        var replacedIds = [];
                    }

                    if (typeof step.resources !== 'undefined' && step.resources !== null && step.resources.length !== 0) {
                        for (var i = 0; i < step.resources.length; i++) {
                            var newId = PathService.getNextResourceId();

                            // Store ID to update excluded resources
                            replacedIds[step.resources[i].id] = newId;

                            // Update resource ID
                            step.resources[i].id = PathService.getNextResourceId();

                            // Check excluded resources
                            for (var oldId in replacedIds) {
                                var pos = step.excludedResources.indexOf(oldId);
                                if (-1 !== pos) {
                                    step.excludedResources[pos] = replacedIds[oldId];
                                }
                            }
                        }
                    }

                    if (step.children.length !== 0) {
                        for (var j = 0; j < step.children.length; j++) {
                            this.replaceResourcesId(step.children[j], replacedIds);
                        }
                    }

                    return this;
                },

                /**
                 *
                 * @param step
                 * @returns ClipboardService
                 */
                replaceStepsId: function (step) {
                    step.resourceId = null;

                    if (step.children.length != 0) {
                        for (var i = 0; i < step.children.length; i++) {
                            this.replaceStepsId(step.children[i]);
                        }
                    }
                    return this;
                },

                /**
                 *
                 * @returns boolean
                 */
                getPasteDisabled: function() {
                    return $rootScope.pasteDisabled;
                },

                /**
                 *
                 * @param data
                 * @returns ClipboardService
                 */
                setPasteDisabled: function(data) {
                    $rootScope.pasteDisabled = data;
                    return this;
                }
            };
        }
    ]);
})();