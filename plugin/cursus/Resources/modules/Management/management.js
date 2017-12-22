/*
 * This file is part of the Claroline Connect package.
 *
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
import 'angular-breadcrumb'
import 'angular-loading-bar'

import '#/main/core/fos-js-router/module'
import '../Cursus/cursus'
import '../Course/course'
import '../Session/session'
import '../SessionEvent/sessionEvent'
import '../DocumentModel/documentModel'

import Routing from './routing.js'
import RootCursusManagementCtrl from './Controller/RootCursusManagementCtrl'
import CursusManagementCtrl from './Controller/CursusManagementCtrl'
import CoursesManagementCtrl from './Controller/CoursesManagementCtrl'
import CourseManagementCtrl from './Controller/CourseManagementCtrl'
import SessionsManagementCtrl from './Controller/SessionsManagementCtrl'
import SessionManagementCtrl from './Controller/SessionManagementCtrl'
import SessionEventManagementCtrl from './Controller/SessionEventManagementCtrl'
import SessionCreationCoursesListModalCtrl from './Controller/SessionCreationCoursesListModalCtrl'
import GeneralParametersCtrl from './Controller/GeneralParametersCtrl'
import LocationsManagementCtrl from './Controller/LocationsManagementCtrl'
import DocumentModelsManagementCtrl from './Controller/DocumentModelsManagementCtrl'
import DocumentModelCreationCtrl from './Controller/DocumentModelCreationCtrl'
import DocumentModelEditionCtrl from './Controller/DocumentModelEditionCtrl'
import LocationCreationModalCtrl from './Controller/LocationCreationModalCtrl'
import LocationEditionModalCtrl from './Controller/LocationEditionModalCtrl'
import CertificateMailEditionCtrl from './Controller/CertificateMailEditionCtrl'

angular.module('CursusManagementModule', [
  'ui.router',
  'ui.translation',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ngAnimate',
  'ncy-angular-breadcrumb',
  'angular-loading-bar',
  'ngTable',
  'ui.fos-js-router',
  'CursusModule',
  'CourseModule',
  'SessionModule',
  'SessionEventModule',
  'DocumentModelModule'
])
.controller('RootCursusManagementCtrl', ['CursusService', RootCursusManagementCtrl])
.controller('CursusManagementCtrl', ['$stateParams', 'CursusService', CursusManagementCtrl])
.controller('CoursesManagementCtrl', ['NgTableParams', 'CourseService', 'SessionService', CoursesManagementCtrl])
.controller('CourseManagementCtrl', ['$stateParams', 'NgTableParams', 'CourseService', 'SessionService', 'DocumentModelService', CourseManagementCtrl])
.controller('SessionsManagementCtrl', ['$uibModal', 'NgTableParams', 'SessionService', 'SessionEventService', SessionsManagementCtrl])
.controller('SessionManagementCtrl', ['$stateParams', 'NgTableParams', 'CourseService', 'SessionService', 'SessionEventService', 'DocumentModelService', SessionManagementCtrl])
.controller('SessionEventManagementCtrl', ['$stateParams', 'NgTableParams', 'CourseService', 'SessionService', 'SessionEventService', 'DocumentModelService', SessionEventManagementCtrl])
.controller('GeneralParametersCtrl', ['$state', 'CourseService', GeneralParametersCtrl])
.controller('LocationsManagementCtrl', ['$http', '$uibModal', 'NgTableParams', 'ClarolineAPIService', LocationsManagementCtrl])
.controller('DocumentModelsManagementCtrl', ['NgTableParams', 'CourseService', 'DocumentModelService', DocumentModelsManagementCtrl])
.controller('DocumentModelCreationCtrl', ['$http', '$state', 'CourseService', DocumentModelCreationCtrl])
.controller('DocumentModelEditionCtrl', ['$http', '$stateParams', '$state', 'CourseService', 'DocumentModelService', DocumentModelEditionCtrl])
.controller('SessionCreationCoursesListModalCtrl', SessionCreationCoursesListModalCtrl)
.controller('LocationCreationModalCtrl', LocationCreationModalCtrl)
.controller('LocationEditionModalCtrl', LocationEditionModalCtrl)
.controller('CertificateMailEditionCtrl', ['$http', '$state', 'CourseService', 'DocumentModelService', CertificateMailEditionCtrl])
.config(Routing)
.config([
  'cfpLoadingBarProvider',
  function configureLoadingBar(cfpLoadingBarProvider) {
    // Configure loader
    cfpLoadingBarProvider.latencyThreshold = 200
    cfpLoadingBarProvider.includeBar = true
    cfpLoadingBarProvider.includeSpinner = true
    //cfpLoadingBarProvider.spinnerTemplate = '<div class="loading">Loading&#8230;</div>';
  }
])