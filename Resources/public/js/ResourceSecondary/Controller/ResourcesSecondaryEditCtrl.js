/**
 * Resources secondary controller
 * @param $scope
 * @param ConfirmService
 * @param ResourceService
 * @returns {ResourcesSecondaryEditCtrl}
 * @constructor
 */
var ResourcesSecondaryEditCtrl = function ResourcesSecondaryEditCtrl($scope, ConfirmService, ResourceService) {
    // Call parent constructor
    ResourcesSecondaryBaseCtrl.apply(this, arguments);

    this.scope           = $scope;
    this.confirmService  = ConfirmService;
    this.resourceService = ResourceService;

    var $this = this;

    /**
     * Configuration for the Claroline Resource Picker
     * @type {object}
     */
    this.resourcePicker = {
        isPickerMultiSelectAllowed: true,
        callback: function selectSecondaryResources(nodes) {
            $this.addResources(nodes);

            // Remove checked nodes for next time
            nodes = {};
        }
    };

    return this;
};

// Extends the base controller
ResourcesSecondaryEditCtrl.prototype = ResourcesSecondaryBaseCtrl.prototype;
ResourcesSecondaryEditCtrl.prototype.constructor = ResourcesSecondaryEditCtrl;

ResourcesSecondaryEditCtrl.prototype.resourceSecondaryPicker = {};

ResourcesSecondaryEditCtrl.prototype.addResources = function (resources) {
    if (angular.isObject(resources)) {
        for (var nodeId in resources) {
            if (resources.hasOwnProperty(nodeId)) {
                var node = resources[nodeId];

                // Initialize a new Resource object (parameters : claro type, mime type, id, name)
                var resource = this.resourceService.new(node[1], node[2], nodeId, node[0]);
                if (!this.resourceService.exists(this.resources, resource)) {
                    // Resource is not in the list => add it
                    this.resources.push(resource);
                }
            }
        }

        this.scope.$apply();
    }
};

/**
 * Delete selected resource from path
 * @type {object}
 */
ResourcesSecondaryEditCtrl.prototype.removeResource = function removeResource(resource) {
    this.confirmService.open(
        // Confirm options
        {
            title:         Translator.trans('resource_delete_title',   { resourceName: resource.name }, 'path_wizards'),
            message:       Translator.trans('resource_delete_confirm', {}                             , 'path_wizards'),
            confirmButton: Translator.trans('resource_delete',         {}                             , 'path_wizards')
        },

        // Confirm success callback
        function () {
            // Remove from included resources
            for (var i = 0; i < this.resources.length; i++) {
                if (resource.id === this.resources[i].id) {
                    this.resources.splice(i, 1);
                    break;
                }
            }

            // Remove from excluded resources
            for (var j = 0; j < this.excluded.length; j++) {
                if (resource.id == this.excluded[j]) {
                    this.excluded.splice(j, 1);
                    break;
                }
            }
        }.bind(this)
    );
};

/**
 * Toggle propagate flag
 * @param {object} resource
 */
ResourcesSecondaryEditCtrl.prototype.togglePropagation = function togglePropagation(resource) {
    resource.propagateToChildren = !resource.propagateToChildren;
};

/**
 * Toggle excluded flag
 * @param {object} resource
 */
ResourcesSecondaryEditCtrl.prototype.toggleExcluded = function toggleExcluded(resource) {
    if (resource.isExcluded) {
        // Include the resource
        for (var i = 0; i < this.excluded.length; i++) {
            if (resource.id == this.excluded[i]) {
                this.excluded.splice(i, 1);
            }
        }
    } else {
        // Exclude the resource
        this.excluded.push(resource.id);
    }

    resource.isExcluded = !resource.isExcluded;
};