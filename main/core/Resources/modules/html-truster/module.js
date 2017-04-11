import angular from 'angular/index'

angular
  .module('ui.html-truster', [])
  .filter('trust_html', ['$sce', function ($sce){
    return function (text) {
      return $sce.trustAsHtml(text)
    }
  }])
