/**
 * Secondary Resources Controller
 */
(function () {
    'use strict';

    angular.module('ResourceModule').controller('SecondaryResourcesCtrl', [
        '$scope',
        'ConfirmService',
        'ResourceService',
        function SecondaryResourcesCtrl($scope, ConfirmService, ResourceService) {
            /**
             * Resources owned by the Step
             * @type {Array}
             */
            this.resources = [];

            /**
             * Resources inherited from the parents
             * @type {Array}
             */
            this.inherited = [];

            /**
             * Resources inherited which should not be available in the Step
             * @type {Array}
             */
            this.excluded = [];

            /**
             * Configuration of the Claroline Resource Picker
             * @type {object}
             */
            this.secondaryResourcesPicker = {
                name: 'picker-secondary-resources',
                parameters: {
                    isPickerMultiSelectAllowed: true,
                    callback: function (nodes) {
                        if (angular.isObject(nodes)) {
                            for (var nodeId in nodes) {
                                if (nodes.hasOwnProperty(nodeId)) {
                                    var node = nodes[nodeId];

                                    // Initialize a new Resource object (parameters : claro type, mime type, id, name)
                                    var resource = ResourceService.new(node[1], node[2], nodeId, node[0]);
                                    if (!ResourceService.exists(this.resources, resource)) {
                                        // Resource is not in the list => add it
                                        this.resources.push(resource);
                                    }
                                }
                            }

                            $scope.$apply();

                            // Remove checked nodes for next time
                            nodes = {};
                        }
                    }.bind(this)
                }
            };

            /**
             * Icons of the Resources
             * @type {object}
             */
            this.resourceIcons = EditorApp.resourceIcons;

            /**
             * Display resource in new window tab
             * @param resource
             */
            this.showResource = function showResource(resource) {
                // Retrieve resource type
                var resourceRoute = Routing.generate('claro_resource_open', {
                    node: resource.resourceId,
                    resourceType: resource.type
                });

                window.open(resourceRoute, '_blank');
            };

            /**
             * Delete selected resource from path
             * @type {object}
             */
            this.removeResource = function removeResource(resource) {
                ConfirmService.open(
                    // Confirm options
                    {
                        title:         Translator.trans('resource_delete_title',   { resourceName: resource.name }, 'path_editor'),
                        message:       Translator.trans('resource_delete_confirm', {}                             , 'path_editor'),
                        confirmButton: Translator.trans('resource_delete',         {}                             , 'path_editor')
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
            this.togglePropagation = function togglePropagation(resource) {
                resource.propagateToChildren = !resource.propagateToChildren;
            };

            /**
             * Toggle excluded flag
             * @param {object} resource
             */
            this.toggleExcluded = function toggleExcluded(resource) {
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
        }
    ]);
})();