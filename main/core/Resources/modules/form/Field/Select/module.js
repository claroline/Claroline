import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import SelectDirective from './SelectDirective'
import HelpBlock from '../../HelpBlock/module'

angular.module('FieldSelect', ['ui.translation', 'HelpBlock']).directive('formSelect', () => new SelectDirective)
