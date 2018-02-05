import angular from 'angular/index'
import '#/main/core/innova/angular-translation'

import RichTextDirective from './RichTextDirective'

angular.module('FieldRichText', ['ui.translation', 'HelpBlock'])
  .directive('formRichText', ['$parse', '$compile', ($parse, $compile) => new RichTextDirective($parse, $compile)])
