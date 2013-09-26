'use strict';

/**
 * Tree Controller
 * @todo : éclater en 5 Controllers minimum (Global / Skills / Scenario / Planner / Validation)
 */
var TreeCtrlProto = [
     '$scope',
     '$http',
     '$modal',
     '$routeParams',
     '$location',
     'ClipboardFactory',
     'HistoryFactory',
     'PathFactory',
     'StepFactory',
     'ResourceFactory',
     function($scope, $http, $modal, $routeParams, $location, ClipboardFactory, HistoryFactory, PathFactory, StepFactory, ResourceFactory) {
         $scope.previewStep = null;
         
         $scope.sortableOptions = {
             update: $scope.update,
             placeholder: 'placeholder',
             connectWith: '.ui-sortable'
         };
         
         $scope.setPreviewStep = function(step) {
             if (step) {
                 $scope.previewStep = step;
             }
             else if (null !== $scope.path && undefined !== $scope.path.steps[0]) {
                 $scope.previewStep = $scope.path.steps[0];
             }
             
             $scope.inheritedResources = ResourceFactory.getInheritedResources($scope.previewStep);
         };
         
         $scope.update = function() {
             var e, i, _i, _len, _ref;
             _ref = $scope.path;
             for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
                 e = _ref[i];
                 e.pos = i;
             }
             HistoryFactory.update(_ref);
         };
         
         $scope.updatePreviewStep = function() {
             // Update preview step
             var step = null;
             if (null !== $scope.previewStep) {
                 step = PathFactory.getStepById($scope.previewStep.id);
             }
             $scope.setPreviewStep(step);
         };
         
         $scope.rename = function() {
             if (undefined != $scope.path.steps[0]) {
                 $scope.path.steps[0].name = $scope.path.name;
             }
             
             HistoryFactory.update($scope.path);
         };
         
         $scope.remove = function(step) {
             function walk(path) {
                 var children = path.children;
                 var i;
                 
                 if (children) {
                     i = children.length;
                     while (i--) {
                         if (children[i] === step) {
                             return children.splice(i, 1);
                         } else {
                             walk(children[i]);
                         }
                     }
                 }
             }
             
             walk($scope.path.steps[0]);
             
             HistoryFactory.update($scope.path);
             
             $scope.updatePreviewStep();
         };
         
         $scope.removeChildren = function(step) {
             step.children = [];
             HistoryFactory.update($scope.path);
         };
         
         $scope.copyToClipboard = function(step) {
             ClipboardFactory.copy(step);
         };
         
         $scope.paste = function(step) {
             ClipboardFactory.paste(step);
             HistoryFactory.update($scope.path);
         };
         
         $scope.addChild = function(step) {
             var newStep = StepFactory.generateNewStep(step);
             step.children.push(newStep);
             HistoryFactory.update($scope.path);
         };
         
         $scope.addSibling = function(step) {
             var newStep = StepFactory.generateNewStep(step);
             
             function insertStep(steps) {
                 var stepInserted = false;
                 for (var i = 0; i < steps.length; i++) {
                     if (steps[i].id === step.id) {
                         steps.splice(i+1, 0, newStep);
                         stepInserted = true;
                     }
                     else {
                         stepInserted = insertStep(steps[i].children);
                     }
                     
                     if (stepInserted) {
                         break;
                     }
                 }
                 return stepInserted;
             }
             
             insertStep($scope.path.steps);
             
             HistoryFactory.update($scope.path);
         };
         
         var dialogOptions = {
             backdrop: true,
             keyboard: false,
             backdropClick: false,
         };
         
         $scope.openTemplateEdit = function(step) {
             StepFactory.setStep(step);
             var d = $dialog.dialog(dialogOptions);
             d.open('partials/modals/template-edit.html', 'TemplateModalCtrl');
         };
         
         $scope.editStep = function(step) {
             StepFactory.setStep(step);
             
             // Create modal form
             var options = jQuery.extend(true, {}, dialogOptions);
             options.dialogClass = 'step-edit';
             
             var d = $dialog.dialog(options);
             d.open('partials/modals/step-edit.html', 'StepModalCtrl')
              .then(function(step) {
                  if (step) {
                      // Inject edited step in path
                      PathFactory.replaceStep(step);
                      
                      // Update history
                      HistoryFactory.update($scope.path);
                  }
              });
         };
         
         // Resources management
         $scope.editResource = function(resourceType, resource) {
             var editResource = false;
             
             if (resource) {
                 editResource = true;
                 // Edit existing document
                 ResourceFactory.setResource(resource);
             }
             
             var options = jQuery.extend(true, {}, dialogOptions);
             
             // Send resource type to form
             options.resolve = {
                 resourceType: function() {
                     return resourceType;
                 }
             };
             
             var d = $dialog.dialog(options);
             d.open('partials/modals/resource-edit.html', 'ResourceModalCtrl')
              .then(function(resource) {
                  if (resource) {
                      // Save resource
                      if (editResource) {
                          // Edit existing resource
                          // Replace old resource by the new one
                          for (var i = 0; i < $scope.previewStep.resources.length; i++) {
                              if ($scope.previewStep.resources[i].id === resource.id) {
                                  $scope.previewStep.resources[i] = resource;
                                  break;
                              }
                          }
                      }
                      else {
                          // Create new resource
                          $scope.previewStep.resources.push(resource);
                      }
                      
                      // Update history
                      HistoryFactory.update($scope.path);
                  }
              });
         };
         
         $scope.removeResource = function(resource) {
             // Search resource to remove
             for (var i = 0; i < $scope.previewStep.resources.length; i++) {
                 if (resource.id === $scope.previewStep.resources[i].id) {
                     $scope.previewStep.resources.splice(i, 1);
                     
                     // Update history
                     HistoryFactory.update($scope.path);
                     break;
                 }
             }
         };
         
         $scope.excludeParentResource= function(resource) {
             resource.isExcluded = true;
             $scope.previewStep.excludedResources.push(resource.id);
             
             // Update history
             HistoryFactory.update($scope.path);
         };
         
         $scope.includeParentResource= function(resource) {
             resource.isExcluded = false;
             for (var i = 0; i < $scope.previewStep.excludedResources.length; i++) {
                 if (resource.id == $scope.previewStep.excludedResources[i]) {
                     $scope.previewStep.excludedResources.splice(i, 1);
                 }
             }
             
             // Update history
             HistoryFactory.update($scope.path);
         };
     }
 ];