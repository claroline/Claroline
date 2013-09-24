/**
 * Resource Factory
 */
var ResourceFactoryProto = [
    'pathFactory', 
    function(pathFactory) {
        var resource = null;
        
        // Base template used to create new resource
        var baseResource = {
            id                  : null,
            resourceId          : null,
            name                : null,
            type                : null,
            subType             : null,
            isDigital           : false,
            propagateToChildren : true
        };
        
        var resourceSubTypes = {
            document: [
                {key: 'text',        label: 'Text'},
                {key: 'sound',       label: 'Sound'},
                {key: 'picture',     label: 'Picture'},
                {key: 'video',       label: 'Video'},
                {key: 'simulation',  label: 'Simulation'},
                {key: 'test',        label: 'Test'},
                {key: 'other',       label: 'Other'},
                {key: 'indifferent', label: 'Indifferent'}
            ],
            tool: [
                {key: 'chat',          label: 'Chat'},
                {key: 'forum',         label: 'Forum'},
                {key: 'deposit_files', label: 'Deposit files'},
                {key: 'other',         label: 'Other'},
                {key: 'indifferent',   label: 'Indifferent'}
            ]
        };
        
        return {
            /**
             * 
             * @param resourceType
             * @returns object
             */
            getResourceSubTypes: function(resourceType) {
                return resourceSubTypes[resourceType] || {};
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
                newResource.id = pathFactory.getNextResourceId();
                return newResource;
            },
            
            /**
             * 
             * @param stepToFind
             * @returns object
             */
            getInheritedResources: function(stepToFind) {
                var stepFound = false;
                var inheritedResources = {
                    documents: [],
                    tools: []
                };

                var path = pathFactory.getPath();
                if (path) {
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
             * 
             * @param stepToFind
             * @param currentStep
             * @param inheritedResources
             * @returns boolean
             */
            retrieveInheritedResources: function(stepToFind, currentStep, inheritedResources) {
                var stepFound = false;
                
                if (stepToFind.id !== currentStep.id) {
                    // Not the step we search for => search in children
                    for (var i = 0; i < currentStep.children.length; i++) {
                        stepFound =  this.retrieveInheritedResources(stepToFind, currentStep.children[i], inheritedResources);
                        if (stepFound) {
                            // Get all resources which must be sent to children
                            var stepDocuments = {
                                stepName: currentStep.name,
                                resources: []
                            };
                            
                            var stepTools = {
                                stepName: currentStep.name,
                                resources: []
                            };
                            
                            for (var j = currentStep.resources.length - 1; j >= 0; j--) {
                                if (currentStep.resources[j].propagateToChildren) {
                                    // Current resource must be available for children
                                    var resource = currentStep.resources[j];
                                    resource.parentStep = currentStep.name;
                                    resource.isExcluded = stepToFind.excludedResources.indexOf(resource.id) != -1;
                                    
                                    if ('document' === resource.type) {
                                        stepDocuments.resources.unshift(resource);
                                    }
                                    else if ('tool' === resource.type) {
                                        stepTools.resources.unshift(resource);
                                    }
                                }
                            }
                            
                            if (stepDocuments.resources.length !== 0)
                                inheritedResources.documents.unshift(stepDocuments);
                            
                            if (stepTools.resources.length !== 0)
                                inheritedResources.tools.unshift(stepTools);
                            
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
];