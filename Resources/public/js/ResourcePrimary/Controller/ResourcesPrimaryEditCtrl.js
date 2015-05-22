/**
 * Resources primary controller
 * @param $scope
 * @param ConfirmService
 * @param ResourceService
 * @returns {ResourcesPrimaryEditCtrl}
 * @constructor
 */
var ResourcesPrimaryEditCtrl = function ResourcesPrimaryEditCtrl($scope, ConfirmService, ResourceService) {
    // Call parent constructor
    ResourcesEditCtrl.apply(this, arguments);

    return this;
};

// Extends the base controller
ResourcesPrimaryEditCtrl.prototype = ResourcesEditCtrl.prototype;
ResourcesPrimaryEditCtrl.prototype.constructor = ResourcesPrimaryEditCtrl;

/**
 * Show or Hide the primary resources panel
 * @type {boolean}
 */
ResourcesPrimaryEditCtrl.prototype.collapsed = false;

/**
 * Configuration for the Claroline Resource Picker
 * @type {object}
 */
ResourcesPrimaryEditCtrl.prototype.resourcePicker = {
    name: 'picker-primary-resource',
    parameters: {
        // A step can allow be linked to one primary Resource, so disable multi-select
        isPickerMultiSelectAllowed: false,

        // Do not allow Path and Activities as primary resource to avoid Inception
        typeBlackList: [ 'innova_path', 'activity' ],

        // On select, set the primary resource of the step
        callback: function (nodes) {
            if (typeof nodes === 'object' && nodes.length !== 0) {
                for (var nodeId in nodes) {
                    if (nodes.hasOwnProperty(nodeId)) {
                        var node = nodes[nodeId];

                        // Initialize a new Resource object (parameters : claro type, mime type, id, name)
                        var resource = this.resourceService.new(node[1], node[2], nodeId, node[0]);
                        if (!this.resourceService.exists(this.resources, resource)) {
                            // While only one resource is authorized, empty the resources array
                            // Resource is not in the list => add it
                            this.resources.push(resource);
                        }

                        break; // We need only one node, so only the first one will be kept
                    }
                }

                this.scope.$apply();

                // Remove checked nodes for next time
                nodes = {};
            }
        }.bind(this)
    }
};

/**
 * Delete resource
 * @type {object}
 */
ResourcesPrimaryEditCtrl.prototype.removeResource = function removeResource(resource) {
    this.confirmService.open(
        // Confirm options
        {
            title:         Translator.trans('resource_delete_title',   { resourceName: resource.name }, 'path_wizards'),
            message:       Translator.trans('resource_delete_confirm', {}                             , 'path_wizards'),
            confirmButton: Translator.trans('resource_delete',         {}                             , 'path_wizards')
        },

        // Confirm success callback
        function () {
            // Remove from resources
            for (var i = 0; i < this.resources.length; i++) {
                if (resource.id === this.resources[i].id) {
                    this.resources.splice(i, 1);
                    break;
                }
            }
        }.bind(this)
    );
};