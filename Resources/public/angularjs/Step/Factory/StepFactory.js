'use strict';

/**
 * Step Factory
 */
function StepFactory($http, $q, PathFactory) {
    // Stored step
    var step = null;
    
    // Base template used to append new step to tree
    var baseStep = {
        id                : null,
        resourceId        : null,
        image             : 'no_image.png',
        name              : 'Step',
        description       : null,
        durationHours     : null,
        durationMinutes   : null,
        who               : null,
        where             : null,
        withTutor         : false,
        withComputer      : true,
        children          : [],
        resources         : [],
        excludedResources : []
    };
    
    var whoList = null;
    var whereList = null;
    var images = null;
    
    return {
        /**
         * Load available values for Step field "who"
         * @returns array
         */
        getWhoList: function() {
            if (null == whoList || whoList.length === 0) {
                // Load list from AJAX
                var deferred = $q.defer();
                $http.get(Routing.generate('innova_path_get_stepwho')).success(function(data) { 
                    whoList = data; 
                    return deferred.resolve(whoList);
                })
                .error(function(data, status) {
                    return deferred.reject('error loading who list');
                });
                
                return deferred.promise;
            }
            else {
                // List already loaded => simply return it
                return whoList;
            }
        },
        
        /**
         * 
         * @returns array
         */
        getWhereList: function() {
            if (null == whereList || whereList.length === 0) {
                // Load list from AJAX
                var deferred = $q.defer();
                $http.get(Routing.generate('innova_path_get_stepwhere')).success(function(data) { 
                    whereList = data; 
                    return deferred.resolve(whereList); 
                })
                .error(function(data, status) {
                    return deferred.reject('error loading where list');
                });
                
                return deferred.promise;
            }
            else {
                // List already loaded => simply return it
                return whereList;
            }
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
            
            if (undefined != step) {
                newStep.name = 'Step' + '-' + stepId;
            }
            
            newStep.id = stepId;
            
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
            if (null !== step) {
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