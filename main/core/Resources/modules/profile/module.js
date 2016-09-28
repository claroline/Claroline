import angular from 'angular/index'

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import '../asset/module'
import '../html-truster/module'
import '../fos-js-router/module'

import Interceptors from '../interceptorsDefault'
import ProfileDirective from './Directive/ProfileDirective'
import '../form/module'
import '../services/module'

angular.module('UserProfile', [
  'ui.bootstrap',
  'ui.fos-js-router',
  'ui.translation',
  'ClarolineAPI',
  'ui.asset',
  'ui.html-truster',
  'FormBuilder',
  'ngTable'
])
  .directive('userProfile', () => new ProfileDirective)
  .config(Interceptors)
