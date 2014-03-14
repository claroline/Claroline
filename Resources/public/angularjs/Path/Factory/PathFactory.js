'use strict';

/**
 * Path Factory
 */
function PathFactory($http, $q) {
    var path = null;
    var maxStepId = 0;
    var maxResourceId = 0;

    return {
        /**
         *
         * @returns PathFactory
         */
        clear: function() {
            path = null;
            maxStepId = 0;
            maxResourceId = 0;

            return this;
        },

        /**
         *
         * @returns object
         */
        getPath: function() {
            return path;
        },

        /**
         *
         * @param data
         * @returns PathFactory
         */
        setPath: function(data) {
            // Store current path
            path = data;

            // Retrieve max step id
            this.getMaxStepId();

            // Retrieve max resource id
            this.getMaxResourceId();

            return this;
        },

        recalculateStepsLevel: function () {
            if (path != null && path.steps != null && path.steps.length !== 0) {
                var currentLevel = 0;
                for (var i = 0; i < path.steps.length; i++) {
                    var step = path.steps[i];
                    this.updateStepLevel(step, currentLevel);
                }
            }
        },

        updateStepLevel: function (step, currentLevel) {
            step.lvl = currentLevel;

            if (step.children != null && step.children.length !== 0) {
                var nextLevel = currentLevel + 1;
                for (var i = 0; i < step.children.length; i++) {
                    var nextStep = step.children[i];
                    this.updateStepLevel(nextStep, nextLevel);
                }
            }
        },

        /**
         * Retrieve step in path using its ID
         *
         * @param stepId
         * @returns object
         */
        getStepById: function(stepId) {
            function search(stepId, currentStep) {
                var step = null;
                if (stepId === currentStep.id) {
                    step = currentStep;
                }
                else if (typeof currentStep.children != 'undefined' && null != currentStep.children && currentStep.children.length !== 0) {
                    for (var i = 0; i < currentStep.children.length; i++) {
                        step = search(stepId, currentStep.children[i]);
                        if (null !== step) {
                            // Step found, stop search
                            break;
                        }
                    }
                }

                return step;
            }

            var step = null;
            if (null !== path && path.length !== 0) {
                for (var i = 0; i < path.steps.length; i++) {
                    step = search(stepId, path.steps[i]);
                    if (null !== step) {
                        break;
                    }
                }
            }

            return step;
        },

        /**
         *
         * @returns Integer
         */
        getMaxStepId: function() {
            maxStepId = 1;
            if (null !== path && typeof path.steps !== 'undefined' && null !== path.steps)
            {
                for (var i = 0; i < path.steps.length; i++) {
                    this.retrieveMaxStepId(path.steps[i]);
                }
            }

           return maxStepId;
        },

        /**
         *
         * @param step
         * @returns PathFactory
         */
        retrieveMaxStepId: function(step) {
            // Check current step
            if (step.id > maxStepId) {
                maxStepId = step.id;
            }

            // Check step children
            if (typeof step.children != 'undefined' && null !== step.children) {
                for (var i = 0; i < step.children.length; i++) {
                    this.retrieveMaxStepId(step.children[i]);
                }
            }

            return this;
        },

        /**
         *
         * @returns Integer
         */
        getNextStepId: function() {
            if (0 === maxStepId) {
                // Max step ID not calculated
                this.getMaxStepId();
            }
            maxStepId++;
            return maxStepId;
        },

        /**
         *
         * @returns Integer
         */
        getMaxResourceId: function() {
            maxResourceId = 1;
            if (null !== path && typeof path.steps !== 'undefined' && null != path.steps)
            {
                for (var i = 0; i < path.steps.length; i++) {
                    this.retrieveMaxResourceId(path.steps[i]);
                }
            }

           return maxResourceId;
        },

        /**
         *
         * @param step
         * @returns PathFactory
         */
        retrieveMaxResourceId: function(step) {
            if (typeof step.resources != 'undefined' && null !== step.resources) {
                // Check current step resources
                for (var i = 0; i < step.resources.length; i++) {
                    if (step.resources[i].id > maxResourceId) {
                        maxResourceId = step.resources[i].id;
                    }
                }
            }

            // Check step children
            if (typeof step.children != 'undefined' && null !== step.children) {
                for (var i = 0; i < step.children.length; i++) {
                    this.retrieveMaxResourceId(step.children[i]);
                }
            }

            return this;
        },

        /**
         *
         * @returns Integer
         */
        getNextResourceId: function() {
            if (0 === maxResourceId) {
                // Max step ID not calculated
                this.getMaxResourceId();
            }
            maxResourceId++;
            
            return maxResourceId;
        },

        /**
         *
         * @param newStep
         * @returns PathFactory
         */
        replaceStep: function(newStep) {
            if (null !== path && typeof path.steps !== 'undefined' && null !== path.steps) {
                var stepFound = false;
                for (var i = 0; i < path.steps.length; i++) {
                    stepFound = this.searchStepToReplace(path.steps[i], newStep);
                    if (stepFound) {
                        break;
                    }
                }
            }

            return this;
        },

        /**
         *
         * @param currentStep
         * @param newStep
         * @returns boolean
         */
        searchStepToReplace: function(currentStep, newStep) {
            var stepFound = false;
            if (currentStep.id === newStep.id) {
                stepFound = true;
                this.updateStep(currentStep, newStep);
            }
            else if (typeof currentStep.children != 'undefined' && null !== currentStep.children) {
                for (var i = 0; i < currentStep.children.length; i++) {
                    stepFound = this.searchStepToReplace(currentStep.children[i], newStep);
                    if (stepFound) {
                        break;
                    }
                }
            }

            return stepFound;
        },

        /**
         *
         * @param oldStep
         * @param newStep
         * @returns PathFactory
         */
        updateStep: function(oldStep, newStep) {
            // Add all newStep properties
            for (var prop in newStep) {
                oldStep[prop] = newStep[prop];
            }

            // Remove properties which no longer exists in new step
            for (var prop in oldStep) {
                if (typeof (newStep[prop]) == 'undefined') {
                    delete oldStep[prop];
                }
            }

            return this;
        },
      
        /**
         * Remove references to specified resource in all path
         * 
         * @param resourceId  resource's id to remove
         * @returns PathFactory
         */
        removeResource: function(resourceId) {
            function removeRefToResource(step) {
                if (typeof step.excludedResources != 'undefined' && null !== step.excludedResources) {
                    // Loop through excluded resource to remove reference to needle
                    for (var i = 0; i < step.excludedResources.length; i++) {
                        if (resourceId == step.excludedResources[i]) {
                            step.excludedResources.splice(i, 1);
                        }
                    }
                }
                
                if (typeof step.children != 'undefined' && null !== step.children) {
                 // Check children
                    for (var j = 0; j < step.children.length; j++) {
                        removeRefToResource(step.children[j]);
                    }
                }
            }
            
            if (path !== null && typeof path.steps !== 'undefined') {
                for (var i = 0; i < path.steps.length; i++) {
                    removeRefToResource(path.steps[i]);
                }
            }
            
            return this;
        }
    };
}