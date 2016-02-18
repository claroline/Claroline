import 'angular/angular.min'

import GroupController from './Controller/GroupController'
import EditGroupModalController from './Controller/EditGroupModalController'
import CreateGroupModalController from './Controller/CreateGroupModalController'
import UserListController from './Controller/UserListController'
import GroupAPIService from './Service/GroupAPIService'
import Routing from './routing.js'
import ClarolineAPIService from '../services/module'

angular.module('GroupsManager', ['ClarolineSearch', 'data-table', 'ui.router', 'ncy-angular-breadcrumb'])
    .controller('GroupController', ['$http', 'ClarolineSearchService', 'ClarolineAPIService', '$uibModal', GroupController])
    .controller('CreateGroupModalController', CreateGroupModalController)
    .controller('EditGroupModalController', EditGroupModalController)
    .controller('UserListController', ['$http', 'ClarolineSearchService', '$stateParams', 'GroupAPIService', 'ClarolineAPIService', UserListController])
    .service('GroupAPIService', GroupAPIService)
    .config(Routing);
