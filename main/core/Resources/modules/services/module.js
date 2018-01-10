import angular from 'angular/index'
import 'angular-sanitize'
import 'angular-ui-bootstrap'

import ConfirmModalController from './Controller/ConfirmModalController'
import ClarolineAPIService from './Service/ClarolineAPIService'
import Interceptors from '../interceptorsDefault'
import '../html-truster/module'

angular.module('ClarolineAPI', ['ui.bootstrap', 'ui.bootstrap.tpls', 'ui.html-truster'])
    .config(Interceptors)
    .controller('ConfirmModalController', ['callback', 'urlObject', 'title', 'content', '$http', '$uibModalInstance', ConfirmModalController])
    .service('ClarolineAPIService', ClarolineAPIService)
