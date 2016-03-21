import angular from 'angular/index'
import EditAppConfig from 'EditAppConfig'

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