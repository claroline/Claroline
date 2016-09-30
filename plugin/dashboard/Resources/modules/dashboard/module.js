
import angular from 'angular/index'
import DashboardCtrl from './Controllers/DashboardCtrl'
import DetailsDirective from './Directives/DetailsDirective'

angular
  .module('Dashboard', [
    'Dashboards'
  ])
  .controller('DashboardCtrl',[
    'Translator',
    'WorkspaceService',
    'DashboardService',
    'user',
    'dashboard',
    'data',
    DashboardCtrl
  ])
  .directive('dashboardDetails', [
    DetailsDirective
  ])
