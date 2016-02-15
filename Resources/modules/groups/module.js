import GroupController from './Controller/GroupController'
import EditModalController from './Controller/EditModalController'
import CreateModalController from './Controller/CreateModalController'
import UserListController from './Controller/UserListController'
import GroupAPIService from './Service/GroupAPIService'
import Routing from './routing.js'
import ClarolineAPI from '../services/module'

angular.module('GroupsManager', ['ClarolineSearch', 'data-table', 'ui.router', 'ncy-angular-breadcrumb'])
    .controller('GroupController', ['$http', 'ClarolineSearchService', 'ClarolineAPIService', '$uibModal', GroupController])
    .controller('CreateModalController', CreateModalController)
    .controller('EditModalController', EditModalController)
    .controller('UserListController', ['$http', 'ClarolineSearchService', '$stateParams', 'GroupAPIService', 'ClarolineAPIService', UserListController])
    .service('GroupAPIService', GroupAPIService)
    .config(Routing);