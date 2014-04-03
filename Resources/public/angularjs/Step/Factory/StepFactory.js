'use strict';

/**
 * Step Factory
 */
function StepFactory($http, $q, PathFactory) {
    // Stored step
    var step = null;
    
    var whoDefault = null;
    var whereDefault = null;

    // Base template used to append new step to tree
    var baseStep = {
        id                : null,
        lvl               : 0,
        resourceId        : null,
        image             : 'no_image.png',
        name              : 'Step',
        description       : null,
        hasDuration       : false,
        durationHours     : 0,
        durationMinutes   : 0,
        who               : null,
        where             : null,
        withTutor         : false,
        withComputer      : true,
        children          : [],
        resources         : [],
        excludedResources : []
    };
    
    var images = null;
    
    return {
        getWhoDefault: function() {
            return whoDefault;
        },

        setWhoDefault: function(defaultValue) {
            whoDefault = defaultValue;
        },

        getWhereDefault: function() {
            return whereDefault;
        },

        setWhereDefault: function(defaultValue) {
            whereDefault = defaultValue;
        },

        /**
         * 
         */
        getImages: function() {
            if (null == images || images.length === 0) {
                // Load images library from AJAX
                var deferred = $q.defer();
                $http.get(Routing.generate('innova_step_images')).success(function(data) { 
                    images = data;
                    return deferred.resolve(images);
                })
                .error(function(data, status) {
                    return deferred.reject('error loading images list');
                });
                
                return deferred.promise;
            }
            else {
                // Images already loaded => simply return it
                return images;
            }
        },
        
        /**
         * Generate a new empty step
         * 
         * @param step
         * @returns object
         */
        generateNewStep: function(step, isNewChild) {
            var stepId = PathFactory.getNextStepId();
            var newStep = jQuery.extend(true, {}, baseStep);
            
            if (undefined != step && null != step) {
                newStep.name = 'Step' + '-' + stepId;
                newStep.lvl = step.lvl + 1;
            }
            
            newStep.id = stepId;
            
            newStep.who = whoDefault.id;
            newStep.where = whereDefault.id;

            return newStep;
        },
        
        /**
         * Store step in factory
         * 
         * @param data - The step to store
         * @returns StepFactory
         */
        setStep: function(data) {
            step = data;
            
            return this;
        },
        
        /**
         * Get step stored in factory
         * 
         * @returns object
         */
        getStep: function() {
            return step;
        },
        
        /**
         * Search resource in path and replace it by a new one
         * 
         * @param newResource
         * @returns StepFactory
         */
        replaceResource: function(newResource) {
            if (null !== step && typeof step.resources !== 'undefined' && null !== step.resources) {
                for (var i = 0; i < step.resources.length; i++) {
                    if (newResource.id === step.resources[i].id) {
                        this.updateResource(oldResource, newResource);
                        break;
                    }
                }
            }
            
            return this;
        },
        
        /**
         * Update resource properties
         * 
         * @param oldResource
         * @param newResource
         * @returns StepFactory
         */
        updateResource: function(oldResource, newResource) {
            for (var prop in newResource) {
                oldResource[prop] = newResource[prop];
            }
            
            return this;
        },
        
        /**
         * Remove selected resource from step
         * 
         * @param step          current step
         * @param resourceId    resource to remove
         * @returns StepFactory
         */
        removeResource: function(step, resourceId) {
            if (typeof step.resources !== 'undefined' && null !== step.resources)
            // Search resource to remove
            for (var i = 0; i < step.resources.length; i++) {
                if (resourceId === step.resources[i].id) {
                    step.resources.splice(i, 1);
                    break;
                }
            }
            
            return this;
        }
    };
}