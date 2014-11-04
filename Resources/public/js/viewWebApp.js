var websiteApp = angular.module('websiteApp', ['ngSanitize', 'mgcrea.ngStrap', 'utilitiesApp', 'resizerApp', window.menuNgPlugin]);

websiteApp.run(['$rootScope', function($rootScope){
    $rootScope.pageLoaded = true;
}]);

angular.element(document).ready(function() {
    angular.bootstrap(document, ['websiteApp']);
});