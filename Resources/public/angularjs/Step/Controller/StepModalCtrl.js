'use strict';

/**
 * Step Modal Controller
 */
function StepModalCtrl($scope, $modal, $modalInstance, PathFactory, StepFactory, HistoryFactory, ResourceFactory) {
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    // Store removed resources to remove their references from path when step will be saved
    var removedResources = [];
    
    // Create a copy to not affect original data before user save
    var localStep = jQuery.extend(true, {}, StepFactory.getStep());

    $scope.stepWho = StepFactory.getWhoList();
    $scope.stepWhere = StepFactory.getWhereList();
    
    $scope.formStep = localStep;
    $scope.inheritedResources = ResourceFactory.getInheritedResources(localStep);

    $scope.isRootNode = false;
    var path = PathFactory.getPath();
    if (undefined != path.steps[0] && path.steps[0].id == localStep.id) {
        // We are editing root node of tree => disable name field (it has the same name than path)
        $scope.isRootNode = true;
    }

    // Tiny MCE options
    if (typeof(configTinyMCE) != 'undefined' && null != configTinyMCE && configTinyMCE.length != 0) {
        // App as a config for tinyMCE => use it
        $scope.tinymceOptions = configTinyMCE;
    } 
    else {
        var home = window.Claroline.Home;

        var language = home.locale.trim();
        var contentCSS = home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css';
        
        // If no config, add default tiny
        $scope.tinymceOptions = {
            relative_urls: false,
            theme: 'modern',
            language: language,
            browser_spellcheck : true,
            autoresize_min_height: 100,
            autoresize_max_height: 500,
            content_css: contentCSS,
            plugins: [
                'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars fullscreen',
                'insertdatetime media nonbreaking save table directionality',
                'template paste textcolor emoticons code'
            ],
            toolbar1: 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | preview fullscreen resourcePicker',
            toolbar2: 'undo redo | forecolor backcolor emoticons | bullist numlist outdent indent | link image media print code',
            paste_preprocess: function (plugin, args) {
                var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery
                var url = link.match(/^(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})$/);

                if (url) {
                    args.content = '<a href="' + link + '">' + link + '</a>';
                    home.generatedContent(link, function (data) {
                        insertContent(data);
                    }, false);
                }
            }
        };
    }
    
    /**
     * Close step edit
     * @returns void
     */
    $scope.close = function() {
        $modalInstance.dismiss('cancel');
    };

    /**
     * Send back edited step to path
     * @returns void
     */
    $scope.save = function(formStep) {
        $modalInstance.close(formStep, removedResources);
    };

    /**
     * Select step image in library
     * @returns void
     */
    // $scope.selectImage = function() {
    //     var modalInstance = $modal.open({
    //         templateUrl: EditorApp.webDir + 'angularjs/Step/Partial/select-image.html',
    //         controller: 'SelectImageModalCtrl',
    //         resolve: {
    //             // Send images to form
    //             images: function() {
    //                 return StepFactory.getImages();
    //             }
    //         }
    //     });
        
    //     // Process modal results
    //     modalInstance.result.then(function(image) {
    //         if (image) {
    //             $scope.formStep.image = image;
    //         } 
    //     });
    // };
    
    /**
     * Edit or add resource
     * @returns void
     */
    $scope.editResource = function(resource) {
        var editResource = false;

        if (undefined != resource && null != resource) {
            editResource = true;
            // Edit existing document
            ResourceFactory.setResource(resource);
        }

        var modalInstance = $modal.open({
            templateUrl: EditorApp.webDir + 'angularjs/Resource/Partial/resource-edit.html',
            controller: 'ResourceModalCtrl'
        });

        // Process modal results
        modalInstance.result.then(function(resource) {
            if (resource) {
                if (typeof $scope.formStep.resources === 'undefined' || null === $scope.formStep.resources) {
                    $scope.formStep.resources = [];
                }
                // Save resource
                if (editResource) {
                    // Edit existing resource
                    // Replace old resource by the new one
                    for (var i = 0; i < $scope.formStep.resources.length; i++) {
                        if ($scope.formStep.resources[i].id === resource.id) {
                            $scope.formStep.resources[i] = resource;
                            break;
                        }
                    }
                }
                else {
                    // Create new resource
                    $scope.formStep.resources.push(resource);
                }
            } 
        });
    };

    /**
     * Delete resource from step
     * @returns void
     */
    $scope.removeResource = function(resource) {
        // Search resource to remove
        StepFactory.removeResource($scope.formStep, resource.id);
        
        // Store removed resource
        removedResources.push(resource.id);
    };

    /**
     * Exclude herited resource from parent step
     * @returns void
     */
    $scope.excludeParentResource= function(resource) {
        resource.isExcluded = true;
        $scope.formStep.excludedResources.push(resource.id);

        // Update history
        HistoryFactory.update($scope.path);
    };

    /**
     * Include herited resource from parent step
     * @returns void
     */
    $scope.includeParentResource= function(resource) {
        resource.isExcluded = false;
        for (var i = 0; i < $scope.previewStep.excludedResources.length; i++) {
            if (resource.id == $scope.previewStep.excludedResources[i]) {
                $scope.formStep.excludedResources.splice(i, 1);
            }
        }
          
        // Update history
        HistoryFactory.update($scope.path);
    };
}