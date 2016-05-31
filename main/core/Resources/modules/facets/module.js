import 'angular/angular.min'

import bootstrap from 'angular-bootstrap'
import translation from 'angular-ui-translation/angular-translation'
import dragula from 'angular-dragula/dist/angular-dragula'

import Interceptors from '../interceptorsDefault'
import FacetManagementDirective from './Directive/FacetManagementDirective'
import ModalController from './Controller/ModalController'
import FieldModalController from './Controller/FieldModalController'
import FacetRolesController from './Controller/FacetRolesController'
import PanelRolesController from './Controller/PanelRolesController'
import '../form/module'
import '../services/module'

angular.module('FacetManager', [
  'ui.bootstrap',
  'ui.translation',
  'FormBuilder',
  'ClarolineAPI',
  dragula(angular)
])
  .directive('facetManagement', () => new FacetManagementDirective)
  .controller('ModalController', ModalController)
  .controller('FieldModalController', FieldModalController)
  .controller('FacetRolesController', FacetRolesController)
  .controller('PanelRolesController', PanelRolesController)
  .config(Interceptors)
