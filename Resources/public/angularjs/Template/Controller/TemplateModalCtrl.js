'use strict';

/**
 * Template Modal Controller
 */
function TemplateModalCtrl($scope, $http, $modalInstance, StepFactory, TemplateFactory, AlertFactory) {
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    var editTemplate = false;
    var currentTemplate = TemplateFactory.getCurrentTemplate();
    if (null === currentTemplate) {
        // Create new Template
        var stepToSave = jQuery.extend(true, {}, StepFactory.getStep());
        $scope.formTemplate = {
            id: null,
            name : 'Template ' + stepToSave.name,
            description : '',
            structure: stepToSave
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
     * @returns void
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
            removeResources(formTemplate.structure);
        }
        
        var method = null;
        var route = null;
        
        var data = '';
        data += 'innova_path_template[name]=' + formTemplate.name;
        data += '&innova_path_template[description]=' + formTemplate.description;
        data += '&innova_path_template[structure]=' + angular.toJson(formTemplate.structure);
        
        
        if (editTemplate) {
            // Update existing path
            method = 'PUT';
            route = Routing.generate('innova_path_template_edit', {id: formTemplate.id});
        }
        else {
            // Create new path
            method = 'POST';
            route = Routing.generate('innova_path_template_add');
        }
        
        $http({
            method: method,
            url: route,
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            data: data
        })
        .success(function (data) {
            if ('error' != data) {
                // No error
                formTemplate.id = data;
                TemplateFactory.replaceTemplate(formTemplate);
                
                AlertFactory.addAlert('success', Translator.get('path_editor:path_template_save_success'));
            }
            else {
                // Server error while saving
                AlertFactory.addAlert('danger', Translator.get('path_editor:path_template_save_error'));
            }
            
            $modalInstance.close();
        })
        .error(function(data, status) {
            AlertFactory.addAlert('danger', Translator.get('path_editor:path_template_save_error'));
        });
    }
}