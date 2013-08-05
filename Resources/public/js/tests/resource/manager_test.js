(function () {

    'use strict';

    describe('The resource manager', function () {

        beforeEach(function () {
            this.server = Claroline.FakeServer.create();
            this.defaultParameters = {
                'parentElement': $(document.createElement('div')),
                'resourceTypes': {
                    'file': {name: 'Fichier'},
                    'directory': {name: 'Répertoire'},
                    'foo': {name: 'Foo', customActions: {'bar': {name: 'Bar', route: '/some_route'}}}
                }
            };
            this.manager = Claroline.ResourceManager;
        });

        afterEach(function () {
            if (Backbone.History.started) {
                Backbone.history.stop();
            }

            this.server.restore();
        });

        it('builds two master views by default', function () {
            this.manager.initialize(this.defaultParameters);
            expect(_.size(this.manager.Controller.views)).toEqual(2);
        });

        it('builds one master view in picker mode', function () {
            this.defaultParameters.isPickerOnly = true;
            this.manager.initialize(this.defaultParameters);
            expect(_.size(this.manager.Controller.views)).toEqual(1);
        });

        it('displays the root directories by default', function () {
            this.manager.initialize(this.defaultParameters);
            this.server.respond();
            var resources = $('.resource', this.manager.Controller.views.main.subViews.resources.$el);
            expect(resources.length).toEqual(2);
            expect($('.resource-name', resources[0]).text()).toEqual('Foo (child of 0)');
            expect($('.resource-name', resources[1]).text()).toEqual('Bar (child of 0)');
        });

        it('can display a specific directory', function () {
            this.defaultParameters.directoryId = '12';
            this.manager.initialize(this.defaultParameters);
            this.server.respond();
            var resources = $('.resource', this.manager.Controller.views.main.subViews.resources.$el);
            expect(resources.length).toEqual(2);
            expect($('.resource-name', resources[0]).text()).toEqual('Foo (child of 12)');
            expect($('.resource-name', resources[1]).text()).toEqual('Bar (child of 12)');
        });
    });
})();