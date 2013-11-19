/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global sinon */
alert('ok')
(function () {
    'use strict';
    window.Claroline = this.Claroline || {};
    var server = window.Claroline.FakeServer = {};

    server.create = function () {
        var sinonServer = sinon.fakeServer.create();
        sinonServer.respondWith('GET', /^\/resource\/directory\/(\d+)$/, function (xhr, directoryId) {
            xhr.respond(200, { 'Content-Type': 'application/json' }, JSON.stringify(
                {
                    path: [{id: 1, name: 'Root'}],
                    creatableTypes: [],
                    resources: [
                        {
                            id: 2,
                            name: 'Foo (child of ' + directoryId + ')',
                            type: 'directory'
                        },
                        {
                            id: 3,
                            name: 'Bar (child of ' + directoryId + ')',
                            type: 'directory'
                        }
                    ]
                }
            ));
        });

        return sinonServer;
    };
})();