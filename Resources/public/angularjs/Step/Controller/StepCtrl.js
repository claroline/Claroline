/**
 * Step Controller
 */
(function () {
    'use strict';

    angular.module('StepModule').controller('StepCtrl', [
        '$scope',
        '$http',
        'HistoryFactory',
        'PathFactory',
        'StepFactory',
        'ResourceFactory',
        function ($scope, $http, HistoryFactory, PathFactory, StepFactory, ResourceFactory) {
            // Store resource icons
            $scope.resourceIcons = EditorApp.resourceIcons;
            $scope.resourceZoom = 75;

            // Resource Picker base config
            $scope.resourcePickerConfig = {
                isPickerMultiSelectAllowed: false,
                webPath: EditorApp.webDir,
                appPath: EditorApp.appDir,
                directoryId: EditorApp.wsDirectoryId,
                resourceTypes: EditorApp.resourceTypes
            };

            // Activity resource picker config
            $scope.activityResourcePicker = {
                name: 'picker-activity',
                parameters: angular.copy($scope.resourcePickerConfig)
            };

            // Adapt default config for Activity picker
            $scope.activityResourcePicker.parameters.typeWhiteList = ['activity'];
            $scope.activityResourcePicker.parameters.callback = function (nodes) {
                if (typeof nodes === 'object' && nodes.length !== 0) {
                    // We need only one node, so only the last one will be kept
                    for (var nodeId in nodes) {
                        // Load activity properties to populate step
                        $http.get(Routing.generate('innova_path_load_activity', { workspaceId: EditorApp.workspaceId, nodeId: nodeId}))
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
                                            var resourceExists = StepFactory.hasResource($scope.previewStep, resource.resourceId);
                                            if (!resourceExists) {
                                                // Generate new local ID
                                                resource['id'] = PathFactory.getNextResourceId();

                                                // Add to secondary resources
                                                $scope.previewStep.resources.push(resource);
                                            }
                                        }
                                    }

                                    // Parameters
                                    $scope.previewStep.withTutor       = data['withTutor'];
                                    $scope.previewStep.who             = data['who'];
                                    $scope.previewStep.where           = data['where'];
                                    $scope.previewStep.durationHours   = data['durationHours'];
                                    $scope.previewStep.durationMinutes = data['durationMinutes'];
                                }
                            });
                    }

                    $scope.$apply();

                    // Update history
                    HistoryFactory.update($scope.path);
                }
            };

            // Primary resource picker config
            $scope.primaryResourcePicker = {
                name: 'picker-primary-resource',
                parameters: angular.copy($scope.resourcePickerConfig)
            };

            // Adapt default config for Primary resource picker
            $scope.primaryResourcePicker.parameters.typeBlackList = ['innova_path', 'activity'];
            $scope.primaryResourcePicker.parameters.callback = function (nodes) {
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
                    HistoryFactory.update($scope.path);

                }
            };

            // Secondary resources picker config
            $scope.secondaryResourcesPicker = {
                name: 'picker-secondary-resources',
                parameters: angular.copy($scope.resourcePickerConfig)
            };

            // Adapt default config for Secondary resources picker
            $scope.secondaryResourcesPicker.parameters.isPickerMultiSelectAllowed = true;
            $scope.secondaryResourcesPicker.parameters.callback = function (nodes) {
                if (typeof nodes === 'object' && nodes.length !== 0) {
                    if (typeof $scope.previewStep.resources !== 'object') {
                        $scope.previewStep.resources = [];
                    }

                    for (var nodeId in nodes) {
                        var node = nodes[nodeId];

                        // Check if resource has already been linked to the the step
                        var resourceExists = StepFactory.hasResource($scope.previewStep, nodeId);
                        if (!resourceExists) {
                            // Resource need to be linked
                            var resource = ResourceFactory.generateNewResource();
                            resource.name = node[0];
                            resource.type = node[2];
                            resource.resourceId = nodeId;

                            $scope.previewStep.resources.push(resource);
                            $scope.$apply();
                        }
                    }

                    // Update history
                    HistoryFactory.update($scope.path);
                }

                // Remove checked nodes for next time
                nodes = {};
            };

            // Tiny MCE options
            $scope.tinymceOptions = {
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

            $scope.incrementDuration = function (type) {
                if ('hour' === type) {
                    if (typeof $scope.previewStep.durationHours === 'undefined' || null === $scope.previewStep.durationHours || $scope.previewStep.durationHours.length === 0) {
                        $scope.previewStep.durationHours = 0;
                    }

                    $scope.previewStep.durationHours += 1;
                }
                else if ('minute' === type) {
                    if (typeof $scope.previewStep.durationMinutes === 'undefined' || null === $scope.previewStep.durationMinutes || $scope.previewStep.durationMinutes.length === 0) {
                        $scope.previewStep.durationMinutes = 0;
                    }

                    if ($scope.previewStep.durationMinutes + 5 < 60) {
                        $scope.previewStep.durationMinutes += 5;
                    }
                }

                // Update history
                HistoryFactory.update($scope.path);
            };

            $scope.decrementDuration = function (type) {
                if ('hour' === type) {
                    if (typeof $scope.previewStep.durationHours === 'undefined' || null === $scope.previewStep.durationHours || $scope.previewStep.durationHours.length === 0) {
                        $scope.previewStep.durationHours = 0;
                    }

                    if ($scope.previewStep.durationHours - 1 >= 0) { // Negative values are not allowed
                        $scope.previewStep.durationHours -= 1;
                    }
                }
                else if ('minute' === type) {
                    if (typeof $scope.previewStep.durationMinutes === 'undefined' || null === $scope.previewStep.durationMinutes || $scope.previewStep.durationMinutes.length === 0) {
                        $scope.previewStep.durationMinutes = 0;
                    }

                    if ($scope.previewStep.durationMinutes - 5 >= 0) { // Negative values are not allowed
                        $scope.previewStep.durationMinutes -= 5;
                    }
                }

                // Update history
                HistoryFactory.update($scope.path);
            };

            $scope.correctDuration = function (type) {
                // Don't allow negative value, so always return absolute value
                if ('hour' === type) {
                    if (typeof $scope.previewStep.durationHours === 'undefined' || null === $scope.previewStep.durationHours || $scope.previewStep.durationHours.length === 0) {
                        $scope.previewStep.durationHours = 0;
                    }

                    $scope.previewStep.durationHours = Math.abs($scope.previewStep.durationHours);
                }
                else if ('minute' === type) {
                    if (typeof $scope.previewStep.durationMinutes === 'undefined' || null === $scope.previewStep.durationMinutes || $scope.previewStep.durationMinutes.length === 0) {
                        $scope.previewStep.durationMinutes = 0;
                    }

                    $scope.previewStep.durationMinutes = Math.abs($scope.previewStep.durationMinutes);

                    // Don't allow more than 60 minutes
                    var minutesToHours = Math.floor($scope.previewStep.durationMinutes / 60);
                    if (minutesToHours > 0) {
                        if (typeof $scope.previewStep.durationHours === 'undefined' || null === $scope.previewStep.durationHours || $scope.previewStep.durationHours.length === 0) {
                            $scope.previewStep.durationHours = 0;
                        }

                        $scope.previewStep.durationHours += minutesToHours;
                        $scope.previewStep.durationMinutes = $scope.previewStep.durationMinutes % 60;
                    }
                }

                // Update history
                HistoryFactory.update($scope.path);
            };

            /**
             * Delete selected resource from path
             */
            $scope.removeResource = function (resource) {
                StepFactory.removeResource($scope.previewStep, resource.id);

                // Loop through path to remove reference to resource
                PathFactory.removeResource(resource.id);

                // Update history
                HistoryFactory.update($scope.path);
            };

            $scope.enableResourcePropagation = function (resource) {
                resource.propagateToChildren = true;

                // Update history
                HistoryFactory.update($scope.path);
            };

            $scope.disableResourcePropagation = function (resource) {
                resource.propagateToChildren = false;

                // Update history
                HistoryFactory.update($scope.path);
            };

            /**
             * Exclude a resource inherited from parents
             */
            $scope.excludeParentResource= function (resource) {
                resource.isExcluded = true;
                $scope.previewStep.excludedResources.push(resource.id);

                // Update history
                HistoryFactory.update($scope.path);
            };

            /**
             * Include a resource inherited from parents which has been excluded
             */
            $scope.includeParentResource= function (resource) {
                resource.isExcluded = false;

                if (typeof $scope.previewStep.excludedResources !== 'undefined' && null !== $scope.previewStep.excludedResources) {
                    for (var i = 0; i < $scope.previewStep.excludedResources.length; i++) {
                        if (resource.id == $scope.previewStep.excludedResources[i]) {
                            $scope.previewStep.excludedResources.splice(i, 1);
                        }
                    }
                }

                // Update history
                HistoryFactory.update($scope.path);
            };

            $scope.removePrimaryResource = function () {
                $scope.previewStep.primaryResource = null;

                // Update history
                HistoryFactory.update($scope.path);
            };

            $scope.showActivity = function () {
                var activityRoute = Routing.generate('innova_path_show_activity', {
                    workspaceId: EditorApp.workspaceId,
                    activityId: $scope.previewStep.activityId
                });

                window.open(activityRoute, '_blank');
            };

            $scope.deleteActivity = function () {
                $scope.previewStep.activityId = null;

                // Update history
                HistoryFactory.update($scope.path);
            };
        }
    ]);
})();