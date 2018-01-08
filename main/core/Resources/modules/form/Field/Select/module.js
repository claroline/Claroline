import angular from 'angular/index'
import 'angular-ui-translation/angular-translation'

import SelectDirective from './SelectDirective'
import '../../HelpBlock/module'

angular.module('FieldSelect', ['ui.translation', 'HelpBlock']).directive('formSelect', () => new SelectDirective)
