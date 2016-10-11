
import angular from 'angular/index'
import '#/main/core/fos-js-router/module'

import DashboardService from './Services/DashboardService'
import DashboardsCtrl from './Controllers/DashboardsCtrl'
import SecondsToHmsFilter from './Filters/SecondsToHmsFilter'

angular
  .module('Dashboards', [])
  .controller('DashboardsCtrl', [
    'user',
    'dashboards',
    'DashboardService',
    DashboardsCtrl
  ])
  .service('DashboardService', [
    '$http',
    '$q',
    'url',
    DashboardService
  ])
  .filter('secondsToHms', [
    SecondsToHmsFilter
  ])
