import 'angular-sanitize'
import ConfirmModalController from './Controller/ConfirmModalController'
import ClarolineAPIService from './Service/ClarolineAPIService'
import Interceptors from '../interceptorsDefault'

angular.module('ClarolineAPI', ['ui.bootstrap', 'ui.bootstrap.tpls', 'ngSanitize'])
    .config(Interceptors)
    .controller('ConfirmModalController', ['callback', 'urlObject', 'title', 'content', '$http', '$uibModalInstance', ConfirmModalController])
    .service('ClarolineAPIService', ClarolineAPIService)

//ClarolineAPIService.$inject('$http', '$httpParamSerializer', '$uibModal')