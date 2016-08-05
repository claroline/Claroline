/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import configTemplate from './Partial/config.html'

export default function($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('tab', {
      url: '/tab/{tabId}',
      template: configTemplate,
      controller: 'AdminHomeTabsConfigCtrl',
      controllerAs: 'ahtcc'
    })
  //$stateProvider
  //  .state ('config', {
  //    url: '/config',
  //    template: configTemplate,
  //    controller: 'AdminHomeTabsConfigCtrl',
  //    controllerAs: 'ahtcc'
  //  })

  $urlRouterProvider.otherwise('/tab/-1')
}
