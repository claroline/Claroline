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