var StepFactoryProto = [
    'pathFactory',
    function(pathFactory) {
        // Stored step
        var step = null;
        
        // Base template used to append new step to tree
        var baseStep = {
            id                : null,
            resourceId        : null,
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
        
        return {
            /**
             * Generate a new empty step
             * 
             * @param step
             * @returns object
             */
            generateNewStep: function(step) {
                var stepId = pathFactory.getNextStepId();
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