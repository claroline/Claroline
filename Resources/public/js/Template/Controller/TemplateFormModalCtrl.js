/**
 * Template Modal Controller
 */
(function () {
    'use strict';

    angular.module('TemplateModule').controller('TemplateFormModalCtrl', [
        '$uibModalInstance',
        'template',
        function ($uibModalInstance, template) {
            this.template = template;

            // Store symfony base partials route
            this.webDir = AngularApp.webDir;

            /**
             * Close template edit
             * @returns void
             */
            this.close = function() {
                $uibModalInstance.dismiss('cancel');
            };

            /**
             * Save template modifications in DB
             * @returns void
             */
            this.save = function () {
                this.template.$save();
            }
        }
    ]);
})();