import 'angular/angular.min'

angular.module('ui.html-truster', ['ngSanitize'])
    .filter('trust_html', ['$sce', function($sce){
        return function(text) {
            return $sce.trustAsHtml(text);
        };
    }]);