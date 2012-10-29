(function () {
    this.Claroline = this.Claroline || {};
    this.Claroline.FakeServer = server = {};

    var routes = [
        ['GET', /^\/resources\/\d+$/, 200, {'Content-Type': 'application/json'}, JSON.stringify(
            [
                {
                    id:1
                },
                {
                    id:2
                }
            ])
        ]
    ];

    server.create = function () {
        var sinonServer = sinon.fakeServer.create();

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

        return sinonServer;
    };
})();