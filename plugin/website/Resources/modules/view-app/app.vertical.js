/**
 * Created by ptsavdar on 15/03/16.
 */
import angular from 'angular/index'

import {} from 'angular-animate'
import MainController from './main.controller'
import {} from '../vendor/angular-ui/multi-level-push-menu/pushmenu'
import {} from '../components/resizer/resizer.module'
import register from '../utils/register'

//import utilities from '../components/utilities/utilities.module'

let registerApp = new register('app', [
  'ui.resizer',
  'website.constants',
  'wxy.pushmenu'
])

registerApp
  .run(['$rootScope', ($rootScope) => {$rootScope.pageLoaded = true}])
  .controller('MainController', MainController)

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'app' ])
})
