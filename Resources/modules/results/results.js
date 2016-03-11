import angular from 'angular/index'
import {} from 'angular-bootstrap'
import listTemplate from './list.component.html'
import controller from './list.component.js'
import service from './results.service.js'

angular.module('ResultModule', ['ui.bootstrap'])
  .service('resultService', [
    '$http',
    service
  ])
  .factory('resultModal', [
    '$uibModal',
    $modal => ({
      open: template => $modal.open({ template })
    })
  ])
  .controller('resultCtrl', [
    'resultService',
    'resultModal',
    controller
  ])
  .directive('results', () => ({
    controllerAs: 'vm',
    bindToController: true,
    controller: 'resultCtrl',
    template: listTemplate
  }))
  .directive('validUser', () => ({
    require: 'ngModel',
    link: (scope, elm, attrs, ctrl) => {
      ctrl.$validators.validUser = modelValue =>
        ctrl.$isEmpty(modelValue) ||
        scope.$parent.vm.users.some(user => user.name === modelValue)
    }
  }))
