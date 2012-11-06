(function () {
    this.Claroline = this.Claroline || {};
    var server = this.Claroline.FakeServer = {};

    server.create = function () {
        var sinonServer = sinon.fakeServer.create();
        sinonServer.respondWith('GET', /^\/resource\/children\/(\d+)$/, function (xhr, directoryId) {
            xhr.respond(200, { 'Content-Type': 'application/json' }, JSON.stringify(
                [
                    {
                        id: 1,
                        name: 'Foo (child of ' + directoryId + ')',
                        type: 'directory'
                    },
                    {
                        id: 2,
                        name: 'Bar (child of ' + directoryId + ')',
                        type: 'directory'
                    }
                ]
            ))
        });
        sinonServer.respondWith('GET', /^\/resource\/parents\/(\d+)$/, function (xhr, directoryId) {
            xhr.respond(200, { 'Content-Type': 'application/json' }, JSON.stringify(
                [
                    {
                        id: 1,
                        name: 'Foo (parent of ' + directoryId + ')',
                        type: 'directory'
                    },
                    {
                        id: 2,
                        name: 'Bar (parent of ' + directoryId + ')',
                        type: 'directory'
                    }
                ]
            ))
        });

/*
        for (var i = 0; i < routes.length; ++i) {
            sinonServer.respondWith(
                routes[i][0],
                routes[i][1],
                [
                    routes[i][2],
                    routes[i][3],
                    routes[i][4]
                ]
            );
        }
*/
        return sinonServer;
    };
})();