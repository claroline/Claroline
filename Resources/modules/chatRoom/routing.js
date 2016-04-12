/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default function($stateProvider, $urlRouterProvider) {
    $stateProvider
        .state ('main', {
            url: '/main',
            template: require('./Partial/main.html'),
            controller: 'ChatRoomMainCtrl',
            controllerAs: 'crmc'
        })
        .state ('text', {
            url: '/text',
            template: require('./Partial/roomText.html'),
            controller: 'ChatRoomTextCtrl',
            controllerAs: 'crc'
        })
        .state ('video', {
            url: '/video',
            template: require('./Partial/roomVideo.html'),
            controller: 'ChatRoomVideoCtrl',
            controllerAs: 'crc'
        })

    $urlRouterProvider.otherwise('/main')
}
