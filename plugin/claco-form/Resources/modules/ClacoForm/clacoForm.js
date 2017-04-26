/*
 * This file is part of the Claroline Connect package.
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import angular from 'angular/index'

import 'angular-ui-router'
import 'angular-bootstrap'
import 'angular-animate'
import 'angular-ui-translation/angular-translation'
import 'angular-loading-bar'
import 'angular-ui-tinymce'

import '#/main/core/fos-js-router/module'
import '#/main/core/form/module'
import '../Field/field'
import '../Category/category'
import '../Keyword/keyword'
import '../Entry/entry'
import '../Comment/comment'

import Routing from './routing.js'
import MenuCtrl from './Controller/MenuCtrl'
import GeneralConfigurationCtrl from './Controller/GeneralConfigurationCtrl'
import FieldsManagementCtrl from './Controller/FieldsManagementCtrl'
import CategoriesManagementCtrl from './Controller/CategoriesManagementCtrl'
import KeywordsManagementCtrl from './Controller/KeywordsManagementCtrl'
import TemplateManagementCtrl from './Controller/TemplateManagementCtrl'
import EntriesListCtrl from './Controller/EntriesListCtrl'
import EntryCreationCtrl from './Controller/EntryCreationCtrl'
import EntryEditionCtrl from './Controller/EntryEditionCtrl'
import EntryViewCtrl from './Controller/EntryViewCtrl'
import EntryRandomCtrl from './Controller/EntryRandomCtrl'
import ClacoFormService from './Service/ClacoFormService'

angular.module('ClacoFormModule', [
  'ui.router',
  'ui.translation',
  'ngAnimate',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'angular-loading-bar',
  'ngTable',
  'ui.fos-js-router',
  'FormBuilder',
  'ui.tinymce',
  'FieldModule',
  'CategoryModule',
  'KeywordModule',
  'EntryModule',
  'CommentModule'
])
.service('ClacoFormService', ClacoFormService)
.controller('MenuCtrl', ['$state', 'ClacoFormService', 'EntryService', MenuCtrl])
.controller('GeneralConfigurationCtrl', ['$state', 'ClacoFormService', 'CategoryService', 'EntryService', 'FieldService', GeneralConfigurationCtrl])
.controller('FieldsManagementCtrl', ['NgTableParams', 'ClacoFormService', 'FieldService', FieldsManagementCtrl])
.controller('CategoriesManagementCtrl', ['NgTableParams', 'ClacoFormService', 'CategoryService', CategoriesManagementCtrl])
.controller('KeywordsManagementCtrl', ['NgTableParams', 'ClacoFormService', 'KeywordService', KeywordsManagementCtrl])
.controller('TemplateManagementCtrl', ['$state', 'ClacoFormService', 'FieldService', TemplateManagementCtrl])
.controller('EntriesListCtrl', ['NgTableParams', 'ClacoFormService', 'EntryService', 'FieldService', 'CategoryService', EntriesListCtrl])
.controller('EntryCreationCtrl', ['$state', 'ClacoFormService', 'EntryService', 'FieldService', 'KeywordService', EntryCreationCtrl])
.controller('EntryEditionCtrl', ['$state', '$stateParams', 'ClacoFormService', 'EntryService', 'FieldService', 'CategoryService', 'KeywordService', EntryEditionCtrl])
.controller('EntryViewCtrl', ['$state', '$stateParams', '$filter', 'NgTableParams', 'ClacoFormService', 'EntryService', 'FieldService', 'CategoryService', 'KeywordService', 'CommentService', EntryViewCtrl])
.controller('EntryRandomCtrl', ['$state', 'ClacoFormService', EntryRandomCtrl])
.directive('template', function ($compile) {
  return {
    restrict: 'A',
    replace: true,
    link: function (scope, element, attrs) {
      scope.$watch(attrs.template, (tpl) => {
        element.html(tpl)
        $compile(element.contents())(scope)
      })
    }
  }
})
.config(Routing)
.config([
  'cfpLoadingBarProvider',
  function configureLoadingBar(cfpLoadingBarProvider) {
    // Configure loader
    cfpLoadingBarProvider.latencyThreshold = 200
    cfpLoadingBarProvider.includeBar = true
    cfpLoadingBarProvider.includeSpinner = true
  }
])
