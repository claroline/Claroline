import angular from 'angular/index'

import 'angular-data-table/release/dataTable.helpers.min'
import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'angular-ui-router'
import 'angular-breadcrumb'

import UserController from './Controller/UserController'
import UserInfoModalController from './Controller/UserInfoModalController'
import RemoveByCsvModalController from './Controller/RemoveByCsvModalController'
import ImportCsvFacetsController from './Controller/ImportCsvFacetsController'
import UserAPIService from './Service/UserAPIService'
import '../groups/module'
import '../search/module'
import '../services/module'
import Routing from './routing.js'
import '../fos-js-router/module'
import '../form/module'
import '#/main/core/fos-js-router/module'

angular.module('UsersManager', [
  'ClarolineSearch',
  'ui.fos-js-router',
  'data-table',
  'ui.bootstrap.tpls',
  'ClarolineAPI',
  'ui.translation',
  'ui.router',
  'GroupsManager',
  'FormBuilder',
  'ncy-angular-breadcrumb'
]) .controller('UserController', ['$http', 'ClarolineSearchService', 'ClarolineAPIService', '$uibModal', UserController])
   .controller('RemoveByCsvModalController', RemoveByCsvModalController)
   .controller('UserInfoModalController', UserInfoModalController)
   .controller('ImportCsvFacetsController', ImportCsvFacetsController)
   .service(
     'UserAPIService', [
       '$http',
       'url',
       UserAPIService
     ])
   .config(Routing)
