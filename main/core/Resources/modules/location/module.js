import 'angular/angular.min'

import bootstrap from 'angular-bootstrap'

import LocationAPIService from './Service/LocationAPIService'
import LocationController from './Controller/LocationController'
import EditLocationModalController from './Controller/EditLocationModalController'
import CreateLocationModalController from './Controller/CreateLocationModalController'
import ClarolineAPI from '../services/module'

var LocationManager = angular.module('LocationManager', [
    'ClarolineAPI',
    'ui.bootstrap.tpls',
    'ui.translation',
    'data-table',
    'ui.router',
    'ncy-angular-breadcrumb'
])
    .service('LocationAPIService', LocationAPIService)
    .controller('EditLocationModalController', EditLocationModalController)
    .controller('CreateLocationModalController', CreateLocationModalController)
    .controller('LocationController', ['$http', 'LocationAPIService', '$uibModal', 'ClarolineAPIService', LocationController])
