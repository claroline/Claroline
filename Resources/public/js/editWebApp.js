var websiteApp = angular.module('websiteApp', ['ngSanitize', 'resizerApp', 'angularFileUpload', 'utilitiesApp', 'treeApp', 'ui.tree', 'mgcrea.ngStrap', 'ui.tinymce', 'ui.resourcePicker', 'wxy.pushmenu', 'ui.flexnav']);

websiteApp.run(['$rootScope', function($rootScope){
    $rootScope.pageLoaded = true;
}]);