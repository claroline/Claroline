// TODO remove me
// Used by : cursus, flashcard, video-player

import angular from 'angular/index'
import '#/main/core/innova/angular-translation'
import 'ng-file-upload'

import './Field/Checkbox/module'
import './Field/Checkboxes/module'
import './Field/Select/module'
import './Field/Text/module'
import './Field/RichText/module'
import './Field/Radio/module'
import './Field/Number/module'
import './Field/Country/module'
import './Field/Date/module'
import './Field/File/module'
import './Field/Lang/module'
import './Field/Email/module'
import './Field/CascadeSelect/module'

import FormDirective from './FormDirective'
import FormBuilderService from './FormBuilderService'
import FieldDirective from './FieldDirective'

angular.module('FormBuilder', [
  'ui.translation',
  'ngFileUpload',
  'FieldCheckbox',
  'FieldCheckboxes',
  'FieldSelect',
  'FieldText',
  'FieldRichText',
  'FieldRadio',
  'FieldNumber',
  'FieldCountry',
  'FieldDate',
  'FieldFile',
  'FieldLang',
  'FieldEmail',
  'FieldCascadeSelect'
])
  .directive('formbuilder', () => new FormDirective)
  .directive('formField', () => new FieldDirective)
  .service('FormBuilderService', FormBuilderService)
