import angular from 'angular/index'
import 'angular-ui-translation/angular-translation'
import '../../HelpBlock/module'
import CascadeSelectDirective from './CascadeSelectDirective'

angular.module('FieldCascadeSelect', ['ui.translation', 'HelpBlock']).directive('formCascadeSelect', () => new CascadeSelectDirective)
