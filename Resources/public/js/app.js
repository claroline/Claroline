var websiteApp = angular.module('websiteApp', ['ngSanitize', 'resizerApp', 'angularFileUpload','utilitiesApp', 'treeApp', 'ui.tree', 'mgcrea.ngStrap', 'ui.tinymce']);

websiteApp.run(['$rootScope', function($rootScope){
    $rootScope.pageLoaded = true;
}]);