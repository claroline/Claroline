import LocationAPIService from './Service/LocationAPIService' 
import CreateModalController from './Controller/CreateModalController' 
import EditModalController from './Controller/EditModalController'
import LocationController from './Controller/LocationController' 

var LocationManager = angular.module('LocationManager', [
    'ClarolineAPI',
    'ui.bootstrap.tpls',
    'ui.translation',
    'data-table',
    'ui.router',
    'ncy-angular-breadcrumb'
])
    .service('LocationAPIService', LocationAPIService)
    .controller('LocationController', ['$http', 'LocationAPIService', '$uibModalStack', '$uibModal', LocationController])
    .controller('CreateModalController', CreateModalController)
    .controller('EditModalController', EditModalController)