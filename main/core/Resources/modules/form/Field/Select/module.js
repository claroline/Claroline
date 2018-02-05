import angular from 'angular/index'
import '#/main/core/innova/angular-translation'

import SelectDirective from './SelectDirective'
import '../../HelpBlock/module'

angular.module('FieldSelect', ['ui.translation', 'HelpBlock']).directive('formSelect', () => new SelectDirective)
