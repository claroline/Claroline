import angular from 'angular/index'
import register from '../utils/register'
import {} from 'bootstrap-colorpicker/dist/js/bootstrap-colorpicker'
import {} from 'ng-file-upload/dist/ng-file-upload-shim'
import {} from 'angular-resource'
import {} from 'angular-route'
import {} from 'angular-touch'
import {} from 'angular-animate'
import {} from 'ng-file-upload'
import {} from 'angular-ui-bootstrap'
import {} from '../vendor/angular-ui/multi-level-push-menu/pushmenu'
import {} from '../components/resizer/resizer.module'
import {} from '../blocks/httpInterceptor/http-interceptor.module'
import {} from '../blocks/router/router.module'
import {} from 'angular-ui-tinymce'
import {} from '../components/flexnav/flexnav.module'
import {} from '../components/bsColorpicker/bs-colorpicker.module'
import {} from '../components/webtree/webtree.module'
import tinyMceConfig from '../components/tinymce/tinymce.config'
import EditAppConfig from './app.config'
import websiteOptions from './website-options.service'
import MainController from './main.controller.js'

let registerApp = new register('app',
  [
    'ui.resizer',
    'ngFileUpload',
    'website.constants',
    'components.utilities',
    'blocks.httpInterceptor',
    'blocks.router',
    'ui.webtree',
    'ui.bootstrap',
    'ui.tinymce',
    'ui.resourcePicker',
    'wxy.pushmenu',
    'ui.flexnav',
    'bs.colorpicker'
  ])
registerApp
  .config(EditAppConfig.config)
  .run(EditAppConfig.run)
  .value('tinyMceConfig', new tinyMceConfig())
  .filter('trustAsHtml', ['$sce', $sce => text => $sce.trustAsHtml(text)])
  .filter('trustAsResourceUrl', ['$sce', $sce => text => $sce.trustAsResourceUrl(text)])
  .service('websiteOptions', websiteOptions)
  .controller('MainController', MainController)

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'app' ])
})
