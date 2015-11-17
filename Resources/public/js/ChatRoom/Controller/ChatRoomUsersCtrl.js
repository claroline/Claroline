/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    angular.module('ChatRoomModule').controller('ChatRoomUsersCtrl', [
        '$scope', 
        '$rootScope',
        function ($scope, $rootScope) {
            $scope.users = [];

            $scope.addUser = function (username, name, color) {
                var isPresent = false;
                for (var i = 0; i < $scope.users.length; i++) {
                    var currentUsername =  $scope.users[i]['username'];

                    if (username === currentUsername) {
                        isPresent = true;
                        break;
                    }
                }

                if (!isPresent) {
                    $scope.users.push({username: username, name: name, color: color});
                    $scope.$apply();
                    $rootScope.$broadcast('newPresenceEvent', {name: name, status: 'connection'});
                }
            };

            $scope.removeUser = function (username) {

                for (var i = 0; i < $scope.users.length; i++) {
                    var currentUsername =  $scope.users[i]['username'];

                    if (username === currentUsername) {
                        var currentName = $scope.users[i]['name'];
                        $scope.users.splice(i, 1);
                        $scope.$apply();
                        $rootScope.$broadcast('newPresenceEvent', {name: currentName, status: 'disconnection'});
                        break;
                    }
                }
            };

            $scope.$on('userConnectionEvent', function (event, userDatas) {
                $scope.addUser(userDatas['username'], userDatas['name'], userDatas['color']);
            });

            $scope.$on('userDisconnectionEvent', function (event, username) {
                $scope.removeUser(username);
            });
        }
    ]);
})();