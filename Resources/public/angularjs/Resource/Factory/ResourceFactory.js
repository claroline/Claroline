'use strict';

/**
 * Resource Factory
 */ 
function ResourceFactory(PathFactory) {
    var resource = null;
    
    // Base template used to create new resource
    var baseResource = {
        id                  : null,
        resourceId          : null,
        name                : null,
        description         : null,
        type                : null,
        url                 : null,
        isDigital           : false,
        propagateToChildren : true
    };
    
    return {
        /**
         * 
         * @returns Array
         */
        getResourceTypes: function() {
            return [
                {key: 'text',          label: Translator.get('path_editor:text')},
                {key: 'sound',         label: Translator.get('path_editor:sound')},
                {key: 'picture',       label: Translator.get('path_editor:picture')},
                {key: 'video',         label: Translator.get('path_editor:video')},
                {key: 'simulation',    label: Translator.get('path_editor:simulation')},
                {key: 'test',          label: Translator.get('path_editor:test')},
                {key: 'chat',          label: Translator.get('path_editor:chat')},
                {key: 'forum',         label: Translator.get('path_editor:forum')},
                {key: 'deposit_files', label: Translator.get('path_editor:deposit_files')},
                {key: 'other',         label: Translator.get('path_editor:other')},
                {key: 'indifferent',   label: Translator.get('path_editor:indifferent')}
            ];
        },
        
        getResourceTypeLabels: function() {
            var labels = {};
            
            var types = this.getResourceTypes();
            if (typeof types !== 'undefined' && null !== types) {
                for (var i = 0; i < types.length; i++) {
                    var currentType = types[i];
                    labels[currentType.key] = currentType.label;
                } 
            }
            
            return labels;
        },
        
        /**
         * 
         * @returns object
         */
        getResource: function() {
            return resource;
        },
        
        /**
         * 
         * @param data
         * @returns ResourceFactory
         */
        setResource: function(data) {
            resource = data;
            
            return this;
        },
        
        /**
         * 
         * @returns object
         */
        generateNewResource: function() {
            var newResource = jQuery.extend(true, {}, baseResource);
            newResource.id = PathFactory.getNextResourceId();
            
            return newResource;
        },
        
        /**
         * 
         * @param stepToFind
         * @returns object
         */
        getInheritedResources: function(stepToFind) {
            var stepFound = false;
            var inheritedResources = [];

            var path = PathFactory.getPath();
            if (path && path.steps) {
                for (var i = 0; i < path.steps.length; i++) {
                    var currentStep = path.steps[i];
                    stepFound = this.retrieveInheritedResources(stepToFind, currentStep, inheritedResources);
                    if (stepFound) {
                        break;
                    }
                }
            }
            
            return inheritedResources;
        },
        
        /**
         * @param stepToFind
         * @param currentStep
         * @param inheritedResources
         * @returns boolean
         */
        retrieveInheritedResources: function(stepToFind, currentStep, inheritedResources) {
            var stepFound = false;
            
            if (stepToFind.id !== currentStep.id && typeof currentStep.children !== 'undefined' && null !== currentStep.children) {
                // Not the step we search for => search in children
                for (var i = 0; i < currentStep.children.length; i++) {
                    stepFound = this.retrieveInheritedResources(stepToFind, currentStep.children[i], inheritedResources);
                    if (stepFound) {
                        if (typeof currentStep.resources !== 'undefined' && null !== currentStep.resources) {
                            // Get all resources which must be sent to children
                            for (var j = currentStep.resources.length - 1; j >= 0; j--) {
                                if (currentStep.resources[j].propagateToChildren) {
                                    // Current resource must be available for children
                                    var resource = currentStep.resources[j];
                                    resource.parentStep = currentStep.name;
                                    resource.isExcluded = stepToFind.excludedResources.indexOf(resource.id) != -1;
                                    inheritedResources.unshift(resource);
                                }
                            }
                        }
                        break;
                    }
                }
            }
            else {
                stepFound = true;
            }
            
            return stepFound;
        }
    }
}