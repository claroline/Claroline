'use strict';

/**
 * Template Modal Controller
 */
var TemplateModalCtrlProto = [
    '$scope',
    '$http',
    '$modalInstance',
    'StepFactory',
    'TemplateFactory',
    function($scope, $http, $modalInstance, StepFactory, TemplateFactory) {
        var editTemplate = false;
        
        var currentTemplate = TemplateFactory.getCurrentTemplate();
        if (null === currentTemplate) {
            // Create new Template
            var stepToSave = jQuery.extend(true, {}, StepFactory.getStep());
            $scope.formTemplate = {
                name : 'Template ' + stepToSave.name,
                description : '',
                step: stepToSave
            };
        }
        else {
            // Edit existing template
            editTemplate = true;
            
            TemplateFactory.setCurrentTemplate(null);
            $scope.formTemplate = jQuery.extend(true, {}, currentTemplate); // Create a copy to not affect original data before user save
        }
        
        /**
         * Close template edit
         * @returns void
         */
        $scope.close = function() {
            $modalInstance.dismiss('cancel');
        };
        
        /**
         * Save template modifications in DB
         * return void
         */
        $scope.save = function (formTemplate) {
            function removeResources(step) {
                step.excludedResources = [];
                step.resources = [];
                
                if (step.children.length !== 0) {
                    for (var i = 0; i < step.children.length; i++) {
                        removeResources(step.children[i]);
                    }
                }
            }
            
            if (!formTemplate.withResources) {
                // No need to save step resources => remove them
                removeResources(formTemplate.step);
            }
            
            if (!editTemplate) {
                // Create new template
                $http
                    .post('../api/index.php/path/templates.json', formTemplate)
                    .success(function(response) {
                        formTemplate.id = response;
                        TemplateFactory.addTemplate(formTemplate);
                        $modalInstance.close();
                    });
            }
            else {
                // Update existing template
                $http
                    .put('../api/index.php/path/templates/' + formTemplate.id + '.json', formTemplate)
                    .success ( function (response) {
                        TemplateFactory.replaceTemplate(formTemplate);
                        $modalInstance.close();
                    });
            }
        }
    }
];