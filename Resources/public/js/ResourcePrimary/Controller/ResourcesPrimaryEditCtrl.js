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
    ResourcesPrimaryBaseCtrl.apply(this, arguments);

    this.scope           = $scope;
    this.confirmService  = ConfirmService;
    this.resourceService = ResourceService;

    /**
     * Configuration for the Claroline Resource Picker
     * @type {object}
     */
    this.resourcePicker = {
        // A step can allow be linked to one primary Resource, so disable multi-select
        isPickerMultiSelectAllowed: false,

        // Do not allow Path and Activities as primary resource to avoid Inception
        typeBlackList: [ 'innova_path', 'activity' ],

        // On select, set the primary resource of the step
        callback: function selectPrimaryResources(nodes) {
            if (angular.isObject(nodes)) {
                for (var nodeId in nodes) {
                    if (nodes.hasOwnProperty(nodeId)) {
                        var node = nodes[nodeId];

                        // Initialize a new Resource object (parameters : claro type, mime type, id, name)
                        var resource = this.resourceService.new(node[1], node[2], nodeId, node[0]);
                        if (!this.resourceService.exists(this.resources, resource)) {
                            // While only one resource is authorized, empty the resources array
                            this.resources.splice(0, this.resources.length);

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
    };

    return this;
};

// Extends the base controller
ResourcesPrimaryEditCtrl.prototype = Object.create(ResourcesPrimaryBaseCtrl.prototype);
ResourcesPrimaryEditCtrl.prototype.constructor = ResourcesPrimaryEditCtrl;

/**
 * Show or Hide the primary resources panel
 * @type {boolean}
 */
ResourcesPrimaryEditCtrl.prototype.collapsed = false;

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