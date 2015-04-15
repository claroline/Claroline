/**
 * Step Controller
 */
(function () {
    'use strict';

    angular.module('StepModule').controller('StepFormCtrl', [
        '$scope',
        '$http',
        'HistoryService',
        'PathService',
        'StepService',
        'ResourceService',
        function ($scope, $http, HistoryService, PathService, StepService, ResourceService) {
            /**
             * Path to public dir
             * @type {string}
             */
            this.webDir = EditorApp.webDir;

            /**
             * Current edited Step
             * @type {object}
             */
            this.step = null;

            // Defines which panels of the form are collapsed or not
            this.collapsedPanels = {
                description       : false,
                resourcePrimary   : false,
                resourceSecondary : true,
                properties        : true
            };

            // Store resource icons
            $scope.resourceIcons = EditorApp.resourceIcons;

            // Activity resource picker config
            this.activityResourcePicker = {
                name: 'picker-activity',
                parameters: {
                    // A step can allow be linked to one Activity, so disable multi-select
                    isPickerMultiSelectAllowed: false,

                    // Only allow Activity selection
                    typeWhiteList: [ 'activity' ],
                    callback: function (nodes) {
                        if (typeof nodes === 'object' && nodes.length !== 0) {
                            // We need only one node, so only the last one will be kept
                            for (var nodeId in nodes) {
                                // Load activity properties to populate step
                                $http.get(Routing.generate('innova_path_load_activity', { nodeId: nodeId }))
                                    .success(function (data) {
                                        if (typeof data !== 'undefined' && data !== null && data.length !== 0) {
                                            // Populate step
                                            $scope.previewStep.activityId  = data['id'];
                                            $scope.previewStep.name        = data['name'];
                                            $scope.previewStep.description = data['description'];

                                            // Primary resources
                                            $scope.previewStep.primaryResource = data['primaryResource'];

                                            // Secondary resources
                                            if (null !== data['resources']) {
                                                for (var i = 0; i < data['resources'].length; i++) {
                                                    var resource = data['resources'][i];
                                                    var resourceExists = StepService.hasResource($scope.previewStep, resource.resourceId);
                                                    if (!resourceExists) {
                                                        // Generate new local ID
                                                        resource['id'] = PathService.getNextResourceId();

                                                        // Add to secondary resources
                                                        $scope.previewStep.resources.push(resource);
                                                    }
                                                }
                                            }

                                            // Parameters
                                            $scope.previewStep.withTutor = data['withTutor'];
                                            $scope.previewStep.who       = data['who'];
                                            $scope.previewStep.where     = data['where'];
                                            $scope.previewStep.duration  = data['duration'];
                                        }
                                    });
                            }

                            $scope.$apply();

                            // Update history
                            HistoryService.update($scope.path);
                        }
                    }
                }
            };

            // Primary resource picker config
            this.primaryResourcePicker = {
                name: 'picker-primary-resource',
                parameters: {
                    // A step can allow be linked to one primary Resource, so disable multi-select
                    isPickerMultiSelectAllowed: false,

                    // Do not allow Path and Activities as primary resource to avoid Inception
                    typeBlackList: [ 'innova_path', 'activity' ],

                    // On select, set the primary resource of the step
                    callback: function (nodes) {
                        if (typeof nodes === 'object' && nodes.length !== 0) {
                            // We need only one node, so only the last one will be kept
                            for (var nodeId in nodes) {
                                var node = nodes[nodeId];
                                $scope.previewStep.primaryResource = {
                                    resourceId: nodeId,
                                    name: node[0],
                                    type: node[2]
                                }
                            }

                            $scope.$apply();

                            // Update history
                            HistoryService.update($scope.path);

                        }
                    }
                }
            };

            // Secondary resources picker config
            this.secondaryResourcesPicker = {
                name: 'picker-secondary-resources',
                parameters: {
                    isPickerMultiSelectAllowed: true,
                    callback: function (nodes) {
                        if (typeof nodes === 'object' && nodes.length !== 0) {
                            if (typeof $scope.previewStep.resources !== 'object') {
                                $scope.previewStep.resources = [];
                            }

                            for (var nodeId in nodes) {
                                var node = nodes[nodeId];

                                // Check if resource has already been linked to the the step
                                var resourceExists = StepService.hasResource($scope.previewStep, nodeId);
                                if (!resourceExists) {
                                    // Resource need to be linked
                                    var resource = ResourceService.new();
                                    resource.name = node[0];
                                    resource.type = node[2];
                                    resource.resourceId = nodeId;

                                    $scope.previewStep.resources.push(resource);
                                    $scope.$apply();
                                }
                            }

                            // Update history
                            HistoryService.update($scope.path);
                        }

                        // Remove checked nodes for next time
                        nodes = {};
                    }
                }
            };


            // Tiny MCE options
            this.tinymceOptions = {
                relative_urls: false,
                theme: 'modern',
                language: EditorApp.locale,
                browser_spellcheck : true,
                entity_encoding : "numeric",
                autoresize_min_height: 100,
                autoresize_max_height: 500,
                plugins: [
                    'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars fullscreen',
                    'insertdatetime media nonbreaking save table directionality',
                    'template paste textcolor emoticons code'
                ],
                toolbar1: 'undo redo | styleselect | bold italic underline | forecolor | alignleft aligncenter alignright | preview fullscreen',
                paste_preprocess: function (plugin, args) {
                    var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery
                    var url = link.match(/^(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})$/);

                    if (url) {
                        args.content = '<a href="' + link + '">' + link + '</a>';
                        window.Claroline.Home.generatedContent(link, function (data) {
                            insertContent(data);
                        }, false);
                    }
                }
            };

            /**
             * Delete selected resource from path
             */
            this.removeResource = function (resource) {
                StepService.removeResource($scope.previewStep, resource.id);
            };

            this.enableResourcePropagation = function (resource) {
                resource.propagateToChildren = true;
            };

            this.disableResourcePropagation = function (resource) {
                resource.propagateToChildren = false;
            };

            /**
             * Exclude a resource inherited from parents
             */
            this.excludeParentResource= function (resource) {
                resource.isExcluded = true;
                this.step.excludedResources.push(resource.id);
            };

            /**
             * Include a resource inherited from parents which has been excluded
             */
            this.includeParentResource = function (resource) {
                resource.isExcluded = false;

                if (typeof this.step.excludedResources !== 'undefined' && null !== this.step.excludedResources) {
                    for (var i = 0; i < this.step.excludedResources.length; i++) {
                        if (resource.id == this.step.excludedResources[i]) {
                            this.step.excludedResources.splice(i, 1);
                        }
                    }
                }
            };

            this.removePrimaryResource = function () {
                this.step.primaryResource = null;
            };

            this.showActivity = function () {
                var activityRoute = Routing.generate('innova_path_show_activity', {
                    activityId: this.step.activityId
                });

                window.open(activityRoute, '_blank');
            };

            this.deleteActivity = function () {
                this.step.activityId = null;
            };
        }
    ]);
})();