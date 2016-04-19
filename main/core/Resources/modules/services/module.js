import 'angular/angular.min'
import 'angular-sanitize'

import ConfirmModalController from './Controller/ConfirmModalController'
import ClarolineAPIService from './Service/ClarolineAPIService'
import Interceptors from '../interceptorsDefault'
import HtmlTruster from '../html-truster/module'
import bootstrap from 'angular-bootstrap'

angular.module('ClarolineAPI', ['ui.bootstrap', 'ui.bootstrap.tpls', 'ui.html-truster'])
    .config(Interceptors)
    .controller('ConfirmModalController', ['callback', 'urlObject', 'title', 'content', '$http', '$uibModalInstance', ConfirmModalController])
    .service('ClarolineAPIService', ClarolineAPIService)
