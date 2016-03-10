import angular from 'angular/index'
import {} from 'angular-bootstrap'
import formTemplate from './result-form.component.html'
import listTemplate from './results.component.html'
import controller from './results.component.js'
import service from './results.service.js'

angular.module('ResultModule', ['ui.bootstrap'])
  .service('resultService', ['$http', service])
  .factory('resultModal', ['$uibModal', $modal => ({
    open: () => $modal.open({ template: formTemplate })
  })])
  .controller('resultCtrl', ['resultService', 'resultModal', controller])
  .directive('results', () => ({
    controllerAs: 'vm',
    bindToController: true,
    controller: 'resultCtrl',
    template: listTemplate
  }))
