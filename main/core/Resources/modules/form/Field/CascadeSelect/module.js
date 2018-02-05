import angular from 'angular/index'
import '#/main/core/innova/angular-translation'
import '../../HelpBlock/module'
import CascadeSelectDirective from './CascadeSelectDirective'

angular.module('FieldCascadeSelect', ['ui.translation', 'HelpBlock']).directive('formCascadeSelect', () => new CascadeSelectDirective)
