
import GroupController from './Controller/GroupController'
import EditModalController from './Controller/EditModalController'
import CreateModalController from './Controller/CreateModalController'
import UserListController from './Controller/UserListController'
import GroupManager from './Service/GroupAPIService'
import Routing from './routing.js'

angular.module('GroupsManager', ['ClarolineSearch', 'data-table', 'ui.router', 'ncy-angular-breadcrumb'])
    .controller('GroupController', ['$http', 'ClarolineSearchService', 'clarolineAPI', '$uibModal', GroupController])
    .controller('CreateModalController', CreateModalController)
    .controller('EditModalController', EditModalController)
    .controller('UserListController', ['$http', 'ClarolineSearchService', '$stateParams', 'GroupAPI', 'clarolineAPI', UserListController])
    .factory('GroupAPI', GroupManager)
    .config(Routing);