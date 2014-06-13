'use strict';

function StepCtrl($scope, $http, HistoryFactory, PathFactory, StepFactory, ResourceFactory) {
    // Store resource icons
    $scope.resourceIcons = EditorApp.resourceIcons;
    $scope.resourceZoom = 75;

    // Resource Picker config
    $scope.resourcePickerConfig = {
        isPickerMultiSelectAllowed: true,
        isPickerOnly: true,
        isWorkspace: true,
        webPath: EditorApp.webDir,
        appPath: EditorApp.appDir,
        directoryId: EditorApp.wsDirectoryId,
        resourceTypes: EditorApp.resourceTypes,
        pickerCallback: function (nodes) {
            if (typeof nodes === 'object' && nodes.length !== 0) {
                if (typeof $scope.previewStep.resources !== 'object') {
                    $scope.previewStep.resources = [];
                }

                for (var nodeId in nodes) {
                    var node = nodes[nodeId];

                    // Check if resource has already been linked to the the step
                    var resourceExists = false;
                    for (var i = 0; i < $scope.previewStep.resources.length; i++) {
                        var res = $scope.previewStep.resources[i];
                        if (res.resourceId === nodeId) {
                            resourceExists = true;
                            break;
                        }
                    }

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
        }
    };

    // Tiny MCE options
    $scope.tinymceOptions = {
        relative_urls: false,
        theme: 'modern',
        language: window.Claroline.Home.locale.trim(),
        browser_spellcheck : true,
        autoresize_min_height: 100,
        autoresize_max_height: 500,
        content_css: window.Claroline.Home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css',
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

    $scope.decrementDuration = function(type) {
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

    $scope.correctDuration = function(type) {
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
    $scope.removeResource = function(resource) {
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
     * Exclude a resource herited from parents
     */
    $scope.excludeParentResource= function(resource) {
        resource.isExcluded = true;
        $scope.previewStep.excludedResources.push(resource.id);

        // Update history
        HistoryFactory.update($scope.path);
    };

    /**
     * Include a resource herited from parents which has been excluded
     */
    $scope.includeParentResource= function(resource) {
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
}