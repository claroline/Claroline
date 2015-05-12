/**
 * Step Controller
 */
(function () {
    'use strict';

    angular.module('StepModule').controller('StepFormCtrl', [
        '$scope',
        'StepService',
        function StepFormCtrl($scope, StepService) {
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

            this.inheritedResources = [];

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
                            for (var nodeId in nodes) {
                                if (nodes.hasOwnProperty(nodeId)) {
                                    // Load activity properties to populate step
                                    StepService.loadActivity(this.step, nodeId);

                                    break; // We need only one node, so only the last one will be kept
                                }
                            }

                            $scope.$apply();

                            // Remove checked nodes for next time
                            nodes = {};
                        }
                    }.bind(this)
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
                            for (var nodeId in nodes) {
                                if (nodes.hasOwnProperty(nodeId)) {
                                    var node = nodes[nodeId];

                                    // Link resource to step
                                    StepService.addPrimaryResource(this.step, node[2], nodeId, node[0]);

                                    break; // We need only one node, so only the first one will be kept
                                }
                            }

                            $scope.$apply();

                            // Remove checked nodes for next time
                            nodes = {};
                        }
                    }.bind(this)
                }
            };

            // Secondary resources picker config
            this.secondaryResourcesPicker = {
                name: 'picker-secondary-resources',
                parameters: {
                    isPickerMultiSelectAllowed: true,
                    callback: function (nodes) {
                        if (typeof nodes === 'object' && nodes.length !== 0) {
                            for (var nodeId in nodes) {
                                if (nodes.hasOwnProperty(nodeId)) {
                                    var node = nodes[nodeId];

                                    // Link resource to step
                                    StepService.addSecondaryResource(this.step, node[2], nodeId, node[0]);
                                }
                            }

                            $scope.$apply();

                            // Remove checked nodes for next time
                            nodes = {};
                        }
                    }.bind(this)
                }
            };


            // Tiny MCE options
            this.tinymceOptions = {
                relative_urls: false,
                theme: 'modern',
                language: EditorApp.locale,
                browser_spellcheck : true,
                entity_encoding : "numeric",
                autoresize_min_height: 150,
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
                StepService.removeResource(this.step, resource);
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
            this.excludeParentResource = function (resource) {
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