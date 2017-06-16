'use strict'

import angular from 'angular/index'

angular.module('app').run([
  '$templateCache',
  function ($templateCache) {
    $templateCache.put('menu_renderer.tpl',
      '<a id="{{data.id}}" ng-style="menuButtonStyle(data)" href="#">{{data.title}}</a>' +
      '<ul ng-if="data.children.length>0" flex-nav ng-model="data.children">' +
      '<li ng-repeat="data in data.children" ng-include="\'menu_renderer.tpl\'"></li>' +
      '</ul>'
    )
  }
])
