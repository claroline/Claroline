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

    angular.module('XmppModule').factory('XmppService', [
        '$rootScope',
        function ($rootScope) {
            var connection = null;
            var username = null;
            var password = null;
            var firstName = null;
            var lastName = null;
            var color = null;
            var xmppHost = null;
            var boshPort = null;
            var boshService = null;

             var onConnectionCallback = function (status) {

                if (status === Strophe.Status.CONNECTED) { 
                    console.log('Connected');
                    connection.send($pres().c('priority').t('-1'));
                    $rootScope.$broadcast('xmppConnectedEvent');
                } else if (status === Strophe.Status.CONNFAIL) {
                    console.log('Connection failed !');
                } else if (status === Strophe.Status.DISCONNECTED) {
                    console.log('Disconnected');
                } else if (status === Strophe.Status.CONNECTING) {
                    console.log('Connecting...');
                } else if (status === Strophe.Status.DISCONNECTING) {
                    console.log('Disconnecting...');   
                }
            };

            return {
                connect: function (
                    server,
                    port, 
                    usernameParam, 
                    passwordParam, 
                    firstNameParam, 
                    lastNameParam, 
                    colorParam
                ) {
                    xmppHost = server;
                    boshPort = port;
                    boshService = 'http://' + server + ':' + boshPort + '/http-bind';
                    username = usernameParam;
                    password = passwordParam;
                    firstName = firstNameParam;
                    lastName = lastNameParam;
                    color = colorParam;

                    connection = new Strophe.Connection(boshService);
                    connection.connect(
                        username + '@' + xmppHost,
                        password, 
                        onConnectionCallback
                    );
                },
                getConnection: function () {

                    return connection;
                },
                getXmppHost: function () {
                  
                    return xmppHost;
                },
                getUsername: function () {

                    return username;
                },
                getFirstName: function () {

                    return firstName;
                },
                getLastName: function () {

                    return lastName;
                },
                getColor: function () {

                    return color;
                }
            };
        }
    ]);
})();