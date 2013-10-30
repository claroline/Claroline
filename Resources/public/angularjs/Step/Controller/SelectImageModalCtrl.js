'use strict';

/**
 * Template Modal Controller
 */
function SelectImageModalCtrl($scope, $modalInstance, StepFactory) {
    $scope.images = StepFactory.getImages();
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    /**
     * Save selected image in step
     * @returns void
     */
    $scope.select = function(image) {
        $modalInstance.close(image);
    };
    
    /**
     * Close select image modal
     * @returns void
     */
    $scope.close = function() {
        $modalInstance.dismiss('cancel');
    };
}