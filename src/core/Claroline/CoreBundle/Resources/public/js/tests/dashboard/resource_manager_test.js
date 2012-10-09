describe('backbone', function() {
    beforeEach(function() {
        this.model = new SomeModel({id: 123, name: 'Some name'});
        this.server = sinon.fakeServer.create();
    });

    afterEach(function() {
        this.model = null;
        this.server.restore();
    });

    it('model should have a name', function() {
        expect(this.model.get('name')).toEqual('Some name');
    });

    it('model should fire a callback when "foo" is triggered', function() {
        var spy = sinon.spy();
        this.model.bind('foo', spy);
        this.model.trigger('foo');
        expect(spy.called).toBeTruthy();
    });

    it('model should make the correct server request on save', function() {
        var spy = sinon.spy(jQuery, 'ajax');
        this.model.save();
        expect(spy.called).toBeTruthy();
        expect(spy.getCall(0).args[0].url).toEqual('/some_model/123');
        jQuery.ajax.restore();
    });

    it("should fire the change event on fetch", function() {
        var callback = sinon.spy();
        this.server.respondWith(
            "GET",
            "/some_model/123",
            [
                200,
                {"Content-Type": "application/json"},
                '{"id":123,"name":"New name"}'
            ]
        );
        this.model.set('name', 'Old name');
        this.model.bind('change', callback);
        this.model.fetch();
        this.server.respond();
        expect(callback.called).toBeTruthy();
        expect(callback.getCall(0).args[0].attributes)
          .toEqual({
            id: 123,
            name: 'New name'
          });
      });

});

describe('sinon', function() {
    it('spy know arguments of method', function() {
        var someObject = {someMethod: function() {}};
        var spy = sinon.spy(someObject, 'someMethod');
        someObject.someMethod('foo');
        expect(spy.calledWith('foo')).toEqual(true);
        someObject.someMethod('foo', 'bar', 'baz');
        expect(spy.calledWith('foo', 'bar', 'baz')).toEqual(true);
    });
});