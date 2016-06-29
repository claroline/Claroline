import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import LangDirective from './LangDirective'

angular.module('FieldLang', ['ui.translation']).directive('formLang', () => new LangDirective)
