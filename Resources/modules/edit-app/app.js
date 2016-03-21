import angular from 'angular/index'
import bsColorpicker from 'mjolnic-bootstrap-colorpicker/dist/js/bootstrap-colorpicker'
import ngFileUploadShim from 'ng-file-upload/ng-file-upload-shim'
import ngResource from 'angular-resource'
import ngRoute from 'angular-route'
import ngTouch from 'angular-touch'
import ngAnimate from 'angular-animate'
import ngSanitize from 'angular-sanitize'
import ngFileUpload from 'ng-file-upload'
import ngStrap from 'angular-strap'
import ngStrapTpl from 'angular-strap/dist/angular-strap.tpl'
import ngUiTree from 'angular-ui-tree'
import MultilevelPushMenu from '../vendor/angular-ui/multi-level-push-menu/pushmenu'
import EditAppConfig from './edit-app.config'

angular
  .module('app',
  [
    'ngSanitize',
    'ui.resizer',
    'ngFileUpload',
    'website.constants',
    'components.utilities',
    'blocks.httpInterceptor',
    'blocks.router',
    'ui.tree',
    'mgcrea.ngStrap',
    'ui.tinymce',
    'ui.resourcePicker',
    'wxy.pushmenu',
    'ui.flexnav',
    'bs.colorpicker'
  ]
)
  .config(EditAppConfig.config)
  .run(EditAppConfig.run)

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'app' ]);
})