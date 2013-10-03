'use strict';

/**
 * Step Factory
 */
var StepFactoryProto = [
    '$http',
    'PathFactory',
    function($http, PathFactory) {
        // Stored step
        var step = null;
        
        // Base template used to append new step to tree
        var baseStep = {
            id                : null,
            resourceId        : null,
            image             : null,
            expanded          : false,
            name              : 'Step',
            type              : 'seq',
            instructions      : null,
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
        
        
        var whoList = [];
        var whereList = [];
        
        return {
            /**
             * 
             * @returns array
             */
            getWhoList: function() {
                if (whoList.length === 0) {
                    // Load list from AJAX
                    $http.get(Routing.generate('innova_path_get_stepwho')).success(function(data) { whoList = data; return whoList; });
                }
                else {
                    return whoList;
                }
            },
            
            /**
             * 
             * @returns array
             */
            getWhereList: function() {
                if (whereList.length === 0) {
                    // Load list from AJAX
                    $http.get(Routing.generate('innova_path_get_stepwhere')).success(function(data) { whereList = data; return whereList; });
                }
                else {
                    return whereList;
                }
            },
            
            /**
             * Generate a new empty step
             * 
             * @param step
             * @returns object
             */
            generateNewStep: function(step) {
                var stepId = PathFactory.getNextStepId();
                var newStep = jQuery.extend(true, {}, baseStep);
                
                if (undefined != step) {
                    newStep.name = step.name + '-' + stepId;
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
            }
        };
    }
];