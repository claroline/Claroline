import angular from 'angular/index'

import bannerCtrl from './banner.controller'
import bannerRenderer from './banner.partial.html'
import bannerConfigurator from './bannerConfigurator.partial.html'

angular
  .module('blog.banner', [])
  .controller('bannerCtrl', bannerCtrl)
  .run(['$templateCache', ($templateCache) => {
    $templateCache.put('banner.html', bannerRenderer)
    $templateCache.put('bannerConfigurator.html', bannerConfigurator)
  }])
  .directive('banner', () => {
    return {
      restrict: 'E',
      controller: 'bannerCtrl',
      controllerAs: 'bannerCtrl',
      templateUrl: 'banner.html'
    }
  })
  .directive('bannerConfigurator', () => {
    return {
      restrict: 'E',
      controller: 'bannerCtrl',
      controllerAs: 'bannerCtrl',
      templateUrl: 'bannerConfigurator.html'
    }
  })