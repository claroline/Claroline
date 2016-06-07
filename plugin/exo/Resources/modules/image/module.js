/**
 * Image module
 */

import 'angular-bootstrap'

import ImageAreaService from './Services/ImageAreaService'
import AreasDirective from './Directives/ImageAreasDirective'
import ImagePointerDirective from './Directives/ImagePointerDirective'

angular.module('Image', ['ui.bootstrap'])
  .service('ImageAreaService', ImageAreaService)
  .directive('imgAreas', ['ImageAreaService', AreasDirective])
  .directive('imgPointer', ['$window', '$document', ImagePointerDirective])
