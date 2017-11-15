import BindHtmlDirective from './bindHtmlDirective'
import CollectionFormController from './collectionFormController'
import CollectionFormDirective from './collectionFormDirective'
import ConfirmClickDirective from './confirmClickDirective'
import LoadingFormDirective from './loadingFormDirective'
import ScrollContainerDirective from './scrollContainerDirective'

import angular from 'angular/index'
import '../translation'
import '../../comments/commentsApp'

var appDirectives = angular.module('app.directives', [])

appDirectives.directive('bindHtml', ['$compile', BindHtmlDirective])
appDirectives.controller('collectionFormController', ['$scope', '$attrs', CollectionFormController])
appDirectives.directive('collectionForm', CollectionFormDirective)
appDirectives.directive('confirmClick', ['$parse', 'translationService', ConfirmClickDirective])
appDirectives.directive('loadingForm', ['$parse', LoadingFormDirective])
appDirectives.directive('scrollContainer', ['commentsManager', '$timeout', ScrollContainerDirective])
