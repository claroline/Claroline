/**
 * Step Controller
 */
(function () {
    'use strict';

    angular.module('StepModule').controller('StepFormCtrl', [
        '$scope',
        'StepService',
        'step',
        'inheritedResources',
        function StepFormCtrl($scope, StepService, step, inheritedResources) {
            /**
             * Path to public dir
             * @type {string}
             */
            this.webDir = EditorApp.webDir;

            /**
             * Current edited Step
             * @type {object}
             */
            this.step = step;

            /**
             * Inherited resources from parents of the Step
             * @type {array}
             */
            this.inheritedResources = inheritedResources || [];

            // Defines which panels of the form are collapsed or not
            this.collapsedPanels = {
                description       : false,
                properties        : true
            };

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
             * Display activity linked to the Step
             */
            this.showActivity = function () {
                var activityRoute = Routing.generate('innova_path_show_activity', {
                    activityId: this.step.activityId
                });

                window.open(activityRoute, '_blank');
            };

            /**
             * Delete the link between the Activity and the Step (Step's data are kept)
             */
            this.deleteActivity = function () {
                this.step.activityId = null;
            };
        }
    ]);
})();