import angular from 'angular/index'
import 'angular-ui-bootstrap'
import '#/main/core/innova/angular-translation'

import FileDirective from './FileDirective'
import FileModelDirective from './FileModelDirective'
import '../../HelpBlock/module'

angular.module('FieldFile', [
  'ui.translation',
  'ui.bootstrap',
  'HelpBlock'
])
  .directive('formFile', () => new FileDirective)
  .directive('fileModel', ['$parse', ($parse) => new FileModelDirective($parse)])
