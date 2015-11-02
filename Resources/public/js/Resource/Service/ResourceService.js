/**
 * Resource Service
 */
(function () {
    'use strict';

    angular.module('ResourceModule').factory('ResourceService', [
        'IdentifierService',
        function ResourceService(IdentifierService) {
            /**
             * Resource object
             * @param {string} [type]
             * @param {string} [mimeType]
             * @param {number} [id]
             * @param {string} [name]
             * @constructor
             */
            var Resource = function Resource(type, mimeType, id, name) {
                // Initialize resource properties
                this.id                  = IdentifierService.generateUUID();
                this.resourceId          = id ? id : null;
                this.name                = name ? name : null;
                this.type                = type ? type : null;
                this.mimeType            = mimeType ? mimeType : null;
                this.propagateToChildren = true;
            };

            return {
                /**
                 * Create a new Resource object
                 * @param   {string} [type]
                 * @param   {string} [mimeType]
                 * @param   {number} [id]
                 * @param   {string} [name]
                 * @returns {Resource}
                 */
                new: function (type, mimeType, id, name) {
                    return new Resource(type, mimeType, id, name);
                },

                /**
                 * Check if a Resource is part of a collection
                 * @param   {array}  resources
                 * @param   {object} resource
                 * @returns {boolean}
                 */
                exists: function (resources, resource) {
                    var resourceExists = false;

                    if (angular.isObject(resources)) {
                        for (var i = 0; i < resources.length; i++) {
                            var currentResource = resources[i];
                            if (currentResource.resourceId === resource.resourceId) {
                                resourceExists = true;

                                break;
                            }
                        }
                    }

                    return resourceExists;
                }
            };
        }
    ]);
})();