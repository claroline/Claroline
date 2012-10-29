describe('The resource manager', function () {
    beforeEach(function () {
        this.manager = Claroline.ResourceManager;
    })

    describe('has a resource model', function () {
        it('which is defined', function () {
            expect(this.manager.Models.Resource).toBeDefined();
        });

        it('which can be instantiated', function () {
            var resource = new this.manager.Models.Resource();
            expect(resource).not.toBeNull();
        });
    });

    describe('has a directory collection', function () {
        it('which is defined', function () {
            expect(this.manager.Collections.Directory).toBeDefined();
        });

        it('which requires an id and a base url at initialization', function () {
            var manager = this.manager;

            // wrong initializations
            expect(function () {//console.debug(this.manager)
                new manager.Collections.Directory();
            }).toThrow(new Error('Directory must have an id'));

            expect(function () {
                new manager.Collections.Directory([], 1);
            }).toThrow(new Error('Directory must have a base url'));

            // valid initialization
            expect(
                new manager.Collections.Directory([], 1, 'fakeUrl')
            ).not.toBeNull();
        });

        it('whose id attribute is accessible', function () {
            var directory = new this.manager.Collections.Directory([], 1, 'fakeUrl');
            expect(directory.id).toBe(1);
        });

        describe('whose #fetch method', function () {
            beforeEach(function() {
                this.server = Claroline.FakeServer.create();
                this.directoryId = 1;
                this.fakeBaseUrl = '/resources';
                this.directory = new this.manager.Collections.Directory([], this.directoryId, this.fakeBaseUrl);
            });

            afterEach(function () {
                this.server.restore();
            });

            it('makes a get request with correct url', function () {
                this.directory.fetch();
                expect(this.server.requests.length).toEqual(1);
                expect(this.server.requests[0].method).toBe('GET');
                expect(this.server.requests[0].url).toBe(this.fakeBaseUrl + '/' + this.directoryId);
            });

            it('populates collection with directory resources', function () {
                this.directory.fetch();
                this.server.respond();
                expect(this.directory.length).toBe(2);
            });
        });
    });

});

