/**
 * Templates List Controller
 * Manages
 */
(function () {
    'use strict';

    angular.module('TemplateModule').controller('TemplateSidebarCtrl', [
        '$uibModal',
        'ClipboardService',
        'AlertService',
        'TemplateService',
        function TemplateSidebarCtrl($uibModal, ClipboardService, AlertService, TemplateService) {
            this.templates = [];

            /**
             * Copy a Template into clipboard
             */
            this.copy = function(template) {
                ClipboardService.copy(template);
            };

            /**
             * Edit a Template
             * @param   {*} template - the Template to edit
             */
            this.edit = function (template) {
                var modalInstance = $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Template/Partial/modal-form.html',
                    controller: 'TemplateFormModalCtrl as templateFormModalCtrl',
                    resolve: {
                        template: function () {
                            return template;
                        }
                    }
                });
            };

            /**
             * Delete a Template
             * @param   {*} template - the Template to delete
             */
            this.delete = function (template) {
                TemplateService.delete(template).then(
                    function success() {
                        // Remove template from template list
                        var pos = this.templates.indexOf(template);
                        if (-1 !== pos) {
                            this.templates.splice(pos, 1);
                        }

                        // Show user confirmation message
                        AlertService.addAlert('success', 'Path template has been deleted.');
                    }.bind(this),
                    function error() {
                        // Display error message
                        AlertService.addAlert('error', 'Error while removing Path template.');
                    }
                );
            };
        }
    ]);
})();