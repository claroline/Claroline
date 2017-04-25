import angular from 'angular/index'

import Interceptors from '#/main/core/interceptorsDefault'

import 'angular-data-table/release/dataTable.helpers.min'
import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'angular-ui-router'
import 'angular-breadcrumb'
import 'angular-ui-tree'

import Routing from './routing.js'
import '../services/module'
import  '../location/module'
import EditOrganizationModalController from './Controller/EditOrganizationModalController'
import OrganizationController from './Controller/OrganizationController'
import OrganizationAPIService from './Service/OrganizationAPIService'

angular.module('OrganizationManager', [
  'ui.router',
  'ui.tree',
  'ui.bootstrap.tpls',
  'LocationManager',
  'ui.translation',
  'ClarolineAPI',
  'ncy-angular-breadcrumb'
])
    .controller('EditOrganizationModalController', EditOrganizationModalController)
    .controller('OrganizationController', ['$http', 'OrganizationAPIService', '$uibModal', 'ClarolineAPIService', OrganizationController])
    .service('OrganizationAPIService', OrganizationAPIService)
    .config(Routing)
    .config(Interceptors)
