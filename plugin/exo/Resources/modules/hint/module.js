
import angular from 'angular/index'

import '#/main/core/fos-js-router/module'
import '#/main/core/modal/module'
import './../paper/module'

import HintService from './Services/HintService'
import HintsCtrl from './Controllers/HintsCtrl'
import HintsDirective from './Directives/HintsDirective'
import HintDirective from './Directives/HintDirective'

angular
  .module('Hint', [
    'ui.fos-js-router',
    'ui.modal',
    'Paper'
  ])
  .service('HintService', [
    '$q',
    '$http',
    'url',
    'UserPaperService',
    HintService
  ])
  .controller('HintsCtrl', [
    'HintService',
    HintsCtrl
  ])
  .directive('hints', [
    () => new HintsDirective
  ])
  .directive('hint', [
    () => new HintDirective
  ])
