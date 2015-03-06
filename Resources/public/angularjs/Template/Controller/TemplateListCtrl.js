/**
 * Templates List Controller
 * Manages
 */
(function () {
    'use strict';

    angular.module('TemplateModule').controller('TemplateListCtrl', [
        'ClipboardService',
        'AlertService',
        'TemplateService',
        function TemplateListCtrl(ClipboardService, AlertService, TemplateService) {
            this.templates = [];

            /**
             * Copy a Template into clipboard
             * @param   {*} template - the Template to copu
             * @returns {TemplateListCtrl}
             */
            this.copy = function(template) {
                ClipboardService.copy(template);

                return this;
            };

            /**
             * Edit a Template
             * @param   {*} template - the Template to edit
             * @returns {TemplateListCtrl}
             */
            this.edit = function (template) {
                return this;
            };

            /**
             * Delete a Template
             * @param   {*} template - the Template to delete
             * @returns {TemplateListCtrl}
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

                return this;
            };
        }
    ]);
})();