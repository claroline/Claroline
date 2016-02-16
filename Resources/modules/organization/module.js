import 'angular/angular.min'
import dataTable from 'angular-data-table/release/dataTable.helpers.min'
import bootstrap from 'angular-bootstrap'
import translation from 'angular-ui-translation/angular-translation'
import UIRouter from 'angular-ui-router'
import breadcrumbs from 'angular-breadcrumb'
import uiTree from 'angular-ui-tree'

import Routing from './routing.js'
import ClarolineAPI from '../services/module'
import LocationManager from '../location/module'
import EditOrganizationModalController from './Controller/EditOrganizationModalController'
import OrganizationController from './Controller/OrganizationController'
import OrganizationAPIService from './Service/OrganizationAPIService'

var OrganizationManager = angular.module('OrganizationManager', [
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