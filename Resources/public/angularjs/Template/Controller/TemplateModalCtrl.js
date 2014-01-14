'use strict';

/**
 * Template Modal Controller
 */
function TemplateModalCtrl($scope, $http, $modalInstance, StepFactory, TemplateFactory, AlertFactory) {
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    $scope.pathTemplateName = {};
    $scope.pathTemplateName.isUnique = false;
    
    var editTemplate = false;
    
    var currentTemplate = TemplateFactory.getCurrentTemplate();
    if (null === currentTemplate) {
        // Create new Template
        var stepToSave = jQuery.extend(true, {}, StepFactory.getStep());
        $scope.formTemplate = {
            id: null,
            name : 'Template ' + stepToSave.name,
            description : '',
            step: stepToSave
        };
    }
    else {
        // Edit existing template
        editTemplate = true;
        
        // Name is unique because it comes from the db
        $scope.pathTemplateName.isUnique = true;
        
        TemplateFactory.setCurrentTemplate(null);
        $scope.formTemplate = jQuery.extend(true, {}, currentTemplate); // Create a copy to not affect original data before user save
    }
    
    /**
     * Check if path template name is unique
     */
    $scope.checkTemplateNameIsUnique = function() {
        $http({
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            url: Routing.generate('innova_pathtemplate_check_unique_name'),
            data: 'pathTemplateName=' + $scope.formTemplate.name
        })
        .success(function (data) {
            $scope.pathTemplateName.isUnique = data == 'true' ? true : false;
        });
    };
    
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
        var data = 'name=' + formTemplate.name + '&description=' + formTemplate.description + '&structure=' + angular.toJson(formTemplate.structure);
        
        if (editTemplate) {
            // Update existing path
            method = 'PUT';
            route = Routing.generate('innova_path_edit_pathtemplate', {id: formTemplate.id});
        }
        else {
            // Create new path
            method = 'POST';
            route = Routing.generate('innova_path_add_pathtemplate');
        }
        
        $http({
            method: method,
            url: route,
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            data: data
        })
        .success(function (data) {
            if (editTemplate) {
                // Update success
                AlertFactory.addAlert('success', 'Template updated.');
            }
            else {
                // Create success
                AlertFactory.addAlert('success', 'Template created.');
                formTemplate.id = data;
            }
            
            TemplateFactory.replaceTemplate(formTemplate);
            $modalInstance.close();
        })
        .error(function(data, status) {
            AlertFactory.addAlert('danger', 'Error while saving template.');
        });
    }
}