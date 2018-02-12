import angular from 'angular/index'
import '#/main/core/innova/angular-translation'

import LangDirective from './LangDirective'

angular.module('FieldLang', ['ui.translation']).directive('formLang', () => new LangDirective)
