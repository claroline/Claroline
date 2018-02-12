import angular from 'angular/index'

import '#/main/core/innova/angular-translation'
import CheckListDirective from './CheckListDirective'
import CheckboxesDirective from './CheckboxesDirective'

angular.module('FieldCheckboxes', ['ui.translation'])
  .directive('checklistModel', ['$parse', '$compile', ($parse, $compile) => new CheckListDirective($parse, $compile)])
  .directive('formCheckboxes', () => new CheckboxesDirective)
