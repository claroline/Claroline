/**
 * Created by ptsavdar on 15/03/16.
 */
/**
 * Created by ptsavdar on 15/03/16.
 */
import angular from 'angular/index'

import {} from 'angular-animate'
import MainController from './main.controller'
import {} from '../components/flexnav/flexnav.module'
import {} from '../components/resizer/resizer.module'
import register from '../utils/register'

let registerApp = new register('app',
  [
    'ui.resizer',
    'website.constants',
    'ui.flexnav'
  ]
)

registerApp
  .run(['$rootScope', ($rootScope) => {$rootScope.pageLoaded = true}])
  .controller('MainController', ['website.data', MainController])

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'app' ])
})