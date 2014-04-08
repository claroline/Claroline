'use strict';

/**
 * Remove useless properties before convert Path to JSON
 */
function PathToJsonFilter() {
    return function (path) {
        function processSteps(steps) {
            for (var i = 0; i < steps.length; i++) {
                var step = steps[i];
                if (undefined !== step.resources && null !== step.resources) {
                    for (var j = 0; j < step.resources.length; j++) {
                        step.resources[j] = cleanResource(step.resources[j]);
                    }

                    if (undefined !== step.children && null !== step.children) {
                        processSteps(step.children);
                    }
                }
            }
        }

        function cleanResource(resource) {
            var newResource = {};
            var propertiesToRemove = ['isExcluded', 'parentStep'];
            for (var prop in resource) {
                if (resource.hasOwnProperty(prop) && propertiesToRemove.indexOf(prop) === -1) {
                    newResource[prop] = resource[prop];
                }
            }

            return newResource;
        }

        var pathToEncode = angular.copy(path);
        // Clean resources
        if (undefined !== pathToEncode.steps && null !== pathToEncode.steps) {
            processSteps(pathToEncode.steps);
        }

        return angular.toJson(pathToEncode);
    };
}