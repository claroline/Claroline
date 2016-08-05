/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import homeTemplate from './Partial/home.html'

export default function($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('tab', {
      url: '/tab/{tabId}',
      template: homeTemplate,
      controller: 'WorkspaceHomeCtrl',
      controllerAs: 'whc'
    })

  $urlRouterProvider.otherwise('/tab/-1')
}
