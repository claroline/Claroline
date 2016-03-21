/**
 * Created by ptsavdar on 15/03/16.
 */
import angular from 'angular/index'

import ngResource from 'angular-resource'
import ngRoute from 'angular-route'
import ngTouch from 'angular-touch'
import ngAnimate from 'angular-animate'
import ngSanitize from 'angular-sanitize'
import ngStrap from 'angular-strap'
import ngStrapTpl from 'angular-strap.tpl'
import ViewAppConfig from 'ViewAppConfig'
import MainController from 'MainController'
import MultilevelPushMenu from '../vendor/angular-ui/multi-level-push-menu/pushmenu'

//import utilities from '../components/utilities/utilities.module'

angular
  .module('app',
  [
    'ngSanitize',
    'mgcrea.ngStrap',
    'ui.resizer',
    'website.constants',
    window.menuNgPlugin
  ]
)
  .run(ViewAppConfig.run)
  .controller('MainController', MainController)

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'app' ]);
});